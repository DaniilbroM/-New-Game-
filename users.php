<?php

class UserManager {
    private $usersFile = 'users.json';
    private $users = [];

    public function __construct() {
        $this->loadUsers();
    }

    private function loadUsers() {
        if (!file_exists($this->usersFile)) {
            $this->users = [];
            return;
        }

        $content = file_get_contents($this->usersFile);
        $this->users = json_decode($content, true) ?: [];

        $changed = false;
        foreach ($this->users as &$user) {
            if (!isset($user['friends']) || !is_array($user['friends'])) {
                $user['friends'] = [];
            }
            if (!isset($user['pending_requests']) || !is_array($user['pending_requests'])) {
                $user['pending_requests'] = [];
            }
            if (!isset($user['chat_history']) || !is_array($user['chat_history'])) {
                $user['chat_history'] = [];
            }
            if (!isset($user['last_chat_reset']) || !is_int($user['last_chat_reset'])) {
                $user['last_chat_reset'] = time();
            }
            if (!isset($user['coins']) || !is_int($user['coins'])) {
                $user['coins'] = 0;
                $changed = true;
            }
            if (!isset($user['level']) || !is_int($user['level'])) {
                $user['level'] = 0;
                $changed = true;
            }
            if (!isset($user['xp']) || !is_int($user['xp'])) {
                $user['xp'] = 0;
                $changed = true;
            }
            if (!isset($user['xp_needed']) || !is_int($user['xp_needed'])) {
                $user['xp_needed'] = 6;
                $changed = true;
            }
            if (!isset($user['selected_character']) || $user['selected_character'] === '' || !is_string($user['selected_character'])) {
                $user['selected_character'] = null;
                $changed = true;
            }
            if (!isset($user['character_selected'])) {
                $user['character_selected'] = false;
                $changed = true;
            }
            if (!isset($user['game_started'])) {
                $user['game_started'] = false;
                $changed = true;
            }
            if (!isset($user['current_location'])) {
                $user['current_location'] = 'village';
                $changed = true;
            }
            if (!isset($user['temp_hp'])) {
                $user['temp_hp'] = null;
                $changed = true;
            }
            if (isset($user['last_chat_reset']) && (time() - $user['last_chat_reset'] > 3 * 24 * 60 * 60)) {
                $user['chat_history'] = [];
                $user['last_chat_reset'] = time();
                $changed = true;
            }
        }
        unset($user);

        if ($changed) {
            $this->saveUsers();
        }
    }

    private function saveUsers() {
        file_put_contents($this->usersFile, json_encode($this->users, JSON_PRETTY_PRINT));
    }

    public function sanitizeInput($input) {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
    }

    public function getAllUsers() {
        return $this->users;
    }

    public function getUserByUsername($username) {
        if ($username === null || $username === '') {
            return null;
        }
        $username = $this->sanitizeInput($username);

        foreach ($this->users as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }

        return null;
    }

    public function updateUser(array $updatedUser) {
        foreach ($this->users as $index => $user) {
            if ($user['username'] === $updatedUser['username']) {
                $this->users[$index] = $updatedUser;
                $this->saveUsers();
                return true;
            }
        }
        return false;
    }

