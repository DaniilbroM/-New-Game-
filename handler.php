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
                        'xp_needed' => $currentUser['xp_needed'] ?? 6,
                        'current_location' => $currentUser['current_location'] ?? 'village',
                        'temp_hp' => $currentUser['temp_hp'] ?? null
                    ],
                    'recommended' => $userManager->searchUsers('', $currentUser['username'], 10),
                    'current_enemy' => $currentEnemy
                ];
            }
        }
    } elseif ($action === 'clear_enemy') {
        unset($_SESSION['current_enemy']);
        $response = ['success' => true];
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
                $validLocations = ['forest', 'city', 'mountain', 'mushroom_kingdom', 'river_village', 'village', 'demon_castle'];
                
                if (!in_array($location, $validLocations, true)) {
                    $response['message'] = 'Invalid location.';
                } else {
                    // Check if there's an active enemy - ONLY block if enemy exists AND has health > 0
                    $hasActiveEnemy = (isset($_SESSION['current_enemy']) && 
                                       $_SESSION['current_enemy'] !== null && 
                                       isset($_SESSION['current_enemy']['health']) && 
                                       $_SESSION['current_enemy']['health'] > 0);
                    
                    if ($hasActiveEnemy) {
                        $response = [
                            'success' => false,
                            'message' => 'You must defeat the enemy before traveling!',
                            'outcome' => 'combat_active'
                        ];
                    } else {
                        // Clear any stale enemy data
                        $_SESSION['current_enemy'] = null;
                        
                        // Handle village specially
                        if ($location === 'village') {
                            $currentUser['current_location'] = 'village';
                            $userManager->updateUser($currentUser);
                            $response = [
                                'success' => true,
                                'outcome' => 'village',
                                'message' => "You return to the peaceful village.",
                                'background' => 'village.png',
                                'enemy' => null
                            ];
                        } else {
                            // Get random encounter outcome based on location
                            $outcome = getRandomEventForLocation($location, $currentUser);
                            
                            if ($outcome['type'] === 'encounter' && $outcome['enemy']) {
                                $enemy = $outcome['enemy'];
                                
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
                                    'burn_active' => false,
                                    'stunned' => false,
                                    'stun_turns' => 0
                                ];
                                
                                $currentUser['current_location'] = $location;
                                $userManager->updateUser($currentUser);
                                
                                $locationName = getLocationDisplayName($location);
                                
                                $response = [
                                    'success' => true,
                                    'outcome' => 'encounter',
                                    'message' => "While exploring the $locationName you encountered a {$enemy->name}!",
                                    'background' => getBackgroundImage($location),
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
                            } elseif ($outcome['type'] === 'heal') {
                                // Healing event - restore player HP
                                $characterKey = $currentUser['selected_character'] ?? 'mage';
                                $character = isset($characters[$characterKey]) ? $characters[$characterKey] : null;
                                $maxHp = $character ? $character->health : 100;
                                $currentUser['temp_hp'] = $maxHp;
                                $userManager->updateUser($currentUser);
                                
                                $_SESSION['current_enemy'] = null;
                                $currentUser['current_location'] = $location;
                                $userManager->updateUser($currentUser);
                                
                                $locationName = getLocationDisplayName($location);
                                
                                $response = [
                                    'success' => true,
                                    'outcome' => 'heal',
                                    'message' => "💚 You found a healing spring! Your HP is fully restored! 💚",
                                    'background' => getBackgroundImage($location),
                                    'enemy' => null,
                                    'healed' => true,
                                    'current_hp' => $maxHp
                                ];
                            } elseif ($outcome['type'] === 'lost') {
                                // Lost state - stays in same location but with random background
                                $currentUser['current_location'] = $location;
                                $userManager->updateUser($currentUser);
                                
                                $locationName = getLocationDisplayName($location);
                                
                                $response = [
                                    'success' => true,
                                    'outcome' => 'lost',
                                    'message' => "🌫️ You got lost in the $locationName! You wander around aimlessly... 🌫️",
                                    'background' => 'random1.png',
                                    'enemy' => null
                                ];
                            } else {
                                // Safe - no enemy
                                $_SESSION['current_enemy'] = null;
                                $currentUser['current_location'] = $location;
                                $userManager->updateUser($currentUser);
                                
                                $locationName = getLocationDisplayName($location);
                                
                                $response = [
                                    'success' => true,
                                    'outcome' => 'safe',
                                    'message' => "The $locationName is peaceful. No enemies in sight.",
                                    'background' => getBackgroundImage($location),
                                    'enemy' => null
                                ];
                            }
                        }
                    }
                }
            }
        }
    } elseif ($action === 'combat_action') {
        if (!isset($_SESSION['current_user'])) {
            $response['message'] = 'Not logged in.';
        } elseif (!isset($_SESSION['current_enemy']) || $_SESSION['current_enemy'] === null) {
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
                $effectMessage .= "🩸 Bleed deals $bleedDamage damage! ";
                if ($enemy['bleed_turns'] <= 0) {
                    $enemy['bleed_active'] = false;
                    $effectMessage .= "Bleed wore off. ";
                }
            }
            if ($enemy['burn_turns'] > 0) {
                $burnDamage = 5;
                $enemy['health'] -= $burnDamage;
                $enemy['burn_turns']--;
                $effectMessage .= "🔥 Burn deals $burnDamage damage! ";
                if ($enemy['burn_turns'] <= 0) {
                    $enemy['burn_active'] = false;
                    $effectMessage .= "Burn wore off. ";
                }
            }
            $enemy['health'] = max(0, $enemy['health']);
            
            // Check if enemy is stunned from previous turn
            $isEnemyStunned = ($enemy['stunned'] && $enemy['stun_turns'] > 0);
            
            if ($combatAction === 'attack') {
                // Get the attack details from the character
                $attackIndex = isset($_POST['attack_index']) ? intval($_POST['attack_index']) : 0;
                $attacks = $character->attacks;
                $selectedAttack = $attacks[$attackIndex] ?? $attacks[0];
                $attackName = $selectedAttack['name'];
                
                // Calculate base damage using character strength
                $characterStrength = $character ? ($character->stats['strength'] ?? 5) : 5;
                $strengthBonus = rand(1, max(1, $characterStrength));
                $baseDamage = rand(5, 12) + $strengthBonus;
                $damage = $baseDamage;
                $criticalHit = false;
                
                // Intelligence gives 5% critical chance per point
                $intelligence = $character ? ($character->stats['intelligence'] ?? 5) : 5;
                $criticalChance = $intelligence * 5;
                $criticalRoll = rand(1, 100);
                
                if ($criticalRoll <= $criticalChance) {
                    $damage = $damage * 2;
                    $criticalHit = true;
                }
                
                $message = "";
                if ($criticalHit) {
                    $message .= "⚡ CRITICAL HIT! ⚡ ";
                }
                $message .= "You used $attackName! ";
                
                // Apply status chances based on attack
                $bleedChance = 0;
                $burnChance = 0;
                $stunChance = 0;
                $currentLocation = $currentUser['current_location'] ?? 'village';
                
                if (strpos($attackName, 'Fireball') !== false) {
                    $burnChance = 30;
                    if ($currentLocation === 'forest') {
                        $damage = round($damage * 1.1);
                        $message .= "(+10% forest damage!) ";
                    }
                } elseif (strpos($attackName, 'Sword Swing') !== false) {
                    $bleedChance = 30;
                    if ($currentLocation === 'city') {
                        $damage = round($damage * 1.1);
                        $message .= "(+10% city damage!) ";
                    }
                } elseif (strpos($attackName, 'Leg Kick') !== false) {
                    $stunChance = 20;
                } elseif (strpos($attackName, 'Map Strike') !== false) {
                    $bleedChance = 20;
                    if ($currentLocation === 'city') {
                        $damage = round($damage * 1.1);
                        $message .= "(+10% city damage!) ";
                    }
                } elseif (strpos($attackName, 'Torch Jab') !== false) {
                    $burnChance = 25;
                    if ($currentLocation === 'forest') {
                        $damage = round($damage * 1.1);
                        $message .= "(+10% forest damage!) ";
                    }
                }
                
                // Apply bleed chance
                if ($bleedChance > 0 && !$enemy['bleed_active']) {
                    $roll = rand(1, 100);
                    if ($roll <= $bleedChance) {
                        $enemy['bleed_active'] = true;
                        $enemy['bleed_turns'] = 2;
                        $message .= "🩸 Bleed applied! ";
                    }
                }
                
                // Apply burn chance
                if ($burnChance > 0 && !$enemy['burn_active']) {
                    $roll = rand(1, 100);
                    if ($roll <= $burnChance) {
                        $enemy['burn_active'] = true;
                        $enemy['burn_turns'] = 3;
                        $message .= "🔥 Burn applied! ";
                    }
                }
                
                // Apply stun chance
                if ($stunChance > 0 && !$enemy['stunned']) {
                    $roll = rand(1, 100);
                    if ($roll <= $stunChance) {
                        $enemy['stunned'] = true;
                        $enemy['stun_turns'] = 1;
                        $message .= "⚡ Stun applied! ";
                    }
                }
                
                // Apply 10% damage increase if bleed is active on enemy
                if ($enemy['bleed_active']) {
                    $damage = round($damage * 1.1);
                    $message .= "(+10% damage from bleed) ";
                }
                
                // Apply damage
                $enemy['health'] -= $damage;
                $enemy['health'] = max(0, $enemy['health']);
                $message .= "You dealt $damage damage to the {$enemy['name']}!";
                
                // Enemy counterattack - ALWAYS happens if not stunned
                $enemyDamage = 0;
                $counterOccurred = false;
                $stunPreventedCounter = false;
                $enemyStrengthBonus = 0;
                
                if ($enemy['health'] > 0) {
                    if ($isEnemyStunned) {
                        $message .= " The {$enemy['name']} is stunned and cannot attack! ";
                        $enemy['stun_turns']--;
                        if ($enemy['stun_turns'] <= 0) {
                            $enemy['stunned'] = false;
                        }
                        $stunPreventedCounter = true;
                    } else {
                        $enemyStrength = $enemy['strength'];
                        $enemyStrengthBonus = rand(1, max(1, $enemyStrength));
                        $enemyDamage = rand(5, 12) + $enemyStrengthBonus;
                        $playerHP -= $enemyDamage;
                        $playerHP = max(0, $playerHP);
                        $currentUser['temp_hp'] = $playerHP;
                        $message .= " The {$enemy['name']} counterattacks for $enemyDamage damage!";
                        $counterOccurred = true;
                    }
                }
                
                $message .= $effectMessage;
                
                $_SESSION['current_enemy'] = $enemy;
                
                $enemyDefeated = $enemy['health'] <= 0;
                $playerDefeated = $playerHP <= 0;
                
                // Check for enemy defeat FIRST before saving user
                if ($enemyDefeated) {
                    // Reward player for defeating enemy
                    $coinsReward = 2;
                    $xpReward = 3;
                    
                    // Bonus XP for demon castle enemies
                    $currentLocation = $currentUser['current_location'] ?? 'village';
                    if ($currentLocation === 'demon_castle') {
                        $coinsReward = 5;
                        $xpReward = 8;
                    }
                    
                    $currentUser['coins'] = ($currentUser['coins'] ?? 0) + $coinsReward;
                    $currentUser['xp'] = ($currentUser['xp'] ?? 0) + $xpReward;
                    $leveledUp = false;
                    
                    // XP requirements: Level 0->1: 6, 1->2: 9, 2->3: 15, 3->4: 30, Level 4 is max
                    $currentLevel = $currentUser['level'] ?? 0;
                    
                    if ($currentLevel < 4) {
                        $xpNeededForNextLevel = 0;
                        if ($currentLevel == 0) {
                            $xpNeededForNextLevel = 6;
                        } elseif ($currentLevel == 1) {
                            $xpNeededForNextLevel = 9;
                        } elseif ($currentLevel == 2) {
                            $xpNeededForNextLevel = 15;
                        } elseif ($currentLevel == 3) {
                            $xpNeededForNextLevel = 30;
                        }
                        
                        while ($currentUser['xp'] >= $xpNeededForNextLevel && $currentLevel < 4) {
                            $currentUser['xp'] -= $xpNeededForNextLevel;
                            $currentLevel++;
                            $leveledUp = true;
                            
                            // Set next level requirement
                            if ($currentLevel == 1) {
                                $xpNeededForNextLevel = 9;
                            } elseif ($currentLevel == 2) {
                                $xpNeededForNextLevel = 15;
                            } elseif ($currentLevel == 3) {
                                $xpNeededForNextLevel = 30;
                            } elseif ($currentLevel == 4) {
                                $xpNeededForNextLevel = 999;
                            }
                        }
                        
                        $currentUser['level'] = $currentLevel;
                        $currentUser['xp_needed'] = $xpNeededForNextLevel;
                    }
                    
                    $userManager->updateUser($currentUser);
                    unset($_SESSION['current_enemy']);
                    
                    $message .= " 💀 You defeated the {$enemy['name']} and gained $coinsReward coins and $xpReward XP! 💀";
                    
                    if ($leveledUp) {
                        $message .= " 🎉 You leveled up to level {$currentUser['level']}! 🎉";
                    }
                } else {
                    $userManager->updateUser($currentUser);
                }
                
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
                    'burn_turns' => $enemy['burn_turns'],
                    'stun_active' => $enemy['stunned'],
                    'stun_turns' => $enemy['stun_turns'],
                    'counter_damage' => $enemyDamage,
                    'counter_occurred' => $counterOccurred,
                    'stun_prevented_counter' => $stunPreventedCounter,
                    'critical_hit' => $criticalHit,
                    'strength_bonus' => $strengthBonus,
                    'enemy_strength_bonus' => $enemyStrengthBonus
                ];
                
                if ($enemyDefeated) {
                    $response['reward'] = $coinsReward;
                    $response['xp_reward'] = $xpReward;
                    $response['leveled_up'] = $leveledUp;
                    $response['new_level'] = $currentUser['level'];
                    $response['current_xp'] = $currentUser['xp'];
                    $response['xp_needed'] = $currentUser['xp_needed'];
                }
                
                if ($playerDefeated) {
                    $response['message'] .= " 💔 You have been defeated! Game Over! 💔";
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

// Helper functions for location logic

function getRandomEventForLocation($location, $currentUser) {
    if ($location === 'demon_castle') {
        // 100% spawn chance in demon castle, with stronger mushroom
        $enemy = getStrongMushroomEnemy();
        return ['type' => 'encounter', 'enemy' => $enemy];
    }
    
    if ($location === 'river_village') {
        // 1/2 chance of healing, 1/2 chance of nothing
        $roll = rand(1, 2);
        if ($roll == 1) {
            return ['type' => 'heal', 'enemy' => null];
        } else {
            return ['type' => 'safe', 'enemy' => null];
        }
    }
    
    if ($location === 'mountain' || $location === 'mushroom_kingdom') {
        // 1/2 chance of enemy, 1/2 chance of nothing
        $roll = rand(1, 2);
        if ($roll == 1) {
            $enemy = getEnemyByType('mushroom', true);
            return ['type' => 'encounter', 'enemy' => $enemy];
        } else {
            return ['type' => 'safe', 'enemy' => null];
        }
    }
    
    if ($location === 'forest' || $location === 'city') {
        // 3/5 chance of enemy (60%), 1/5 nothing (20%), 1/5 lost (20%)
        $roll = rand(1, 5);
        if ($roll <= 3) {  // 1,2,3 = enemy
            $enemy = getEnemyByType('mushroom', true);
            return ['type' => 'encounter', 'enemy' => $enemy];
        } elseif ($roll == 4) {  // 4 = nothing
            return ['type' => 'safe', 'enemy' => null];
        } else {  // 5 = lost
            return ['type' => 'lost', 'enemy' => null];
        }
    }
    
    return ['type' => 'safe', 'enemy' => null];
}

function getStrongMushroomEnemy() {
    // Stronger mushroom with twice the HP and strength
    $baseHealth = rand(80, 120);  // Twice the normal range (40-60 becomes 80-120)
    $baseStrength = 6 + round(($baseHealth - 80) / 10);  // Starting at 6 instead of 3
    
    $enemy = new Enemy(
        'mushroom',
        'Demon Mushroom',
        $baseHealth,
        $baseStrength,
        20,
        ['hp' => $baseHealth, 'strength' => $baseStrength, 'stamina' => 20, 'intelligence' => 1],
        'Mushroom/Mushroom-Idle.png',
        'Mushroom/Mushroom-Attack.png',
        'Mushroom/Mushroom-Die.png',
        'Mushroom/Mushroom-Hit.png',
        'Mushroom/Mushroom-Run.png',
        'Mushroom/Mushroom-Stun.png'
    );
    
    return $enemy;
}

function getLocationDisplayName($location) {
    $names = [
        'forest' => 'Forest',
        'city' => 'City',
        'mountain' => 'Mountain',
        'mushroom_kingdom' => 'Mushroom Kingdom',
        'river_village' => 'River Village',
        'village' => 'Village',
        'demon_castle' => 'Demon Castle'
    ];
    return $names[$location] ?? ucfirst($location);
}

function getBackgroundImage($location) {
    $images = [
        'forest' => 'forest.png',
        'city' => 'city.png',
        'mountain' => 'mountain.png',
        'mushroom_kingdom' => 'kingdom.png',
        'river_village' => 'river.png',
        'village' => 'village.png',
        'demon_castle' => 'Demon.png'
    ];
    return $images[$location] ?? $location . '.png';
}

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>