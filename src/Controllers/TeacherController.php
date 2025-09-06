<?php

namespace App\Controllers;

use App\Core\Database;

/**
 * Handles all teacher-specific functionality, like managing quizzes and groups.
 */
class TeacherController extends Controller {

    /**
     * Ensures the user is a teacher before any action.
     */
    public function __construct() {
        $this->isTeacher();
    }

    /**
     * The main entry point for the /teacher route, redirects to the groups list.
     */
    public function index() {
        header('Location: /teacher/groups');
        exit();
    }

    // --- Group Management ---

    /**
     * Displays the list of the teacher's groups.
     * @return mixed
     */
    public function groupsIndex() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT g.id, g.name, g.created_at, COUNT(gm.id) as member_count
             FROM groups g
             LEFT JOIN group_members gm ON g.id = gm.group_id
             WHERE g.teacher_id = :teacher_id
             GROUP BY g.id
             ORDER BY g.created_at DESC"
        );
        $stmt->execute(['teacher_id' => $_SESSION['user']['id']]);
        $groups = $stmt->fetchAll();

        return view('teacher/groups/index', [
            'groups' => $groups,
            'title' => 'My Groups'
        ]);
    }

    /**
     * Displays the form to create a new group.
     * @return mixed
     */
    public function groupsCreate() {
        return view('teacher/groups/create', ['title' => 'Create Group']);
    }

    /**
     * Processes the creation of a new group.
     */
    public function groupsStore() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['name'])) {
            die('Group name is required.');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO groups (name, teacher_id) VALUES (:name, :teacher_id)"
        );
        $stmt->execute([
            'name' => $_POST['name'],
            'teacher_id' => $_SESSION['user']['id']
        ]);

        header('Location: /teacher/groups');
        exit();
    }

    /**
     * Displays the form to edit a group's name.
     * @return mixed
     */
    public function groupsEdit() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name FROM groups WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $_GET['id'], 'teacher_id' => $_SESSION['user']['id']]);
        $group = $stmt->fetch();

        if (!$group) {
            die('Group not found or you do not have permission to edit it.');
        }

        return view('teacher/groups/edit', [
            'group' => $group,
            'title' => 'Edit Group'
        ]);
    }

    /**
     * Processes the update of a group's name.
     */
    public function groupsUpdate() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['name']) || empty($_POST['id'])) {
            die('Group name and ID are required.');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "UPDATE groups SET name = :name WHERE id = :id AND teacher_id = :teacher_id"
        );
        $stmt->execute([
            'name' => $_POST['name'],
            'id' => $_POST['id'],
            'teacher_id' => $_SESSION['user']['id']
        ]);

        header('Location: /teacher/groups');
        exit();
    }

    /**
     * Processes the deletion of a group.
     */
    public function groupsDestroy() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['id'])) {
            die('Group ID is required.');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "DELETE FROM groups WHERE id = :id AND teacher_id = :teacher_id"
        );
        $stmt->execute([
            'id' => $_POST['id'],
            'teacher_id' => $_SESSION['user']['id']
        ]);

        header('Location: /teacher/groups');
        exit();
    }

    /**
     * Displays the page to view and manage a single group's members.
     * @return mixed
     */
    public function viewGroup() {
        if (!isset($_GET['id'])) {
            die('Group ID is required.');
        }

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id, name FROM groups WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $_GET['id'], 'teacher_id' => $_SESSION['user']['id']]);
        $group = $stmt->fetch();

        if (!$group) {
            die('Group not found or you do not have permission to view it.');
        }

        $stmt = $db->prepare(
            "SELECT u.id, u.name, u.email
             FROM users u
             JOIN group_members gm ON u.id = gm.user_id
             WHERE gm.group_id = :group_id"
        );
        $stmt->execute(['group_id' => $group['id']]);
        $members = $stmt->fetchAll();

        return view('teacher/groups/view', [
            'group' => $group,
            'members' => $members,
            'title' => 'Manage Group'
        ]);
    }

    /**
     * Processes adding a new member to a group.
     */
    public function addMember() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['group_id']) || empty($_POST['email'])) {
            die('Group ID and email are required.');
        }

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND role = 'student'");
        $stmt->execute(['email' => $_POST['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            die('No student account found with that email address.');
        }

        $stmt = $db->prepare("SELECT id FROM groups WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $_POST['group_id'], 'teacher_id' => $_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            die('You do not have permission to modify this group.');
        }

        $stmt = $db->prepare(
            "INSERT IGNORE INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)"
        );
        $stmt->execute(['group_id' => $_POST['group_id'], 'user_id' => $user['id']]);

        header('Location: /teacher/groups/view?id=' . $_POST['group_id']);
        exit();
    }

    /**
     * Processes removing a member from a group.
     */
    public function removeMember() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['group_id']) || empty($_POST['user_id'])) {
            die('Group ID and User ID are required.');
        }

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id FROM groups WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $_POST['group_id'], 'teacher_id' => $_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            die('You do not have permission to modify this group.');
        }

        $stmt = $db->prepare(
            "DELETE FROM group_members WHERE group_id = :group_id AND user_id = :user_id"
        );
        $stmt->execute(['group_id' => $_POST['group_id'], 'user_id' => $_POST['user_id']]);

        header('Location: /teacher/groups/view?id=' . $_POST['group_id']);
        exit();
    }

    // --- Quiz Management ---

    /**
     * Displays the list of the teacher's quizzes.
     * @return mixed
     */
    public function quizzesIndex() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT q.id, q.title, q.description, q.created_at, COUNT(qu.id) as question_count
             FROM quizzes q
             LEFT JOIN questions qu ON q.id = qu.quiz_id
             WHERE q.teacher_id = :teacher_id
             GROUP BY q.id
             ORDER BY q.created_at DESC"
        );
        $stmt->execute(['teacher_id' => $_SESSION['user']['id']]);
        $quizzes = $stmt->fetchAll();

        return view('teacher/quizzes/index', [
            'quizzes' => $quizzes,
            'title' => 'My Quizzes'
        ]);
    }

    /**
     * Displays the form to create a new quiz.
     * @return mixed
     */
    public function quizzesCreate() {
        return view('teacher/quizzes/create', ['title' => 'Create Quiz']);
    }

    /**
     * Processes the creation of a new quiz.
     */
    public function quizzesStore() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['title'])) {
            die('Quiz title is required.');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO quizzes (title, description, teacher_id) VALUES (:title, :description, :teacher_id)"
        );
        $stmt->execute([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'teacher_id' => $_SESSION['user']['id']
        ]);

        $quizId = $db->lastInsertId();

        header('Location: /teacher/quizzes/' . $quizId . '/questions');
        exit();
    }

    /**
     * Displays the form to edit a quiz's details.
     * @return mixed
     */
    public function quizzesEdit() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, title, description FROM quizzes WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $_GET['id'], 'teacher_id' => $_SESSION['user']['id']]);
        $quiz = $stmt->fetch();

        if (!$quiz) {
            die('Quiz not found or you do not have permission to edit it.');
        }

        return view('teacher/quizzes/edit', [
            'quiz' => $quiz,
            'title' => 'Edit Quiz'
        ]);
    }

    /**
     * Processes the update of a quiz's details.
     */
    public function quizzesUpdate() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['title']) || empty($_POST['id'])) {
            die('Quiz title and ID are required.');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "UPDATE quizzes SET title = :title, description = :description WHERE id = :id AND teacher_id = :teacher_id"
        );
        $stmt->execute([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'id' => $_POST['id'],
            'teacher_id' => $_SESSION['user']['id']
        ]);

        header('Location: /teacher/quizzes');
        exit();
    }

    /**
     * Processes the deletion of a quiz.
     */
    public function quizzesDestroy() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['id'])) {
            die('Quiz ID is required.');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "DELETE FROM quizzes WHERE id = :id AND teacher_id = :teacher_id"
        );
        $stmt->execute([
            'id' => $_POST['id'],
            'teacher_id' => $_SESSION['user']['id']
        ]);

        header('Location: /teacher/quizzes');
        exit();
    }
}
