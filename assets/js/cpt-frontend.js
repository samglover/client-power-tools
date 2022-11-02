// Handles login link clicks.
const loggedIn = document.getElementById('cpt-login-already-logged-in');
const loginLinks = document.querySelectorAll('.cpt-login-link, a[href*="#cpt-login"]');

// Shows/Hides the Login Modal
const loginModal = document.getElementById('cpt-login');
const cptModals = document.querySelectorAll('.cpt-modal');
const modalScreens = document.querySelectorAll('.cpt-modal-screen');

function showLogin() {
  loginModal.style.display = 'grid';
  modalScreens[0].style.display = 'block';
}

if (loginLinks) {
  loginLinks.forEach(function(element) {
    element.addEventListener('click', function(event) {
      event.preventDefault();
      showLogin();
    });
  });
}

// Changes link & button text if already logged in.
if (loggedIn && loginLinks) {
  loginLinks.forEach(function(element) {
    element.innerText = 'Log Out';
  });
}

// Displays the Login Modal on the Dashboard Page
if (!!loggedIn && cpt_vars.postID == cpt_vars.dashboardID) showLogin();

// Displays the Login Modal Based on URL Query Parameters
const baseURL = [location.protocol, '//', location.host, location.pathname].join('');
const params = new URLSearchParams(location.search);

if (params.has('cpt_login')) showLogin();

// Handles Dismiss Button Clicks and Clears Query Parameters
if (cptModals) {
  let i = 0;
  cptModals.forEach(function() {
    let thisModal   = cptModals[i];
    let thisScreen  = modalScreens[i];

    cptModals[i].querySelector('.cpt-modal-dismiss-button').addEventListener('click', function(event) {
      event.preventDefault();
      thisModal.style.display = 'none';
      thisScreen.style.display = 'none';

      // Removes query parameters from the URL just in case the user tries to
      // bookmark it or copy and paste some reason.
      params.delete('cpt_login');
      params.delete('cpt_notice');;
      params.delete('user');

      if (params.toString().length > 0) {
        history.replaceState({}, '', baseURL + '?' + params);
      } else {
        history.replaceState({}, '', baseURL);
      }
    });

    i++;
  });
}

const messages = document.getElementById('cpt-login-messages');
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
const submitButtonValue = submitButton ? submitButton.value : 'Log In';

if (codeLink) codeLink.addEventListener('click', function(event) {
  event.preventDefault();
  this.style.display = 'none';
  passwordRow.style.display = 'none';
  passwordLink.style.display = 'block';

  submitButton.value = 'Send Code';
  submitButton.removeEventListener('click', checkPassword);
  submitButton.addEventListener('click', sendLoginCode);
});

if (passwordLink) passwordLink.addEventListener('click', function(event) {
  event.preventDefault();
  this.style.display = 'none';
  passwordRow.style.display = 'block';
  codeLink.style.display = 'block';

  submitButton.value = submitButtonValue;
  submitButton.removeEventListener('click', sendLoginCode);
  submitButton.addEventListener('click', checkPassword);
});

if (submitButton) submitButton.addEventListener('click', checkPassword);

function displayMessages(response) {
  messages.style.display = 'block';
  messages.className = response.success ? 'success' : 'error';
  messages.innerText = response.data.message;
}

function checkPassword(event) {
  event.preventDefault();
  jQuery.ajax({
    type: 'POST',
    url: cpt_vars.ajaxURL,
    data: {
      _ajax_nonce: cpt_vars.nonce,
      action: 'check_password',
      email: emailField.value,
      password: passwordField.value
    },
    // beforeSend: function() {},
    success: function(response) {
      // console.debug(response);
      displayMessages(response);
      if (response.success) location.reload();
    },
    failure: function(error) {
      console.debug(error);
    }
  });
}

// Sends the Login Code
function sendLoginCode(event) {
  event.preventDefault();
  jQuery.ajax({
    type: 'POST',
    url: cpt_vars.ajaxURL,
    data: {
      _ajax_nonce: cpt_vars.nonce,
      action: 'send_login_code',
      email: emailField.value
    },
    // beforeSend: function() {},
    success: function(response) {
      // console.debug(response);
      displayMessages(response);
      if (response.success) showCodeField();
    },
    failure: function(error) {
      console.debug(error);
    }
  });
}

if (params.get('cpt_login') == 'code') showCodeField();

function showCodeField() {
  emailRow.style.display = 'none';
  passwordRow.style.display = 'none';
  loginTypeLinks.style.display = 'none';
  codeRow.style.display = 'block';
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
      _ajax_nonce: cpt_vars.nonce,
      action: 'check_login_code',
      email: emailField.value ? emailField.value : decodeURIComponent(params.get('user')),
      code: codeField.value
    },
    // beforeSend: function() {},
    success: function(response) {
      console.debug(response);
      displayMessages(response);
      if (response.success || response.data.tries >= 3) location.reload();
    },
    failure: function(error) {
      console.debug(error);
    }
  });
}

// Handles Dismiss Button Clicks for Notices/Inline Modals
// (Not technically modals, but the code overlaps for efficiency.)
const cptInlineModal = document.querySelector('.cpt-notice');
const inlineModalDismiss = document.querySelector('.cpt-notice-dismiss-button');

if (cptInlineModal && inlineModalDismiss) {
  inlineModalDismiss.addEventListener('click', function() {
    cptInlineModal.style.display = 'none';
  });
}
