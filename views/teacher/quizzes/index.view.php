<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quizzes</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>My Quizzes</h1>
    <p><a href="/teacher/quizzes/create">Create New Quiz</a></p>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Questions</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td><?= htmlspecialchars($quiz['title']) ?></td>
                <td><?= htmlspecialchars($quiz['description']) ?></td>
                <td><?= htmlspecialchars($quiz['question_count']) ?></td>
                <td><?= htmlspecialchars($quiz['created_at']) ?></td>
                <td>
                    <a href="/teacher/quizzes/<?= $quiz['id'] ?>/questions">Manage Questions</a>
                    <a href="/teacher/quizzes/edit?id=<?= $quiz['id'] ?>">Edit</a>
                    <form action="/teacher/quizzes/delete" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                        <input type="hidden" name="id" value="<?= $quiz['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <a href="/dashboard">Back to Dashboard</a>
</body>
</html>
