const flipSound = new Audio('audio/flip.ogg');
const correctSound = new Audio('audio/correct.mp3');
const wrongSound = new Audio('audio/wrong.mp3');

const allPossiblePairs = [
    { id: 1, textOpen: "<div>", textClose: "</div>", color: "color-1", def: "<div>: A block-level container." },
    { id: 2, textOpen: "<h1>", textClose: "</h1>", color: "color-2", def: "<h1>: The main heading tag." },
    { id: 3, textOpen: "<p>", textClose: "</p>", color: "color-3", def: "<p>: Defines a paragraph." },
    { id: 4, textOpen: "<a>", textClose: "</a>", color: "color-4", def: "<a>: Anchor tag for links." },
    { id: 5, textOpen: "<ul>", textClose: "</ul>", color: "color-5", def: "<ul>: An unordered list." },
    { id: 6, textOpen: "<span>", textClose: "</span>", color: "color-6", def: "<span>>: Inline container." }
];

let currentLevel = 1;
let pairsCount = 4;
let score = 0;
let timeLeft = 30;
let timerInterval;
let flippedCards = [];
let matchedCount = 0;
let isGameOver = false;

function getMode() {
    const params = new URLSearchParams(window.location.search);
    const mode = (params.get('mode') || 'java').toLowerCase();
    return mode;
}

function submitScore() {
    const body = new URLSearchParams({
        score: String(score),
        mode: getMode(),
    });

    return fetch('../api/submit_score.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body,
        cache: 'no-store',
        keepalive: true,
    }).catch(() => {});
}

function initGame() {
    isGameOver = false;
    matchedCount = 0;
    flippedCards = [];
    const grid = document.getElementById('card-grid');
    grid.innerHTML = ""; 
    
    let levelPairs = allPossiblePairs.slice(0, pairsCount);
    let cardData = [];
    levelPairs.forEach(p => {
        cardData.push({ id: p.id, text: p.textOpen, color: p.color, def: p.def });
        cardData.push({ id: p.id, text: p.textClose, color: p.color, def: p.def });
    });

    cardData.sort(() => Math.random() - 0.5);

    cardData.forEach(data => {
        const card = document.createElement('div');
        card.className = 'card';
        card.dataset.pairId = data.id;
        card.dataset.def = data.def;
        card.innerHTML = `
            <div class="card-back"></div>
            <div class="card-front ${data.color}">
                ${data.text.replace('<', '&lt;').replace('>', '&gt;')}
            </div>
        `;
        card.addEventListener('click', flipCard);
        grid.appendChild(card);
    });
    startTimer();
}

function flipCard() {
    if (isGameOver) return;
    if (flippedCards.length < 2 && !this.classList.contains('flipped')) {
        flipSound.currentTime = 0;
        flipSound.play().catch(() => {});
        this.classList.add('flipped');
        flippedCards.push(this);
        if (flippedCards.length === 2) setTimeout(checkMatch, 500);
    }
}

function checkMatch() {
    const [c1, c2] = flippedCards;
    if (c1.dataset.pairId === c2.dataset.pairId) {
        playSound('audio/correct.mp3'); 
        
        score += 100 * currentLevel;
        document.getElementById('score').textContent = score;
        
        const defContainer = document.querySelector('.definition-container');
        const defText = document.getElementById('definition-text');
        
        defText.textContent = c1.dataset.def;
        defContainer.classList.add('shock-container');
        defText.classList.add('shock-text');

        createStars();

        setTimeout(() => {
            defContainer.classList.remove('shock-container');
            defText.classList.remove('shock-text');
        }, 600);

        matchedCount++;
        flippedCards = [];
        if (matchedCount === pairsCount) setTimeout(nextLevel, 1000);
    } else {
        playSound('audio/wrong.mp3'); 
        
        setTimeout(() => {
            c1.classList.remove('flipped');
            c2.classList.remove('flipped');
            flippedCards = [];
        }, 400);
    }
}

function createStars() {
    const container = document.querySelector('.definition-container');
    const starCount = 15;

    const startX = container.offsetWidth / 2;
    const startY = container.offsetHeight / 2;

    for (let i = 0; i < starCount; i++) {
        const star = document.createElement('div');
        star.className = 'star-particle';
        star.style.left = startX + 'px';
        star.style.top = startY + 'px';

        const travelDistX = (Math.random() - 0.5) * 1000;
        const travelDistY = (Math.random() - 0.5) * 1000;

        star.style.setProperty('--tx', `${travelDistX}px`);
        star.style.setProperty('--ty', `${travelDistY}px`);

        container.appendChild(star);
        setTimeout(() => star.remove(), 800);
    }
}

function nextLevel() {
    clearInterval(timerInterval);
    currentLevel++;
    document.getElementById('level').textContent = currentLevel;
    if (pairsCount < allPossiblePairs.length) pairsCount++;
    timeLeft = 30 + (currentLevel * 5); 
    setTimeout(initGame, 1000);
}

function startTimer() {
    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        timeLeft--;
        document.getElementById('timer').textContent = `00:${timeLeft.toString().padStart(2, '0')}`;
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            isGameOver = true;
            submitScore().finally(() => {
                showGameOver();
            });
        }
    }, 1000);
}

function showGameOver() {
    const modal = document.getElementById('gameover-modal');
    const backdrop = document.getElementById('gameover-backdrop');
    const scoreEl = document.getElementById('gameover-score');
    const restartBtn = document.getElementById('gameover-restart');

    if (scoreEl) scoreEl.textContent = String(score);

    if (modal) {
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
    }
    if (backdrop) backdrop.hidden = false;

    if (restartBtn) {
        restartBtn.onclick = () => location.reload();
    }
}

function playSound(audioPath) {
    const sound = new Audio(audioPath);
    sound.currentTime = 0;
    sound.play().catch(() => {});
}

initGame();