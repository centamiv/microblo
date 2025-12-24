<?php

use PHPUnit\Framework\TestCase;
// global classes

class VersionFooterTest extends TestCase
{
    private string $contentDir;
    private string $templatesDir;
    private string $publicDir;

    protected function setUp(): void
    {
        $this->contentDir = sys_get_temp_dir() . '/microblo_ver_test_' . uniqid();
        $this->templatesDir = $this->contentDir . '/templates';
        $this->publicDir = dirname(__DIR__) . '/public'; // Actual public dir where Microblo.php lives

        mkdir($this->contentDir);
        mkdir($this->templatesDir);

        // Mock layout to just echo version
        file_put_contents($this->templatesDir . '/layout.php', 'VERSION: <?= $version ?>');
        // Mock index
        file_put_contents($this->templatesDir . '/index.php', 'INDEX');
    }

    protected function tearDown(): void
    {
        $this->recursiveRmdir($this->contentDir);
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

    public function testFooterShowsVersion()
    {
        $config = [
            'site_name' => 'Test',
            'path_content' => $this->contentDir,
            'path_cache' => sys_get_temp_dir(),
            'path_templates' => $this->templatesDir,
            'lang_default' => 'en'
        ];

        // Ensure version.php exists for the test or expect 'dev'
        // Since we are using the REAL Microblo.php, it looks in real public/version.php
        // We know we generated it as 1.0.0

        ob_start();
        $app = new Microblo($config);
        $app->run();
        $output = ob_get_clean();

        // We expect "VERSION: 1.0.0" (assuming version.php exists and has 1.0.0)
        // Or "VERSION: dev" if not found

        // Let's actually check what's in version.php
        $versionFile = $this->publicDir . '/version.php';
        $expectedVersion = 'dev';
        if (file_exists($versionFile)) {
            $expectedVersion = require $versionFile;
        }

        $this->assertEquals("VERSION: $expectedVersion", $output);
    }
}
