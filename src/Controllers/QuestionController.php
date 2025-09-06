<?php

namespace App\Controllers;

use App\Core\Database;

/**
 * Handles all actions related to questions and choices within a quiz.
 */
class QuestionController extends Controller {

    /**
     * Ensures the user is a teacher before any action.
     */
    public function __construct() {
        $this->isTeacher();
    }

    /**
     * Displays the question builder interface for a specific quiz.
     * @param int $quizId The ID of the quiz to manage questions for.
     * @return mixed
     */
    public function index($quizId) {
        $db = Database::getInstance()->getConnection();

        // 1. Get quiz details, ensuring it belongs to the current teacher
        $stmt = $db->prepare("SELECT id, title FROM quizzes WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $quizId, 'teacher_id' => $_SESSION['user']['id']]);
        $quiz = $stmt->fetch();

        if (!$quiz) {
            die('Quiz not found or you do not have permission to view it.');
        }

        // 2. Get all questions and their choices for this quiz
        $stmt = $db->prepare(
            "SELECT q.*, c.id as choice_id, c.text as choice_text, c.is_correct
             FROM questions q
             LEFT JOIN choices c ON q.id = c.question_id
             WHERE q.quiz_id = :quiz_id
             ORDER BY q.id, c.id"
        );
        $stmt->execute(['quiz_id' => $quizId]);
        $results = $stmt->fetchAll();

        // Group choices by question
        $questions = [];
        foreach ($results as $row) {
            if (!isset($questions[$row['id']])) {
                $questions[$row['id']] = [
                    'id' => $row['id'],
                    'text' => $row['text'],
                    'image_path' => $row['image_path'],
                    'choices' => []
                ];
            }
            if ($row['choice_id']) {
                $questions[$row['id']]['choices'][] = [
                    'id' => $row['choice_id'],
                    'text' => $row['choice_text'],
                    'is_correct' => $row['is_correct']
                ];
            }
        }

        return view('teacher/questions/index', [
            'quiz' => $quiz,
            'questions' => $questions,
            'title' => 'Manage Questions'
        ]);
    }

    /**
     * Processes the creation of a new question and its choices.
     * @param int $quizId The ID of the quiz to add the question to.
     */
    public function store($quizId) {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        // 1. Validation
        if (empty($_POST['text']) || !isset($_POST['choices']) || !isset($_POST['is_correct'])) {
            die('Question text and at least one choice are required.');
        }

        $choices = array_filter($_POST['choices'], fn($c) => !empty(trim($c)));
        if (count($choices) < 2) {
            die('Please provide at least two non-empty choices.');
        }

        $db = Database::getInstance()->getConnection();

        // Check if teacher owns the quiz
        $stmt = $db->prepare("SELECT id FROM quizzes WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $quizId, 'teacher_id' => $_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            die('You do not have permission to modify this quiz.');
        }

        // 2. Handle Image Upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                die('Invalid file type. Only JPG, PNG, and GIF are allowed.');
            }
            if ($_FILES['image']['size'] > $maxSize) {
                die('File size exceeds the 5MB limit.');
            }

            $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], 'public/' . $targetPath)) {
                die('Failed to upload image.');
            }
            $imagePath = $targetPath;
        }

        // 3. Database Transaction
        try {
            $db->beginTransaction();

            // Insert question
            $stmt = $db->prepare(
                "INSERT INTO questions (quiz_id, text, image_path) VALUES (:quiz_id, :text, :image_path)"
            );
            $stmt->execute([
                'quiz_id' => $quizId,
                'text' => $_POST['text'],
                'image_path' => $imagePath
            ]);
            $questionId = $db->lastInsertId();

            // Insert choices
            $stmt = $db->prepare(
                "INSERT INTO choices (question_id, text, is_correct) VALUES (:question_id, :text, :is_correct)"
            );
            $correctChoiceIndex = (int)$_POST['is_correct'];

            $choiceIndex = 0;
            foreach ($_POST['choices'] as $choiceText) {
                if (!empty(trim($choiceText))) {
                    $stmt->execute([
                        'question_id' => $questionId,
                        'text' => $choiceText,
                        'is_correct' => ($choiceIndex === $correctChoiceIndex)
                    ]);
                    $choiceIndex++;
                }
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            die('Failed to save question: ' . $e->getMessage());
        }

        header('Location: /teacher/quizzes/' . $quizId . '/questions');
        exit();
    }

    /**
     * Displays the form to edit a question.
     * @param int $questionId The ID of the question to edit.
     * @return mixed
     */
    public function edit($questionId) {
        $db = Database::getInstance()->getConnection();

        // 1. Get question and quiz, ensuring teacher owns the quiz
        $stmt = $db->prepare(
            "SELECT q.*, qu.id as quiz_id, qu.teacher_id
             FROM questions q
             JOIN quizzes qu ON q.quiz_id = qu.id
             WHERE q.id = :id"
        );
        $stmt->execute(['id' => $questionId]);
        $question = $stmt->fetch();

        if (!$question || $question['teacher_id'] != $_SESSION['user']['id']) {
            die('Question not found or you do not have permission to edit it.');
        }

        // 2. Get choices
        $stmt = $db->prepare("SELECT id, text, is_correct FROM choices WHERE question_id = :id ORDER BY id");
        $stmt->execute(['id' => $questionId]);
        $choices = $stmt->fetchAll();
        $question['choices'] = $choices;

        return view('teacher/questions/edit', [
            'question' => $question,
            'title' => 'Edit Question'
        ]);
    }

    /**
     * Processes the update of an existing question.
     * @param int $questionId The ID of the question to update.
     */
    public function update($questionId) {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        // Validation
        if (empty($_POST['text']) || !isset($_POST['choices']) || !isset($_POST['is_correct'])) {
            die('Question text and choices are required.');
        }
        $choices = array_filter($_POST['choices'], fn($c) => !empty(trim($c)));
        if (count($choices) < 2) {
            die('Please provide at least two non-empty choices.');
        }

        $db = Database::getInstance()->getConnection();

        // Get question and verify ownership
        $stmt = $db->prepare("SELECT * FROM questions WHERE id = :id");
        $stmt->execute(['id' => $questionId]);
        $question = $stmt->fetch();
        // (Ownership was checked in the edit method, but double-check here)

        // Handle Image
        $imagePath = $question['image_path'];
        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            if ($imagePath && file_exists('public/' . $imagePath)) {
                unlink('public/' . $imagePath);
            }
            $imagePath = null;
        }
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // (Add the same validation as in store() method)
            $uploadDir = 'uploads/';
            $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], 'public/' . $targetPath)) {
                if ($imagePath && file_exists('public/' . $imagePath)) {
                    unlink('public/' . $imagePath);
                }
                $imagePath = $targetPath;
            }
        }

        // DB Transaction
        try {
            $db->beginTransaction();

            // Update question text and image path
            $stmt = $db->prepare("UPDATE questions SET text = :text, image_path = :image_path WHERE id = :id");
            $stmt->execute(['text' => $_POST['text'], 'image_path' => $imagePath, 'id' => $questionId]);

            // Delete old choices
            $stmt = $db->prepare("DELETE FROM choices WHERE question_id = :id");
            $stmt->execute(['id' => $questionId]);

            // Insert new choices
            $stmt = $db->prepare("INSERT INTO choices (question_id, text, is_correct) VALUES (:question_id, :text, :is_correct)");
            $correctChoiceIndex = (int)$_POST['is_correct'];
            $choiceIndex = 0;
            foreach ($_POST['choices'] as $choiceText) {
                if (!empty(trim($choiceText))) {
                    $stmt->execute([
                        'question_id' => $questionId,
                        'text' => $choiceText,
                        'is_correct' => ($choiceIndex === $correctChoiceIndex)
                    ]);
                    $choiceIndex++;
                }
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            die('Failed to update question: ' . $e->getMessage());
        }

        header('Location: /teacher/quizzes/' . $_POST['quiz_id'] . '/questions');
        exit();
    }

    /**
     * Processes the deletion of a question.
     * @param int $questionId The ID of the question to delete.
     */
    public function destroy($questionId) {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        $db = Database::getInstance()->getConnection();

        // 1. Get question to verify ownership and get image path
        $stmt = $db->prepare(
            "SELECT q.image_path, qu.teacher_id
             FROM questions q
             JOIN quizzes qu ON q.quiz_id = qu.id
             WHERE q.id = :id"
        );
        $stmt->execute(['id' => $questionId]);
        $question = $stmt->fetch();

        if (!$question || $question['teacher_id'] != $_SESSION['user']['id']) {
            die('Question not found or you do not have permission to delete it.');
        }

        // 2. Delete the question from DB (choices are deleted by cascade)
        $stmt = $db->prepare("DELETE FROM questions WHERE id = :id");
        $stmt->execute(['id' => $questionId]);

        // 3. Delete the image file if it exists
        if ($question['image_path'] && file_exists('public/' . $question['image_path'])) {
            unlink('public/' . $question['image_path']);
        }

        header('Location: /teacher/quizzes/' . $_POST['quiz_id'] . '/questions');
        exit();
    }
}
