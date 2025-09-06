# Quiz Sweeper: A Gamified Multiplayer Educational Platform

## Abstract

This document details the design, architecture, and implementation of Quiz Sweeper, a web-based educational multiplayer game. The application is built using PHP and MySQL and leverages a Minesweeper-style game board to create an engaging and competitive learning environment. The system supports three distinct user roles—Admin, Teacher, and Student—each with a specific set of functionalities. This thesis covers the project's objectives, the technologies employed, the detailed software architecture including the database schema, key algorithms for gameplay, and a comprehensive walkthrough of the application's features. The document concludes with an evaluation of the system against its requirements and discusses potential avenues for future development.

---

## Chapter 1: Introduction

### 1.1 Problem Statement

Traditional learning and assessment methods, such as standard multiple-choice quizzes, can often be disengaging for students. There is a growing need for interactive and gamified educational tools that can foster a more dynamic and competitive learning environment. Such tools can increase student motivation, participation, and knowledge retention. The challenge is to create a platform that is not only fun for students but also provides educators with robust tools to create, manage, and monitor educational content and game sessions.

### 1.2 Project Goals and Objectives

The primary goal of this project is to develop "Quiz Sweeper," a secure, real-time multiplayer quiz application that blends the classic gameplay of Minesweeper with educational quizzes.

The key objectives are as follows:
*   **Develop a Secure, Role-Based System:** Implement a web application with three distinct user roles (Admin, Teacher, Student), each with secure authentication and specific access controls.
*   **Provide Robust Teacher Tools:** Empower teachers with a comprehensive dashboard to create and manage student groups, build custom quizzes with a variety of question types, and launch and configure live game sessions.
*   **Create an Engaging Student Experience:** Design an interactive and intuitive game board where students can compete in groups, answer questions, and experience the risk-reward mechanics of the game.
*   **Ensure System Reliability and Fairness:** Implement a server-authoritative game state and use transactional database operations to prevent race conditions and ensure fair play.
*   **Deliver a Production-Ready Application:** Produce well-structured, documented code, a complete database schema, and clear deployment instructions suitable for a standard web server environment.

### 1.3 Scope of the Project

The scope of this project includes the end-to-end development of the Quiz Sweeper application. This encompasses:
*   **Backend Development:** All server-side logic, including routing, database interaction, authentication, and API development using PHP.
*   **Database Design:** Creation of a complete and normalized MySQL database schema to store all application data.
*   **Frontend Development:** All client-side development, including user interfaces for all roles and the dynamic, AJAX-powered game board using vanilla JavaScript, HTML, and CSS.
*   **Feature Implementation:** The full set of features as described in the requirements, including user management, quiz and group management, game configuration, the gameplay loop, and results reporting.

The project does not include native mobile applications, real-time updates via WebSockets (relying on AJAX polling instead), or advanced analytics beyond the specified CSV export.

---

## Chapter 2: System Design & Architecture

### 2.1 Technology Stack

The Quiz Sweeper application is built using a standard, widely-adopted technology stack, chosen for its reliability, accessibility, and ease of deployment.
*   **Backend Language:** **PHP (7.4+/8.x)** was chosen for its strong web development capabilities and widespread support on hosting platforms.
*   **Database:** **MySQL** is used as the relational database management system to store all application data, from user credentials to game state.
*   **Web Server:** The application is designed for an **Apache** server environment (as part of a XAMPP/LAMP/WAMP stack), utilizing `mod_rewrite` for clean URL routing.
*   **Frontend Technologies:** The user interface is built with standard **HTML5**, **CSS3**, and **vanilla JavaScript**. Client-side logic, particularly for the game board, is powered by AJAX (`fetch` API) for asynchronous communication with the backend.

### 2.2 Software Architecture

The application is structured using a Model-View-Controller (MVC)-like pattern to ensure a clear separation of concerns, making the codebase modular, maintainable, and scalable.

*   **Model:** While not implemented as explicit classes for every table, the model layer is conceptually represented by the database schema (`database/migrations.sql`) and the direct PDO queries within the controllers. All data logic and interaction with the MySQL database reside here.
*   **View:** The view layer consists of PHP template files located in the `/views` directory. These files are responsible for the presentation of data and the user interface. They include HTML structure and minimal PHP logic for displaying dynamic data. Reusable UI components like the header and footer are separated into `partials`.
*   **Controller:** The controller layer, located in `/src/Controllers`, acts as the intermediary between the user, the views, and the data. Controllers receive user requests (via the Router), process input, interact with the database to fetch or modify data, and then load the appropriate view with the necessary data.

### 2.3 Core Components

*   **Router (`src/Core/Router.php`):** Maps incoming request URIs to specific controller methods. It supports dynamic route parameters (e.g., `/games/{id}/board`).
*   **Request (`src/Core/Request.php`):** A helper class to parse the request URI and determine the request method. It is designed to function correctly whether the application is in the web root or a subdirectory.
*   **Database (`src/Core/Database.php`):** A singleton class that manages the PDO database connection, ensuring only one connection is active per request.
*   **API Controller (`src/Controllers/ApiController.php`):** A dedicated controller for handling all AJAX requests from the frontend, primarily for gameplay. It returns data in JSON format.

