<?php if (!defined('MICROBLO_APP')) { http_response_code(403); exit; } ?>
<!DOCTYPE html>
<html lang="<?= mb_current_language() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= mb_site_name() ?></title>
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

        header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        footer {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
    </style>
</head>

<body>
    <header>
        <h1><?= mb_site_name() ?></h1>
        <p>
            <?php foreach (mb_supported_languages() as $lang): ?>
                <a href="<?= mb_translated_link($lang) ?>"><?= strtoupper($lang) ?></a>
            <?php endforeach; ?>
        </p>
    </header>

    <header>
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

    <footer>
        <p>&copy; <?= date('Y') ?> <?= mb_site_name() ?></p>
        <p>Made with <a href="https://github.com/centamiv/microblo" target="_blank">Microblo</a></p>
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
</body>

</html>