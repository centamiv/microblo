<?php if (!defined('MICROBLO_APP')) { http_response_code(403); exit; } ?>
<article class="page-single">
    <?= mb_post_content() ?>
</article>

<footer>
    <p><a href="<?= mb_link('home') ?>">&larr; Back to Home</a></p>
</footer>