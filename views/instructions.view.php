<?php require('partials/header.view.php'); ?>

<h1>How to Play Quiz Sweeper</h1>
<p>Welcome to Quiz Sweeper! Here's everything you need to know to get started.</p>

<hr>

<h2>The Basics</h2>
<p>Quiz Sweeper is a multiplayer game where students, divided into groups, compete on a shared game board. The goal is to answer questions correctly to earn points for your group while avoiding penalties. The group with the highest score at the end wins!</p>

<h3>The Game Board</h3>
<p>The game board is a grid of hidden tiles. Each tile conceals one of four things:</p>
<ul>
    <li><strong>Question (❓):</strong> The most common tile. Revealing this will open a multiple-choice question. Answer correctly to earn points for your group!</li>
    <li><strong>Bomb (💣):</strong> Unlucky! Revealing a bomb will deduct a significant number of points from your group's score.</li>
    <li><strong>Knife (🔪):</strong> Even unluckier! A knife will reset your group's score to zero.</li>
    <li><strong>Band-Aid (🩹):</strong> Lucky find! A band-aid gives your group a one-time protection. The next time anyone in your group reveals a Bomb or a Knife, the band-aid will be used up, and your score will be protected from the penalty. You can see if your group has protection by the shield icon (🛡️) next to your score.</li>
</ul>

<hr>

<h2>Roles & Process</h2>

<h3>1. The Admin</h3>
<p>The Admin has the highest level of control.</p>
<ul>
    <li><strong>User Management:</strong> The Admin is responsible for creating, editing, and deleting all user accounts. They must create Teacher accounts so games can be made.</li>
    <li><strong>Global Settings:</strong> The Admin can set the default values for game creation (e.g., default board size, default points for a correct answer) to ensure consistency.</li>
</ul>

<h3>2. The Teacher</h3>
<p>The Teacher is the game master. They set up the quizzes and run the games.</p>
<ol>
    <li><strong>Create Quizzes:</strong> From the Teacher Dashboard, go to "My Quizzes". Here you can create new quizzes, each with its own set of questions. The quiz builder allows you to write a question, provide up to 5 choices, and mark the correct one. You can even add an image to a question.</li>
    <li><strong>Create Groups:</strong> Go to "My Groups" to create student groups. You can then add registered students to these groups by searching for their email address.</li>
    <li><strong>Start a Game:</strong> Once you have a quiz and at least one group, click "Start New Game". Here you will configure the game by selecting a quiz, choosing which groups will participate, and setting the rules (board size, number of bombs, knives, band-aids, and scoring).</li>
    <li><strong>The Lobby:</strong> After creating the game, you will be taken to the Lobby. Here you can see the students in each group and wait for them to join. When you're ready, click "Launch Game" to begin!</li>
    <li><strong>Monitor & End:</strong> As the teacher, you can view the game board in real-time to see progress. You can end the game at any time from this view.</li>
    <li><strong>View Results:</strong> After the game ends, you will be taken to the results page, where you can see the final ranked scoreboard and export a detailed CSV file of all the answers.</li>
</ol>

<h3>3. The Student</h3>
<p>The Student is the player.</p>
<ol>
    <li><strong>Join a Game:</strong> When you log in, your dashboard will show any active games your teacher has started for one of your groups. Click "Join Game" to enter the game board.</li>
    <li><strong>Play the Game:</strong> Click on any unrevealed tile on the board.
        <ul>
            <li>If it's a **Question**, a modal will pop up. Read the question, select your answer, and submit. You'll get immediate feedback in the modal, and the tile on the board will turn green (correct) or red (incorrect).</li>
            <li>If you hit a **Bomb**, **Knife**, or **Band-Aid**, the effect is applied to your group's score instantly.</li>
        </ul>
    </li>
    <li><strong>Teamwork:</strong> The board is shared with everyone in the game. Your score is your group's score. Work with your group members to find all the questions and avoid the penalties!</li>
    <li><strong>View Results:</strong> When the game is over, you'll see the final scoreboard. You can also view results from past games on your dashboard.</li>
</ol>


<?php require('partials/footer.view.php'); ?>
