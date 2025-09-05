<?php

namespace App\Controllers;

use App\Core\Database;

class ApiController extends Controller {

    public function __construct() {
        $this->isLoggedIn();
        // Set header to return JSON
        header('Content-Type: application/json');
    }

    private function verifyGameParticipant($gameId, $userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM group_members gm
             JOIN group_scores gs ON gm.group_id = gs.group_id
             WHERE gs.game_id = :game_id AND gm.user_id = :user_id"
        );
        $stmt->execute(['game_id' => $gameId, 'user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getGameState($gameId) {
        $userId = $_SESSION['user']['id'];
        $isTeacher = $_SESSION['user']['role'] === 'teacher';

        // Security check
        if (!$isTeacher && !$this->verifyGameParticipant($gameId, $userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }

        $db = Database::getInstance()->getConnection();

        // Get game status
        $stmt = $db->prepare("SELECT status, teacher_id FROM games WHERE id = :id");
        $stmt->execute(['id' => $gameId]);
        $game = $stmt->fetch();

        // If user is not the teacher of the game, deny access
        if (!$isTeacher && $game['teacher_id'] != $_SESSION['user']['id'] && !$this->verifyGameParticipant($gameId, $userId)) {
             http_response_code(403);
             echo json_encode(['error' => 'Forbidden']);
             exit();
        }

        // Get all tiles
        $stmt = $db->prepare("SELECT tile_index, type, revealed, revealed_by_user_id FROM game_tiles WHERE game_id = :id");
        $stmt->execute(['id' => $gameId]);
        $tiles = $stmt->fetchAll();

        // Get all scores
        $stmt = $db->prepare("SELECT group_id, score FROM group_scores WHERE game_id = :id");
        $stmt->execute(['id' => $gameId]);
        $scores = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        echo json_encode([
            'status' => $game['status'],
            'tiles' => $tiles,
            'scores' => $scores
        ]);
        exit();
    }

    private function validateApiCsrf() {
        $sentToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!validate_csrf_token($sentToken)) {
            http_response_code(403);
            echo json_encode(['error' => 'CSRF token validation failed.']);
            exit();
        }
    }

    public function revealTile($gameId) {
        $this->validateApiCsrf();
        $userId = $_SESSION['user']['id'];
        if ($_SESSION['user']['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['error' => 'Only students can reveal tiles.']);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $tileIndex = $data['tile_index'] ?? null;

        if (!is_numeric($tileIndex)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tile index is required.']);
            exit();
        }

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Lock the tile
            $stmt = $db->prepare("SELECT * FROM game_tiles WHERE game_id = :game_id AND tile_index = :tile_index FOR UPDATE");
            $stmt->execute(['game_id' => $gameId, 'tile_index' => $tileIndex]);
            $tile = $stmt->fetch();

            if (!$tile) {
                throw new \Exception("Tile not found.");
            }
            if ($tile['revealed']) {
                throw new \Exception("Tile already revealed.");
            }

            // Get user's group
            $stmt = $db->prepare("SELECT group_id FROM group_members WHERE user_id = :user_id AND group_id IN (SELECT group_id FROM group_scores WHERE game_id = :game_id)");
            $stmt->execute(['user_id' => $userId, 'game_id' => $gameId]);
            $groupId = $stmt->fetchColumn();
            if (!$groupId) {
                throw new \Exception("User is not in a participating group.");
            }

            // Update the tile
            $stmt = $db->prepare("UPDATE game_tiles SET revealed = 1, revealed_by_user_id = :user_id, revealed_at = NOW() WHERE id = :id");
            $stmt->execute(['user_id' => $userId, 'id' => $tile['id']]);

            $response = ['status' => 'ok', 'type' => $tile['type'], 'tile_index' => $tileIndex];

            // Handle penalties
            if ($tile['type'] === 'bomb' || $tile['type'] === 'knife') {
                $gameStmt = $db->prepare("SELECT bomb_penalty FROM games WHERE id = :id");
                $gameStmt->execute(['id' => $gameId]);
                $game = $gameStmt->fetch();

                if ($tile['type'] === 'bomb') {
                    $scoreChange = -1 * abs($game['bomb_penalty']);
                    $scoreStmt = $db->prepare("UPDATE group_scores SET score = score + :change WHERE game_id = :game_id AND group_id = :group_id");
                    $scoreStmt->execute(['change' => $scoreChange, 'game_id' => $gameId, 'group_id' => $groupId]);
                } elseif ($tile['type'] === 'knife') {
                    $scoreStmt = $db->prepare("UPDATE group_scores SET score = 0 WHERE game_id = :game_id AND group_id = :group_id");
                    $scoreStmt->execute(['game_id' => $gameId, 'group_id' => $groupId]);
                }
            } elseif ($tile['type'] === 'question') {
                // Fetch question data to send to the client
                $qStmt = $db->prepare(
                    "SELECT q.id, q.text, q.image_path, c.id as choice_id, c.text as choice_text
                     FROM questions q JOIN choices c ON q.id = c.question_id
                     WHERE q.id = :id"
                );
                $qStmt->execute(['id' => $tile['question_id']]);
                $qResults = $qStmt->fetchAll();

                $questionData = [
                    'id' => $qResults[0]['id'],
                    'text' => $qResults[0]['text'],
                    'image_path' => $qResults[0]['image_path'],
                    'choices' => array_map(fn($r) => ['id' => $r['choice_id'], 'text' => $r['choice_text']], $qResults)
                ];
                $response['data'] = $questionData;
            }

            $db->commit();
            echo json_encode($response);

        } catch (\Exception $e) {
            $db->rollBack();
            http_response_code(409); // Conflict
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    public function submitAnswer($gameId) {
        $this->validateApiCsrf();
        $userId = $_SESSION['user']['id'];
        if ($_SESSION['user']['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['error' => 'Only students can answer questions.']);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $tileId = $data['tile_id'] ?? null; // This is the HTML ID "tile-X"
        $choiceId = $data['choice_id'] ?? null;

        if (!$tileId || !$choiceId) {
            http_response_code(400);
            echo json_encode(['error' => 'Tile ID and Choice ID are required.']);
            exit();
        }

        $tileIndex = (int)str_replace('tile-', '', $tileId);

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Get tile and question info
            $stmt = $db->prepare("SELECT id, question_id FROM game_tiles WHERE game_id = :game_id AND tile_index = :tile_index");
            $stmt->execute(['game_id' => $gameId, 'tile_index' => $tileIndex]);
            $tile = $stmt->fetch();
            if (!$tile) throw new \Exception("Tile not found.");

            // Check if already answered
            $stmt = $db->prepare("SELECT id FROM answers WHERE tile_id = :tile_id");
            $stmt->execute(['tile_id' => $tile['id']]);
            if ($stmt->fetch()) throw new \Exception("Question already answered.");

            // Check if choice is correct
            $stmt = $db->prepare("SELECT is_correct FROM choices WHERE id = :id AND question_id = :question_id");
            $stmt->execute(['id' => $choiceId, 'question_id' => $tile['question_id']]);
            $isCorrect = (bool)$stmt->fetchColumn();

            // Get game scoring rules
            $stmt = $db->prepare("SELECT correct_points, wrong_points FROM games WHERE id = :id");
            $stmt->execute(['id' => $gameId]);
            $game = $stmt->fetch();

            $points = $isCorrect ? $game['correct_points'] : $game['wrong_points'];

            // Get user's group
            $stmt = $db->prepare("SELECT group_id FROM group_members WHERE user_id = :user_id AND group_id IN (SELECT group_id FROM group_scores WHERE game_id = :game_id)");
            $stmt->execute(['user_id' => $userId, 'game_id' => $gameId]);
            $groupId = $stmt->fetchColumn();

            // Update score
            $stmt = $db->prepare("UPDATE group_scores SET score = score + :points WHERE game_id = :game_id AND group_id = :group_id");
            $stmt->execute(['points' => $points, 'game_id' => $gameId, 'group_id' => $groupId]);

            // Log the answer
            $stmt = $db->prepare(
                "INSERT INTO answers (game_id, tile_id, user_id, group_id, question_id, choice_id, is_correct, points_awarded)
                 VALUES (:game_id, :tile_id, :user_id, :group_id, :question_id, :choice_id, :is_correct, :points)"
            );
            $stmt->execute([
                'game_id' => $gameId, 'tile_id' => $tile['id'], 'user_id' => $userId, 'group_id' => $groupId,
                'question_id' => $tile['question_id'], 'choice_id' => $choiceId,
                'is_correct' => $isCorrect, 'points' => $points
            ]);

            $db->commit();

            echo json_encode(['correct' => $isCorrect]);

        } catch (\Exception $e) {
            $db->rollBack();
            http_response_code(409);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }
}
