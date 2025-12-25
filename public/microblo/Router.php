<?php
if (!defined('MICROBLO_APP') && !defined('MICROBLO_ADMIN')) { http_response_code(403); exit; }

class Router
{
    private array $queryParams;
    private array $cookies;

    /**
     * Router constructor.
     *
     * @param array $queryParams Usually $_GET.
     * @param array $cookies Usually $_COOKIE.
     */
    public function __construct(array $queryParams, array $cookies)
    {
        $this->queryParams = $queryParams;
        $this->cookies = $cookies;
    }

    /**
     * Determine the preferred language based on URL, Cookie, or Browser headers.
     *
     * @param string $defaultLang Default language fallback.
     * @param array $supportedLangs List of supported language codes.
     * @return string The selected language code.
     */
    public function getLanguage(string $defaultLang, array $supportedLangs = []): string
    {
        $candidate = null;

        if (isset($this->queryParams['lang'])) {
            $candidate = $this->queryParams['lang'];
        } elseif (isset($this->cookies['microblo_lang'])) {
            $candidate = $this->cookies['microblo_lang'];
        }

        if ($candidate) {
            if (empty($supportedLangs) || in_array($candidate, $supportedLangs)) {
                if (isset($this->queryParams['lang']) && !headers_sent()) {
                    setcookie('microblo_lang', $candidate, time() + (86400 * 30), "/");
                }
                return $candidate;
            }
        }

        // Parse Accept-Language header to find best match among supported languages
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $part) {
                $parts = explode(';q=', $part);
                $langCode = trim($parts[0]);
                $primary = substr($langCode, 0, 2);

                if (empty($supportedLangs) || in_array($primary, $supportedLangs)) {
                    return $primary;
                }
            }
        }

        return $defaultLang;
    }

    /**
     * Resolve the current route based on query parameters.
     *
     * @return array Route definition array.
     */
    public function getRoute(): array
    {
        if (!empty($this->queryParams['slug'])) {
            return ['type' => 'post', 'slug' => $this->queryParams['slug']];
        }

        if (!empty($this->queryParams['page'])) {
            return ['type' => 'page', 'slug' => $this->queryParams['page']];
        }

        return ['type' => 'home', 'page_num' => (int)($this->queryParams['p'] ?? 1)];
    }
}
