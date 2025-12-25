<?php
if (!defined('MICROBLO_APP') && !defined('MICROBLO_ADMIN')) { http_response_code(403); exit; }

class Microblo
{
    private Router $router;
    private PostParser $parser;
    private Cache $cache;
    private array $config;
    /** @var array<string, mixed> Global context for templates */
    private array $context = [];
    private string $currentContent = '';
    private array $currentItem = [];
    private array $currentList = [];

    private string $pathContent;
    private string $pathCache;
    private string $pathTemplates;

    /**
     * Microblo constructor.
     *
     * @param array $config Configuration array.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->router = new Router($_GET, $_COOKIE);
        $this->parser = new PostParser();

        $this->pathContent = __DIR__ . '/../content';
        $this->pathCache = __DIR__ . '/../cache';
        $this->pathTemplates = __DIR__ . '/../template';

        $this->cache = new Cache($this->pathCache, $config['cache_ttl'] ?? 3600);

        if (file_exists(__DIR__ . '/helpers.php')) {
            require_once __DIR__ . '/helpers.php';
        }
    }

    /**
     * Main application execution point.
     * Determines route, checks cache, renders content, and manages output buffering.
     */
    public function run(): void
    {
        $route = $this->router->getRoute();
        $lang = $this->router->getLanguage(
            $this->config['lang_default'],
            $this->config['supported_languages'] ?? []
        );

        $cacheKey = $this->generateCacheKey($route, $lang);

        $cachedContent = $this->cache->get($cacheKey);
        if ($cachedContent !== null) {
            echo $cachedContent;
            return;
        }

        ob_start();

        $this->context = [
            'lang' => $lang,
            'route' => $route,
            'site_name' => $this->config['site_name'],
            'config' => $this->config
        ];

        switch ($route['type']) {
            case 'post':
                $this->renderPost($route['slug'], $lang);
                break;
            case 'page':
                $this->renderPage($route['slug'], $lang);
                break;
            case 'home':
            default:
                $this->renderHome($route['page_num'] ?? 1, $lang);
                break;
        }

        $content = ob_get_flush();
        $code = http_response_code();
        if ($content && ($code === 200 || $code === false)) {
            $this->cache->set($cacheKey, $content);
        }
    }

    private function generateCacheKey(array $route, string $lang): string
    {
        $prefix = $route['type'];
        $suffix = $lang;
        $id = match ($route['type']) {
            'post', 'page' => $route['slug'],
            'home' => 'p' . ($route['page_num'] ?? 1),
            default => 'unknown'
        };
        return "{$prefix}_{$id}_{$suffix}";
    }

    private function renderPost(string $slug, string $lang): void
    {
        $file = $this->findContentFile('posts', $slug, $lang);
        if ($file) {
            $post = $this->parser->parse($file);
            $this->currentItem = $post;
            $this->renderTemplate('single', ['post' => $post, 'lang' => $lang]);
        } else {
            $this->render404();
        }
    }

    private function renderPage(string $slug, string $lang): void
    {
        $file = $this->findContentFile('pages', $slug, $lang);
        if ($file) {
            $page = $this->parser->parse($file);
            $this->currentItem = $page;
            $this->renderTemplate('page', ['page' => $page, 'lang' => $lang]);
        } else {
            $this->render404();
        }
    }

    private function renderHome(int $pageNum, string $lang): void
    {
        $limit = 10;
        $posts = $this->getRecentPosts($limit, $pageNum, $lang);
        $total = $this->getTotalPostsCount($lang);
        $totalPages = ceil($total / $limit);

        $this->currentList = $posts;

        $this->renderTemplate('index', [
            'posts' => $posts,
            'page_num' => $pageNum,
            'total_pages' => $totalPages,
            'lang' => $lang
        ]);
    }

    /**
     * Retrieve recent posts with pagination.
     *
     * @param int $limit Posts per page.
     * @param int $page Current page number.
     * @param string $lang Language code.
     * @return array List of posts.
     */
    public function getRecentPosts(int $limit, int $page, string $lang): array
    {
        $cacheKey = "posts_{$limit}_{$page}_{$lang}";
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return unserialize($cached);
        }

        $dir = $this->pathContent . '/posts';
        $pattern = $dir . "/*-$lang.md";
        $files = glob($pattern);
        rsort($files);

        $offset = ($page - 1) * $limit;
        $files = array_slice($files, $offset, $limit);

        $posts = [];
        foreach ($files as $file) {
            $data = $this->parser->parse($file);

            // Filename: yyyy-mm-dd-slug-lang.md
            $basename = basename($file, '.md');
            $suffix = "-$lang";
            $slug = 'unknown';

            if (str_ends_with($basename, $suffix)) {
                $basenameWithoutLang = substr($basename, 0, -strlen($suffix));
                // Explode to get slug (yyyy, mm, dd, slug)
                $parts = explode('-', $basenameWithoutLang, 4);
                if (count($parts) >= 4) {
                    $slug = $parts[3];
                }
            }

            if ($slug === 'unknown') {
                $slug = substr($basename, 0, -strlen($suffix));
            }

            $data['slug'] = $slug;
            $data['description'] = substr(strip_tags($data['content']), 0, 150) . '...';

            $posts[] = $data;
        }

