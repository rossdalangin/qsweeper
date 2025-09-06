<?php

namespace App\Controllers;

use App\Core\Database;

/**
 * Handles game management functionality, primarily for teachers.
 */
class GameController extends Controller {

    /**
     * Ensures a user is logged in before any game action.
     * Specific role checks are done within each method.
     */
    public function __construct() {
        $this->isLoggedIn();
    }

    /**
     * Displays the form for a teacher to create and configure a new game.
     * @return mixed
     */
    public function create() {
        $this->isTeacher(); // Only teachers can create games

        $db = Database::getInstance()->getConnection();

        // Get the teacher's quizzes to populate a dropdown
        $stmt = $db->prepare("SELECT id, title FROM quizzes WHERE teacher_id = :teacher_id");
        $stmt->execute(['teacher_id' => $_SESSION['user']['id']]);
        $quizzes = $stmt->fetchAll();

        // Get the teacher's groups to populate a multi-select
        $stmt = $db->prepare("SELECT id, name FROM groups WHERE teacher_id = :teacher_id");
        $stmt->execute(['teacher_id' => $_SESSION['user']['id']]);
        $groups = $stmt->fetchAll();

        // Get global default settings
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        return view('teacher/games/create', [
            'quizzes' => $quizzes,
            'groups' => $groups,
            'settings' => $settings,
            'title' => 'Start New Game'
        ]);
    }

    /**
     * Processes the creation of a new game, including generating all tiles.
     */
    public function store() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');
        $this->isTeacher();

        // Validation
        $required = ['quiz_id', 'group_ids', 'rows', 'cols', 'bomb_count', 'knife_count', 'bandaid_count', 'correct_points', 'wrong_points', 'bomb_penalty'];
        foreach ($required as $field) {
            if (empty($_POST[$field]) && $_POST[$field] !== '0') die("Field {$field} is required.");
        }

        $rows = (int)$_POST['rows'];
        $cols = (int)$_POST['cols'];
        $bombs = (int)$_POST['bomb_count'];
        $knives = (int)$_POST['knife_count'];
        $bandaids = (int)$_POST['bandaid_count'];
        $totalTiles = $rows * $cols;
        $specialTiles = $bombs + $knives + $bandaids;
        if ($specialTiles >= $totalTiles) {
            die("The number of special tiles (bombs, knives, band-aids) must be less than the total number of tiles.");
        }

        $db = Database::getInstance()->getConnection();

        // Check if quiz has enough questions
        $questionTiles = $totalTiles - $specialTiles;
        $stmt = $db->prepare("SELECT id FROM questions WHERE quiz_id = :quiz_id");
        $stmt->execute(['quiz_id' => $_POST['quiz_id']]);
        $questions = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        if (count($questions) < $questionTiles) {
            die("The selected quiz does not have enough questions for the board size. It has " . count($questions) . " but needs " . $questionTiles . ".");
        }

        // Database Transaction
        try {
            $db->beginTransaction();

            // Insert game record
            $stmt = $db->prepare(
                "INSERT INTO games (quiz_id, teacher_id, `rows`, cols, bomb_count, knife_count, bandaid_count, correct_points, wrong_points, bomb_penalty, status)
                 VALUES (:quiz_id, :teacher_id, :rows, :cols, :bomb_count, :knife_count, :bandaid_count, :correct_points, :wrong_points, :bomb_penalty, 'lobby')"
            );
            $stmt->execute([
                'quiz_id' => $_POST['quiz_id'],
                'teacher_id' => $_SESSION['user']['id'],
                'rows' => $rows, 'cols' => $cols,
                'bomb_count' => $bombs, 'knife_count' => $knives, 'bandaid_count' => $bandaids,
                'correct_points' => $_POST['correct_points'],
                'wrong_points' => $_POST['wrong_points'],
                'bomb_penalty' => $_POST['bomb_penalty']
            ]);
            $gameId = $db->lastInsertId();

            // Insert group scores
            $stmt = $db->prepare("INSERT INTO group_scores (game_id, group_id, score) VALUES (:game_id, :group_id, 0)");
            foreach ($_POST['group_ids'] as $groupId) {
                $stmt->execute(['game_id' => $gameId, 'group_id' => $groupId]);
            }

            // Tile Generation
            $tiles = [];
            for ($i = 0; $i < $bombs; $i++) $tiles[] = ['type' => 'bomb', 'question_id' => null];
            for ($i = 0; $i < $knives; $i++) $tiles[] = ['type' => 'knife', 'question_id' => null];
            for ($i = 0; $i < $bandaids; $i++) $tiles[] = ['type' => 'bandaid', 'question_id' => null];

            shuffle($questions);
            for ($i = 0; $i < $questionTiles; $i++) $tiles[] = ['type' => 'question', 'question_id' => $questions[$i]];

            shuffle($tiles);

            // Insert tiles
            $stmt = $db->prepare(
                "INSERT INTO game_tiles (game_id, tile_index, type, question_id) VALUES (:game_id, :tile_index, :type, :question_id)"
            );
            foreach ($tiles as $index => $tile) {
                $stmt->execute([
                    'game_id' => $gameId,
                    'tile_index' => $index,
                    'type' => $tile['type'],
                    'question_id' => $tile['question_id']
                ]);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            die("Failed to create game: " . $e->getMessage());
        }

        // Redirect to lobby
        header('Location: /games/' . $gameId . '/lobby');
        exit();
    }

