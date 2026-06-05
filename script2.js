// DOM Elements
const nav = document.querySelector('.popout');
const profile = document.querySelector('.profile');
const usernameDisplay = document.querySelector('.usernameDisplay');
const profileButton = document.querySelector('.profileButton');
const closeButton = document.querySelector('.close');
const switchTheme = document.querySelector('.switch2');
const switchThemes = document.querySelector('.b3');
const body = document.querySelector('body');
const topNav = document.querySelector('.top');
const notificationsButton = document.querySelector('.notificationsButton');
const notifications = document.querySelector('.notifications');
const achievementsButton = document.querySelector('.achievementsButton');
const achievements = document.querySelector('.achievements');
const friendsButton = document.querySelector('.friendsButton');
const friends = document.querySelector('.friends');
const friendSearch = document.getElementById('friendSearch');
const friendResults = document.querySelector('.friend-results');
const notificationList = document.querySelector('.notification-list');
const chatPanel = document.getElementById('chatPanel');
const chatWithLabel = document.getElementById('chatWithLabel');
const chatStatus = document.getElementById('chatStatus');
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const gameStartButton = document.querySelector('.gameStartButton');
const gameStartOverlay = document.querySelector('.gameStartOverlay');
const gameStartPanel = document.querySelector('.gameStartPanel');
const characterSelectPanel = document.querySelector('.character-select-panel');
const characterName = document.querySelector('.character-name');
const characterRole = document.querySelector('.character-role');
const characterSpriteImg = document.querySelector('.character-select-panel .character-sprite .Character_spritesheet');
const startCharacterSpriteImg = document.querySelector('.start-character-preview .Character_spritesheet');
const topStatus = document.querySelector('.top-status');
const coinCount = document.querySelector('.coin-count');
const levelCount = document.querySelector('.level-count');
const characterSpriteWrapper = document.querySelector('.character-sprite');
const profileCharacterLabel = document.querySelector('.profile-details .current-character');
const characterSelectorButtons = document.querySelectorAll('.char-select');
const characterSelectButton = document.querySelector('.character-select-button');
const statHP = document.querySelector('.stat-hp');
const statIntelligence = document.querySelector('.stat-intelligence');
const statStrength = document.querySelector('.stat-strength');
const statStamina = document.querySelector('.stat-stamina');
const attackList = document.querySelector('.attack-list');
const forest = document.querySelector('.goForest');
const city = document.querySelector('.goCity');
const bg = document.querySelector('.character-in-game');
const anim = document.querySelector('.character-in-game2');
const battleUI = document.querySelector('.battle-ui');
const battleAttacks = document.querySelector('.battle-attacks');
const hpFill = document.querySelector('.hp-fill');
const hpValue = document.querySelector('.hp-value');
const villageTypewriter = document.querySelector('.village-typewriter');
const villageTypewriterSecondary = document.querySelector('.village-typewriter-secondary');
const villageTypewrap = document.querySelector('.village-typewrap');
let currentCharacter = 'mage';
let typewriterToken = null;
const previewCharacterImages = {
    mage: 'magepng.png',
    knight: 'knightpng.png',
    traveler: 'traveler.png'
};

const loadingOverlay = document.createElement('div');
loadingOverlay.className = 'loading-overlay';
loadingOverlay.innerHTML = `<img src="loading.gif" alt="Loading...">`;
if (gameStartPanel) {
    gameStartPanel.appendChild(loadingOverlay);
} else {
    document.body.appendChild(loadingOverlay);
}

function showLoading() {
    if (gameStartPanel) {
        gameStartPanel.classList.add('loading');
    }
    loadingOverlay.classList.add('visible');
}

function hideLoading() {
    if (gameStartPanel) {
        gameStartPanel.classList.remove('loading');
    }
    loadingOverlay.classList.remove('visible');
}

let typewriterTimers = [];

function clearTypewriter() {
    if (villageTypewriter) {
        villageTypewriter.textContent = '';
        villageTypewriter.style.animation = 'none';
        villageTypewriter.style.width = 'auto';
        villageTypewriter.style.borderRight = 'none';
        villageTypewriter.classList.remove('typing');
    }
    if (villageTypewriterSecondary) {
        villageTypewriterSecondary.textContent = '';
        villageTypewriterSecondary.style.animation = 'none';
        villageTypewriterSecondary.style.width = 'auto';
        villageTypewriterSecondary.style.borderRight = 'none';
        villageTypewriterSecondary.classList.remove('typing');
    }
    typewriterTimers.forEach(window.clearTimeout);
    typewriterTimers = [];
}

