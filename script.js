const signupBox = document.getElementById('signupBox');
const showLoginBtn = document.getElementById('showLoginBtn');
const showSignupBtn = document.getElementById('showSignupBtn');
const signupForm = document.getElementById('signupForm');
const loginForm = document.getElementById('loginForm');
const signupError = document.getElementById('signupError');
const loginError = document.getElementById('loginError');
const signupSuccess = document.getElementById('signupSuccess');


const formbox = document.querySelector('.form-box');
const loginBox = document.getElementById('loginBox');
const audio = new Audio('welcome.mp3');
audio.preload = 'auto';



signupForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    signupError.style.display = 'none';
    signupSuccess.style.display = 'none';
    
    const formData = new FormData(signupForm);
    formData.append('action', 'signup');
    
    try {
        const response = await fetch('handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            localStorage.setItem('hasUsers', 'true');
            signupSuccess.textContent = result.message;
            signupSuccess.style.display = 'block';
            
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);
        } else {
            signupError.textContent = result.message;
            signupError.style.display = 'block';
        }
    } catch (error) {
        signupError.textContent = 'An error occurred. Please try again.';
        signupError.style.display = 'block';
    }
});

loginForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    loginError.style.display = 'none';
    
    const formData = new FormData(loginForm);
    formData.append('action', 'login');
    
    try {
        const response = await fetch('handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            loginError.style.display = 'none';
            const successDiv = document.createElement('div');
            successDiv.className = 'form-success';
            successDiv.textContent = result.message;
            successDiv.style.display = 'block';
            loginBox.insertBefore(successDiv, loginForm);
            
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);
        } else {
            loginError.textContent = result.message;
            loginError.style.display = 'block';
        }
    } catch (error) {
        loginError.textContent = 'An error occurred. Please try again.';
        loginError.style.display = 'block';
    }
});

function checkUsersExist() {
    fetch('users.json')
        .then(response => response.json())
        .then(users => {
            if (users && users.length > 0) {
                localStorage.setItem('hasUsers', 'true');
                loginBox.style.display = 'block';
                signupBox.style.display = 'none';
            } else {
                localStorage.setItem('hasUsers', 'false');
                loginBox.style.display = 'none';
                signupBox.style.display = 'block';
            }
        })
        .catch(() => {
            loginBox.style.display = 'none';
            signupBox.style.display = 'block';
        });
}

showLoginBtn.addEventListener('click', function() {
    signupBox.style.animation = 'fadeInDown 0.3s ease-out reverse';
    setTimeout(() => {
        signupBox.style.display = 'none';
        signupBox.style.animation = '';
        loginBox.style.display = 'block';
        loginBox.style.animation = 'fillPage 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
        signupError.style.display = 'none';
        signupSuccess.style.display = 'none';
        signupForm.reset();
    }, 200);
});

showSignupBtn.addEventListener('click', function() {
    loginBox.style.animation = 'fadeInDown 0.3s ease-out reverse';
    setTimeout(() => {
        loginBox.style.display = 'none';
        loginBox.style.animation = '';
        signupBox.style.display = 'block';
        signupBox.style.animation = 'fillPage 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
        loginError.style.display = 'none';
        loginForm.reset();
    }, 200);
});

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('logout') === 'true') {
    loginBox.style.display = 'block';
    signupBox.style.display = 'none';
} else {
    checkUsersExist();
}