    /**
     * Displays the game lobby for a teacher before a game starts.
     * @param int $gameId The ID of the game.
     * @return mixed
     */
    public function lobby($gameId) {
        $this->isTeacher();

        $db = Database::getInstance()->getConnection();

        // Get game details
        $stmt = $db->prepare("SELECT * FROM games WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $gameId, 'teacher_id' => $_SESSION['user']['id']]);
        $game = $stmt->fetch();
        if (!$game) die('Game not found.');

        // Get participating groups and their members
        $stmt = $db->prepare(
           "SELECT g.id as group_id, g.name as group_name, u.id as user_id, u.name as user_name
            FROM group_scores gs
            JOIN `groups` g ON gs.group_id = g.id
            JOIN group_members gm ON g.id = gm.group_id
            JOIN users u ON gm.user_id = u.id
            WHERE gs.game_id = :game_id AND u.role = 'student'
            ORDER BY g.name, u.name"
        );
        $stmt->execute(['game_id' => $gameId]);
        $results = $stmt->fetchAll();

        $groups = [];
        foreach($results as $row) {
            if (!isset($groups[$row['group_id']])) {
                $groups[$row['group_id']] = [
                    'name' => $row['group_name'],
                    'members' => []
                ];
            }
            $groups[$row['group_id']]['members'][] = [
                'id' => $row['user_id'],
                'name' => $row['user_name']
            ];
        }

        return view('teacher/games/lobby', [
            'game' => $game,
            'groups' => $groups,
            'title' => 'Game Lobby'
        ]);
    }

    /**
     * Starts a game from the lobby.
     * @param int $gameId The ID of the game.
     */
    public function startGame($gameId) {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');
        $this->isTeacher();
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "UPDATE games SET status = 'running', started_at = NOW()
             WHERE id = :id AND teacher_id = :teacher_id AND status = 'lobby'"
        );
        $stmt->execute(['id' => $gameId, 'teacher_id' => $_SESSION['user']['id']]);

        header('Location: /games/' . $gameId . '/board');
        exit();
    }

    /**
     * Cancels a game from the lobby.
     * @param int $gameId The ID of the game.
     */
    public function cancelGame($gameId) {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');
        $this->isTeacher();
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "UPDATE games SET status = 'cancelled'
             WHERE id = :id AND teacher_id = :teacher_id AND status = 'lobby'"
        );
        $stmt->execute(['id' => $gameId, 'teacher_id' => $_SESSION['user']['id']]);

