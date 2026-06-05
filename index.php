<?php
session_start();
require_once 'users.php';

$userManager = new UserManager();


if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
    
    
}




?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


    <h1 class="welcome-title">Welcome To The Game, Advanturer</h1>
    
    <div class="forms-container">
        <div class="form-box" id="signupBox">
            <h2>Sign Up</h2>
            <form id="signupForm">
                <label for="regname">Name:</label>
                <input type="text" id="regname" name="name" maxlength="50" required>
                
                <label for="reglname">Last Name:</label>
                <input type="text" id="reglname" name="LName" maxlength="50" required>
                
                <label for="reguser">Username:</label>
                <input type="text" id="reguser" name="username" maxlength="20" required>
                
                <label for="regpass">Create a Password:</label>
                <input type="password" id="regpass" name="password" minlength="6" required>
                
                <div class="form-error" id="signupError"></div>
                <div class="form-success" id="signupSuccess"></div>
                
                <button type="submit" name="submit1" class="submit">Sign Up</button>
            </form>
            <button class="switch-btn" id="showLoginBtn">Log in Instead</button>
        </div>
        
        <div class="form-box" id="loginBox">
            <h3>Log in</h3>
            <form id="loginForm">
                <label for="loginUser">Username:</label>
                <input type="text" id="loginUser" name="username" required>
                
                <label for="loginPass">Password:</label>
                <input type="password" id="loginPass" name="password" required>
                
                <div class="form-error" id="loginError"></div>
                
                <button type="submit" name="submit2" class="submit">Log in</button>
            </form>
            <button class="switch-btn" id="showSignupBtn">Sign up Instead</button>
        </div>
    </div>
    
    
    
    
    
    <div class="detail-box">
        <h4 class="details">Begin your adventure and shape your own destiny.</h4>
        <p class="details-description">Every choice matters in this Game. Battle the Demons,and earn achievements</p>
        <p class="details-note">To get started, log in or create an account.</p>
    </div>

    <script src="script.js"></script>
</body>
</html>