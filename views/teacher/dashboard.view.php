<h1>Teacher Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($user['name']) ?>!</p>

<div class="dashboard-layout">
    <aside class="dashboard-nav card">
        <a href="/games/create" style="font-weight: bold; color: var(--success-color);">Start New Game</a>
        <a href="/teacher/quizzes">My Quizzes</a>
        <a href="/teacher/groups">My Groups</a>
        <a href="/instructions">How to Play</a>
    </aside>
    <div class="dashboard-content card">
        <h2>Quick Actions</h2>
        <p>Select an option from the navigation menu to get started.</p>
        <!-- More dashboard widgets could go here -->
    </div>
</div>
