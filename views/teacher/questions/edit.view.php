<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
</head>
<body>
    <h1>Edit Question</h1>

    <form action="/teacher/questions/<?= $question['id'] ?>/edit" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="quiz_id" value="<?= $question['quiz_id'] ?>">

        <div>
            <label for="question_text">Question Text</label><br>
            <textarea id="question_text" name="text" required style="width: 500px;"><?= htmlspecialchars($question['text']) ?></textarea>
        </div>

        <div>
            <label for="image">Optional Image</label>
            <input type="file" id="image" name="image">
            <?php if ($question['image_path']): ?>
                <p>Current image: <img src="/<?= htmlspecialchars($question['image_path']) ?>" alt="Current Image" style="max-width: 100px;"></p>
                <label><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
            <?php endif; ?>
        </div>
        <br>
        <div>
            <label>Choices (provide at least 2, up to 5)</label><br>
            <?php
            $choices = $question['choices'];
            for ($i = 0; $i < 5; $i++):
                $choice = $choices[$i] ?? null;
                $is_correct = $choice['is_correct'] ?? false;
            ?>
            <div>
                <input type="radio" name="is_correct" value="<?= $i ?>" <?= $is_correct ? 'checked' : '' ?>>
                <input type="text" name="choices[]" placeholder="Choice <?= $i + 1 ?>" value="<?= htmlspecialchars($choice['text'] ?? '') ?>">
            </div>
            <?php endfor; ?>
        </div>
        <br>
        <button type="submit">Save Changes</button>
        <a href="/teacher/quizzes/<?= $question['quiz_id'] ?>/questions">Cancel</a>
    </form>

</body>
</html>
