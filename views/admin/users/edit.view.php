<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>
<body>
    <h1>Edit User: <?= htmlspecialchars($user['name']) ?></h1>

    <form action="/admin/users/edit" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
            <small>Leave blank to keep current password.</small>
        </div>

        <div>
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <button type="submit">Update User</button>
        <a href="/admin/users">Cancel</a>
    </form>
</body>
</html>