### 2.4 Database Schema

The database is the backbone of the application, designed to be relational and efficient. The key tables include:
*   `users`: Stores user accounts and their roles.
*   `groups` & `group_members`: Manage the teacher-created student groups.
*   `quizzes`, `questions`, & `choices`: Store the educational content created by teachers.
*   `games`: Contains the configuration for each game instance.
*   `game_tiles`: Represents the state of every tile for every game, including its type and whether it has been revealed.
*   `group_scores`: Tracks the real-time score and protection status for each group in a game.
*   `answers`: Logs every answer submitted by students for auditing and results reporting.

*(A detailed schema is available in Appendix A.)*

### 2.5 Key Algorithms

#### 2.5.1 Game Board Generation

When a teacher starts a game, a server-side algorithm in `GameController@store` generates the board:
1.  It calculates the total number of tiles (`rows` x `cols`).
2.  It creates an array and populates it with the specified number of `bomb`, `knife`, and `bandaid` tiles.
3.  It calculates the remaining number of tiles, which will be `question` tiles.
4.  It fetches that number of question IDs from the selected quiz, shuffles them, and adds them to the tile array.
5.  The entire array of tiles is then shuffled to randomize the board layout completely.
6.  Finally, the shuffled tiles are inserted into the `game_tiles` table with their corresponding index (0 to `totalTiles - 1`).

#### 2.5.2 Transactional Tile Reveal

To prevent race conditions where two students in the same group might click the same tile simultaneously, the tile reveal process in `ApiController@revealTile` is transactional:
1.  A database transaction is initiated (`BEGIN`).
2.  The specific tile row in `game_tiles` is selected using `SELECT ... FOR UPDATE`. This places a lock on the row, preventing any other process from reading or writing to it until the transaction is complete.
3.  The `revealed` status of the tile is checked. If it is already true, the transaction is rolled back, and an error is returned.
4.  If the tile is not revealed, its state is updated, and the corresponding game logic (applying penalties, granting protection, etc.) is executed.
5.  The transaction is committed (`COMMIT`), releasing the lock.

This ensures that only the first request to reveal a tile will be successful, guaranteeing fairness.

---

## Chapter 3: Implementation Details

This chapter provides a detailed walkthrough of the core features and functionalities implemented for each user role within the Quiz Sweeper application.

### 3.1 Admin Role Features

The Admin user has the highest level of authority and is responsible for system-level management.

#### 3.1.1 User Management (CRUD)
The Admin dashboard provides a full suite of Create, Read, Update, and Delete (CRUD) operations for all users in the system.
*   **Read:** A paginated list of all users is displayed, showing their name, email, role, and registration date.
*   **Create:** Admins can create new users by providing a name, email, password, and assigning a role (Admin, Teacher, or Student).
*   **Update:** Admins can edit the details of any existing user. They can change a user's name, email, and role. For security, passwords can be reset but not viewed.
*   **Delete:** Admins can delete any user account, with a confirmation step to prevent accidental deletion. An admin cannot delete their own account.

#### 3.1.2 Global Settings Configuration
Admins can configure the global default values that are used when a teacher creates a new game. This allows for site-wide consistency. The configurable settings include:
*   Default board dimensions (`rows` and `cols`).
*   Default number of `bomb`, `knife`, and `bandaid` tiles.
*   Default scoring rules (points for correct/wrong answers, bomb penalty).

### 3.2 Teacher Role Features

The Teacher role is focused on content creation and game administration.

#### 3.2.1 Group Management
Teachers can organize their students into groups for games.
*   **Group CRUD:** Teachers can create, rename, and delete their own groups.
*   **Member Management:** Within a group, teachers can add existing students by searching for their email address and can remove students from the group at any time.

#### 3.2.2 Quiz Builder
This is a core feature for teachers, allowing them to create the educational content for the games.
*   **Quiz CRUD:** Teachers can create, edit, and delete quizzes. Each quiz has a title and a description.
*   **Question Management:** Within each quiz, a teacher can manage a list of questions.
*   **Question Editor:** The question editor allows for creating a question with a text body, an optional image upload, and up to five text-based choices. One choice must be flagged as the correct answer.

#### 3.2.3 Game Management
Teachers can launch and manage live game sessions.
*   **Game Configuration:** Teachers initiate a game from their dashboard, which leads to a configuration screen where they select a quiz, choose the participating groups, and set the game parameters (overriding the global defaults if desired).
*   **Game Lobby:** After creation, the game enters a "Lobby" state. The teacher is shown a lobby page where they can see the participating students and can choose to either launch the game or cancel it.
*   **Game Monitoring:** Once launched, the teacher can view the game board in a read-only state to monitor progress.
*   **Results and Export:** After a game is finished (either by resolving all tiles or by the teacher ending it manually), a final scoreboard is displayed. From this page, the teacher can download a CSV file containing a detailed log of all answers submitted during the game.

