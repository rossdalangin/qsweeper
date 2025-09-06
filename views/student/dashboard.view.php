<h1>Student Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($user['name']) ?>! | <a href="/instructions">How to Play</a></p>

<div class="card">
    <h2>Active Games</h2>
    <?php if (empty($activeGames)): ?>
        <p>No active games at the moment. Please wait for your teacher to start one.</p>
    <?php else: ?>
        <ul class="game-list">
            <?php foreach ($activeGames as $game): ?>
                <li>
                    <span>Game #<?= htmlspecialchars($game['id']) ?> - Started at <?= htmlspecialchars($game['started_at']) ?></span>
                    <a href="/games/<?= $game['id'] ?>/board" class="button">Join Game</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Game History</h2>
    <?php if (empty($pastGames)): ?>
        <p>You haven't played any games yet.</p>
    <?php else: ?>
        <ul class="game-list">
            <?php foreach ($pastGames as $game): ?>
                <li>
                    <span>Game #<?= htmlspecialchars($game['id']) ?> - Finished at <?= htmlspecialchars($game['finished_at']) ?></span>
                    <a href="/games/<?= $game['id'] ?>/results" class="button">View Results</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
