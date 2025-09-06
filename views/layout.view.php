<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Quiz Sweeper') ?></title>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php require 'partials/header.view.php'; ?>

    <main>
        <?php require $view; // This variable is passed from the view() helper ?>
    </main>

    <?php require 'partials/footer.view.php'; ?>

    <?php if (isset($js)): ?>
        <script src="<?= htmlspecialchars($js) ?>"></script>
    <?php endif; ?>
</body>
</html>
