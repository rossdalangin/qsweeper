<h1>Game Over!</h1>
<h2>Final Scoreboard</h2>

<div class="card">
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
</div>

<hr>

<a href="/exports/game/<?= $game['id'] ?>/csv" class="button">Export Results as CSV</a>
<a href="/dashboard" class="button">Return to Dashboard</a>
