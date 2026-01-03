# Microblo

Microblo is a "old school" blog engine, designed to be extremely fast, robust, and simple. It uses **flat files** (Markdown) instead of a database and relies on classic query strings for URLs, meaning it works on virtually any PHP hosting without complex configuration.

## Installation & Deployment

Microblo is designed for "copy & paste" deployment. **The application resides entirely within the `public/` directory.**

### 1. Upload Files
Copy the **contents of the `public/` folder** from this repository to your web server's public root (e.g., `public_html`).

> **Zero Config:** You do not need `.htaccess`, rewrite rules, or database setup. It just works.

### 2. Set Permissions
Ensure your web server has **write permissions** for the following directories:

*   `content/` (recursive) - for posts, pages, and uploads.
*   `cache/` - for performance caching.

## Configuration

Rename `config.default.php` to `config.php` and edit it to customize your site.

## Usage

### Admin Panel (Optional)
If you prefer a UI, navigate to `/admin.php` to log in. You can upload images and write posts from there. 

> **security tip**: leave `admin_user` or `admin_pass` empty in `config.php` to completely disable the admin interface. 

### Performance
Because Microblo uses standard PHP and file caching, it allows for high traffic with minimal resources. The "Old School" approach means less overhead and fewer things that can break.

### RSS Feed
Microblo generates an automatic RSS feed for your posts.
*   **Default Feed:** `index.php?rss`
*   **Language Specific:** `index.php?rss&lang=it`

## Themes

*   **Terminal**: A retro, hacker-style theme (default).
*   **Bootstrap**: A clean, standard blog layout.

To switch, change `'theme_name'` in `config.php`.

## Manual Content Creation

Microblo is built for hackers and writers who love control. You can manage everything by simply creating text files via FTP or SSH.

> **Important**: The first `# h1` tag in your Markdown file will be used as the **title** of the post or page.

### Creating a Post
Create a file in `content/posts/`. The filename **must** follow this format:
`YYYY-MM-DD-slug-lang.md`

**Example:** `content/posts/2025-01-30-my-first-post-en.md`

**File Content:**
```markdown
# Used to be, web was simple.

This is a paragraph in standard **Markdown**. 
No database queries, just pure text processing.
```

The URL will be: `index.php?slug=my-first-post`

### Creating a Page
Create a file in `content/pages/`. The filename format is:
`slug-lang.md`

**Example:** `content/pages/about-me-en.md`

**File Content:**
```markdown
# About Me

I am a developer who loves simple tools.
```
The URL will be: `index.php?page=about-me`


## Contributing

Contributions are welcome! If you have ideas for improvements or find any issues, please feel free to open a pull request or submit an issue.

## License

Microblo is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
