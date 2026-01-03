<?php if (!defined('MICROBLO_ADMIN')) {
    http_response_code(403);
    exit;
} ?>
<header style="display: flex; justify-content: space-between; align-items: baseline;">
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="admin.php?action=images" class="btn btn-default">Images</a>
        <a href="admin.php?action=edit&type=posts" class="btn btn-default">New Post</a>
        <a href="admin.php?action=edit&type=pages" class="btn btn-default">New Page</a>
        <a href="admin.php?action=logout" class="btn btn-error">Logout</a>
    </nav>
</header>
<hr>

<h2>Posts</h2>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Slug</th>
            <th>Languages</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td style="white-space: nowrap;"><?= $post['date'] ?? '-' ?></td>
                <td style="white-space: nowrap; width: 100%;"><?= $post['slug'] ?></td>
                <td style="white-space: nowrap;"><?= implode(', ', array_keys($post['files'])) ?></td>
                <td style="white-space: nowrap;">
                    <a href="admin.php?action=edit&type=posts&slug=<?= $post['slug'] ?>">Edit</a>
                    <a href="admin.php?action=delete&type=posts&slug=<?= $post['slug'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Pages</h2>
<table>
    <thead>
        <tr>
            <th>Slug</th>
            <th>Languages</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $page): ?>
            <tr>
                <td style="white-space: nowrap; width: 100%;"><?= $page['slug'] ?></td>
                <td style="white-space: nowrap;"><?= implode(', ', array_keys($page['files'])) ?></td>
                <td style="white-space: nowrap;">
                    <a href="admin.php?action=edit&type=pages&slug=<?= $page['slug'] ?>">Edit</a>
                    <a href="admin.php?action=delete&type=pages&slug=<?= $page['slug'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>