<?php

use PHPUnit\Framework\TestCase;

define('MICROBLO_APP', true);

// Classes are now global


class MicrobloTest extends TestCase
{

    // --- Router Tests ---
    public function testRouterExtractsSlug()
    {
        $router = new Router(['slug' => 'test-post'], []);
        $route = $router->getRoute();
        $this->assertEquals('post', $route['type']);
        $this->assertEquals('test-post', $route['slug']);
    }

    public function testRouterDefaultsToHome()
    {
        $router = new Router([], []);
        $route = $router->getRoute();
        $this->assertEquals('home', $route['type']);
        $this->assertEquals(1, $route['page_num']);
    }

    public function testLanguageSelection()
    {
        // defaults
        $router = new Router([], []);
        $this->assertEquals('en', $router->getLanguage('en'));

        // param
        $router = new Router(['lang' => 'it'], []);
        $this->assertEquals('it', $router->getLanguage('en'));

        // cookie
        $router = new Router([], ['microblo_lang' => 'fr']);
        $this->assertEquals('fr', $router->getLanguage('en'));
    }

    public function testLanguageFallbackIfUnsupported()
    {
        // Param is unsupported
        $router = new Router(['lang' => 'de'], []);
        // Validation with supported langs
        $this->assertEquals('en', $router->getLanguage('en', ['en', 'it']));

        // Cookie is unsupported
        $router = new Router([], ['microblo_lang' => 'de']);
        $this->assertEquals('en', $router->getLanguage('en', ['en', 'it']));

        // Param is supported
        $router = new Router(['lang' => 'it'], []);
        $this->assertEquals('it', $router->getLanguage('en', ['en', 'it']));
    }

    // --- PostParser Tests ---
    // --- PostParser Tests ---
    public function testParserExtractsTitleFromFrontmatter()
    {
        $parser = new PostParser();
        $file = sys_get_temp_dir() . '/test_fm.md';
        $content = "---\ntitle: Foo\n---\nBar Body";
        file_put_contents($file, $content);

        $result = $parser->parse($file);

        // FM title support removed by user, expect fallback to filename (Test_fm)
        $this->assertEquals('Test_fm', $result['title']);
        $this->assertEquals('<p>Bar Body</p>', trim($result['content']));

        unlink($file);
    }

    public function testParserExtractsTitleFromH1()
    {
        $parser = new PostParser();
        $file = sys_get_temp_dir() . '/test_h1.md';
        $content = "Some intro.\n# My H1 Title\nMore text.";
        file_put_contents($file, $content);

        $result = $parser->parse($file);

        $this->assertEquals('My H1 Title', $result['title']);
        // Verify H1 is present or acceptable in content (current impl keeps it)
        // ParsedownExtended/Parsedown adds IDs to headers
        $this->assertStringContainsString('My H1 Title</h1>', $result['content']);

        unlink($file);
    }

    public function testParserExtractsTitleFromSlug()
    {
        $parser = new PostParser();
        // Filename format: yyyy-mm-dd-my-slug-en.md
        $file = sys_get_temp_dir() . '/2023-01-01-my-cool-post-en.md';
        $content = "Just text.";
        file_put_contents($file, $content);

        $result = $parser->parse($file);

        $this->assertEquals('My cool post', $result['title']);
        $this->assertEquals('2023-01-01', $result['date']);

        unlink($file);
    }

    // --- Cache Tests ---
    public function testCacheWritesAndReads()
    {
        $dir = sys_get_temp_dir() . '/microblo_cache';
        if (is_dir($dir)) array_map('unlink', glob($dir . '/*')); // Clean start

        $cache = new Cache($dir);
        $cache->set('foo', 'bar');

        $this->assertEquals('bar', $cache->get('foo'));

        // Cleanup
        $cache->flush();
        rmdir($dir);
    }

    // --- Microblo Helper Tests ---
    public function testTranslatedLinkGeneration()
    {
        $config = require __DIR__ . '/../public/config.php';
        // Mock content path to temp dir
        $tempDir = sys_get_temp_dir() . '/microblo_test_content';
        if (!is_dir($tempDir)) mkdir($tempDir);
        if (!is_dir($tempDir . '/posts')) mkdir($tempDir . '/posts');

        $config['path_content'] = $tempDir;

        // Create dummy post in EN only
        touch($tempDir . '/posts/2023-01-01-test-post-en.md');
        // Create dummy post in IT and EN
        touch($tempDir . '/posts/2023-01-01-dual-post-en.md');
        touch($tempDir . '/posts/2023-01-01-dual-post-it.md');

        // Test 1: Home fallback for single language post
        $_GET = ['slug' => 'test-post', 'lang' => 'en'];
        $app = new Microblo($config);
        // Force router state simply by init? Router reads globals. 
        // We need to re-instantiate or mock router, but Microblo inits router in constructor with globals.
        // So resetting globals works.

        $link = $app->getTranslatedLink('it');
        // valid 'it' file doesn't exist for test-post -> fallback home
        $this->assertEquals('index.php?lang=it', $link);

        // Test 2: Context preserved for dual language post
        $_GET = ['slug' => 'dual-post', 'lang' => 'en'];
        $app = new Microblo($config);

        $link = $app->getTranslatedLink('it');
        // valid 'it' file exists -> preserve slug
        $this->assertEquals('index.php?slug=dual-post&lang=it', $link);

        // Cleanup
        array_map('unlink', glob($tempDir . '/posts/*'));
        rmdir($tempDir . '/posts');
        rmdir($tempDir);
    }