function animateTypewriter(element, text, speed = 30, delay = 0) {
    if (!element) return;
    element.textContent = '';
    element.classList.add('typing');
    const interval = Math.max(20, speed);
    const startTimer = window.setTimeout(() => {
        let index = 0;
        const ticker = window.setInterval(() => {
            if (!element) return;
            index += 1;
            element.textContent = text.slice(0, index);
            if (index >= text.length) {
                window.clearInterval(ticker);
                element.classList.remove('typing');
                element.style.borderRight = 'none';
            }
        }, interval);
        typewriterTimers.push(ticker);
    }, delay * 1000);
    typewriterTimers.push(startTimer);
}

function setTypewriterMessage(primary, secondary = '') {
    if (!villageTypewriter) return;
    clearTypewriter();
    villageTypewriter.style.opacity = '1';
    animateTypewriter(villageTypewriter, primary, 25, 0);
    if (secondary && villageTypewriterSecondary) {
        const delay = Math.max(0.5, (primary.length * 25) / 1000) + 0.2;
        typewriterTimers.push(window.setTimeout(() => {
            if (villageTypewriterSecondary) {
                animateTypewriter(villageTypewriterSecondary, secondary, 22, 0);
            }
        }, delay * 1000));
    }
}

function clearAdventureState() {
    if (battleUI) {
        battleUI.classList.add('hidden');
    }
    if (gameStartPanel) {
        gameStartPanel.classList.remove('adventure');
        gameStartPanel.classList.remove('city');
    }
}

function setBattleAttacks(key) {
    if (!battleAttacks || !characters[key]) return;
    battleAttacks.innerHTML = '';
    characters[key].attacks.forEach((attack) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'attack-button';
        button.textContent = attack.name;
        button.title = attack.description;
        button.addEventListener('click', () => {
            setTypewriterMessage(`${attack.name}: ${attack.description}`);
        });
        battleAttacks.appendChild(button);
    });
}

function setCharacterHP(key) {
    if (!hpFill || !hpValue || !characters[key]) return;
    const hp = characters[key].stats.hp || 0;
    const maxHp = characters[key].health || hp || 100;
    const percent = Math.min(100, Math.max(0, Math.round((hp / maxHp) * 100)));
    hpFill.style.width = `${percent}%`;
    // For the traveler we display a simplified "70hp" format
    if (key === 'traveler') {
        hpValue.textContent = `${hp}hp`;
    } else {
        hpValue.textContent = `${hp} / ${maxHp} HP`;
    }
}

function setEnvironmentBackground(location) {
    if (!gameStartPanel) return;
    const image = location === 'forest'
        ? 'forest.png'
        : location === 'city'
            ? 'city.png'
            : location;
    gameStartPanel.style.backgroundImage = `url('${image}')`;
    gameStartPanel.style.backgroundSize = 'cover';
    gameStartPanel.style.backgroundPosition = 'center';
}

function startAreaEntryAnimation() {
    if (!bg || !anim) return;
    setCharacterAction(currentCharacter, 'stand', 'stand');
    requestAnimationFrame(() => {
        setCharacterAction(currentCharacter, 'move', 'move');
        const onAnimationEnd = event => {
            if (event.target !== bg || event.animationName !== 'slideToRight') return;
            bg.removeEventListener('animationend', onAnimationEnd);
            window.clearTimeout(fallbackTimer);
            setCharacterAction(currentCharacter, 'stand', 'stand');
        };
        const fallbackTimer = window.setTimeout(() => {
            bg.removeEventListener('animationend', onAnimationEnd);
            setCharacterAction(currentCharacter, 'stand', 'stand');
        }, 2400);
        bg.addEventListener('animationend', onAnimationEnd);
    });
}

if (bg) {
    bg.addEventListener('transitionend', event => {
        if (event.target !== bg || event.propertyName !== 'transform') return;
        setCharacterAction(currentCharacter, 'stand', 'stand');
    });
}

