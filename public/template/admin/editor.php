<?php if (!defined('MICROBLO_ADMIN')) {
    http_response_code(403);
    exit;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Editor - Microblo</title>
    <link rel="stylesheet" href="template/admin/css/terminal.css">
    <style>
        .tab {
            display: none;
        }

        .tab.active {
            display: block;
        }

        body {
            padding: 20px;
            max-width: 1024px;
            margin: 0 auto;
        }

        .tab-btn.active {
            color: #1d2021;
            background: var(--accent);
        }

        button {
            width: unset !important;
        }

        .tab-bar {
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .actions {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <header style="display: flex; justify-content: space-between; align-items: baseline;">
        <h1><?= $slug ? 'Edit' : 'New' ?> <?= ucfirst($type) ?></h1>
        <nav>
            <a href="admin.php?action=dashboard" class="btn btn-default">Back to Dashboard</a>
        </nav>
    </header>
    <hr>

    <h2>Content for <mark><?= $slug ?></mark></h2>

    <form action="admin.php?action=save" method="post">
        <input type="hidden" name="type" value="<?= $type ?>">
        <input type="hidden" name="original_slug" value="<?= $slug ?>">
        <input type="hidden" name="original_date" value="<?= $date ?>">

        <fieldset style="width: 100%;">
            <label>
                Slug: <input type="text" name="slug" value="<?= htmlspecialchars($slug ?? '') ?>" required>
            </label>
            <?php if ($type === 'posts'): ?>
                <label>
                    Date: <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
                </label>
            <?php endif; ?>

            <div class="tab-bar">
                <?php foreach ($languages as $i => $lang): ?>
                    <button type="button" id="btn-<?= $lang ?>" class="tab-btn <?= $i === 0 ? 'active' : '' ?>" onclick="openTab('<?= $lang ?>')"><?= strtoupper($lang) ?></button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($languages as $i => $lang): ?>
                <div id="tab-<?= $lang ?>" class="tab <?= $i === 0 ? 'active' : '' ?>">
                    <?php if ($type === 'posts'): ?>
                        <label>
                            <input type="checkbox" name="hidden[<?= $lang ?>]" <?= !empty($hidden[$lang]) ? 'checked' : '' ?>> Hidden (hide from lists)
                        </label>
                    <?php endif; ?>

                    <label>Content Markdown [<?= $lang ?>]:
                        <textarea name="content[<?= $lang ?>]" style="width: 100%; height: 500px; border: 1px solid var(--foreground);"><?= htmlspecialchars($content[$lang]) ?></textarea>
                    </label>

                    <label>
                        Meta Description [<?= $lang ?>]: <input type="text" name="metaDescription[<?= $lang ?>]" value="<?= htmlspecialchars($metaDescription[$lang]) ?>">
                    </label>
                </div>
            <?php endforeach; ?>

        </fieldset>
        <div class="actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="admin.php" class="btn btn-default">Cancel</a>
        </div>
    </form>
    <script>
        function openTab(lang) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + lang).classList.add('active');
            document.getElementById('btn-' + lang).classList.add('active');
        }
    </script>
</body>

</html>