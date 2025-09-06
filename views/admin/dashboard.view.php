<h1>Admin Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($user['name']) ?>!</p>

<div class="dashboard-layout">
    <aside class="dashboard-nav card">
        <a href="/admin/users">User Management</a>
        <a href="/admin/settings">Global Settings</a>
        <a href="/admin/logs">Game Logs</a>
        <a href="/instructions">How to Play</a>
    </aside>
    <div class="dashboard-content card">
        <h2>System Overview</h2>
        <p>Select an option from the navigation menu to manage the application.</p>
        <!-- More dashboard widgets could go here -->
    </div>
</div>