    public function testPaginationDataIsAccessibleInContext()
    {
        $config = require __DIR__ . '/../public/config.php';
        // Mock content path
        $tempDir = sys_get_temp_dir() . '/microblo_test_pagination';
        if (!is_dir($tempDir)) mkdir($tempDir);
        if (!is_dir($tempDir . '/posts')) mkdir($tempDir . '/posts');

        $config['path_content'] = $tempDir;
        $config['path_templates'] = $tempDir . '/templates'; // Mock templates dir
        $config['theme_name'] = 'terminal';

        if (!is_dir($config['path_templates'])) mkdir($config['path_templates']);
        if (!is_dir($config['path_templates'] . '/terminal')) mkdir($config['path_templates'] . '/terminal');

        // Create simplistic index template that dumps pagination data using helper
        // We can't easily mock the helper function call inside the template without loading helpers.php
        // But helpers.php is loaded in Microblo constructor if it exists. 
        // Let's assume helpers.php is loaded (it is in the app).
        // We'll create a template that uses $app->getPaginationData() or just checks if context has it.
        // The fix was updating $this->context. So we can check $app->getPaginationData() inside the template execution?
        // Actually, the fix updates $this->context. valid check matches renderTemplate logic.

        // Let's just create enough posts for 2 pages (limit 10).
        for ($i = 1; $i <= 15; $i++) {
            $num = str_pad($i, 2, '0', STR_PAD_LEFT);
            touch($tempDir . "/posts/2023-01-{$num}-post-{$i}-en.md");
        }

        // Mock layout and index
        file_put_contents($config['path_templates'] . '/terminal/layout.php', '<?php echo $app->getContent(); ?>');
        file_put_contents($config['path_templates'] . '/terminal/index.php', '<?php 
            $p = $app->getPaginationData(); 
            echo "Page " . $p["current_page"] . " of " . $p["total_pages"];
        ?>');

        // Capture output
        $_GET = ['lang' => 'en']; // defaults
        $app = new Microblo($config);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $this->assertEquals('Page 1 of 2', $output);

        // Cleanup
        array_map('unlink', glob($tempDir . '/posts/*'));
        array_map('unlink', glob($config['path_templates'] . '/terminal/*'));
        rmdir($config['path_templates'] . '/terminal');
        array_map('unlink', glob($config['path_templates'] . '/*'));
        rmdir($tempDir . '/posts');
        rmdir($config['path_templates']);
        rmdir($tempDir);
    }

    public function testParserExtractsHiddenAndDate()
    {
        $parser = new PostParser();
        $file = sys_get_temp_dir() . '/test_hidden.md';
        $content = "---\ntitle: Hidden Post\nhidden: true\ndate: 2025-01-01\n---\nSecret Content";
        file_put_contents($file, $content);

        $result = $parser->parse($file);

        $this->assertTrue($result['hidden']);
        // $this->assertEquals('2025-01-01', $result['date']); // FM date disabled by user

        unlink($file);
    }

    public function testGetRecentPostsFiltersHiddenAndFuture()
    {
        $config = require __DIR__ . '/../public/config.php';
        $tempDir = sys_get_temp_dir() . '/microblo_test_filtering';
        $cacheDir = sys_get_temp_dir() . '/microblo_test_cache';

        // Ensure clean start
        if (is_dir($tempDir)) {
            array_map('unlink', glob($tempDir . '/posts/*'));
            if (is_dir($tempDir . '/posts')) rmdir($tempDir . '/posts');
            rmdir($tempDir);
        }
        if (is_dir($cacheDir)) {
            array_map('unlink', glob($cacheDir . '/*'));
            rmdir($cacheDir);
        }

        if (!is_dir($tempDir)) mkdir($tempDir);
        if (!is_dir($tempDir . '/posts')) mkdir($tempDir . '/posts');
        if (!is_dir($cacheDir)) mkdir($cacheDir);

        $config['path_content'] = $tempDir;
        $config['path_cache'] = $cacheDir;
        $config['cache_ttl'] = 0; // Disable cache for test

        // 1. Normal post (Visible)
        $file1 = $tempDir . '/posts/2023-01-01-normal-en.md';
        file_put_contents($file1, "---\ntitle: Normal\n---\nContent");

        // 2. Hidden post (Hidden)
        $file2 = $tempDir . '/posts/2023-01-02-hidden-en.md';
        file_put_contents($file2, "---\ntitle: Hidden\nhidden: true\n---\nContent");

        // 3. Future post (Hidden)
        $futureDate = date('Y-m-d', strtotime('+1 day'));
        $file3 = $tempDir . "/posts/{$futureDate}-future-en.md";
        // Note: filename date is future, parser should pick it up if not in frontmatter
        file_put_contents($file3, "---\ntitle: Future\n---\nContent");

        // 4. Future via Frontmatter (Hidden) - REMOVED as User disabled FM date
        // $file4 = $tempDir . '/posts/2023-01-03-future-fm-en.md';
        // $futureDate2 = date('Y-m-d', strtotime('+2 days'));
        // file_put_contents($file4, "---\ntitle: FutureFM\ndate: $futureDate2\n---\nContent");

        $app = new Microblo($config);
        $posts = $app->getRecentPosts(10, 1, 'en');

        // Only "Normal" should be returned
        $this->assertCount(1, $posts);
        $this->assertEquals('normal', $posts[0]['slug']);

        // Cleanup
        array_map('unlink', glob($tempDir . '/posts/*'));
        rmdir($tempDir . '/posts');
        rmdir($tempDir);
        array_map('unlink', glob($cacheDir . '/*'));
        rmdir($cacheDir);
    }
}
