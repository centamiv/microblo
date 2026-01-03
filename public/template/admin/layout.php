<?php if (!defined('MICROBLO_ADMIN')) {
    http_response_code(403);
    exit;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Microblo Admin' ?></title>
    <link rel="stylesheet" href="template/admin/css/terminal.css">
    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>

<body style="<?= $bodyStyle ?? 'padding: 20px; max-width: 1024px; margin: 0 auto;' ?>">
    <?= $content ?>
</body>

</html>