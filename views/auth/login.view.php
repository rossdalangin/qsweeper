<?php require __DIR__ . '/../partials/header.view.php'; ?>

<h1>Login</h1>

<?php if (isset($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/login" method="POST">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
    </div>

    <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>

    <button type="submit">Login</button>
</form>

<?php require __DIR__ . '/../partials/footer.view.php'; ?>
