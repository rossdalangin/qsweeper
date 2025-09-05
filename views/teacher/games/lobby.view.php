<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Lobby</title>
</head>
<body>
    <h1>Game Lobby</h1>
    <h2>Game ID: <?= htmlspecialchars($game['id']) ?></h2>
    <p>Share this Game ID with your students. They can join from their dashboard.</p>
    <p>Status: <strong><?= strtoupper(htmlspecialchars($game['status'])) ?></strong></p>

    <hr>

    <h3>Participating Groups</h3>
    <div style="display: flex; flex-wrap: wrap;">
    <?php foreach ($groups as $group): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin: 10px; min-width: 200px;">
            <h4><?= htmlspecialchars($group['name']) ?></h4>
            <ul>
                <?php foreach ($group['members'] as $member): ?>
                    <li><?= htmlspecialchars($member['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
    </div>

    <hr>

    <div>
        <form action="/games/<?= $game['id'] ?>/start" method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" style="font-size: 1.2em; background-color: lightgreen;">Launch Game</button>
        </form>
        <form action="/games/<?= $game['id'] ?>/cancel" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this game?');">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" style="font-size: 1.2em; background-color: lightcoral;">Cancel Game</button>
        </form>
    </div>

</body>
</html>