function handleLocationResult(data, location) {
    if (!data.success) {
        setTypewriterMessage('Unable to continue the journey.');
        hideLoading();
        return;
    }

    setEnvironmentBackground(data.background || location);
    gameStartPanel.classList.add('adventure');
    if (location === 'city') {
        gameStartPanel.classList.add('city');
    } else {
        gameStartPanel.classList.remove('city');
    }
    setTypewriterMessage(data.message);

    if (battleUI) {
        battleUI.classList.remove('hidden');
    }
    setBattleAttacks(currentCharacter);
    setCharacterHP(currentCharacter);
    if (data.outcome === 'lost') {
        setCharacterAction(currentCharacter, 'stand', 'stand');
        hideLoading();
        return;
    }

    setCharacterAction(currentCharacter, 'stand', 'stand');
    hideLoading();
}

function fetchLocationOutcome(location) {
    const payload = new URLSearchParams({
        action: 'location_event',
        location
    });

    fetch('handler.php', {
        method: 'POST',
        body: payload
    }).then(response => response.json()).then(data => {
        handleLocationResult(data, location);
    }).catch(() => {
        setTypewriterMessage('Something went wrong while exploring.');
        hideLoading();
    });
}

function startLocationFlow(location) {
    if (!currentCharacter || !bg || !anim) return;
    clearAdventureState();
    hideLoading();

    setCharacterAction(currentCharacter, 'stand', 'stand');
    bg.classList.remove('move-start');
    bg.classList.remove('move');
    anim.classList.remove('move');

    requestAnimationFrame(() => {
        setInGameCharacter(currentCharacter, 'move');

        const onAnimationEnd = event => {
            if (event.target !== bg || event.animationName !== 'slideToRight') return;
            bg.removeEventListener('animationend', onAnimationEnd);
            showLoading();
            const loadingImg = loadingOverlay.querySelector('img');
            if (loadingImg) {
                loadingImg.addEventListener('animationend', () => fetchLocationOutcome(location), { once: true });
            } else {
                fetchLocationOutcome(location);
            }
        };

        bg.addEventListener('animationend', onAnimationEnd);
    });
}

if (forest) {
    forest.addEventListener('click', () => startLocationFlow('forest'));
}
if (city) {
    city.addEventListener('click', () => startLocationFlow('city'));
}

function setCharacterAction(key, bgAction = 'stand', animAction = 'stand') {
    const safeKey = key ? key.toLowerCase() : '';
    const safeBgAction = bgAction ? bgAction.toLowerCase() : '';
    const safeAnimAction = animAction ? animAction.toLowerCase() : '';

    if (bg) {
        bg.className = ['character-in-game', 'pixelart', safeKey, safeBgAction].filter(Boolean).join(' ');
    }
    if (anim) {
        if (previewCharacterImages[safeKey]) {
            anim.src = previewCharacterImages[safeKey];
        }
        anim.className = ['character-in-game2', 'pixelart', safeKey, safeAnimAction].filter(Boolean).join(' ');
        if (safeAnimAction === 'stand' && safeKey === 'knight') {
            anim.style.animation = 'none';
        } else {
            anim.style.animation = '';
        }
    }
}

function setInGameCharacter(key, action = 'stand') {
    setCharacterAction(key, action, action);
}

function updateProfileCharacterLabel() {
    if (!profileCharacterLabel) return;
    profileCharacterLabel.textContent = currentUserState.character_selected && currentUserState.selectedCharacter ?
        characters[currentUserState.selectedCharacter]?.name || currentUserState.selectedCharacter :
        'No character chosen';
}

function updateStartButtonVisual() {
    if (!gameStartButton) return;
    const buttonImage = currentUserState.game_started ? 'Continue.png' : 'StartGame.png';
    gameStartButton.style.backgroundImage = `url('${buttonImage}')`;
}

function saveGameState(state = {}) {
    if (!window.fetch) return Promise.resolve({ success: false });
    const payload = new URLSearchParams({
        action: 'save_game_state',
        selected_character: state.selected_character != null ? state.selected_character : (currentUserState.selectedCharacter != null ? currentUserState.selectedCharacter : ''),
        coins: state.coins != null ? state.coins : currentUserState.coins,
        level: state.level != null ? state.level : currentUserState.level,
        character_selected: state.character_selected != null ? state.character_selected : currentUserState.character_selected,
        game_started: state.game_started != null ? state.game_started : currentUserState.game_started,
    });

    return fetch('handler.php', {
        method: 'POST',
        body: payload,
    }).then(response => response.json()).then(data => {
        if (data.success) {
            currentUserState.selectedCharacter = payload.get('selected_character') || null;
            currentUserState.coins = Number(payload.get('coins'));
            currentUserState.level = Number(payload.get('level'));
            currentUserState.character_selected = payload.get('character_selected') === 'true';
            currentUserState.game_started = payload.get('game_started') === 'true';
            updateProfileCharacterLabel();
            updateStartButtonVisual();
        }
        return data;
    }).catch(() => {
        // ignore save failures for now
        return { success: false };
    });
}

