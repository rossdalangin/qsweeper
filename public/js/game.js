document.addEventListener('DOMContentLoaded', () => {
    const gameBoard = document.getElementById('game-board');
    if (!gameBoard) return;

    const gameId = window.location.pathname.match(/games\/(\d+)\/board/)[1];
    const pollingInterval = 3000; // 3 seconds

    const updateUI = (state) => {
        // Update game status
        const statusEl = document.getElementById('game-status');
        if (statusEl) {
            statusEl.textContent = state.status.toUpperCase();
        }
        if (state.status === 'finished') {
            // Redirect to results page if game is over
            window.location.href = `/games/${gameId}/results`;
            return;
        }

        // Update scores and protection status
        for (const [groupId, groupData] of Object.entries(state.scores)) {
            const scoreEl = document.getElementById(`score-${groupId}`);
            if (scoreEl) {
                scoreEl.textContent = groupData.score;
            }
            const protectionEl = document.getElementById(`protection-${groupId}`);
            if (protectionEl) {
                protectionEl.textContent = groupData.has_protection ? '🛡️' : '';
            }
        }

        // Update tiles
        state.tiles.forEach(tileData => {
            const tileEl = document.getElementById(`tile-${tileData.tile_index}`);
            if (tileEl && tileData.revealed && tileEl.tagName === 'BUTTON') {
                // This tile has been revealed by someone, replace the button with a div
                const newDiv = document.createElement('div');
                newDiv.id = tileEl.id;
                newDiv.className = 'tile revealed';

                let content = '';
                if (tileData.type === 'bomb') {
                    newDiv.classList.add('bomb');
                    content = '💣';
                } else if (tileData.type === 'knife') {
                    newDiv.classList.add('knife');
                    content = '🔪';
                } else if (tileData.type === 'bandaid') {
                    newDiv.classList.add('bandaid');
                    content = '🩹';
                } else if (tileData.type === 'question') {
                    newDiv.classList.add('question');
                    content = '❓';
                }
                newDiv.innerHTML = content;
                tileEl.replaceWith(newDiv);
            }
        });
    };

    const fetchGameState = async () => {
        try {
            const response = await fetch(`/api/games/${gameId}/state`);
            if (!response.ok) {
                console.error('Failed to fetch game state');
                return;
            }
            const state = await response.json();
            updateUI(state);
        } catch (error) {
            console.error('Error fetching game state:', error);
        }
    };

    const handleTileClick = async (event) => {
        if (!event.target.matches('button.tile')) return;

        const tileButton = event.target;
        const tileIndex = tileButton.dataset.index;
        tileButton.disabled = true; // Prevent double clicks

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/api/games/${gameId}/reveal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ tile_index: tileIndex })
            });

            if (!response.ok) {
                const errorData = await response.json();
                alert(`Error: ${errorData.error}`);
                tileButton.disabled = false; // Re-enable on error
                return;
            }

            const result = await response.json();

            if (result.message) {
                alert(result.message);
            }

            // If it's a question, show the modal. Otherwise, the poller will handle the UI update.
            if (result.type === 'question') {
                showQuestionModal(result.data, tileButton.id);
            }
            // Immediate UI update for the revealed tile, even before polling
            fetchGameState();

        } catch (error) {
            console.error('Error revealing tile:', error);
            tileButton.disabled = false;
        }
    };

    const showQuestionModal = (questionData, tileId) => {
        document.getElementById('question-text').textContent = questionData.text;

        const imageContainer = document.getElementById('question-image-container');
        imageContainer.innerHTML = '';
        if (questionData.image_path) {
            const img = document.createElement('img');
            img.src = `/${questionData.image_path}`;
            img.style.maxWidth = '100%';
            imageContainer.appendChild(img);
        }

        const choicesContainer = document.getElementById('choices-container');
        choicesContainer.innerHTML = '';
        questionData.choices.forEach((choice, index) => {
            const div = document.createElement('div');
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'choice_id';
            radio.id = `choice-${choice.id}`;
            radio.value = choice.id;
            if (index === 0) radio.checked = true;

            const label = document.createElement('label');
            label.htmlFor = `choice-${choice.id}`;
            label.textContent = choice.text;

            div.appendChild(radio);
            div.appendChild(label);
            choicesContainer.appendChild(div);
        });

        document.getElementById('modal-tile-id').value = tileId;
        document.getElementById('question-modal').style.display = 'flex';
    };

    const handleAnswerSubmit = async (event) => {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const choiceId = formData.get('choice_id');
        const tileId = formData.get('tile_id');

        if (!choiceId) {
            alert('Please select an answer.');
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/api/games/${gameId}/answer`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ tile_id: tileId, choice_id: choiceId })
            });

            if (!response.ok) {
                const errorData = await response.json();
                alert(`Error: ${errorData.error}`);
                return;
            }

            const result = await response.json();

            // Highlight correct/incorrect choices
            const correctLabel = document.querySelector(`label[for="choice-${result.correctChoiceId}"]`);
            if (correctLabel) {
                correctLabel.classList.add('correct-answer');
            }
            if (!result.correct) {
                const incorrectLabel = document.querySelector(`label[for="choice-${choiceId}"]`);
                if (incorrectLabel) {
                    incorrectLabel.classList.add('incorrect-answer');
                }
            }

            // Also update the tile on the board immediately
            const tileEl = document.getElementById(tileId);
            if (tileEl) {
                tileEl.classList.add(result.correct ? 'correct' : 'incorrect');
            }

            // Hide modal after a delay
            setTimeout(() => {
                document.getElementById('question-modal').style.display = 'none';
            }, 2000);

        } catch (error) {
            console.error('Error submitting answer:', error);
            // Hide modal even on error to not get stuck
            document.getElementById('question-modal').style.display = 'none';
        }
    };

    document.getElementById('question-form').addEventListener('submit', handleAnswerSubmit);
    gameBoard.addEventListener('click', handleTileClick);

    // Start polling
    setInterval(fetchGameState, pollingInterval);
    // Initial fetch
    fetchGameState();
});
