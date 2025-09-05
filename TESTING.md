# Test Plan & Acceptance Criteria

This document outlines the test plan and a detailed checklist of acceptance criteria to verify that the Quiz Sweeper application meets all specified requirements.

## Test Plan

**Testing Strategy:** Manual, end-to-end testing will be performed for all user stories and features. The focus is on ensuring the application is functional, secure, and meets the user experience requirements from the perspective of each role.

**Roles to Test:**
*   Admin
*   Teacher
*   Student

**Browsers to Test:**
*   Latest version of Chrome
*   Latest version of Firefox
*   Latest version of Edge

**Testing Environment:**
*   A local XAMPP/LAMP server environment as described in the `README.md`.

## Acceptance Criteria Checklist

### Authentication & Roles
- [ ] **Auth:** Users can log in with correct credentials.
- [ ] **Auth:** Users cannot log in with incorrect credentials.
- [ ] **Auth:** Passwords are securely hashed in the `users` table (`password_hash`).
- [ ] **Auth:** Sessions are used to maintain login state.
- [ ] **Roles:** Users are correctly assigned 'admin', 'teacher', or 'student' roles.
- [ ] **ACL:** Users can only access routes/pages appropriate for their role (e.g., student cannot access `/admin`).
- [ ] **CSRF:** All forms (login, user creation, game config, etc.) include and validate a CSRF token.
- [ ] **Logout:** Users can log out, successfully destroying the session.

### Admin Interface
- [ ] **User Mgmt:** Admin can view a list of all users.
- [ ] **User Mgmt:** Admin can create a new user with any role.
- [ ] **User Mgmt:** Admin can edit an existing user's details (name, email, role).
- [ ] **User Mgmt:** Admin can delete a user.
- [ ] **User Mgmt:** Admin cannot delete their own account.
- [ ] **Settings:** Admin can view and update global default settings (board size, scoring).
- [ ] **Logs:** Admin can view the audit/game logs.

### Teacher Dashboard
- [ ] **Group Mgmt:** Teacher can create a new group.
- [ ] **Group Mgmt:** Teacher can edit a group's name.
- [ ] **Group Mgmt:** Teacher can delete a group.
- [ ] **Group Mgmt:** Deleting a group also removes its members from the junction table.
- [ ] **Members:** Teacher can view members of a group.
- [ ] **Members:** Teacher can add an existing student to a group by email.
- [ ] **Members:** Teacher can remove a student from a group.
- [ ] **Quiz Mgmt:** Teacher can create a new quiz (title, description).
- [ ] **Quiz Mgmt:** Teacher can edit a quiz's details.
- [ ] **Quiz Mgmt:** Teacher can delete a quiz.
- [ ] **Quiz Builder:** Teacher can view questions for a quiz.
- [ ] **Quiz Builder:** Teacher can add a new question with text.
- [ ] **Quiz Builder:** Teacher can upload an optional image for a question.
- [ ] **Quiz Builder:** Teacher can add up to 5 choices for a question.
- [ ] **Quiz Builder:** Teacher can flag exactly one choice as correct.
- [ ] **Quiz Builder:** Teacher can edit an existing question and its choices.
- [ ] **Quiz Builder:** Teacher can delete a question.

### Game Setup & Results
- [ ] **Game Start:** Teacher can access the "Start Game" configuration page.
- [ ] **Game Start:** The form is pre-filled with global default settings.
- [ ] **Game Start:** Teacher can select a quiz and one or more groups.
- [ ] **Game Start:** Teacher can customize board size, tile counts, and scoring rules.
- [ ] **Game Start:** Validation prevents starting a game if `bombs + knives >= rows * cols`.
- [ ] **Game Start:** Validation prevents starting a game if the quiz has too few questions.
- [ ] **Lobby:** After creating a game, teacher is redirected to a lobby page.
- [ ] **Lobby:** Lobby page shows the participating groups and students.
- [ ] **Lobby:** Teacher can click "Launch Game" to start the game.
- [ ] **Lobby:** Teacher can click "Cancel Game" to cancel a game in the lobby.
- [ ] **Results:** After a game is finished, a results page is shown with a ranked scoreboard.
- [ ] **CSV Export:** Teacher can click a link on the results page to download a CSV of game answers.
- [ ] **CSV Export:** The downloaded CSV file contains the correct headers and data.

### Student Dashboard & Gameplay
- [ ] **Dashboard:** Student can see a list of currently `running` games they are eligible for.
- [ ] **Dashboard:** Student can see a list of `finished` games they participated in.
- [ ] **Dashboard:** Student can join an active game, taking them to the game board.
- [ ] **Game Board:** The board is rendered as a grid of clickable, unrevealed tiles.
- [ ] **Tile Reveal (Transaction):** Clicking a tile successfully reveals it. Concurrent clicks on the same tile only result in one successful reveal.
- [ ] **Tile Reveal (Bomb):** Revealing a bomb tile deducts the configured points from the group score and shows a bomb icon.
- [ ] **Tile Reveal (Knife):** Revealing a knife tile sets the group score to zero and shows a knife icon.
- [ ] **Tile Reveal (Question):** Revealing a question tile opens a modal with the question and choices.
- [ ] **Question Modal:** Submitting an answer closes the modal.
- [ ] **Answer (Correct):** A correct answer awards points and turns the tile green.
- [ ] **Answer (Incorrect):** An incorrect answer applies `wrong_points` and turns the tile red.
- [ ] **State:** Revealed tiles are disabled and cannot be re-answered.
- [ ] **State:** The game state (tile reveals, scores) updates for all players within a few seconds (via polling).
- [ ] **Game End:** When the game status becomes `finished`, students are redirected to the results page.

### Security & Reliability
- [ ] **SQL Injection:** All database queries use prepared statements (PDO).
- [ ] **XSS:** All user-generated output is escaped using `htmlspecialchars()`.
- [ ] **Race Conditions:** The tile reveal process is transactional (`SELECT ... FOR UPDATE`).
- [ ] **ACL:** A user cannot affect a game they are not a part of (e.g., cannot reveal tiles in another game).
- [ ] **ACL:** A teacher cannot manage another teacher's quizzes or groups.
