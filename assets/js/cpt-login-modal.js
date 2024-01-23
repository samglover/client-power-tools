const baseURL = [location.protocol, '//', location.host, location.pathname].join('');
const params = new URLSearchParams(location.search);
const loggedIn = document.querySelector('body.logged-in') ? true : false;
const loginLinks = document.querySelectorAll('.cpt-login-link, a[href*="#cpt-login"], a[href*="#cpt-login"]');
const loginModal = document.getElementById('cpt-login');
const loginDismiss = document.querySelector('#cpt-login .cpt-login-modal-dismiss');

if (loginLinks) loginLinks.forEach(element => element.addEventListener('click', showLogin));
if (loginDismiss) loginDismiss.addEventListener('click', closeLogin);
addEventListener('keyup', (event) => {
  if (event.key === 'Escape') closeLogin();
});

// Changes link & button text if already logged in.
if (loggedIn && loginLinks) {
  loginLinks.forEach(function(element) {
    element.innerText = 'Log Out';
  });
}

// Displays the Login Modal 
if (!loggedIn && cpt_vars.isCPT) showLogin(); // On the dashboard page to not-logged-in visitors
if (params.has('cpt_login')) showLogin(); // Based on URL query parameters

function showLogin(event) {
  if (event) event.preventDefault();
  loginModal.classList.add('visible');
}

function closeLogin(event) {
  if (event) event.preventDefault();
  loginModal.classList.remove('visible');

  params.delete('cpt_login');
  params.delete('user');

  if (params.toString().length > 0) {
    history.replaceState({}, '', baseURL + '?' + params);
  } else {
    history.replaceState({}, '', baseURL);
  }
}


// Handles the Internal Navigation and Login Code Functionality
const notices = document.getElementById('cpt-login-notices');
const nonceField = document.getElementById('cpt-login-nonce');
const emailRow = document.getElementById('cpt-login-email');
const emailField = document.getElementById('cpt-login-email-field');
const passwordRow = document.getElementById('cpt-login-password');
const passwordField = document.getElementById('cpt-login-password-field');
const codeRow = document.getElementById('cpt-login-code');
const codeField = document.getElementById('cpt-login-code-field');
const loginTypeLinks = document.getElementById('cpt-login-type-links');
const codeLink = document.getElementById('cpt-login-code-link');
const passwordLink = document.getElementById('cpt-password-link');
const submitButton = document.getElementById('cpt-login-submit-button');

if (submitButton) submitButton.addEventListener('click', sendLoginCode);

if (codeLink) codeLink.addEventListener('click', function(event) {
  event.preventDefault();
  this.style.display = 'none';
  passwordRow.style.display = 'none';
  passwordLink.style.display = 'block';

  submitButton.value = codeRow.dataset.buttonText;
  submitButton.removeEventListener('click', checkPassword);
  submitButton.addEventListener('click', sendLoginCode);
});

if (passwordLink) passwordLink.addEventListener('click', function(event) {
  event.preventDefault();
  this.style.display = 'none';
  passwordRow.style.display = 'block';
  codeLink.style.display = 'block';

  submitButton.value = passwordRow.dataset.buttonText;
  submitButton.removeEventListener('click', sendLoginCode);
  submitButton.addEventListener('click', checkPassword);
});

function displayNotices(response) {
  notices.classList.add('visible');
  if (response.success) notices.classList.add('notice-success');
  if (!response.success) notices.classList.add('notice-error');
  notices.innerHTML = '<p class="cpt-notice-message">' + response.data.message + '</p>';
}

function sendLoginCode(event) {
  event.preventDefault();
  jQuery.ajax({
    type: 'POST',
    url: cpt_vars.ajaxURL,
    data: {
      _ajax_nonce: nonceField.value,
      action: 'send_login_code',
      email: emailField.value
    },
    // beforeSend: function() {},
    success: function(response) {
      // console.debug(response);
      displayNotices(response);
      if (response.success) showCodeField();
    },
    failure: function(error) {
      console.debug(error);
    }
  });
}

if (params.get('cpt_login') == 'code') showCodeField();

function showCodeField() {
  if (emailRow) emailRow.style.display = 'none';
  if (passwordRow) passwordRow.style.display = 'none';
  if (loginTypeLinks) loginTypeLinks.style.display = 'none';
  if (codeRow) codeRow.style.display = 'block';
  submitButton.value = 'Check Code';
  submitButton.removeEventListener('click', sendLoginCode);
  submitButton.addEventListener('click', checkLoginCode);
}

function checkLoginCode(event) {
  event.preventDefault();
  jQuery.ajax({
    type: 'POST',
    url: cpt_vars.ajaxURL,
    data: {
      _ajax_nonce: nonceField.value,
      action: 'check_login_code',
      email: emailField.value ? emailField.value : decodeURIComponent(params.get('user')),
      code: codeField.value
    },
    // beforeSend: function() {},
    success: function(response) {
      // console.debug(response);
      displayNotices(response);
      if (response.success || response.data.tries >= 3) location.reload();
    },
    failure: function(error) {
      console.debug(error);
    }
  });
}

function checkPassword(event) {
  event.preventDefault();
  jQuery.ajax({
    type: 'POST',
    url: cpt_vars.ajaxURL,
    data: {
      _ajax_nonce: nonceField.value,
      action: 'check_password',
      email: emailField.value,
      password: passwordField.value
    },
    // beforeSend: function() {},
    success: function(response) {
      // console.debug(response);
      displayNotices(response);
      if (response.success) location.reload();
    },
    failure: function(error) {
      console.debug(error);
    }
  });
}