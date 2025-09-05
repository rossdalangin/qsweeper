<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Group</title>
</head>
<body>
    <h1>Edit Group</h1>

    <form action="/teacher/groups/edit" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($group['id']) ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div>
            <label for="name">Group Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($group['name']) ?>" required>
        </div>

        <button type="submit">Update Group</button>
        <a href="/teacher/groups">Cancel</a>
    </form>
</body>
</html>
