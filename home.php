<?php

session_start();

if (!isset($_SESSION['current_user'])) {
    header("Location: index.php");
    exit();
}

require_once 'users.php';
require_once 'characters.php';
require_once 'enemy.php';

$userManager = new UserManager();
$currentUser = $userManager->getUserByUsername($_SESSION['current_user']);

if ($currentUser === null) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$safeUsername = htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8');
$safeName = htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8');
$safeLName = htmlspecialchars($currentUser['LName'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Home/Game</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
  
<nav class="top">
  <p class="usernameDisplay"><strong><?php echo $safeUsername; ?></strong></p>
  <div class="top-status hidden">
    <span class="coins">Coins: <strong class="coin-count">0</strong></span>
    <span class="level">Lvl <strong class="level-count">0</strong></span>
    <div class="xp-bar-container">
        <div class="xp-bar">
            <div class="xp-fill" style="width: 0%;"></div>
            <span class="xp-value">0 / 6 XP</span>
        </div>
    </div>
  </div>
  <nav class="popout">
    <p class="profileButton">Profile</p>
    <p class="friendsButton">Friends</p>
    <p class="notificationsButton">Notifications</p>
    <p class="achievementsButton">Achievements</p>
    <button class="logoutButton" type="button" onclick="window.location.href='logout.php'">Log Out</button>
    <div class="theme-row">
      <span class="switch3">Switch Theme</span>
      <div class="b3"><div class="switch2"></div></div>
    </div>
  </nav>
</nav>  
  
<?php
$characterSelected = !empty($currentUser['character_selected']);
$selectedCharacterKey = $characterSelected && isset($currentUser['selected_character']) && $currentUser['selected_character'] !== '' ? $currentUser['selected_character'] : null;
$selectedCharacter = $selectedCharacterKey && isset($characters[$selectedCharacterKey]) ? $characters[$selectedCharacterKey] : null;
$savedLocation = $currentUser['current_location'] ?? 'village';
?>

<!-- Victory Modal -->
<div id="victoryModal" class="game-modal hidden">
    <div class="game-modal-content victory-content">
        <h1 class="modal-title victory-title">YOU WON!!</h1>
        <p class="modal-message">Future Updates Might Be Incoming</p>
        <button class="modal-restart-btn" id="victoryRestartBtn">Restart Game</button>
    </div>
</div>

<!-- Game Over Modal -->
<div id="gameOverModal" class="game-modal hidden">
    <div class="game-modal-content gameover-content">
        <h1 class="modal-title gameover-title">YOU LOST!</h1>
        <p class="modal-message">Better luck next time, adventurer!</p>
        <button class="modal-restart-btn" id="gameOverRestartBtn">Restart Game</button>
    </div>
</div>

<div class="gameStartOverlay">
    <div class="gameStartPanel" data-saved-location="<?php echo htmlspecialchars($savedLocation, ENT_QUOTES, 'UTF-8'); ?>">
        
        <button class="gameStartButton" aria-label="Start the Game"></button>

        <div class="village-typewrap" aria-hidden="false">
            <div class="village-bg" aria-hidden="true"></div>
            <p class="village-typewriter"></p>
            <p class="village-typewriter-secondary"></p>
        </div>

        <!-- Village Actions - Only visible when in village -->
        <div class="village-actions" id="villageActions">
            <button class="goForest" data-action="forest">Go Through The Forest</button>
            <button class="goCity" data-action="city">Go through a city</button>
        </div>

        <!-- Location Buttons Container - Dynamically populated -->
        <div class="location-buttons-container hidden" id="locationButtonsContainer"></div>

<?php if ($selectedCharacter): ?>
        <div class="character-wrapper">
            <div class="character-in-game pixelart <?php echo htmlspecialchars($selectedCharacterKey, ENT_QUOTES, 'UTF-8'); ?> stand">
                <div class="hp-overlay" data-max-hp="<?php echo htmlspecialchars($selectedCharacter->health, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="hp-bar">
                            <div class="hp-fill" style="width: 100%;"></div>
                            <span class="hp-value"><?php echo ($selectedCharacterKey === 'traveler') ? htmlspecialchars($selectedCharacter->health, ENT_QUOTES, 'UTF-8') . 'hp' : (htmlspecialchars($selectedCharacter->health, ENT_QUOTES, 'UTF-8') . ' / ' . htmlspecialchars($selectedCharacter->health, ENT_QUOTES, 'UTF-8') . ' HP'); ?></span>
                        </div>
                </div>
                <div class="player" data-character="<?php echo htmlspecialchars($selectedCharacterKey, ENT_QUOTES, 'UTF-8'); ?>"></div>
                <img class="character-in-game2 pixelart <?php echo htmlspecialchars($selectedCharacterKey, ENT_QUOTES, 'UTF-8'); ?> stand"
                     src="<?php echo htmlspecialchars($selectedCharacterKey === 'mage' ? 'magepng.png' : ($selectedCharacterKey === 'knight' ? 'knightpng.png' : 'traveler.png'), ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($selectedCharacter->name, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
        <div class="battle-ui hidden">
            <div class="battle-header">Attacks</div>
            <div class="battle-attacks"></div>
        </div>
<?php endif; ?>

        <div class="start-character-preview">
            <div class="character-sprite small game-character-sprite">
            </div>
        </div>
    </div>

    <div class="character-select-panel hidden">
        <div class="character-card">
            <div class="character-card-preview">
                <div class="character-sprite selection-character-sprite">
                    <div class="Character_shadow"></div>
                    <img class="Character_spritesheet pixelart mage-idle" src="Mage.png" alt="Character sprite preview">
                </div>
                <div class="character-headline">
                    <h2 class="character-name">Mage</h2>
                    <p class="character-role">Arcane Caster</p>
                </div>
            </div>
            <div class="character-selector">
                <button type="button" class="char-select active" data-character="mage">Mage</button>
                <button type="button" class="char-select" data-character="knight">Knight</button>
                <button type="button" class="char-select" data-character="traveler">Traveler</button>
            </div>
            <div class="character-stats-grid">
                <div class="stat-row"><span>HP</span><strong class="stat-hp">80</strong></div>
                <div class="stat-row"><span>Intelligence</span><strong class="stat-intelligence">9</strong></div>
                <div class="stat-row"><span>Strength</span><strong class="stat-strength">6</strong></div>
                <div class="stat-row"><span>Stamina</span><strong class="stat-stamina">70</strong></div>
            </div>
            <div class="attack-label">Attacks</div>
            <div class="attack-list"></div>
            <button type="button" class="character-select-button">Select</button>
        </div>
    </div>
</div>

<!-- Enemy Container -->
<div class="enemy-container hidden">
    <div class="enemy-card">
        <div class="enemy-hp-overlay">
            <div class="hp-bar">
                <div class="hp-fill enemy-hp-fill" style="width: 100%;"></div>
                <span class="hp-value enemy-hp-value">50 / 50 HP</span>
            </div>
        </div>
        <img class="enemy-sprite pixelart" src="Mushroom/Mushroom-Idle.png" alt="Mushroom">
        <div class="enemy-stats hidden">
            <span class="enemy-strength">3</span>
            <span class="enemy-max-hp">50</span>
        </div>
    </div>
</div>

<nav class="profile">
  <button class="close">Close</button>
  <div class="profile-details">
      <p class="profile-label"><strong>First Name:</strong></p>
      <p class="profile-value"><?php echo $safeName; ?></p>
      <p class="profile-label"><strong>Last Name:</strong></p>
      <p class="profile-value"><?php echo $safeLName; ?></p>
      <p class="profile-label"><strong>Current Character:</strong></p>
      <p class="profile-value current-character"><?php echo $characterSelected && $selectedCharacter ? ucfirst($selectedCharacterKey) : 'No character chosen'; ?></p>
      <p class="profile-label"><strong>Level:</strong></p>
      <p class="profile-value profile-level"><?php echo $currentUser['level'] ?? 0; ?></p>
      <div class="xp-bar-container">
          <div class="xp-bar">
              <div class="xp-fill profile-xp-fill" style="width: <?php echo (($currentUser['xp'] ?? 0) / max(1, ($currentUser['xp_needed'] ?? 6))) * 100; ?>%;"></div>
              <span class="xp-value profile-xp-value"><?php echo ($currentUser['xp'] ?? 0) . ' / ' . ($currentUser['xp_needed'] ?? 6) . ' XP'; ?></span>
          </div>
      </div>
  </div>
</nav>

<div class="notifications">
    <div class="panel-header"><strong>Notifications</strong></div>
    <div class="notification-list">
        <p class="notification-empty">Loading notifications...</p>
    </div>
</div>

<div class="achievements">
    <div class="panel-header"><strong>Achievements</strong></div>
    <div class="achievement-list">
        <p class="achievement-empty">No achievements yet.</p>
    </div>
</div>

<div class="friends">
    <div class="panel-header"><strong>Connect & Chat</strong></div>
    <input id="friendSearch" type="search" placeholder="Search for friends or see suggestions">
    <div class="friend-results">
        <p class="friend-empty">Loading recommended players...</p>
    </div>
    <div class="chat-panel hidden" id="chatPanel">
        <div class="chat-header">
            <strong id="chatWithLabel">Chat</strong>
            <span class="chat-status" id="chatStatus"></span>
        </div>
        <div class="chat-messages" id="chatMessages">
            <p class="chat-empty">Select a friend to start chatting.</p>
        </div>
        <form id="chatForm" class="chat-form">
            <textarea id="chatInput" placeholder="Type a quick message..." rows="3"></textarea>
            <button type="submit" class="submit chat-submit">Send</button>
        </form>
    </div>
</div>

<script>
const characters = <?php echo json_encode($characters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const currentUserState = <?php echo json_encode([
    'selectedCharacter' => $currentUser['selected_character'] ?? null,
    'coins' => $currentUser['coins'] ?? 0,
    'level' => $currentUser['level'] ?? 0,
    'xp' => $currentUser['xp'] ?? 0,
    'xp_needed' => $currentUser['xp_needed'] ?? 6,
    'game_started' => $currentUser['game_started'] ?? false,
    'character_selected' => $currentUser['character_selected'] ?? false,
    'current_location' => $currentUser['current_location'] ?? 'village',
    'temp_hp' => $currentUser['temp_hp'] ?? null
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
<script src="script2.js"></script>
</body>
</html>