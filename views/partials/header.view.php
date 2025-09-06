<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Quiz Sweeper' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="/"><strong>Quiz Sweeper</strong></a>
            </div>
            <div>
                <a href="/about">About</a>
                <a href="/instructions">How to Play</a>
            <div>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/dashboard">Dashboard</a>
                    <form action="/logout" method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="/login">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>
