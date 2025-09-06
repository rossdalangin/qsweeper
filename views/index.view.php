<div style="text-align: center; padding: 40px;">
    <h1>Welcome to Quiz Sweeper!</h1>
    <p>A multiplayer, Minesweeper-style quiz game for the classroom.</p>
    <br>
    <?php if (isset($_SESSION['user'])): ?>
        <a href="/dashboard" class="button">Go to Your Dashboard</a>
    <?php else: ?>
        <a href="/login" class="button">Login to Get Started</a>
    <?php endif; ?>
</div>
