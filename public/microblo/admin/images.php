<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Images - Microblo</title>
    <link rel="stylesheet" href="microblo/admin/css/terminal.css">
    <style>
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .image-item {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        .image-item img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 10px;
            max-height: 100px;
            object-fit: cover;
        }

        .image-actions {
            display: flex;
            justify-content: space-between;
            font-size: 0.8em;
            align-items: baseline;
        }
    </style>
</head>

<body style="padding: 20px; max-width: 1024px; margin: 0 auto;">
    <header style="display: flex; justify-content: space-between; align-items: baseline;">
        <h1>Image Management</h1>
        <nav>
            <a href="admin.php?action=dashboard" class="btn btn-default">Back to Dashboard</a>
        </nav>
    </header>
    <hr>
    <h2>Upload new image</h2>
    <form action="admin.php?action=upload_image" method="post" enctype="multipart/form-data">
        <fieldset>
            <input type="file" name="image" required accept="image/*">
            <button type="submit" class="btn btn-primary">Upload</button>
        </fieldset>
    </form>

    <h2>Existing images</h2>
    <div class="image-grid">
        <?php foreach ($images as $img): ?>
            <div class="image-item">
                <img src="<?= htmlspecialchars($img['url']) ?>" alt="<?= htmlspecialchars($img['name']) ?>">
                <div style="word-break: break-all; font-size: 0.8em; margin-bottom: 5px;"><?= htmlspecialchars($img['name']) ?></div>
                <div class="image-actions">
                    <button onclick="copyUrl('<?= htmlspecialchars($img['url']) ?>')" class="btn btn-small">Copy URL</button>
                    <a href="admin.php?action=delete_image&name=<?= urlencode($img['name']) ?>" onclick="return confirm('Delete this image?')" style="color: red;">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function copyUrl(url) {
            if (navigator.clipboard && window.isSecureContext) {
                // Navigator clipboard api method'
                return navigator.clipboard.writeText(url).then(() => {
                    alert('URL copied to clipboard!');
                }, (err) => {
                    fallbackCopyTextToClipboard(url);
                });
            } else {
                // Fallback
                fallbackCopyTextToClipboard(url);
            }
        }

        function fallbackCopyTextToClipboard(text) {
            var textArea = document.createElement("textarea");
            textArea.value = text;

            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                alert('URL copied to clipboard!');
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
            }

            document.body.removeChild(textArea);
        }
    </script>
</body>

</html>