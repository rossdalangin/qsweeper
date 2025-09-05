# Quiz Sweeper

Quiz Sweeper is a PHP + MySQL multiplayer quiz game that uses a Minesweeper-like board. Teachers create quizzes and groups, start games with configurable penalty tiles (bombs/knives), and students click tiles to answer questions — scores are aggregated per group and displayed at the end. The app includes Admin, Teacher, and Student roles, secure auth, CSV exports, and a responsive UI.

## Features

*   **Role-Based Access Control:** Secure authentication system for Admins, Teachers, and Students.
*   **Admin Dashboard:** Manage all users, configure global game defaults, and view audit logs.
*   **Teacher Dashboard:**
    *   Create and manage student groups.
    *   Full-featured quiz builder with support for questions, choices, and image uploads.
    *   Launch games with custom configurations (board size, tile counts, scoring rules).
    *   View final game results and export them as a CSV file.
*   **Student Dashboard & Gameplay:**
    *   View and join active games.
    *   Interactive, real-time game board.
    *   Click tiles to answer questions or encounter penalties.
    *   Live score updates via AJAX polling.

## Tech Stack

*   **Backend:** PHP (7.4+/8.x)
*   **Database:** MySQL
*   **Frontend:** HTML, CSS, Vanilla JavaScript (with AJAX for dynamic updates)
*   **Server:** Apache (or equivalent like Nginx) with `mod_rewrite` enabled.

## Project Structure

```
/
├── database/         # SQL migration and seed files
├── public/           # Public web root, contains index.php, assets (CSS, JS)
│   ├── css/
│   ├── js/
│   └── uploads/      # User-uploaded images for questions
├── src/              # Main application source code (MVC-like structure)
│   ├── Controllers/
│   ├── Core/         # Core classes (Router, Database, etc.)
│   └── Models/       # (Optional, can be expanded)
├── views/            # PHP view templates
├── config.example.php # Example configuration file
├── .htaccess         # Routes all requests to index.php
└── README.md
```

## Installation and Setup (XAMPP / WAMP / LAMP)

1.  **Clone the Repository**
    Clone this project into your web server's root directory (e.g., `C:\xampp\htdocs\quiz-sweeper` or `/var/www/html/quiz-sweeper`).

2.  **Create the Database**
    *   Open your MySQL database management tool (like phpMyAdmin).
    *   Create a new, empty database. For example, name it `quiz_sweeper`.

3.  **Import Database Schema & Data**
    *   In phpMyAdmin, select your new database.
    *   Go to the "Import" tab.
    *   First, import the schema by uploading and running `database/migrations.sql`. This will create all the necessary tables.
    *   Next, import the seed data by uploading and running `database/seed.sql`. This will populate the database with sample users and content.

4.  **Configure the Application**
    *   In the project's root directory, find `config.example.php` and create a copy named `config.php`.
    *   Open `config.php` and edit the following values to match your environment:
        ```php
        // Database Configuration
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'quiz_sweeper'); // The name of the database you created
        define('DB_USER', 'root');         // Your MySQL username
        define('DB_PASS', '');             // Your MySQL password

        // Application Configuration
        // The public URL to your project's public folder
        define('APP_URL', 'http://localhost/quiz-sweeper/public');
        ```

5.  **Configure the Web Server (Document Root)**
    *   **Recommended:** For best security, configure your Apache virtual host to point the `DocumentRoot` directly to the project's `/public` directory. This prevents direct web access to source code and configuration files.
    *   **Alternative (.htaccess):** If you cannot change the document root, the `.htaccess` file in the `/public` directory should handle routing correctly, provided your Apache server has `mod_rewrite` enabled and allows `.htaccess` overrides (`AllowOverride All`).

6.  **Ready to Go!**
    *   Navigate to the `APP_URL` you configured (e.g., `http://localhost/quiz-sweeper/public`).
    *   You should see the application's home page.

## How to Use (Sample Data)

The seed data creates four users. The password for all of them is `password`.

*   **Admin:** `admin@quizzes.com`
*   **Teacher:** `teacher@quizzes.com`
*   **Student 1:** `student1@quizzes.com`
*   **Student 2:** `student2@quizzes.com`

**Typical Workflow:**
1.  Log in as the **Teacher**.
2.  Navigate to "My Quizzes" and create a new quiz or manage the existing "PHP Basics Quiz".
3.  Navigate to "My Groups" and manage the sample "PHP Beginners Group", ensuring the two students are members.
4.  From the dashboard, click "Start New Game".
5.  Configure the game (select the quiz, groups, board size, etc.) and click "Create Game & Open Lobby".
6.  In a separate browser or incognito window, log in as **Student One**.
7.  From the student dashboard, join the active game.
8.  As the student, click on tiles to play. As the teacher, you can monitor the game board from your view.

## Test Plan

For a detailed test plan and acceptance criteria checklist, please see [TESTING.md](TESTING.md).
