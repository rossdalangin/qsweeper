<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
</head>
<body>
    <header>
        <h1>Teacher Dashboard</h1>
        <nav>
            <a href="/teacher/groups">My Groups</a>
            <a href="/teacher/quizzes">My Quizzes</a>
            <a href="/games/create" style="font-weight: bold; color: green;">Start New Game</a>
            <a href="/instructions">How to Play</a>
            <!-- Add other teacher links here -->
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