        $this->cache->set($cacheKey, serialize($posts));
        return $posts;
    }

    /**
     * Get total number of posts for a language.
     *
     * @param string $lang Language code.
     * @return int Total posts.
     */
    public function getTotalPostsCount(string $lang): int
    {
        $cacheKey = "count_posts_{$lang}";
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return (int)$cached;
        }

        $dir = $this->pathContent . '/posts';
        $pattern = $dir . "/*-$lang.md";
        $files = glob($pattern);
        $count = count($files);

        $this->cache->set($cacheKey, (string)$count);
        return $count;
    }

    /**
     * Retrieve all pages for the menu.
     *
     * @param string $lang Language code.
     * @return array List of pages.
     */
    public function getPages(string $lang): array
    {
        $cacheKey = "pages_{$lang}";
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return unserialize($cached);
        }

        $dir = $this->pathContent . '/pages';
        $files = glob($dir . "/*-$lang.md");
        $pages = [];
        foreach ($files as $file) {
            $data = $this->parser->parse($file);
            $basename = basename($file, '.md');
            $slug = substr($basename, 0, - (strlen($lang) + 1));

            $pages[] = [
                'slug' => $slug,
                'title' => $data['title'] ?? ucfirst($slug)
            ];
        }

        $this->cache->set($cacheKey, serialize($pages));
        return $pages;
    }

    private function render404(): void
    {
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }

    private function findContentFile(string $type, string $slug, string $lang): ?string
    {
        $dir = $this->pathContent . '/' . $type;
        if (!is_dir($dir)) return null;

        $pattern = ($type === 'posts')
            ? $dir . "/*-$slug-$lang.md"
            : $dir . "/$slug-$lang.md";

        $files = glob($pattern);
        return $files[0] ?? null;
    }

    private function renderTemplate(string $name, array $data = []): void
    {
        $this->context = array_merge($this->context, $data);
        $data = $this->context;
        $data['app'] = $this;
        extract($data);
        $configFile = $this->config;

        $currentLang = $lang ?? $this->config['lang_default'];
        $menuPages = $this->getPages($currentLang);

        ob_start();
        $theme = $this->config['theme_name'] ?? 'terminal';
        $templatePath = $this->pathTemplates . '/' . $theme . '/' . $name . '.php';
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo "Template $name not found.";
        }
        $this->currentContent = ob_get_clean();

        require $this->pathTemplates . '/' . $theme . '/layout.php';
    }

    /**
     * Singleton accessor.
     *
     * @param array $config
     * @return self
     */
    public static function instance(array $config = []): self
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self($config);
        }
        return $instance;
    }

    /**
     * Get a configuration value.
     *
     * @param string $key Config key.
     * @return mixed Config value or null.
     */
    public function getConfig(string $key): mixed
    {
        return $this->config[$key] ?? null;
    }

    /**
     * Get the main content rendered for the current view.
     *
     * @return string HTML content.
     */
    public function getContent(): string
    {
        return $this->currentContent;
    }

    /**
     * Get the current item (post or page) data.
     *
     * @return array
     */
    public function getCurrentItem(): array
    {
        return $this->currentItem;
    }

    /**
     * Get the current list of items (e.g., posts on home page).
     *
     * @return array
     */
    public function getCurrentList(): array
    {
        return $this->currentList;
    }

    /**
     * Get pagination metadata.
     *
     * @return array
     */
    public function getPaginationData(): array
    {
        return [
            'current_page' => $this->context['page_num'] ?? 1,
            'total_pages' => $this->context['total_pages'] ?? 1
        ];
    }

    /**
     * Get the current active language code.
     *
     * @return string
     */
    public function getCurrentLanguage(): string
    {
        return $this->context['lang'] ?? $this->config['lang_default'];
    }

    /**
     * Generate a localized link for swapping languages on the current page.
     *
     * @param string $targetLang
     * @return string URL.
     */
    public function getTranslatedLink(string $targetLang): string
    {
        $route = $this->router->getRoute();

        if ($route['type'] === 'home') {
            return "index.php?lang=$targetLang";
        }

        if ($route['type'] === 'post' || $route['type'] === 'page') {
            $typeDir = ($route['type'] === 'post') ? 'posts' : 'pages';
            $slug = $route['slug'];

            if ($this->findContentFile($typeDir, $slug, $targetLang)) {
                $param = ($route['type'] === 'post') ? 'slug' : 'page';
                return "index.php?$param=$slug&lang=$targetLang";
            }
        }

        return "index.php?lang=$targetLang";
    }
}
