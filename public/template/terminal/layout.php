<?php if (!defined('MICROBLO_APP')) {
    http_response_code(403);
    exit;
} ?>
<!DOCTYPE html>
<html lang="<?= mb_current_language() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (mb_meta_description()): ?>
        <meta name="description" content="<?= mb_meta_description() ?>">
    <?php endif; ?>
    <?php if (mb_post_title()): ?>
        <title><?= mb_post_title() ?> - <?= mb_site_name() ?></title>
    <?php else: ?>
        <title><?= mb_site_name() ?></title>
    <?php endif; ?>
    <link rel="stylesheet" href="template/terminal/css/terminal.css">
    <style>
        body {
            padding: 20px;
            max-width: 1024px;
            margin: 0 auto;
        }

        hr {
            margin: 20px 0 0 0;
        }

        .space-between {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .space-around {
            display: flex;
            justify-content: space-around;
            align-items: baseline;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.27/dist/katex.min.css"
        integrity="sha384-Pu5+C18nP5dwykLJOhd2U4Xen7rjScHN/qusop27hdd2drI+lL5KvX7YntvT8yew" crossorigin="anonymous">

</head>

<body>
    <header class="space-between">
        <h1><?= mb_site_name() ?></h1>
        <p>
            <?php foreach (mb_supported_languages() as $lang): ?>
                <a href="<?= mb_translated_link($lang) ?>"><?= strtoupper($lang) ?></a>
            <?php endforeach; ?>
        </p>
    </header>

    <header class="space-between">
        <nav>
            <a href="<?= mb_link('home') ?>">Home</a>
            <?php foreach (mb_menu_pages() as $mp): ?>
                <a href="<?= mb_link('page', $mp['slug']) ?>"><?= htmlspecialchars($mp['title']) ?></a>
            <?php endforeach; ?>
        </nav>
        <nav>
            <?php foreach (mb_external_links() as $name => $url): ?>
                <a href="<?= htmlspecialchars($url) ?>" target="_blank"><?= htmlspecialchars($name) ?></a>
            <?php endforeach; ?>
        </nav>
    </header>

    <hr>

    <main>
        <?= mb_content() ?>
    </main>

    <hr>

    <footer class="space-around">
        <p>&copy; <?= date('Y') ?> <?= mb_site_name() ?>
            |
            <a href="<?= mb_link('rss') ?>" target="_blank">RSS</a>
            | Made with
            <a href="https://github.com/centamiv/microblo" target="_blank">Microblo</a>
        </p>
    </footer>

    <?php if ($analytics_id = mb_analytics_id()): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= mb_analytics_id() ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', '<?= mb_analytics_id() ?>');
        </script>
    <?php endif; ?>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.27/dist/katex.min.js"
        integrity="sha384-2B8pfmZZ6JlVoScJm/5hQfNS2TI/6hPqDZInzzPc8oHpN5SgeNOf4LzREO6p5YtZ"
        crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.27/dist/contrib/auto-render.min.js"
        integrity="sha384-hCXGrW6PitJEwbkoStFjeJxv+fSOOQKOPbJxSfM6G5sWZjAyWhXiTIIAmQqnlLlh"
        crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            renderMathInElement(document.body, {
                delimiters: [{
                    left: '$$',
                    right: '$$',
                    display: true
                },
                {
                    left: '$',
                    right: '$',
                    display: false
                },
                ],
                throwOnError: false
            });
        });
    </script>
</body>

</html>