<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Over!</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .rank-1 { font-size: 1.5em; font-weight: bold; color: gold; }
    </style>
</head>
<body>
    <h1>Game Over!</h1>
    <h2>Final Scoreboard</h2>

    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Group</th>
                <th>Final Score</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($scores as $index => $score): ?>
            <tr class="rank-<?= $index + 1 ?>">
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($score['group_name']) ?></td>
                <td><?= htmlspecialchars($score['score']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <a href="/exports/game/<?= $game['id'] ?>/csv">Export Results as CSV</a>
    <br><br>
    <a href="/dashboard">Return to Dashboard</a>

</body>
</html>
