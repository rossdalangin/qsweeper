<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="/admin/users">User Management</a>
            <a href="/admin/settings">Global Settings</a>
            <a href="/admin/logs">Game Logs</a>
            <!-- Add other admin links here -->
        </nav>
        <form action="/logout" method="POST" style="display:inline;">
            <button type="submit">Logout</button>
        </form>
    </header>

    <main>
        <h2>Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>
        <p>Select an option from the navigation to get started.</p>
    </main>
</body>
</html>
