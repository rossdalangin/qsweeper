<h1>Game Lobby</h1>
<h2>Game ID: <?= htmlspecialchars($game['id']) ?></h2>
<p>Share this Game ID with your students. They can join from their dashboard.</p>
<p>Status: <strong><?= strtoupper(htmlspecialchars($game['status'])) ?></strong></p>

<hr>

<h3>Participating Groups</h3>
<div style="display: flex; flex-wrap: wrap;">
<?php foreach ($groups as $group): ?>
    <div class="card" style="flex: 1; min-width: 220px;">
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
        <button type="submit" style="font-size: 1.2em; background-color: var(--success-color);">Launch Game</button>
    </form>
    <form action="/games/<?= $game['id'] ?>/cancel" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this game?');">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit" style="font-size: 1.2em; background-color: var(--danger-color);">Cancel Game</button>
    </form>
</div>
