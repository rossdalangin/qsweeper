<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions for <?= htmlspecialchars($quiz['title']) ?></title>
    <style>
        .question { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        .choices li { list-style-type: none; }
        .choices .correct { font-weight: bold; color: green; }
    </style>
</head>
<body>
    <h1>Manage Questions for "<?= htmlspecialchars($quiz['title']) ?>"</h1>
    <a href="/teacher/quizzes">Back to My Quizzes</a>

    <hr>

    <h2>Existing Questions</h2>
    <?php if (empty($questions)): ?>
        <p>This quiz has no questions yet.</p>
    <?php else: ?>
        <?php foreach ($questions as $question): ?>
        <div class="question">
            <h4>Q: <?= htmlspecialchars($question['text']) ?></h4>
            <?php if ($question['image_path']): ?>
                <img src="/<?= htmlspecialchars($question['image_path']) ?>" alt="Question Image" style="max-width: 200px;">
            <?php endif; ?>
            <ul class="choices">
            <?php foreach ($question['choices'] as $choice): ?>
                <li class="<?= $choice['is_correct'] ? 'correct' : '' ?>">
                    <?= htmlspecialchars($choice['text']) ?>
                </li>
            <?php endforeach; ?>
            </ul>
            <a href="/teacher/questions/<?= $question['id'] ?>/edit">Edit</a>
            <form action="/teacher/questions/<?= $question['id'] ?>/delete" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit">Delete</button>
            </form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr>

    <h2>Add New Question</h2>
    <form action="/teacher/quizzes/<?= $quiz['id'] ?>/questions" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div>
            <label for="question_text">Question Text</label><br>
            <textarea id="question_text" name="text" required style="width: 500px;"></textarea>
        </div>
        <div>
            <label for="image">Optional Image</label>
            <input type="file" id="image" name="image">
        </div>
        <br>
        <div>
            <label>Choices (provide at least 2, up to 5)</label><br>
            <?php for ($i = 0; $i < 5; $i++): ?>
            <div>
                <input type="radio" name="is_correct" value="<?= $i ?>" <?= $i==0 ? 'checked' : ''?>>
                <input type="text" name="choices[]" placeholder="Choice <?= $i + 1 ?>">
            </div>
            <?php endfor; ?>
        </div>
        <br>
        <button type="submit">Add Question</button>
    </form>

</body>
</html>
