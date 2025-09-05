<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        .game-list { list-style: none; padding: 0; }
        .game-list li { background: #f4f4f4; margin: 5px 0; padding: 10px; }
    </style>
</head>
<body>
    <header>
        <h1>Student Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($user['name']) ?>!</p>
        <form action="/logout" method="POST" style="display:inline;">
            <button type="submit">Logout</button>
        </form>
    </header>

    <main>
        <h2>Active Games</h2>
        <?php if (empty($activeGames)): ?>
            <p>No active games at the moment. Please wait for your teacher to start one.</p>
        <?php else: ?>
            <ul class="game-list">
                <?php foreach ($activeGames as $game): ?>
                    <li>
                        Game #<?= htmlspecialchars($game['id']) ?> - Started at <?= htmlspecialchars($game['started_at']) ?>
                        <a href="/games/<?= $game['id'] ?>/board" style="margin-left: 20px;">Join Game</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <hr>

        <h2>Game History</h2>
        <?php if (empty($pastGames)): ?>
            <p>You haven't played any games yet.</p>
        <?php else: ?>
            <ul class="game-list">
                <?php foreach ($pastGames as $game): ?>
                    <li>
                        Game #<?= htmlspecialchars($game['id']) ?> - Finished at <?= htmlspecialchars($game['finished_at']) ?>
                        <a href="/games/<?= $game['id'] ?>/results" style="margin-left: 20px;">View Results</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