        header('Location: /dashboard');
        exit();
    }

    /**
     * Displays the main game board for both students and teachers.
     * @param int $gameId The ID of the game.
     * @return mixed
     */
    public function board($gameId) {
        $user = $_SESSION['user'];
        $db = Database::getInstance()->getConnection();

        // Fetch game data
        $stmt = $db->prepare("SELECT * FROM games WHERE id = :id");
        $stmt->execute(['id' => $gameId]);
        $game = $stmt->fetch();
        if (!$game) die('Game not found.');

        // Verify that the current user (teacher or student) is actually part of this game.
        $isParticipant = false;
        if ($user['role'] === 'teacher' && $game['teacher_id'] == $user['id']) {
            $isParticipant = true;
        } elseif ($user['role'] === 'student') {
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM group_members gm
                 JOIN group_scores gs ON gm.group_id = gs.group_id
                 WHERE gs.game_id = :game_id AND gm.user_id = :user_id"
            );
            $stmt->execute(['game_id' => $gameId, 'user_id' => $user['id']]);
            if ($stmt->fetchColumn() > 0) {
                $isParticipant = true;
            }
        }
        if (!$isParticipant) {
            die("You are not authorized to view this game.");
        }

        // Fetch all tiles for the board
        $stmt = $db->prepare("SELECT * FROM game_tiles WHERE game_id = :id ORDER BY tile_index ASC");
        $stmt->execute(['id' => $gameId]);
        $tiles = $stmt->fetchAll();

        // Fetch all group scores
        $stmt = $db->prepare("SELECT * FROM group_scores WHERE game_id = :id");
        $stmt->execute(['id' => $gameId]);
        $scores = $stmt->fetchAll();

        return view('game/board', [
            'game' => $game,
            'tiles' => $tiles,
            'scores' => $scores,
            'user' => $user,
            'title' => "Game #" . $gameId
        ]);
    }

    /**
     * Manually ends a running game.
     * @param int $gameId The ID of the game.
     */
    public function endGame($gameId) {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');
        $this->isTeacher();
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "UPDATE games SET status = 'finished', finished_at = NOW()
             WHERE id = :id AND teacher_id = :teacher_id AND status = 'running'"
        );
        $stmt->execute(['id' => $gameId, 'teacher_id' => $_SESSION['user']['id']]);

        header('Location: /games/' . $gameId . '/results');
        exit();
    }

    /**
     * Displays the final results and scoreboard for a finished game.
     * @param int $gameId The ID of the game.
     * @return mixed
     */
    public function results($gameId) {
        $this->isLoggedIn();
        $db = Database::getInstance()->getConnection();

        // Get game, ensuring it is finished
        $stmt = $db->prepare("SELECT * FROM games WHERE id = :id AND status = 'finished'");
        $stmt->execute(['id' => $gameId]);
        $game = $stmt->fetch();
        if (!$game) die('Game not found or has not finished.');

        // TODO: Verify user is part of this game (low priority as no sensitive data is shown)

        // Get final scores, ranked
        $stmt = $db->prepare(
            "SELECT gs.score, g.name as group_name
             FROM group_scores gs
             JOIN `groups` g ON gs.group_id = g.id
             WHERE gs.game_id = :game_id
             ORDER BY gs.score DESC"
        );
        $stmt->execute(['game_id' => $gameId]);
        $scores = $stmt->fetchAll();

        return view('teacher/games/results', [
            'game' => $game,
            'scores' => $scores,
            'title' => 'Game Results'
        ]);
    }

    /**
     * Generates and serves a CSV file of a game's results.
     * @param int $gameId The ID of the game.
     */
    public function exportCsv($gameId) {
        $this->isTeacher();
        $db = Database::getInstance()->getConnection();

        // Fetch detailed answer log for the game
        $stmt = $db->prepare(
            "SELECT
                a.answered_at, u.name as user_name, g.name as group_name,
                q.text as question_text, c.text as choice_text,
                a.is_correct, a.points_awarded
             FROM answers a
             JOIN users u ON a.user_id = u.id
             JOIN `groups` g ON a.group_id = g.id
             LEFT JOIN questions q ON a.question_id = q.id
             LEFT JOIN choices c ON a.choice_id = c.id
             WHERE a.game_id = :game_id
             ORDER BY a.answered_at ASC"
        );
        $stmt->execute(['game_id' => $gameId]);
        $results = $stmt->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="game_results_' . $gameId . '.csv"');

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, ['Timestamp', 'Player', 'Group', 'Question', 'Answer', 'Correct', 'Points']);

        // Data rows
        foreach ($results as $row) {
            fputcsv($output, [
                $row['answered_at'],
                $row['user_name'],
                $row['group_name'],
                $row['question_text'],
                $row['choice_text'],
                $row['is_correct'] ? 'Yes' : 'No',
                $row['points_awarded']
            ]);
        }

        fclose($output);
        exit();
    }
}
