<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start a New Game</title>
</head>
<body>
    <h1>Start a New Game</h1>

    <form action="/games/create" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <h3>1. Select Quiz and Groups</h3>
        <div>
            <label for="quiz_id">Quiz</label>
            <select id="quiz_id" name="quiz_id" required>
                <?php foreach ($quizzes as $quiz): ?>
                    <option value="<?= $quiz['id'] ?>"><?= htmlspecialchars($quiz['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="group_ids">Groups</label><br>
            <select id="group_ids" name="group_ids[]" multiple required size="5">
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <h3>2. Board Configuration</h3>
        <div>
            <label for="rows">Rows</label>
            <input type="number" id="rows" name="rows" value="<?= htmlspecialchars($settings['default_board_rows']) ?>" required>
        </div>
        <div>
            <label for="cols">Columns</label>
            <input type="number" id="cols" name="cols" value="<?= htmlspecialchars($settings['default_board_cols']) ?>" required>
        </div>
        <div>
            <label for="bomb_count">Number of Bombs</label>
            <input type="number" id="bomb_count" name="bomb_count" value="<?= htmlspecialchars($settings['default_bomb_count']) ?>" required>
        </div>
        <div>
            <label for="knife_count">Number of Knives</label>
            <input type="number" id="knife_count" name="knife_count" value="<?= htmlspecialchars($settings['default_knife_count']) ?>" required>
        </div>

        <h3>3. Scoring Rules</h3>
        <div>
            <label for="correct_points">Points for Correct Answer</label>
            <input type="number" id="correct_points" name="correct_points" value="<?= htmlspecialchars($settings['default_correct_points']) ?>" required>
        </div>
        <div>
            <label for="wrong_points">Points for Wrong Answer</label>
            <input type="number" id="wrong_points" name="wrong_points" value="<?= htmlspecialchars($settings['default_wrong_points']) ?>" required>
        </div>
        <div>
            <label for="bomb_penalty">Bomb Penalty (points to deduct)</label>
            <input type="number" id="bomb_penalty" name="bomb_penalty" value="<?= htmlspecialchars($settings['default_bomb_penalty']) ?>" required>
        </div>

        <br>
        <button type="submit">Create Game & Open Lobby</button>
        <a href="/dashboard">Cancel</a>
    </form>
</body>
</html>
