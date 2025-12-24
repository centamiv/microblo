<div class="row">
    <div class="col-lg-8">
        <?php if (mb_site_description()): ?>
            <div class="alert alert-secondary" role="alert">
                <?= mb_site_description() ?>
            </div>
        <?php endif; ?>

        <h2 class="my-4">Recent Posts</h2>

        <?php if (empty(mb_posts())): ?>
            <div class="alert alert-info">No posts found.</div>
        <?php else: ?>
            <?php foreach (mb_posts() as $post): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title"><?= mb_post_title($post) ?></h2>
                        <div class="card-text text-truncate" style="max-height: 100px; overflow: hidden;">
                            <!-- Only showing title effectively, could strip tags for a summary if mb_post_content was available plainly but we usually output full HTML. For a list we might want just a link. -->
                            <!-- Reverting to a simpler list style might be cleaner if we don't have excerpts, but cards are requested for bootstrap style usually. Let's just link the title. -->
                            Click to read more...
                        </div>
                        <a href="<?= mb_link('post', mb_post_slug($post)) ?>" class="btn btn-primary mt-2">Read More &rarr;</a>
                    </div>
                    <div class="card-footer text-muted">
                        Posted on <?= mb_post_date($post) ?? '-' ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php $paging = mb_pagination(); ?>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center mb-4">
                    <li class="page-item <?= $paging['prev'] ? '' : 'disabled' ?>">
                        <a class="page-link" href="<?= $paging['prev'] ?: '#' ?>">&larr; Older</a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Page <?= $paging['current'] ?> of <?= $paging['total'] ?></span>
                    </li>
                    <li class="page-item <?= $paging['next'] ? '' : 'disabled' ?>">
                        <a class="page-link" href="<?= $paging['next'] ?: '#' ?>">Newer &rarr;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Sidebar Widgets Column -->
    <div class="col-lg-4">
        <!-- Search Widget -->
        <div class="card mb-4">
            <div class="card-header">Search</div>
            <div class="card-body">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Enter search term..." disabled title="Not implemented yet">
                    <button class="btn btn-secondary" id="button-search" type="button" disabled>Go!</button>
                </div>
            </div>
        </div>
    </div>
</div>