function activateGameSession() {
    if (gameStartPanel) {
        gameStartPanel.classList.add('selected');
    }
    if (characterSelectPanel) {
        characterSelectPanel.classList.add('hidden');
    }
    if (topStatus) {
        topStatus.classList.remove('hidden');
    }
    if (coinCount) {
        coinCount.textContent = currentUserState.coins;
    }
    if (levelCount) {
        levelCount.textContent = currentUserState.level;
    }
    if (startCharacterSpriteImg) {
        const selectedKey = currentUserState.character_selected && currentUserState.selectedCharacter ? currentUserState.selectedCharacter : null;
        const c = selectedKey ? characters[selectedKey] : null;
        if (c) {
            startCharacterSpriteImg.src = previewCharacterImages[selectedKey] || c.spriteImage;
            startCharacterSpriteImg.className = `Character_spritesheet pixelart start-preview ${c.spriteClass}`;
            // ensure in-game background and sprite use the correct classes and start in 'stand' pose
            setInGameCharacter(selectedKey, 'stand');
        } else {
            startCharacterSpriteImg.src = '';
            startCharacterSpriteImg.className = 'Character_spritesheet pixelart start-preview';
        }
    }
}

function initializeGameState() {
    currentCharacter = currentUserState.selectedCharacter || 'mage';
    characterSelectorButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.character === currentCharacter));
    renderCharacter(currentCharacter);
    updateProfileCharacterLabel();
    updateStartButtonVisual();
    // apply initial character classes and stand pose
    setInGameCharacter(currentCharacter, 'stand');

    if (currentUserState.game_started) {
        currentUserState.coins = currentUserState.coins || 0;
        currentUserState.level = currentUserState.level || 1;
        activateGameSession();
    }
}

// Character data is loaded from PHP characters.php

function renderCharacter(key) {
    const c = characters[key];
    if (!c) {
        if (characterName) characterName.textContent = 'Choose a character';
        if (characterRole) characterRole.textContent = '';
        if (characterSpriteImg) {
            characterSpriteImg.src = '';
            characterSpriteImg.className = 'Character_spritesheet pixelart';
            characterSpriteImg.style.left = '0px';
            characterSpriteImg.style.top = '0px';
        }
        if (startCharacterSpriteImg) {
            startCharacterSpriteImg.src = '';
            startCharacterSpriteImg.className = 'Character_spritesheet pixelart start-preview';
        }
        return;
    }

    characterName.textContent = c.name;
    characterRole.textContent = c.role;

    // For traveler in selection, use traveler-stand-preview animation
    if (key === 'traveler') {
        characterSpriteImg.src = c.spriteImage;
        characterSpriteImg.className = 'traveler-stand-preview pixelart';
        characterSpriteImg.style.left = '0px';
        characterSpriteImg.style.top = '0px';
    } else {
        characterSpriteImg.src = c.spriteImage;
        characterSpriteImg.className = `Character_spritesheet pixelart ${c.spriteClass}`;
        characterSpriteImg.style.left = '0px';
        characterSpriteImg.style.top = '0px';
    }

    if (startCharacterSpriteImg) {
        startCharacterSpriteImg.src = c.spriteImage;
        startCharacterSpriteImg.className = `Character_spritesheet pixelart start-preview ${c.spriteClass}`;
    }

    statHP.textContent = c.stats.hp;
    statIntelligence.textContent = c.stats.intelligence;
    statStrength.textContent = c.stats.strength;
    statStamina.textContent = c.stats.stamina;

    attackList.innerHTML = '';
    c.attacks.forEach(a => {
        const node = document.createElement('div');
        node.className = 'attack-item';
        node.innerHTML = `
            <div class="attack-bubble"></div>
            <img src="${a.icon}" alt="${a.name}">
            <span>${a.name}</span>
        `;
        const bubble = node.querySelector('.attack-bubble');
        node.addEventListener('mouseenter', () => bubble.textContent = a.description);
        node.addEventListener('mouseleave', () => bubble.textContent = '');
        attackList.appendChild(node);
    });
}

