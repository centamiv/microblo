<?php if (!defined('MICROBLO_APP')) {
    http_response_code(403);
    exit;
} ?>
<article class="post-single">
    <?= mb_post_content() ?>
</article>

<footer class="space-between">
    <p><a href="<?= mb_link('home') ?>">&larr; Back to Home</a></p>
    <p><?= mb_post_date() ?></p>
</footer>