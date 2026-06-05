<?php
session_start();
require_once 'users.php';

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
                    ],
                    'recommended' => $userManager->searchUsers('', $currentUser['username'], 10),
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
                    $forestOptions = [
                        [
                            'outcome' => 'encounter',
                            'message' => 'While walking through the forest you encountered Dark Elfs.',
                            'detail' => 'Prepare for a close-range fight among the trees.',
                            'background' => 'forest.png'
                        ],
                        [
                            'outcome' => 'none',
                            'message' => 'The forest trail is clear and no enemies were found.',
                            'detail' => 'Use this quiet moment to catch your breath.',
                            'background' => 'forest.png'
                        ],
                        [
                            'outcome' => 'lost',
                            'message' => 'You got lost in the forest.',
                            'detail' => 'Stay calm and look for a familiar path.',
                            'background' => 'random1.png'
                        ]
                    ];

                    $cityOptions = [
                        [
                            'outcome' => 'encounter',
                            'message' => 'While walking through the city you encountered City Knights.',
                            'detail' => 'The streets are crowded and this fight could attract attention.',
                            'background' => 'city.png'
                        ],
                        [
                            'outcome' => 'none',
                            'message' => 'The city streets are calm and no enemies were found.',
                            'detail' => 'Take advantage of this pause to plan your next move.',
                            'background' => 'city.png'
                        ],
                        [
                            'outcome' => 'lost',
                            'message' => 'On the way to the city you got lost.',
                            'detail' => 'Try to find a familiar street or landmark.',
                            'background' => 'random1.png'
                        ]
                    ];

                    $options = $location === 'forest' ? $forestOptions : $cityOptions;
                    $choice = random_int(0, count($options) - 1);
                    $selection = $options[$choice];

                    $currentUser['last_area'] = $location;
                    $saved = $userManager->updateUser($currentUser);

                    $response = [
                        'success' => true,
                        'outcome' => $selection['outcome'],
                        'message' => $selection['message'],
                        'detail' => $selection['detail'],
                        'background' => $selection['background'],
                        'next' => null,
                    ];
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
                $level = max(1, intval($_POST['level'] ?? $currentUser['level']));
                $characterSelected = filter_var($_POST['character_selected'] ?? ($currentUser['character_selected'] ?? false), FILTER_VALIDATE_BOOLEAN);
                $gameStarted = filter_var($_POST['game_started'] ?? ($currentUser['game_started'] ?? false), FILTER_VALIDATE_BOOLEAN);

                $currentUser['selected_character'] = $selectedCharacter;
                $currentUser['coins'] = $coins;
                $currentUser['level'] = $level;
                $currentUser['character_selected'] = $characterSelected;
                $currentUser['game_started'] = $gameStarted;

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

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>