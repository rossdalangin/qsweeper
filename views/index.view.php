<?php require('partials/header.view.php'); ?>

<div style="text-align: center; padding: 40px;">
    <h1>Welcome to Quiz Sweeper!</h1>
    <p>A multiplayer, Minesweeper-style quiz game for the classroom.</p>
    <br>
    <?php if (isset($_SESSION['user'])): ?>
        <a href="/dashboard" style="padding: 10px 20px; background: #333; color: white; text-decoration: none;">Go to Your Dashboard</a>
    <?php else: ?>
        <a href="/login" style="padding: 10px 20px; background: #333; color: white; text-decoration: none;">Login to Get Started</a>
    <?php endif; ?>
</div>

<?php require('partials/footer.view.php'); ?>
