<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Quiz Sweeper' ?></title>
    <style>
        body { font-family: sans-serif; margin: 0; }
        header { background: #f4f4f4; padding: 1rem; border-bottom: 1px solid #ddd; }
        nav { display: flex; align-items: center; justify-content: space-between; max-width: 1000px; margin: auto; }
        nav a { text-decoration: none; color: #333; margin-right: 15px; }
        nav form { margin: 0; }
        main { max-width: 1000px; margin: 20px auto; padding: 0 1rem; }
    </style>
</head>
<body>
    <header>
        <nav>
            <div>
                <a href="/"><strong>Quiz Sweeper</strong></a>
                <a href="/about">About</a>
                <a href="/instructions">How to Play</a>
            </div>
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
