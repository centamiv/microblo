<?php
if (!defined('MICROBLO_ADMIN')) { http_response_code(403); exit; }

class AdminController
{
    private array $config;
    private string $pathContent;
    private string $pathCache;

    /**
     * AdminController constructor.
     *
     * @param array $config Configuration array.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pathContent = __DIR__ . '/../content';
        $this->pathCache = __DIR__ . '/../cache';
    }

    /**
     * Handle incoming admin requests.
     * Routes to specific actions or shows login.
     */
    public function handleRequest(): void
    {
        if (!$this->isAuthenticated()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
                $this->processLogin();
            } else {
                $this->renderView('login');
            }
            return;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            session_destroy();
            header('Location: admin.php');
            exit;
        }

        $action = $_GET['action'] ?? 'dashboard';

        switch ($action) {
            case 'edit':
                $this->edit();
                break;
            case 'save':
                $this->save();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'images':
                $this->images();
                break;
            case 'upload_image':
                $this->uploadImage();
                break;
            case 'delete_image':
                $this->deleteImage();
                break;
            case 'dashboard':
            default:
                $this->dashboard();
                break;
        }
    }

    private function isAuthenticated(): bool
    {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    private function processLogin(): void
    {
        $user = $_POST['user'] ?? '';
        $pass = $_POST['pass'] ?? '';

        if (($user === $this->config['admin_user']) && ($pass === $this->config['admin_pass'])) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        }

        $this->renderView('login', ['error' => 'Invalid credentials']);
    }

    private function dashboard(): void
    {
        $posts = $this->getGroupedContent('posts');
        $pages = $this->getGroupedContent('pages');
        $this->renderView('dashboard', ['posts' => $posts, 'pages' => $pages]);
    }

    private function edit(): void
    {
        $type = $_GET['type'] ?? 'posts';
        $slug = $_GET['slug'] ?? null;
        $date = date('Y-m-d');
        $content = [];

        $languages = $this->config['supported_languages'] ?? ['en'];

        foreach ($languages as $lang) {
            $content[$lang] = '';

            if ($slug) {
                $file = $this->findFile($type, $slug, $lang);
                if ($file && file_exists($file)) {
                    $parsed = (new PostParser)->parse($file);

                    $content[$lang] = $parsed['markdown'];
                    $date = $parsed['date'];
                }
            }
        }

        $this->renderView('editor', [
            'type' => $type,
            'slug' => $slug,
            'date' => $date,
            'content' => $content,
            'languages' => $languages
        ]);
    }

    private function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $type = $_POST['type'] ?? 'posts';
        $slug = $_POST['slug'] ?? '';
        $originalSlug = $_POST['original_slug'] ?? '';
        $originalDate = $_POST['original_date'] ?? '';
        $date = $_POST["date"] ?? date('Y-m-d');

        $slug = preg_replace('/[^a-z0-9-]/', '-', strtolower(trim($slug)));
        if (empty($slug)) {
            die("Slug required");
        }

        $languages = $this->config['supported_languages'] ?? ['en'];

        $dir = $this->pathContent . "/$type";
        if (!is_dir($dir)) mkdir($dir, 0777, true);


        foreach ($languages as $lang) {

            if ($type === 'posts') {
                $filename = "$dir/$originalDate-$originalSlug-$lang.md";
                if (file_exists($filename)) unlink($filename);
                $filename = "$dir/$date-$slug-$lang.md";
            } else {
                $filename = "$dir/$originalSlug-$lang.md";
                if (file_exists($filename)) unlink($filename);
                $filename = "$dir/$slug-$lang.md";
            }


            $content = $_POST["content"][$lang] ?? '';

            if (empty($content)) {
                continue;
            };

            file_put_contents($filename, $content);
        }

        $cache = new Cache($this->pathCache);
        $cache->flush();

        header("Location: admin.php");
        exit;
    }

    private function delete(): void
    {
        $type = $_GET['type'] ?? null;
        $slug = $_GET['slug'] ?? null;

        if ($type && $slug) {
            $languages = $this->config['supported_languages'] ?? ['en'];
            foreach ($languages as $lang) {
                $file = $this->findFile($type, $slug, $lang);
                if ($file && file_exists($file)) {
                    unlink($file);
                }
            }

            $cache = new Cache($this->pathCache);
            $cache->flush();
        }
        header("Location: admin.php");
        exit;
    }

    private function findFile($type, $slug, $lang): ?string
    {
        $dir = $this->pathContent . "/$type";
        if ($type === 'posts') {
            $pattern = $dir . "/*-$slug-$lang.md";
            $files = glob($pattern);
            return $files[0] ?? null;
        } else {
            return "$dir/$slug-$lang.md";
        }
    }

    private function getGroupedContent(string $type): array
    {
        $dir = $this->pathContent . "/$type";
        $files = glob($dir . "/*.md");
        $items = [];

        foreach ($files as $file) {
            $basename = basename($file, '.md');
            $parts = explode('-', $basename);
            $lang = end($parts);

            // Attempt to extract slug by stripping language
            if (in_array($lang, $this->config['supported_languages'] ?? [])) {
                $base = substr($basename, 0, -strlen("-{$lang}"));

                if ($type === 'posts') {
                    // Format: yyyy-mm-dd-slug
                    $dateParts = explode('-', $base, 4);
                    if (count($dateParts) >= 4) {
                        $slug = $dateParts[3];
                        $date = "{$dateParts[0]}-{$dateParts[1]}-{$dateParts[2]}";
                    } else {
                        $slug = $base;
                        $date = '';
                    }
                } else {
                    $slug = $base;
                    $date = '';
                }
            } else {
                $slug = $basename;
                $lang = '??';
            }

            $items[$slug]['slug'] = $slug;
            if (!empty($date)) $items[$slug]['date'] = $date;
            $items[$slug]['files'][$lang] = true;
        }
        return $items;
    }

    private function images(): void
    {
        $dir = $this->pathContent . '/images';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $files = glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $images = [];
        foreach ($files as $file) {
            $images[] = [
                'name' => basename($file),
                'url' => 'content/images/' . basename($file)
            ];
        }

        $this->renderView('images', ['images' => $images]);
    }

    private function uploadImage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $dir = $this->pathContent . '/images';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $name = basename($_FILES['image']['name']);
            $name = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $name);

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                die("Invalid file type");
            }

            move_uploaded_file($tmpName, "$dir/$name");
        }

        header('Location: admin.php?action=images');
        exit;
    }

    private function deleteImage(): void
    {
        $name = $_GET['name'] ?? null;
        if ($name) {
            // Security: Use basename to prevent traversal
            $name = basename($name);
            $file = $this->pathContent . '/images/' . $name;
            if (file_exists($file)) {
                unlink($file);
            }
        }
        header('Location: admin.php?action=images');
        exit;
    }

    private function renderView(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . "/../template/admin/$view.php";
    }
}
