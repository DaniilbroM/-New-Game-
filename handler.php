<?php
// Turn off display errors to prevent HTML output in JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Include required files
require_once 'users.php';
require_once 'characters.php';
require_once 'enemy.php';

$userManager = new UserManager();

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'signup') {
        $name = trim($_POST['name'] ?? '');
        $lname = trim($_POST['LName'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($lname) || empty($username) || empty($password)) {
            $response['message'] = 'Please fill in all fields.';
        } else {
            $result = $userManager->registerUser($name, $lname, $username, $password);

            if ($result['success']) {
                $_SESSION['current_user'] = $username;
                $response['success'] = true;
                $response['message'] = 'Registration successful! Redirecting...';
                $response['redirect'] = 'home.php';
            } else {
                $response['message'] = $result['message'];
            }
        }
    } elseif ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $response['message'] = 'Please enter username and password.';
        } else {
            $result = $userManager->loginUser($username, $password);

            if ($result['success']) {
                $_SESSION['current_user'] = $username;
                $response['success'] = true;
                $response['message'] = 'Login successful! Redirecting...';
                $response['redirect'] = 'home.php';
            } else {
                $response['message'] = $result['message'];
            }
        }
    } elseif ($action === 'fetch_user') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } else {
            $currentUser = $userManager->getUserByUsername($_SESSION['current_user']);
            if (!$currentUser) {
                $response['message'] = 'User not found.';
            } else {
                $friends = $userManager->getFriendProfiles($_SESSION['current_user']);
                $pendingRequests = $currentUser['pending_requests'] ?? [];
                $unreadMessageCount = $userManager->getUnreadMessageCount($_SESSION['current_user']);
                
                // Get current enemy from session if exists
                $currentEnemy = isset($_SESSION['current_enemy']) ? $_SESSION['current_enemy'] : null;
                
                $response = [
                    'success' => true,
                    'user' => [
                        'username' => $currentUser['username'],
                        'name' => $currentUser['name'],
                        'LName' => $currentUser['LName'],
                        'friends' => $friends,
                        'pending_requests' => $pendingRequests,
                        'chat_history' => $currentUser['chat_history'] ?? [],
                        'unread_message_count' => $unreadMessageCount,
                        'notification_count' => count($pendingRequests) + $unreadMessageCount,
                        'coins' => $currentUser['coins'] ?? 0,
                        'level' => $currentUser['level'] ?? 0,
                        'xp' => $currentUser['xp'] ?? 0,
                        'xp_needed' => $currentUser['xp_needed'] ?? 100,
                        'current_location' => $currentUser['current_location'] ?? 'village',
                        'temp_hp' => $currentUser['temp_hp'] ?? null
                    ],
                    'recommended' => $userManager->searchUsers('', $currentUser['username'], 10),
                    'current_enemy' => $currentEnemy
                ];
            }
        }
    } elseif ($action === 'search_users') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } else {
            $term = trim($_POST['term'] ?? '');
            $response = [
                'success' => true,
                'results' => $userManager->searchUsers($term, $_SESSION['current_user'], 15)
            ];
        }
    } elseif ($action === 'send_friend_request') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } else {
            $target = trim($_POST['target'] ?? '');
            if (empty($target)) {
                $response['message'] = 'Please choose a user to add.';
            } else {
                $result = $userManager->addFriendRequest($_SESSION['current_user'], $target);
                $response = $result;
            }
        }
    } elseif ($action === 'send_message') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } else {
            $target = trim($_POST['to'] ?? '');
            $message = trim($_POST['message'] ?? '');
            if (empty($target) || empty($message)) {
                $response['message'] = 'Please choose a friend and type a message.';
            } else {
                $result = $userManager->sendMessage($_SESSION['current_user'], $target, $message);
                $response = $result;
            }
        }
    } elseif ($action === 'mark_messages_read') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } else {
            $friend = trim($_POST['friend'] ?? '');
            if (empty($friend)) {
                $response['message'] = 'Friend not specified.';
            } else {
                $result = $userManager->markMessagesRead($_SESSION['current_user'], $friend);
                $response = $result;
            }
        }
    } elseif ($action === 'respond_friend_request') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } else {
            $from = trim($_POST['from'] ?? '');
            $accept = filter_var($_POST['accept'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (empty($from)) {
                $response['message'] = 'Missing request sender.';
            } else {
                $result = $userManager->respondToFriendRequest($_SESSION['current_user'], $from, $accept);
                $response = $result;
            }
        }
    } elseif ($action === 'location_event') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } else {
            $currentUser = $userManager->getUserByUsername($_SESSION['current_user']);
            if (!$currentUser) {
                $response['message'] = 'User not found.';
            } else {
                $location = trim($_POST['location'] ?? '');
                if (!in_array($location, ['forest', 'city'], true)) {
                    $response['message'] = 'Invalid location.';
                } else {
                    // Save current location to user data
                    $currentUser['current_location'] = $location;
                    $userManager->updateUser($currentUser);
                    
                    // Always encounter mushroom (100% chance)
                    $enemy = getRandomEnemyForLocation($location);
                    
                    if ($enemy) {
                        // Store enemy in session for combat
                        $_SESSION['current_enemy'] = [
                            'type' => $enemy->type,
                            'name' => $enemy->name,
                            'health' => $enemy->health,
                            'maxHealth' => $enemy->maxHealth,
                            'strength' => $enemy->strength,
                            'stamina' => $enemy->stamina,
                            'stats' => $enemy->stats,
                            'idleAnimation' => $enemy->idleAnimation,
                            'attackAnimation' => $enemy->attackAnimation,
                            'dieAnimation' => $enemy->dieAnimation,
                            'hitAnimation' => $enemy->hitAnimation,
                            'runAnimation' => $enemy->runAnimation,
                            'stunnedAnimation' => $enemy->stunnedAnimation,
                            'bleed_turns' => 0,
                            'burn_turns' => 0,
                            'bleed_active' => false,
                            'burn_active' => false
                        ];
                        
                        $response = [
                            'success' => true,
                            'outcome' => 'encounter',
                            'message' => "While walking through the $location you encountered a {$enemy->name}!",
                            'detail' => "The {$enemy->name} has {$enemy->health} HP and looks ready to fight.",
                            'background' => $location === 'forest' ? 'forest.png' : 'city.png',
                            'enemy' => [
                                'type' => $enemy->type,
                                'name' => $enemy->name,
                                'health' => $enemy->health,
                                'maxHealth' => $enemy->maxHealth,
                                'strength' => $enemy->strength,
                                'stamina' => $enemy->stamina,
                                'idleAnimation' => $enemy->idleAnimation
                            ]
                        ];
                    } else {
                        $response = [
                            'success' => true,
                            'outcome' => 'none',
                            'message' => "The $location is quiet. No enemies in sight.",
                            'detail' => "You walk through peacefully.",
                            'background' => $location === 'forest' ? 'forest.png' : 'city.png'
                        ];
                    }
                }
            }
        }
    } elseif ($action === 'combat_action') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } elseif (!isset($_SESSION['current_enemy'])) {
            $response['message'] = 'No enemy to fight.';
        } else {
            $combatAction = $_POST['combat_action'] ?? '';
            $currentUser = $userManager->getUserByUsername($_SESSION['current_user']);
            $enemy = $_SESSION['current_enemy'];
            $characterKey = $currentUser['selected_character'] ?? 'mage';
            $character = isset($characters[$characterKey]) ? $characters[$characterKey] : null;
            
            // Initialize temp HP if not set
            if (!isset($currentUser['temp_hp']) && $character) {
                $currentUser['temp_hp'] = $character->health;
            }
            $playerHP = $currentUser['temp_hp'] ?? ($character ? $character->health : 100);
            
            // Apply bleed and burn effects at start of turn
            $effectMessage = '';
            if ($enemy['bleed_turns'] > 0) {
                $bleedDamage = 2;
                $enemy['health'] -= $bleedDamage;
                $enemy['bleed_turns']--;
                $effectMessage .= " Bleed deals $bleedDamage damage! ";
                if ($enemy['bleed_turns'] <= 0) {
                    $enemy['bleed_active'] = false;
                    $effectMessage .= " Bleed wore off. ";
                }
            }
            if ($enemy['burn_turns'] > 0) {
                $burnDamage = 5;
                $enemy['health'] -= $burnDamage;
                $enemy['burn_turns']--;
                $effectMessage .= " Burn deals $burnDamage damage! ";
                if ($enemy['burn_turns'] <= 0) {
                    $enemy['burn_active'] = false;
                    $effectMessage .= " Burn wore off. ";
                }
            }
            $enemy['health'] = max(0, $enemy['health']);
            
            if ($combatAction === 'attack') {
                // Get the attack details from the character
                $attackIndex = isset($_POST['attack_index']) ? intval($_POST['attack_index']) : 0;
                $attacks = $character->attacks;
                $selectedAttack = $attacks[$attackIndex] ?? $attacks[0];
                $attackName = $selectedAttack['name'];
                $attackDesc = $selectedAttack['description'];
                
                // Calculate damage based on character strength
                $characterStrength = $character ? ($character->stats['strength'] ?? 5) : 5;
                $baseDamage = $characterStrength + random_int(1, 10);
                $damage = $baseDamage;
                
                $message = "You used $attackName! ";
                
                // Apply bleed chance (30% for Fireball, 20% for Map Strike, 20% for Leg Kick, 30% for Sword Swing)
                $bleedChance = 0;
                $burnChance = 0;
                
                if (strpos($attackName, 'Fireball') !== false) {
                    $burnChance = 30;
                } elseif (strpos($attackName, 'Sword Swing') !== false) {
                    $bleedChance = 30;
                } elseif (strpos($attackName, 'Leg Kick') !== false) {
                    $bleedChance = 20;
                } elseif (strpos($attackName, 'Map Strike') !== false) {
                    $bleedChance = 20;
                } elseif (strpos($attackName, 'Torch Jab') !== false) {
                    $burnChance = 25;
                }
                
                // Apply bleed chance
                if ($bleedChance > 0 && !$enemy['bleed_active']) {
                    $roll = random_int(1, 100);
                    if ($roll <= $bleedChance) {
                        $enemy['bleed_active'] = true;
                        $enemy['bleed_turns'] = 2;
                        $message .= " Bleed applied! Enemy will take 2 damage for 2 turns and receive 10% more damage! ";
                    }
                }
                
                // Apply burn chance
                if ($burnChance > 0 && !$enemy['burn_active']) {
                    $roll = random_int(1, 100);
                    if ($roll <= $burnChance) {
                        $enemy['burn_active'] = true;
                        $enemy['burn_turns'] = 3;
                        $message .= " Burn applied! Enemy will take 5 damage for 3 turns! ";
                    }
                }
                
                // Apply 10% damage increase if bleed is active
                if ($enemy['bleed_active']) {
                    $damage = round($damage * 1.1);
                    $message .= " (+10% damage from bleed) ";
                }
                
                // Apply damage
                $enemy['health'] -= $damage;
                $enemy['health'] = max(0, $enemy['health']);
                $message .= "You dealt $damage damage to the {$enemy['name']}!";
                
                // Enemy counterattack if still alive
                $enemyDamage = 0;
                if ($enemy['health'] > 0) {
                    $enemyDamage = $enemy['strength'] + random_int(1, 8);
                    $playerHP -= $enemyDamage;
                    $playerHP = max(0, $playerHP);
                    $currentUser['temp_hp'] = $playerHP;
                    $message .= " The {$enemy['name']} counterattacks for $enemyDamage damage!";
                }
                
                $message .= $effectMessage;
                
                $_SESSION['current_enemy'] = $enemy;
                $userManager->updateUser($currentUser);
                
                $enemyDefeated = $enemy['health'] <= 0;
                $playerDefeated = $playerHP <= 0;
                
                $response = [
                    'success' => true,
                    'enemy_hp' => $enemy['health'],
                    'enemy_max_hp' => $enemy['maxHealth'],
                    'player_hp' => $playerHP,
                    'message' => $message,
                    'enemy_defeated' => $enemyDefeated,
                    'player_defeated' => $playerDefeated,
                    'bleed_active' => $enemy['bleed_active'],
                    'bleed_turns' => $enemy['bleed_turns'],
                    'burn_active' => $enemy['burn_active'],
                    'burn_turns' => $enemy['burn_turns']
                ];
                
                if ($enemyDefeated) {
                    // Reward player for defeating enemy - 2 coins and 3 XP
                    $coinsReward = 2;
                    $xpReward = 3;
                    
                    $currentUser['coins'] = ($currentUser['coins'] ?? 0) + $coinsReward;
                    
                    // Add XP and check for level up
                    $currentUser['xp'] = ($currentUser['xp'] ?? 0) + $xpReward;
                    $leveledUp = false;
                    
                    $xpNeeded = $currentUser['xp_needed'] ?? 100;
                    while ($currentUser['xp'] >= $xpNeeded) {
                        $currentUser['xp'] -= $xpNeeded;
                        $currentUser['level'] = ($currentUser['level'] ?? 0) + 1;
                        $currentUser['xp_needed'] = 100 + ($currentUser['level'] * 25);
                        $leveledUp = true;
                    }
                    
                    $userManager->updateUser($currentUser);
                    unset($_SESSION['current_enemy']);
                    
                    $response['reward'] = $coinsReward;
                    $response['xp_reward'] = $xpReward;
                    $response['leveled_up'] = $leveledUp;
                    $response['new_level'] = $currentUser['level'];
                    $response['current_xp'] = $currentUser['xp'];
                    $response['xp_needed'] = $currentUser['xp_needed'];
                    $response['message'] .= " You defeated the {$enemy['name']} and gained $coinsReward coins and $xpReward XP!";
                    
                    if ($leveledUp) {
                        $response['message'] .= " You leveled up to level {$currentUser['level']}!";
                    }
                } else {
                    // Save updated enemy and player HP to session
                    $_SESSION['current_enemy'] = $enemy;
                }
                
                if ($playerDefeated) {
                    $response['message'] .= " You have been defeated!";
                    unset($_SESSION['current_enemy']);
                    $currentUser['temp_hp'] = $character ? $character->health : 100;
                    $userManager->updateUser($currentUser);
                }
            }
        }
    } elseif ($action === 'save_game_state') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } else {
            $currentUser = $userManager->getUserByUsername($_SESSION['current_user']);
            if (!$currentUser) {
                $response['message'] = 'User not found.';
            } else {
                $selectedCharacter = isset($_POST['selected_character']) ? trim($_POST['selected_character']) : ($currentUser['selected_character'] ?? null);
                if ($selectedCharacter === '') {
                    $selectedCharacter = null;
                }
                $coins = max(0, intval($_POST['coins'] ?? $currentUser['coins']));
                $level = max(0, intval($_POST['level'] ?? $currentUser['level']));
                $xp = max(0, intval($_POST['xp'] ?? $currentUser['xp']));
                $xpNeeded = max(1, intval($_POST['xp_needed'] ?? $currentUser['xp_needed']));
                $characterSelected = filter_var($_POST['character_selected'] ?? ($currentUser['character_selected'] ?? false), FILTER_VALIDATE_BOOLEAN);
                $gameStarted = filter_var($_POST['game_started'] ?? ($currentUser['game_started'] ?? false), FILTER_VALIDATE_BOOLEAN);
                $currentLocation = $_POST['current_location'] ?? ($currentUser['current_location'] ?? 'village');
                $tempHp = isset($_POST['temp_hp']) && $_POST['temp_hp'] !== '' ? intval($_POST['temp_hp']) : $currentUser['temp_hp'];

                $currentUser['selected_character'] = $selectedCharacter;
                $currentUser['coins'] = $coins;
                $currentUser['level'] = $level;
                $currentUser['xp'] = $xp;
                $currentUser['xp_needed'] = $xpNeeded;
                $currentUser['character_selected'] = $characterSelected;
                $currentUser['game_started'] = $gameStarted;
                $currentUser['current_location'] = $currentLocation;
                $currentUser['temp_hp'] = $tempHp;

                $updated = $userManager->updateUser($currentUser);
                if ($updated && isset($_SESSION['users']) && is_array($_SESSION['users'])) {
                    foreach ($_SESSION['users'] as &$sessionUser) {
                        if ($sessionUser['username'] === $currentUser['username']) {
                            $sessionUser = $currentUser;
                            break;
                        }
                    }
                    unset($sessionUser);
                }

                $response = [
                    'success' => $updated,
                    'message' => $updated ? 'Game state saved.' : 'Unable to save game state.'
                ];
            }
        }
    }
}

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>