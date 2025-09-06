-- Quiz Sweeper Seed Data

-- Note: Passwords should be generated using password_hash() in PHP.
-- For this seed, we use a placeholder. The password for all users is 'password'.
-- Admin: admin@quizzes.com
-- Teacher: teacher@quizzes.com
-- Student 1: student1@quizzes.com
-- Student 2: student2@quizzes.com

-- 1. Create Users
-- Hashed password for 'password' is '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`) VALUES
(1, 'Admin User', 'admin@quizzes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'Teacher User', 'teacher@quizzes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(3, 'Student One', 'student1@quizzes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(4, 'Student Two', 'student2@quizzes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- 2. Create a Quiz for the Teacher
INSERT INTO `quizzes` (`id`, `teacher_id`, `title`, `description`) VALUES
(1, 2, 'PHP Basics Quiz', 'A fun quiz to test your basic knowledge of PHP.');

-- 3. Add Questions to the Quiz
INSERT INTO `questions` (`id`, `quiz_id`, `text`) VALUES
(1, 1, 'What does PHP stand for?'),
(2, 1, 'Which function is used to print output to the screen?'),
(3, 1, 'How do you start a session in PHP?');

-- 4. Add Choices for each Question
-- Choices for Question 1
INSERT INTO `choices` (`question_id`, `text`, `is_correct`) VALUES
(1, 'Personal Home Page', FALSE),
(1, 'PHP: Hypertext Preprocessor', TRUE),
(1, 'Private Home Page', FALSE),
(1, 'Programmed Hyperlink Preprocessor', FALSE),
(1, 'Pretty Home Page', FALSE);

-- Choices for Question 2
INSERT INTO `choices` (`question_id`, `text`, `is_correct`) VALUES
(2, 'print()', FALSE),
(2, 'console.log()', FALSE),
(2, 'echo', TRUE),
(2, 'write()', FALSE),
(2, 'display()', FALSE);

-- Choices for Question 3
INSERT INTO `choices` (`question_id`, `text`, `is_correct`) VALUES
(3, 'session_begin()', FALSE),
(3, 'start_session()', FALSE),
(3, 'init_session()', FALSE),
(3, 'session_start()', TRUE),
(3, 'new Session()', FALSE);

-- 5. Create a Group and Add Students
INSERT INTO `groups` (`id`, `teacher_id`, `name`) VALUES
(1, 2, 'PHP Beginners Group');

INSERT INTO `group_members` (`group_id`, `user_id`) VALUES
(1, 3),
(1, 4);

-- 6. Create a Sample Game (in 'lobby' state)
INSERT INTO `games` (`id`, `quiz_id`, `teacher_id`, `rows`, `cols`, `bomb_count`, `knife_count`, `correct_points`, `wrong_points`, `bomb_penalty`, `status`) VALUES
(1, 1, 2, 5, 5, 3, 2, 10, 0, 5, 'lobby');

-- 7. Add Default Global Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('default_board_rows', '10'),
('default_board_cols', '10'),
('default_bomb_count', '5'),
('default_knife_count', '3'),
('default_correct_points', '10'),
('default_wrong_points', '0'),
('default_bomb_penalty', '20'),
('default_bandaid_count', '2');
