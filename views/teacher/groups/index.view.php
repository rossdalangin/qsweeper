<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Groups</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>My Groups</h1>
    <p><a href="/teacher/groups/create">Create New Group</a></p>

    <table>
        <thead>
            <tr>
                <th>Group Name</th>
                <th>Members</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groups as $group): ?>
            <tr>
                <td><?= htmlspecialchars($group['name']) ?></td>
                <td><?= htmlspecialchars($group['member_count']) ?></td>
                <td><?= htmlspecialchars($group['created_at']) ?></td>
                <td>
                    <a href="/teacher/groups/view?id=<?= $group['id'] ?>">View/Manage</a>
                    <a href="/teacher/groups/edit?id=<?= $group['id'] ?>">Edit</a>
                    <form action="/teacher/groups/delete" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                        <input type="hidden" name="id" value="<?= $group['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <a href="/dashboard">Back to Dashboard</a>
</body>
</html>
