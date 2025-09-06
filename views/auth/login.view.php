<h1>Login</h1>

<?php if (isset($error)): ?>
    <div class="card" style="border-color: var(--danger-color);">
        <p style="color: var(--danger-color); margin: 0;"><?= htmlspecialchars($error) ?></p>
    </div>
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
