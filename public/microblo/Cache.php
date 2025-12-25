<?php
if (!defined('MICROBLO_APP') && !defined('MICROBLO_ADMIN')) { http_response_code(403); exit; }

class Cache
{
    private string $cacheDir;
    private int $ttl;

    /**
     * Cache constructor.
     *
     * @param string $cacheDir Directory to store cache files.
     * @param int $ttl Time to live in seconds.
     */
    public function __construct(string $cacheDir, int $ttl = 3600)
    {
        $this->cacheDir = $cacheDir;
        $this->ttl = $ttl;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Retrieve a value from the cache.
     *
     * @param string $key The unique cache key.
     * @return string|null The cached content or null if not found or expired.
     */
    public function get(string $key): ?string
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            if (time() - filemtime($file) > $this->ttl) {
                unlink($file);
                return null;
            }
            return file_get_contents($file);
        }
        return null;
    }

    /**
     * Store a value in the cache.
     *
     * @param string $key The unique cache key.
     * @param string $content The content to cache.
     */
    public function set(string $key, string $content): void
    {
        file_put_contents($this->getFilePath($key), $content);
    }

    /**
     * Clear all cached files.
     */
    public function flush(): void
    {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    /**
     * Generate the file path for a given cache key.
     *
     * @param string $key
     * @return string
     */
    private function getFilePath(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
