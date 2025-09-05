<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Settings</title>
</head>
<body>
    <h1>Global Settings</h1>

    <form action="/admin/settings" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <h3>Default Game Configuration</h3>

        <div>
            <label for="default_board_rows">Default Board Rows</label>
            <input type="number" id="default_board_rows" name="settings[default_board_rows]" value="<?= htmlspecialchars($settings['default_board_rows'] ?? '') ?>" required>
        </div>

        <div>
            <label for="default_board_cols">Default Board Columns</label>
            <input type="number" id="default_board_cols" name="settings[default_board_cols]" value="<?= htmlspecialchars($settings['default_board_cols'] ?? '') ?>" required>
        </div>

        <div>
            <label for="default_bomb_count">Default Bomb Count</label>
            <input type="number" id="default_bomb_count" name="settings[default_bomb_count]" value="<?= htmlspecialchars($settings['default_bomb_count'] ?? '') ?>" required>
        </div>

        <div>
            <label for="default_knife_count">Default Knife Count</label>
            <input type="number" id="default_knife_count" name="settings[default_knife_count]" value="<?= htmlspecialchars($settings['default_knife_count'] ?? '') ?>" required>
        </div>

        <h3>Default Scoring Rules</h3>

        <div>
            <label for="default_correct_points">Points for Correct Answer</label>
            <input type="number" id="default_correct_points" name="settings[default_correct_points]" value="<?= htmlspecialchars($settings['default_correct_points'] ?? '') ?>" required>
        </div>

        <div>
            <label for="default_wrong_points">Points for Wrong Answer</label>
            <input type="number" id="default_wrong_points" name="settings[default_wrong_points]" value="<?= htmlspecialchars($settings['default_wrong_points'] ?? '') ?>" required>
        </div>

        <div>
            <label for="default_bomb_penalty">Bomb Penalty</label>
            <input type="number" id="default_bomb_penalty" name="settings[default_bomb_penalty]" value="<?= htmlspecialchars($settings['default_bomb_penalty'] ?? '') ?>" required>
        </div>

        <br>
        <button type="submit">Save Settings</button>
        <a href="/dashboard">Back to Dashboard</a>
    </form>
</body>
</html>
