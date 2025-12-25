<?php
if (!defined('MICROBLO_APP') && !defined('MICROBLO_ADMIN')) { http_response_code(403); exit; }

/**
 * Get the site name from configuration.
 *
 * @return string
 */
function mb_site_name(): string
{
    return Microblo::instance()->getConfig('site_name') ?? '';
}

/**
 * Get the site description from configuration.
 *
 * @return string
 */
function mb_site_description(): string
{
    return Microblo::instance()->getConfig('site_description') ?? '';
}

/**
 * Get external links from configuration.
 *
 * @return array
 */
function mb_external_links(): array
{
    return Microblo::instance()->getConfig('external_links') ?? [];
}

/**
 * Get analytics ID from configuration.
 *
 * @return string
 */
function mb_analytics_id(): string
{
    return Microblo::instance()->getConfig('analytics_id') ?? '';
}

/**
 * Get list of supported languages.
 *
 * @return array
 */
function mb_supported_languages(): array
{
    return Microblo::instance()->getConfig('supported_languages') ?? [];
}

/**
 * Get the main content HTML for the current page.
 *
 * @return string
 */
function mb_content(): string
{
    return Microblo::instance()->getContent();
}

/**
 * Get pages for the navigation menu.
 *
 * @return array
 */
function mb_menu_pages(): array
{
    return Microblo::instance()->getPages(mb_current_language());
}

/**
 * Get the current language code.
 *
 * @return string
 */
function mb_current_language(): string
{
    return Microblo::instance()->getCurrentLanguage();
}

/**
 * Get a link to the current page in a different language.
 *
 * @param string $lang Target language.
 * @return string
 */
function mb_translated_link(string $lang): string
{
    return Microblo::instance()->getTranslatedLink($lang);
}

/**
 * Generate a link.
 *
 * @param string $type Link type (home, post, page).
 * @param string|null $slug Slug for post/page.
 * @return string
 */
function mb_link(string $type, ?string $slug = null): string
{
    return match ($type) {
        'home' => 'index.php',
        'post' => 'index.php?slug=' . urlencode($slug),
        'page' => 'index.php?page=' . urlencode($slug),
        default => '#'
    };
}

/**
 * Get title of a post.
 *
 * @param array|null $post Post data or null for current item.
 * @return string
 */
function mb_post_title(?array $post = null): string
{
    $post = $post ?? Microblo::instance()->getCurrentItem();
    return htmlspecialchars($post['title'] ?? 'Untitled');
}

/**
 * Get date of a post.
 *
 * @param array|null $post Post data or null for current item.
 * @return string
 */
function mb_post_date(?array $post = null): string
{
    $post = $post ?? Microblo::instance()->getCurrentItem();
    return htmlspecialchars($post['date'] ?? 'Unknown');
}

/**
 * Get content (HTML) of a post.
 *
 * @param array|null $post Post data or null for current item.
 * @return string
 */
function mb_post_content(?array $post = null): string
{
    $post = $post ?? Microblo::instance()->getCurrentItem();
    return $post['content'] ?? '';
}

/**
 * Get short description of a post.
 *
 * @param array|null $post Post data or null for current item.
 * @return string
 */
function mb_post_description(?array $post = null): string
{
    $post = $post ?? Microblo::instance()->getCurrentItem();
    return htmlspecialchars($post['description'] ?? '');
}

/**
 * Get slug of a post.
 *
 * @param array|null $post Post data or null for current item.
 * @return string
 */
function mb_post_slug(?array $post = null): string
{
    $post = $post ?? Microblo::instance()->getCurrentItem();
    return basename($post['slug'] ?? '#');
}

/**
 * Get current list of posts (e.g. for homepage loop).
 *
 * @return array
 */
function mb_posts(): array
{
    return Microblo::instance()->getCurrentList();
}

/**
 * Get pagination links.
 *
 * @return array With 'current', 'total', 'prev', 'next'.
 */
function mb_pagination(): array
{
    $data = Microblo::instance()->getPaginationData();
    $current = $data['current_page'];
    $total = $data['total_pages'];
    $lang = Microblo::instance()->getCurrentLanguage();

    $buildUrl = function ($p) use ($lang) {
        return "index.php?p=$p&lang=$lang";
    };

    return [
        'current' => $current,
        'total' => $total,
        'prev' => ($current > 1) ? $buildUrl($current - 1) : null,
        'next' => ($current < $total) ? $buildUrl($current + 1) : null
    ];
}
