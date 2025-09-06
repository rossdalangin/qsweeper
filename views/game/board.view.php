<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game #<?= $game['id'] ?></title>
    <meta name="csrf-token" content="<?= csrf_token() ?>"> <!-- For JS AJAX requests -->
    <style>
        body { font-family: sans-serif; }
        .game-container { display: flex; gap: 20px; }
        .board {
            display: grid;
            grid-template-columns: repeat(<?= $game['cols'] ?>, 50px);
            grid-template-rows: repeat(<?= $game['rows'] ?>, 50px);
            gap: 3px;
            border: 2px solid #333;
            padding: 5px;
            background-color: #666;
        }
        .tile {
            width: 50px;
            height: 50px;
            border: 1px solid #999;
            background-color: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8em;
            cursor: pointer;
        }
        .tile:hover { background-color: #ddd; }
        .tile.revealed {
            background-color: #f1f1f1;
            cursor: default;
        }
        .tile.correct { background-color: #90ee90; }
        .tile.incorrect { background-color: #ff7f7f; }
        .tile.bomb { background-color: #333; color: white; }
        .tile.knife { background-color: #ffc107; color: black; }
        .tile.bandaid { background-color: #d4edda; color: black; }

        #choices-container label.correct-answer { color: green; font-weight: bold; }
        #choices-container label.incorrect-answer { color: red; text-decoration: line-through; }

        #question-modal {
            display: none; /* Hidden by default */
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #question-modal-content {
            background: white; padding: 20px; border-radius: 5px; width: 80%; max-width: 600px;
        }
    </style>
</head>
<body>

    <h1>Game #<?= $game['id'] ?></h1>
    <p>Status: <strong id="game-status"><?= strtoupper(htmlspecialchars($game['status'])) ?></strong></p>

    <div class="game-container">
        <div id="game-board" class="board">
            <?php foreach ($tiles as $tile): ?>
                <?php
                    $isStudent = $user['role'] === 'student';
                    $isRevealed = $tile['revealed'];
                    $tileId = "tile-" . $tile['tile_index'];

                    if ($isStudent && !$isRevealed) {
                        // Student view, unrevealed tile
                        echo "<button class='tile' id='{$tileId}' data-index='{$tile['tile_index']}'></button>";
                    } else {
                        // Student view (revealed) OR Teacher view (always "revealed")
                        $class = 'tile revealed';
                        $content = '';
                        if ($tile['type'] === 'bomb')  { $class .= ' bomb'; $content = '💣'; }
                        if ($tile['type'] === 'knife') { $class .= ' knife'; $content = '🔪'; }
                        if ($tile['type'] === 'bandaid') { $class .= ' bandaid'; $content = '🩹'; }
                        if ($tile['type'] === 'question') { $class .= ' question'; $content = '❓'; }
                        // Correct/incorrect state for answered questions will be applied by JS
                        echo "<div class='{$class}' id='{$tileId}'>{$content}</div>";
                    }
                ?>
            <?php endforeach; ?>
        </div>

        <div>
            <h2>Scores</h2>
            <ul id="scores-list">
                <?php foreach ($scores as $score): ?>
                    <li data-group-id="<?= $score['group_id'] ?>">
                        Group <?= htmlspecialchars($score['group_id']) ?>:
                        <strong id="score-<?= $score['group_id'] ?>"><?= htmlspecialchars($score['score']) ?></strong>
                        <span class="protection-status" id="protection-<?= $score['group_id'] ?>"></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Question Modal -->
    <div id="question-modal" style="display: none;">
        <div id="question-modal-content">
            <h3 id="question-text"></h3>
            <div id="question-image-container"></div>
            <form id="question-form">
                <input type="hidden" name="tile_id" id="modal-tile-id">
                <div id="choices-container"></div>
                <button type="submit">Submit Answer</button>
            </form>
        </div>
    </div>

    <?php if ($user['role'] === 'teacher'): ?>
    <hr>
    <form action="/games/<?= $game['id'] ?>/end" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit">End Game Manually</button>
    </form>
    <?php endif; ?>

    <script src="/js/game.js"></script>
</body>
</html>
