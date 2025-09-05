<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz</title>
</head>
<body>
    <h1>Edit Quiz</h1>

    <form action="/teacher/quizzes/edit" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($quiz['id']) ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div>
            <label for="title">Quiz Title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" required>
        </div>

        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($quiz['description']) ?></textarea>
        </div>

        <button type="submit">Save Changes</button>
        <a href="/teacher/quizzes">Cancel</a>
    </form>
</body>
</html>