    public function registerUser($name, $lname, $username, $password) {
        $name = $this->sanitizeInput($name);
        $lname = $this->sanitizeInput($lname);
        $username = $this->sanitizeInput($username);

        if (empty($name) || empty($lname) || empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        foreach ($this->users as $user) {
            if ($user['username'] === $username) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $newUser = [
            'name' => $name,
            'LName' => $lname,
            'username' => $username,
            'password' => $hashedPassword,
            'friends' => [],
            'pending_requests' => [],
            'chat_history' => [],
            'last_chat_reset' => time(),
            'coins' => 0,
            'level' => 0,
            'xp' => 0,
            'xp_needed' => 6,
            'selected_character' => null,
            'character_selected' => false,
            'game_started' => false,
            'current_location' => 'village',
            'temp_hp' => null
        ];

        $this->users[] = $newUser;
        if (isset($_SESSION['users']) && is_array($_SESSION['users'])) {
            $_SESSION['users'][] = $newUser;
        }
        $this->saveUsers();

        return ['success' => true, 'message' => 'Registration successful'];
    }

    public function loginUser($username, $password) {
        if ($username === null || $password === null) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }
        
        $username = $this->sanitizeInput($username);

        foreach ($this->users as $user) {
            if ($user['username'] === $username) {
                if (password_verify($password, $user['password'])) {
                    if (!isset($user['friends']) || !is_array($user['friends'])) {
                        $user['friends'] = [];
                    }
                    if (!isset($user['pending_requests']) || !is_array($user['pending_requests'])) {
                        $user['pending_requests'] = [];
                    }
                    if (!isset($user['chat_history']) || !is_array($user['chat_history'])) {
                        $user['chat_history'] = [];
                    }
                    if (!isset($user['last_chat_reset']) || !is_int($user['last_chat_reset'])) {
                        $user['last_chat_reset'] = time();
                    }
                    if (!isset($user['coins'])) {
                        $user['coins'] = 0;
                    }
                    if (!isset($user['level'])) {
                        $user['level'] = 0;
                    }
                    if (!isset($user['xp'])) {
                        $user['xp'] = 0;
                    }
                    if (!isset($user['xp_needed'])) {
                        $user['xp_needed'] = 6;
                    }
                    if (!isset($user['selected_character'])) {
                        $user['selected_character'] = null;
                    }
                    if (!isset($user['character_selected'])) {
                        $user['character_selected'] = false;
                    }
                    if (!isset($user['game_started'])) {
                        $user['game_started'] = false;
                    }
                    if (!isset($user['current_location'])) {
                        $user['current_location'] = 'village';
                    }
                    if (!isset($user['temp_hp'])) {
                        $user['temp_hp'] = null;
                    }
                    return ['success' => true, 'user' => $user];
                }
                break;
            }
        }

        return ['success' => false, 'message' => 'Invalid username or password'];
    }

    public function addXP($username, $xpGain) {
        $user = $this->getUserByUsername($username);
        if (!$user) return false;
        
        $user['xp'] += $xpGain;
        $leveledUp = false;
        $oldLevel = $user['level'];
        
        // XP requirements: Level 0->1: 6, 1->2: 9, 2->3: 15, 3->4: 30, Level 4 is max
        while ($user['level'] < 4 && $user['xp'] >= $user['xp_needed']) {
            $user['xp'] -= $user['xp_needed'];
            $user['level']++;
            $leveledUp = true;
            
            // Set next level requirement
            if ($user['level'] == 1) {
                $user['xp_needed'] = 9;
            } elseif ($user['level'] == 2) {
                $user['xp_needed'] = 15;
            } elseif ($user['level'] == 3) {
                $user['xp_needed'] = 30;
            } elseif ($user['level'] == 4) {
                $user['xp_needed'] = 999; // Max level
            }
        }
        
        $this->updateUser($user);
        return ['success' => true, 'leveled_up' => $leveledUp, 'new_level' => $user['level']];
    }

    public function addCoins($username, $coinsGain) {
        $user = $this->getUserByUsername($username);
        if (!$user) return false;
        
        $user['coins'] += $coinsGain;
        $this->updateUser($user);
        return ['success' => true, 'new_coins' => $user['coins']];
    }

    public function getChatHistory($username, $friendUsername) {
        $username = $this->sanitizeInput($username);
        $friendUsername = $this->sanitizeInput($friendUsername);

        $user = $this->getUserByUsername($username);
        if (!$user || !isset($user['chat_history'][$friendUsername])) {
            return [];
        }

        return $user['chat_history'][$friendUsername];
    }

    public function sendMessage($fromUsername, $toUsername, $message) {
        $fromUsername = $this->sanitizeInput($fromUsername);
        $toUsername = $this->sanitizeInput($toUsername);
        $message = trim($message);

        if (empty($message)) {
            return ['success' => false, 'message' => 'Message cannot be empty.'];
        }

        $fromUser = $this->getUserByUsername($fromUsername);
        $toUser = $this->getUserByUsername($toUsername);

        if (!$fromUser || !$toUser) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if (!in_array($toUsername, $fromUser['friends'], true)) {
            return ['success' => false, 'message' => 'You can only send messages to friends.'];
        }

        if (!isset($fromUser['chat_history'][$toUsername]) || !is_array($fromUser['chat_history'][$toUsername])) {
            $fromUser['chat_history'][$toUsername] = [];
        }
        if (!isset($toUser['chat_history'][$fromUsername]) || !is_array($toUser['chat_history'][$fromUsername])) {
            $toUser['chat_history'][$fromUsername] = [];
        }

        $entry = [
            'sender' => $fromUsername,
            'text' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
            'created_at' => time(),
            'read' => true
        ];

        $recipientEntry = [
            'sender' => $fromUsername,
            'text' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
            'created_at' => time(),
            'read' => false
        ];

        $fromUser['chat_history'][$toUsername][] = $entry;
        $toUser['chat_history'][$fromUsername][] = $recipientEntry;

        $this->updateUser($fromUser);
        $this->updateUser($toUser);

        return ['success' => true, 'message' => 'Message sent.'];
    }

    public function markMessagesRead($username, $friendUsername) {
        $username = $this->sanitizeInput($username);
        $friendUsername = $this->sanitizeInput($friendUsername);

        $user = $this->getUserByUsername($username);
        if (!$user || !isset($user['chat_history'][$friendUsername]) || !is_array($user['chat_history'][$friendUsername])) {
            return ['success' => true, 'message' => 'No messages to mark as read.'];
        }

        $changed = false;
        foreach ($user['chat_history'][$friendUsername] as &$message) {
            if (!$message['read'] && $message['sender'] === $friendUsername) {
                $message['read'] = true;
                $changed = true;
            }
        }
        unset($message);

        if ($changed) {
            $this->updateUser($user);
        }

        return ['success' => true, 'message' => 'Messages marked as read.'];
    }

    public function getUnreadMessageCount($username) {
        $user = $this->getUserByUsername($username);
        if (!$user || !isset($user['chat_history']) || !is_array($user['chat_history'])) {
            return 0;
        }

        $count = 0;
        foreach ($user['chat_history'] as $messages) {
            foreach ($messages as $message) {
                if (isset($message['sender'], $message['read']) && !$message['read']) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function getFriendProfiles($username) {
        $user = $this->getUserByUsername($username);
        if (!$user || !isset($user['friends']) || !is_array($user['friends'])) {
            return [];
        }

        $friends = [];
        foreach ($user['friends'] as $friendUsername) {
            $friend = $this->getUserByUsername($friendUsername);
            if ($friend) {
                $friends[] = [
                    'username' => $friend['username'],
                    'name' => $friend['name'],
                    'LName' => $friend['LName']
                ];
            }
        }

        return $friends;
    }

    public function getNotifications($username) {
        $user = $this->getUserByUsername($username);
        if (!$user) {
            return [];
        }
        return isset($user['pending_requests']) ? $user['pending_requests'] : [];
    }

    public function getUnreadNotifications($username) {
        $user = $this->getUserByUsername($username);
        if (!$user) {
            return 0;
        }

        $count = isset($user['pending_requests']) ? count($user['pending_requests']) : 0;
        $count += $this->getUnreadMessageCount($username);

        return $count;
    }

    public function addFriendRequest($fromUsername, $toUsername) {
        $fromUsername = $this->sanitizeInput($fromUsername);
        $toUsername = $this->sanitizeInput($toUsername);

        if ($fromUsername === $toUsername) {
            return ['success' => false, 'message' => 'You cannot add yourself.'];
        }

        $fromUser = $this->getUserByUsername($fromUsername);
        $toUser = $this->getUserByUsername($toUsername);

        if (!$fromUser || !$toUser) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if (in_array($toUsername, $fromUser['friends'], true)) {
            return ['success' => false, 'message' => 'This user is already your friend.'];
        }

        foreach ($toUser['pending_requests'] as $request) {
            if ($request['from'] === $fromUsername) {
                return ['success' => false, 'message' => 'Friend request already sent.'];
            }
        }

        $toUser['pending_requests'][] = [
            'from' => $fromUsername,
            'created_at' => time()
        ];

        $this->updateUser($toUser);

        return ['success' => true, 'message' => 'Friend request sent.'];
    }

    public function searchUsers($term, $currentUsername, $limit = 20) {
        $term = $this->sanitizeInput($term);
        $currentUsername = $this->sanitizeInput($currentUsername);
        $results = [];

        foreach ($this->users as $user) {
            if ($user['username'] === $currentUsername) {
                continue;
            }

            $friends = isset($user['friends']) ? $user['friends'] : [];
            if (in_array($currentUsername, $friends, true)) {
                continue;
            }

            $pendingRequests = isset($user['pending_requests']) ? $user['pending_requests'] : [];
            $isAlreadyRequested = false;
            foreach ($pendingRequests as $request) {
                if ($request['from'] === $currentUsername) {
                    $isAlreadyRequested = true;
                    break;
                }
            }

            if ($isAlreadyRequested) {
                continue;
            }

            if ($term === '' || stripos($user['username'], $term) !== false || stripos($user['name'], $term) !== false || stripos($user['LName'], $term) !== false) {
                $results[] = [
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'LName' => $user['LName']
                ];
            }

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    public function respondToFriendRequest($username, $fromUsername, $accept) {
        $username = $this->sanitizeInput($username);
        $fromUsername = $this->sanitizeInput($fromUsername);

        $user = $this->getUserByUsername($username);
        $fromUser = $this->getUserByUsername($fromUsername);

        if (!$user || !$fromUser) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $pendingRequests = isset($user['pending_requests']) ? $user['pending_requests'] : [];
        $newPendingRequests = [];
        $found = false;

        foreach ($pendingRequests as $request) {
            if ($request['from'] === $fromUsername) {
                $found = true;
                continue;
            }
            $newPendingRequests[] = $request;
        }

        if (!$found) {
            return ['success' => false, 'message' => 'Friend request not found.'];
        }

        $user['pending_requests'] = $newPendingRequests;

        if ($accept) {
            if (!isset($user['friends']) || !is_array($user['friends'])) {
                $user['friends'] = [];
            }
            if (!in_array($fromUsername, $user['friends'], true)) {
                $user['friends'][] = $fromUsername;
            }
            if (!isset($fromUser['friends']) || !is_array($fromUser['friends'])) {
                $fromUser['friends'] = [];
            }
            if (!in_array($username, $fromUser['friends'], true)) {
                $fromUser['friends'][] = $username;
            }
        }

        $this->updateUser($user);
        $this->updateUser($fromUser);

        return [
            'success' => true,
            'message' => $accept ? 'Friend request accepted.' : 'Friend request denied.'
        ];
    }
}
?>