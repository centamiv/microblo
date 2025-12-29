<?php if (!defined('MICROBLO_ADMIN')) {
    http_response_code(403);
    exit;
} ?>
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