<?php

use PHPUnit\Framework\TestCase;
// global classes

class MigrationTest extends TestCase
{
    private string $contentDir;

    protected function setUp(): void
    {
        $this->contentDir = sys_get_temp_dir() . '/microblo_test_content_' . uniqid();
        mkdir($this->contentDir);
        mkdir($this->contentDir . '/posts');
        mkdir($this->contentDir . '/pages');
        mkdir($this->contentDir . '/templates'); // Mock templates dir to avoid errors if checked

        // Mock layout
        file_put_contents($this->contentDir . '/templates/layout.php', '<?= $content ?>');
    }

    protected function tearDown(): void
    {
        $this->recursiveRmdir($this->contentDir);
    }

    private function recursiveRmdir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->recursiveRmdir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function testGetRecentPostsWithNewFormat()
    {
        // Create 2023-10-21-hello-en.md
        file_put_contents($this->contentDir . '/posts/2023-10-21-hello-en.md', "---\ntitle: Hello\n---\nContent");

        // Create 2023-10-22-world-it.md
        file_put_contents($this->contentDir . '/posts/2023-10-22-world-it.md', "---\ntitle: Mondo\n---\nContenuto");

        $config = [
            'site_name' => 'Test',
            'path_content' => $this->contentDir,
            'path_cache' => sys_get_temp_dir(),
            'path_templates' => $this->contentDir . '/templates', // added
            'lang_default' => 'en'
        ];

        $microblo = new Microblo($config);

        // Check English
        $postsEn = $microblo->getRecentPosts(10, 1, 'en');
        $this->assertCount(1, $postsEn);
        $this->assertEquals('hello', $postsEn[0]['meta']['slug']);

        // Check Italian
        $postsIt = $microblo->getRecentPosts(10, 1, 'it');
        $this->assertCount(1, $postsIt);
        $this->assertEquals('world', $postsIt[0]['meta']['slug']);
    }

    public function testGetPagesWithNewFormat()
    {
        // Create about-en.md
        file_put_contents($this->contentDir . '/pages/about-en.md', "---\ntitle: About\n---\nMe");

        // Create contact-it.md
        file_put_contents($this->contentDir . '/pages/contact-it.md', "---\ntitle: Contatti\n---\nMe");

        $config = [
            'site_name' => 'Test',
            'path_content' => $this->contentDir,
            'path_cache' => sys_get_temp_dir(),
            'path_templates' => $this->contentDir . '/templates', // added
            'lang_default' => 'en'
        ];

        $microblo = new Microblo($config);

        $pagesEn = $microblo->getPages('en');
        $this->assertCount(1, $pagesEn);
        $this->assertEquals('about', $pagesEn[0]['slug']);

        $pagesIt = $microblo->getPages('it');
        $this->assertCount(1, $pagesIt);
        $this->assertEquals('contact', $pagesIt[0]['slug']);
    }
}