// Character selection event listeners
characterSelectorButtons.forEach(button => {
    button.addEventListener('click', () => {
        const selected = button.dataset.character;
        characterSelectorButtons.forEach(btn => btn.classList.toggle('active', btn === button));
        currentCharacter = selected;
        renderCharacter(selected);
        // update in-game preview while selecting
        try {
            setInGameCharacter(selected, 'stand');
        } catch (e) {
            // ignore if DOM not ready
        }
    });
});

if (characterSelectButton) {
    characterSelectButton.addEventListener('click', () => {
        if (!gameStartPanel || !characterSelectPanel) return;
        currentUserState.selectedCharacter = currentCharacter;
        currentUserState.character_selected = true;
        currentUserState.game_started = true;
        activateGameSession();
        saveGameState({
            selected_character: currentUserState.selectedCharacter,
            coins: currentUserState.coins,
            level: currentUserState.level,
            character_selected: true,
            game_started: true
        }).then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    });
}





















// Game start button
if (gameStartButton) {
    gameStartButton.addEventListener('click', () => {
        if (currentUserState.game_started) {
            activateGameSession();
            saveGameState({ game_started: true });
            return;
        }
        if (characterSelectPanel) {
            characterSelectPanel.classList.remove('hidden');
        }
    });
}

if (gameStartOverlay) {
    gameStartOverlay.addEventListener('click', event => {
        if (!characterSelectPanel || characterSelectPanel.classList.contains('hidden')) return;
        if (characterSelectPanel.contains(event.target) || (gameStartButton && gameStartButton.contains(event.target))) return;
        characterSelectPanel.classList.add('hidden');
    });
}


// Initialize saved or default game state
initializeGameState();

// Theme management
let light = false;
let notificationsShow = false;
let friendsShow = false;
let achievementsShow = false;
let activeChatFriend = null;
let canClickUsername = true;

const currentUser = {
    username: null,
    friends: [],
    chat_history: {},
    pending_requests: [],
    unread_message_count: 0
};

const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'light') {
    setTheme('light');
}

function setTheme(theme) {
    light = theme === 'light';
    if (light) {
        switchTheme.classList.add('move');
        body.classList.add('light');
        body.classList.remove('dark');
        topNav.classList.add('light');
        topNav.classList.remove('dark');
        nav.classList.add('light');
        nav.classList.remove('dark');
        usernameDisplay.classList.add('light');
        usernameDisplay.classList.remove('dark');
        notifications.classList.add('light');
        notifications.classList.remove('dark');
        achievements.classList.add('light');
        achievements.classList.remove('dark');
        friends.classList.add('light');
        friends.classList.remove('dark');
    } else {
        switchTheme.classList.remove('move');
        body.classList.add('dark');
        body.classList.remove('light');
        topNav.classList.add('dark');
        topNav.classList.remove('light');
        nav.classList.add('dark');
        nav.classList.remove('light');
        usernameDisplay.classList.add('dark');
        usernameDisplay.classList.remove('light');
        notifications.classList.add('dark');
        notifications.classList.remove('light');
        achievements.classList.add('dark');
        achievements.classList.remove('light');
        friends.classList.add('dark');
        friends.classList.remove('light');
    }
    document.querySelectorAll('.popout a, .popout p').forEach(el => {
        el.style.transition = 'color 0.3s ease';
    });
    localStorage.setItem('theme', theme);
}

function toggleVisibility(element, show) {
    if (!element) return;
    if (show) {
        element.style.display = 'block';
        element.classList.remove('hide');
        element.classList.add('show');
    } else {
        element.classList.remove('show');
        element.classList.add('hide');
        setTimeout(() => {
            element.style.display = 'none';
        }, 250);
    }
}

function fetchJson(body) {
    return fetch('handler.php', {
        method: 'POST',
        body: new URLSearchParams(body)
    }).then(res => res.json());
}

