-- Quiz Sweeper DB Migrations

-- Users Table: Stores admin, teacher, and student accounts
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'teacher', 'student') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Groups Table: Teachers can create groups of students
CREATE TABLE IF NOT EXISTS `groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Group Members Table: Junction table for users and groups
CREATE TABLE IF NOT EXISTS `group_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role_in_group` ENUM('member') DEFAULT 'member', -- Could be extended later (e.g., 'leader')
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_member` (`group_id`, `user_id`),
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Quizzes Table: Stores quizzes created by teachers
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Questions Table: Stores questions for each quiz
CREATE TABLE IF NOT EXISTS `questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `quiz_id` INT NOT NULL,
  `text` TEXT NOT NULL,
  `image_path` VARCHAR(255) NULL, -- Path to an optional uploaded image
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
);

-- Choices Table: Stores the multiple-choice options for each question
CREATE TABLE IF NOT EXISTS `choices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT NOT NULL,
  `text` VARCHAR(255) NOT NULL,
  `is_correct` BOOLEAN NOT NULL DEFAULT FALSE,
  FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
);

-- Games Table: Stores information about each game instance
CREATE TABLE IF NOT EXISTS `games` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `quiz_id` INT NOT NULL,
  `teacher_id` INT NOT NULL,
  `rows` INT NOT NULL,
  `cols` INT NOT NULL,
  `bomb_count` INT NOT NULL,
  `knife_count` INT NOT NULL,
  `correct_points` INT NOT NULL DEFAULT 10,
  `wrong_points` INT NOT NULL DEFAULT 0,
  `bomb_penalty` INT NOT NULL DEFAULT 20,
  `status` ENUM('lobby', 'running', 'finished', 'cancelled') NOT NULL DEFAULT 'lobby',
  `started_at` TIMESTAMP NULL,
  `finished_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Game Tiles Table: Represents the state of each tile on the board for a game
CREATE TABLE IF NOT EXISTS `game_tiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT NOT NULL,
  `tile_index` INT NOT NULL, -- e.g., 0 to (rows*cols - 1)
  `type` ENUM('question', 'bomb', 'knife') NOT NULL,
  `question_id` INT NULL, -- Null if type is not 'question'
  `revealed` BOOLEAN NOT NULL DEFAULT FALSE,
  `revealed_by_user_id` INT NULL,
  `revealed_at` TIMESTAMP NULL,
  UNIQUE KEY `unique_tile` (`game_id`, `tile_index`),
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`revealed_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Group Scores Table: Tracks the score for each group in a game
CREATE TABLE IF NOT EXISTS `group_scores` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT NOT NULL,
  `group_id` INT NOT NULL,
  `score` INT NOT NULL DEFAULT 0,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_group_score` (`game_id`, `group_id`),
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE
);

-- Answers Table: Logs each answer attempt by a user
CREATE TABLE IF NOT EXISTS `answers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT NOT NULL,
  `tile_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `group_id` INT NOT NULL,
  `question_id` INT NULL,
  `choice_id` INT NULL,
  `is_correct` BOOLEAN NULL,
  `points_awarded` INT NOT NULL,
  `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tile_id`) REFERENCES `game_tiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`choice_id`) REFERENCES `choices`(`id`) ON DELETE SET NULL
);

-- Audit Logs Table (Optional but good practice)
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `actor_id` INT NULL, -- Can be null for system actions
  `action` VARCHAR(255) NOT NULL,
  `target_id` INT NULL,
  `target_type` VARCHAR(50) NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`actor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Add Indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_groups_teacher_id ON groups(teacher_id);
CREATE INDEX idx_quizzes_teacher_id ON quizzes(teacher_id);
CREATE INDEX idx_questions_quiz_id ON questions(quiz_id);
CREATE INDEX idx_choices_question_id ON choices(question_id);
CREATE INDEX idx_games_teacher_id ON games(teacher_id);
CREATE INDEX idx_games_quiz_id ON games(quiz_id);
CREATE INDEX idx_answers_user_id ON answers(user_id);
CREATE INDEX idx_answers_game_id ON answers(game_id);

-- Settings Table: For global application configuration by admins
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(255) NOT NULL UNIQUE,
  `setting_value` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
