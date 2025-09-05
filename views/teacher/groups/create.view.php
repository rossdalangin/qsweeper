<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Group</title>
</head>
<body>
    <h1>Create New Group</h1>

    <form action="/teacher/groups/create" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div>
            <label for="name">Group Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <button type="submit">Create Group</button>
        <a href="/teacher/groups">Cancel</a>
    </form>
</body>
</html>
