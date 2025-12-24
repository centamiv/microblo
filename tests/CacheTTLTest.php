<?php

use PHPUnit\Framework\TestCase;
// global classes

class CacheTTLTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/microblo_ttl_test_' . uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->cacheDir)) {
            $files = glob($this->cacheDir . '/*');
            array_map('unlink', $files);
            rmdir($this->cacheDir);
        }
    }

    public function testCacheExpiresAfterTTL()
    {
        // Set TTL to 1 second
        $cache = new Cache($this->cacheDir, 1);

        $key = 'test_key';
        $content = 'test_content';

        // Set cache
        $cache->set($key, $content);

        // Assert hit immediately
        $this->assertEquals($content, $cache->get($key), 'Cache should hit immediately');

        // Manually age the file to simulated expired state (older than 1 sec)
        // filemtime + ttl < time() -> expired
        // So filemtime needs to be < time() - ttl
        // Let's set it to 2 seconds ago
        $file = $this->cacheDir . '/' . md5($key) . '.cache';
        touch($file, time() - 2);

        // Assert miss
        clearstatcache(); // Important!
        $this->assertNull($cache->get($key), 'Cache should miss after expiration');

        // Assert file deleted
        $this->assertFileDoesNotExist($file, 'Expired cache file should be deleted');
    }
}
