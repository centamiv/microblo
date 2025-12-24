<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login - Microblo</title>
    <link rel="stylesheet" href="microblo/admin/css/terminal.css">
</head>

<body style="padding: 50px; max-width: 400px; margin: 0 auto;">
    <h2>Login</h2>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <fieldset>
            <input type="text" name="user" placeholder="Username" required>
            <input type="password" name="pass" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </fieldset>
    </form>
</body>

</html>