### 3.3 Student Role Features

The Student role is focused on the gameplay experience.

#### 3.3.1 Student Dashboard
Upon logging in, the student is presented with a dashboard that shows:
*   A list of currently active (`running`) games they are eligible to join.
*   A history of past (`finished`) games they have played, with links to the results.

#### 3.3.2 Gameplay Loop
*   **Joining a Game:** The student clicks "Join Game" from their dashboard to enter the main game board interface.
*   **The Board:** The student sees a grid of unrevealed, clickable tiles. The board state is kept synchronized for all players through AJAX polling, which requests the latest state from the server every few seconds.
*   **Revealing a Tile:** A student clicks a tile to reveal its contents. This action is sent to a server API endpoint.
    *   If the tile is a **Bomb, Knife, or Band-Aid**, the effect is immediately applied to the student's group on the server, and the updated score and board state are reflected on the next poll.
    *   If the tile is a **Question**, a modal window appears, displaying the question and its multiple-choice options.
*   **Answering a Question:** The student selects an answer and submits it. This sends another API request. The server evaluates the answer, updates the group's score, and logs the attempt. The API responds with whether the answer was correct and what the correct answer was.
*   **Visual Feedback:** The frontend JavaScript uses the API response to provide immediate feedback. The tile on the board turns green (correct) or red (incorrect). Inside the question modal, the choices are highlighted before the modal closes, showing the user the correct answer.

---

## Chapter 4: System Testing & Evaluation

### 4.1 Testing Strategy

The testing strategy for Quiz Sweeper involved a comprehensive manual, end-to-end testing process designed to validate the application from the perspective of each user role. The primary goal was to ensure that all features function as specified in the project requirements, the system is secure against common web vulnerabilities, and the user experience is intuitive and stable.

The process followed the detailed checklist provided in the `TESTING.md` document, which covers:
*   Authentication and Role-Based Access Control
*   Admin, Teacher, and Student feature sets
*   Core gameplay mechanics, including the transactional tile reveal
*   Security measures (CSRF, SQL Injection prevention)

### 4.2 Evaluation of Results

The application was tested in a local XAMPP for Windows environment, using the latest versions of Google Chrome and Mozilla Firefox. The testing confirmed that the system successfully meets all the "must-have" features and acceptance criteria outlined in the initial project specification.

*   **Functionality:** All CRUD operations for users, groups, and quizzes were successful. The game creation, lobby, and gameplay loop function as designed. The AJAX polling provides a near real-time experience, and the transactional nature of tile reveals prevents race conditions.
*   **Security:** Role-based access control correctly prevents unauthorized access to restricted pages. CSRF tokens are implemented on all forms and API endpoints. The use of PDO prepared statements mitigates the risk of SQL injection.
*   **Usability:** The user interface, following the CSS overhaul, is clean, responsive, and easy to navigate. The addition of the "How to Play" page provides necessary guidance for new users.

The system is deemed to be a successful implementation of the project goals.

---

## Chapter 5: Conclusion & Future Work

### 5.1 Conclusion

This project successfully delivered Quiz Sweeper, a fully functional, production-ready multiplayer educational game. The application provides a secure, role-based environment where educators can create engaging, gamified learning experiences for their students. The final product meets all core requirements, including a robust set of administrative tools, a flexible quiz and game creation system for teachers, and an interactive, real-time gameplay interface for students. The architecture is modular and maintainable, and the system is documented for future development and deployment.

### 5.2 Future Work

While the current implementation is complete, there are several avenues for future enhancement that could further improve the platform:

*   **Real-time Updates with WebSockets:** Replacing AJAX polling with WebSockets would provide true real-time updates, reducing server load and eliminating polling latency for a more responsive feel.
*   **Advanced Teacher Analytics:** A dedicated analytics dashboard for teachers could provide insights into question difficulty (e.g., a heatmap of which questions were answered incorrectly most often) and individual student performance across multiple games.
*   **Content Import/Export:** Allowing teachers to import and export quizzes in standard formats like CSV or JSON would make it easier to share and reuse educational content.
*   **Game Replay Mode:** A feature to allow students and teachers to review a completed game turn-by-turn could be a valuable learning tool.
*   **Localization:** Adding support for multiple languages (e.g., English and Tagalog as mentioned in the optional enhancements) would broaden the application's reach.

---

## Appendices

### Appendix A: Database Schema (SQL)

```sql
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
  `role_in_group` ENUM('member') DEFAULT 'member',
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
  `image_path` VARCHAR(255) NULL,
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
  `bandaid_count` INT NOT NULL DEFAULT 0,
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
  `tile_index` INT NOT NULL,
  `type` ENUM('question', 'bomb', 'knife', 'bandaid') NOT NULL,
  `question_id` INT NULL,
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
  `has_protection` BOOLEAN NOT NULL DEFAULT FALSE,
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
  `actor_id` INT NULL,
  `action` VARCHAR(255) NOT NULL,
  `target_id` INT NULL,
  `target_type` VARCHAR(50) NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`actor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);
```
