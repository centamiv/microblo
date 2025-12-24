<?php

use PHPUnit\Framework\TestCase;
// global classes

class CachingTest extends TestCase
{
    private string $contentDir;
    private string $cacheDir;
    private string $templatesDir;

    protected function setUp(): void
    {
        $this->contentDir = sys_get_temp_dir() . '/microblo_test_content_' . uniqid();
        $this->cacheDir = sys_get_temp_dir() . '/microblo_test_cache_' . uniqid();
        $this->templatesDir = $this->contentDir . '/templates';

        mkdir($this->contentDir);
        mkdir($this->contentDir . '/posts');
        mkdir($this->contentDir . '/pages');
        mkdir($this->templatesDir);

        // Layout
        file_put_contents($this->templatesDir . '/layout.php', 'LAYOUT START <?= $content ?> LAYOUT END');
        // Index
        file_put_contents($this->templatesDir . '/index.php', 'INDEX PAGE');
    }

    protected function tearDown(): void
    {
        $this->recursiveRmdir($this->contentDir);
        $this->recursiveRmdir($this->cacheDir);
    }

    private function recursiveRmdir($dir)
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->recursiveRmdir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function testRunCachesHomeOutput()
    {
        $config = [
            'site_name' => 'Test',
            'path_content' => $this->contentDir,
            'path_cache' => $this->cacheDir,
            'path_templates' => $this->templatesDir,
            'lang_default' => 'en'
        ];

        // First Run: Should generate and cache
        ob_start();
        $app = new Microblo($config); // New instance
        $app->run();
        $output1 = ob_get_clean();

        $this->assertEquals('LAYOUT START INDEX PAGE LAYOUT END', $output1);

        // Check if cache dir has files
        $files = glob($this->cacheDir . '/*.cache');
        $this->assertNotEmpty($files, 'Cache file should be created');

        // Verify cache content
        $cachedContent = file_get_contents($files[0]);
        $this->assertEquals($output1, $cachedContent);

        // Modify template to prove we are hitting cache
        file_put_contents($this->templatesDir . '/index.php', 'MODIFIED PAGE');

        // Second Run: Should return cached content (original)
        ob_start();
        $app2 = new Microblo($config);
        $app2->run();
        $output2 = ob_get_clean();

        $this->assertEquals($output1, $output2, 'Should return cached content, ignoring template change');
    }
}
