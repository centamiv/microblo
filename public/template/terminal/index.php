<?php if (!defined('MICROBLO_APP')) {
    http_response_code(403);
    exit;
} ?>
<?php if (mb_site_description()): ?>
    <p>
        <?= mb_site_description() ?>
    </p>
<?php endif; ?>

<h2>Recent Posts</h2>

<?php if (empty(mb_posts())): ?>
    <p>No posts found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Title</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (mb_posts() as $post): ?>
                <tr>
                    <td style="white-space: nowrap;"><?= mb_post_date($post) ?? '-' ?></td>
                    <td style="width: 100%;"><a
                            href="<?= mb_link('post', mb_post_slug($post)) ?>"><?= mb_post_title($post) ?></a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="space-between" style="margin-top: 20px;">
        <?php $paging = mb_pagination(); ?>
        <div>
            <?php if ($paging['prev']): ?>
                <a href="<?= $paging['prev'] ?>">&larr; Previous</a>
            <?php endif; ?>
        </div>

        <span>Page <?= $paging['current'] ?> of <?= $paging['total'] ?></span>

        <div>
            <?php if ($paging['next']): ?>
                <a href="<?= $paging['next'] ?>">Next &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>