function formatTime(timestamp) {
    const date = new Date(timestamp * 1000);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function renderFriendList(friends) {
    friendResults.innerHTML = '';

    if (!friends || friends.length === 0) {
        friendResults.innerHTML = '<p class="friend-empty">No friends yet. Use the search bar to find new players.</p>';
        return;
    }

    friends.forEach(friend => {
        const messages = currentUser.chat_history[friend.username] || [];
        const unreadCount = messages.filter(msg => msg.sender === friend.username && !msg.read).length;

        const card = document.createElement('div');
        card.className = 'friend-card';
        card.innerHTML = `
            <div>
                <strong>${friend.name}</strong>
                <div class="friend-username" data-username="${friend.username}">@${friend.username}</div>
            </div>
            <button class="chat-button" type="button">${unreadCount ? `Chat (${unreadCount})` : 'Chat'}</button>
        `;
        const usernameElement = card.querySelector('.friend-username');
        usernameElement.addEventListener('click', () => openChatWith(friend));
        usernameElement.addEventListener('dblclick', () => {
            if (activeChatFriend === friend.username) {
                closeChatPanel();
            }
        });
        card.querySelector('button').addEventListener('click', () => openChatWith(friend));
        friendResults.appendChild(card);
    });
}

function renderSuggestedUsers(users) {
    friendResults.innerHTML = '';

    if (!users || users.length === 0) {
        friendResults.innerHTML = '<p class="friend-empty">No suggested players found.</p>';
        return;
    }

    users.forEach(user => {
        const card = document.createElement('div');
        card.className = 'friend-card';
        card.innerHTML = `
            <div>
                <strong>${user.name}</strong>
                <div class="friend-username">@${user.username}</div>
            </div>
            <button class="add-request" type="button">Add</button>
        `;
        card.querySelector('button').addEventListener('click', () => sendFriendRequest(user.username));
        friendResults.appendChild(card);
    });
}

function renderNotifications(pendingRequests, chatHistory) {
    notificationList.innerHTML = '';
    const unreadMessages = [];

    if (chatHistory) {
        Object.entries(chatHistory).forEach(([friendUsername, messages]) => {
            const friendUnread = messages.filter(msg => msg.sender === friendUsername && !msg.read);
            if (friendUnread.length) {
                unreadMessages.push({ friendUsername, messages: friendUnread });
            }
        });
    }

    if ((!pendingRequests || pendingRequests.length === 0) && unreadMessages.length === 0) {
        notificationList.innerHTML = '<p class="notification-empty">No notifications yet.</p>';
        return;
    }

    pendingRequests.forEach(request => {
        const item = document.createElement('div');
        item.className = 'notification-item';
        item.innerHTML = `
            <div>
                <strong>@${request.from}</strong> sent a friend request.
            </div>
            <div class="notification-actions">
                <button class="accept-request" type="button">Accept</button>
                <button class="deny-request" type="button">Deny</button>
            </div>
        `;
        item.querySelector('.accept-request').addEventListener('click', () => respondFriendRequest(request.from, true));
        item.querySelector('.deny-request').addEventListener('click', () => respondFriendRequest(request.from, false));
        notificationList.appendChild(item);
    });

    unreadMessages.forEach(notification => {
        const lastMessage = notification.messages[notification.messages.length - 1];
        const item = document.createElement('div');
        item.className = 'notification-item';
        item.innerHTML = `
            <div>
                <strong>@${notification.friendUsername}</strong> sent ${notification.messages.length} new message${notification.messages.length > 1 ? 's' : ''}.
                <p class="notification-preview">${lastMessage.text}</p>
            </div>
            <div class="notification-actions">
                <button class="open-chat" type="button">Open Chat</button>
            </div>
            <form class="notification-reply" data-friend="${notification.friendUsername}">
                <textarea placeholder="Reply directly..." rows="2"></textarea>
                <button type="submit" class="submit">Reply</button>
            </form>
        `;
        item.querySelector('.open-chat').addEventListener('click', () => openChatWith(getFriendProfile(notification.friendUsername)));
        item.querySelector('.notification-reply').addEventListener('submit', event => {
            event.preventDefault();
            const textarea = event.currentTarget.querySelector('textarea');
            const text = textarea.value.trim();
            const friend = event.currentTarget.dataset.friend;
            if (text) {
                activeChatFriend = friend;
                const friendProfile = getFriendProfile(friend);
                toggleVisibility(notifications, false);
                notificationsShow = false;
                openChatWith(friendProfile);
                sendMessage(friend, text);
                textarea.value = '';
            }
        });
        notificationList.appendChild(item);
    });
}

function renderChatMessages(messages) {
    if (!messages || messages.length === 0) {
        chatMessages.innerHTML = '<p class="chat-empty">No messages yet. Send a quick hello!</p>';
        return;
    }

    chatMessages.innerHTML = '';
    messages.forEach(message => {
        const item = document.createElement('div');
        item.className = `chat-message ${message.sender === currentUser.username ? 'sent' : 'received'}`;
        item.innerHTML = `
            <div>${message.text}</div>
            <div class="message-time">${formatTime(message.created_at)}</div>
        `;
        chatMessages.appendChild(item);
    });
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function getFriendProfile(username) {
    return currentUser.friends.find(friend => friend.username === username) || { username, name: '', LName: '' };
}

function updateNotificationStatus(count) {
    usernameDisplay.classList.toggle('has-notifications', count > 0);
    notificationsButton.classList.toggle('has-notifications', count > 0);
}

function closeChatPanel() {
    if (chatPanel && !chatPanel.classList.contains('hidden')) {
        chatPanel.classList.add('hidden');
        activeChatFriend = null;
    }
}

function openChatWith(friend) {
    if (activeChatFriend === friend.username && chatPanel && !chatPanel.classList.contains('hidden')) {
        closeChatPanel();
        return;
    }

    activeChatFriend = friend.username;
    chatWithLabel.textContent = `Chat with ${friend.name ? friend.name : friend.username}`;
    chatStatus.textContent = '';
    if (notifications) {
        toggleVisibility(notifications, false);
        notificationsShow = false;
    }
    chatPanel.classList.remove('hidden');
    renderChatMessages(currentUser.chat_history[activeChatFriend] || []);
    markMessagesRead(activeChatFriend);
}

function markMessagesRead(friendUsername) {
    fetchJson({ action: 'mark_messages_read', friend: friendUsername })
        .then(() => {
            loadCurrentUser();
        })
        .catch(() => {});
}

function loadCurrentUser() {
    fetchJson({ action: 'fetch_user' })
        .then(data => {
            if (!data.success) {
                notificationList.innerHTML = `<p class="notification-empty">${data.message}</p>`;
                friendResults.innerHTML = '<p class="friend-empty">Unable to load recommended users.</p>';
                return;
            }

            currentUser.username = data.user.username;
            currentUser.friends = data.user.friends || [];
            currentUser.chat_history = data.user.chat_history || {};
            currentUser.pending_requests = data.user.pending_requests || [];
            currentUser.unread_message_count = data.user.unread_message_count || 0;

            renderNotifications(currentUser.pending_requests, currentUser.chat_history);
            if (friendSearch.value.trim() === '') {
                renderFriendList(currentUser.friends);
            } else {
                searchUsers(friendSearch.value.trim());
            }
            updateNotificationStatus(data.user.notification_count || 0);
        })
        .catch(() => {
            notificationList.innerHTML = '<p class="notification-empty">Unable to reach server.</p>';
            friendResults.innerHTML = '<p class="friend-empty">Unable to reach server.</p>';
        });
}

function searchUsers(term) {
    fetchJson({ action: 'search_users', term })
        .then(data => {
            if (!data.success) {
                friendResults.innerHTML = `<p class="friend-empty">${data.message}</p>`;
                return;
            }
            renderSuggestedUsers(data.results || []);
        })
        .catch(() => {
            friendResults.innerHTML = '<p class="friend-empty">Search failed.</p>';
        });
}

function sendFriendRequest(username) {
    fetchJson({ action: 'send_friend_request', target: username })
        .then(data => {
            if (data.success) {
                searchUsers(friendSearch.value.trim());
            } else {
                alert(data.message);
            }
        })
        .catch(() => {
            alert('Unable to send friend request.');
        });
}

function sendMessage(username, text) {
    fetchJson({ action: 'send_message', to: username, message: text })
        .then(data => {
            if (data.success) {
                if (activeChatFriend === username) {
                    chatInput.value = '';
                }
                loadCurrentUser();
                if (activeChatFriend === username) {
                    renderChatMessages(currentUser.chat_history[username] || []);
                }
            } else {
                alert(data.message);
            }
        })
        .catch(() => {
            alert('Unable to send message.');
        });
}

function respondFriendRequest(from, accept) {
    fetchJson({ action: 'respond_friend_request', from, accept: accept ? 'true' : 'false' })
        .then(data => {
            if (data.success) {
                loadCurrentUser();
            } else {
                alert(data.message);
            }
        })
        .catch(() => {
            alert('Unable to update request.');
        });
}

// Event Listeners
usernameDisplay.addEventListener('click', function() {
    if (!canClickUsername) return;
    
    canClickUsername = false;
    usernameDisplay.classList.add('disabled');
    
    if (nav.classList.contains('show')) {
        nav.classList.remove('show');
        nav.classList.add('hide');
        
        setTimeout(() => {
            if (!nav.classList.contains('show')) {
                nav.classList.remove('hide');
                nav.style.display = 'none';
            }
        }, 600);
    } else {
        nav.style.display = 'block';
        nav.classList.remove('hide');
        nav.classList.add('show');
    }
    
    setTimeout(() => {
        canClickUsername = true;
        usernameDisplay.classList.remove('disabled');
    }, 750);
});

profileButton.addEventListener('click', function() {
    profile.style.display = 'block';
    profile.classList.remove('hide');
    profile.classList.add('show');
    
    nav.classList.remove('show');
    nav.classList.add('hide');
    
    setTimeout(() => {
        if (!nav.classList.contains('show')) {
            nav.classList.remove('hide');
            nav.style.display = 'none';
        }
    }, 600);
});

function closeProfileHandler() {
    if (profile.classList.contains('show')) {
        profile.classList.remove('show');
        profile.classList.add('hide');
        
        if (closeButton) {
            closeButton.style.pointerEvents = 'none';
        }
        
        setTimeout(() => {
            profile.classList.remove('hide');
            profile.style.display = 'none';
            
            if (closeButton) {
                closeButton.style.pointerEvents = '';
            }
            
            if (canClickUsername) {
                nav.style.display = 'block';
                nav.classList.remove('hide');
                nav.classList.add('show');
            }
        }, 800);
    }
}

friendsButton.addEventListener('click', function() {
    if (!friendsShow) {
        friendSearch.value = '';
        renderFriendList(currentUser.friends);
        toggleVisibility(notifications, false);
        toggleVisibility(achievements, false);
        notificationsShow = false;
        achievementsShow = false;
    }
    toggleVisibility(friends, !friendsShow);
    friendsShow = !friendsShow;
});

notificationsButton.addEventListener('click', function() {
    toggleVisibility(notifications, !notificationsShow);
    if (!notificationsShow) {
        toggleVisibility(friends, false);
        toggleVisibility(achievements, false);
        friendsShow = false;
        achievementsShow = false;
    }
    notificationsShow = !notificationsShow;
});

if (achievementsButton) {
    achievementsButton.addEventListener('click', function() {
        if (!achievementsShow) {
            toggleVisibility(friends, false);
            toggleVisibility(notifications, false);
            friendsShow = false;
            notificationsShow = false;
        }
        toggleVisibility(achievements, !achievementsShow);
        achievementsShow = !achievementsShow;
    });
}

switchThemes.addEventListener('click', function() {
    setTheme(light ? 'dark' : 'light');
});

if (closeButton) {
    closeButton.addEventListener('click', closeProfileHandler);
}

if (friendSearch) {
    friendSearch.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            searchUsers('');
        }
    });
    friendSearch.addEventListener('input', function() {
        searchUsers(this.value.trim());
    });
}

document.addEventListener('click', function(event) {
    if (!friendSearch || !friendResults) return;
    const target = event.target;
    const insideSearchArea = friendSearch.contains(target) || friendResults.contains(target);
    const insideChatArea = chatPanel && chatPanel.contains(target);
    const insideNavArea = nav && nav.contains(target);
    const insideNotifications = notifications && notifications.contains(target);

    if (!insideSearchArea && friendSearch.value.trim() !== '') {
        renderFriendList(currentUser.friends);
    }

    const insideAchievements = achievements && achievements.contains(target);

    if (!insideChatArea && !insideSearchArea && !insideNavArea && !insideNotifications && !insideAchievements) {
        closeChatPanel();
    }
});

if (chatForm) {
    chatForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const message = chatInput.value.trim();
        if (!message || !activeChatFriend) {
            return;
        }
        sendMessage(activeChatFriend, message);
    });
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && profile.classList.contains('show')) {
        closeProfileHandler();
    }
});

















// Initialize
loadCurrentUser();