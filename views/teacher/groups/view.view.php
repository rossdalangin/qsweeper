<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Group: <?= htmlspecialchars($group['name']) ?></title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Manage Group: <?= htmlspecialchars($group['name']) ?></h1>
    <a href="/teacher/groups">Back to All Groups</a>

    <hr>

    <h2>Members</h2>
    <?php if (empty($members)): ?>
        <p>This group has no members yet.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member): ?>
            <tr>
                <td><?= htmlspecialchars($member['name']) ?></td>
                <td><?= htmlspecialchars($member['email']) ?></td>
                <td>
                    <form action="/teacher/groups/remove-member" method="POST" onsubmit="return confirm('Are you sure?');">
                        <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                        <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <hr>

    <h2>Add Member</h2>
    <p>Enter the email address of the student you want to add. The user must already have a student account.</p>
    <form action="/teacher/groups/add-member" method="POST">
        <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
        <input type="hidden"name="csrf_token" value="<?= csrf_token() ?>">
        <div>
            <label for="email">Student Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit">Add Member</button>
    </form>

</body>
</html>
