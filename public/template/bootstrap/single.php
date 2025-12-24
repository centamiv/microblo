<div class="row">
    <div class="col-lg-8">
        <article>
            <header class="mb-4">
                <h1 class="fw-bolder mb-1"><!-- Title handled inside content or we assume it's part of the post content rendered by mb_post_content usually? 
                Wait, in terminal/single.php it just echoes mb_post_content(). The parser usually puts the H1 there. 
                Let's stick to wrapping content. -->
                </h1>
                <div class="text-muted fst-italic mb-2">Posted on <?= mb_post_date() ?></div>
            </header>

            <section class="mb-5">
                <?= mb_post_content() ?>
            </section>
        </article>

        <nav class="blog-pagination" aria-label="Pagination">
            <a class="btn btn-outline-primary" href="<?= mb_link('home') ?>">&larr; Back to Home</a>
        </nav>
    </div>
</div>