// Shows/Hides the Login Modal
const loginModal = document.getElementById('cpt-login');
const loginPanel = document.getElementById('cpt-login-modal-login');
const resetPasswordPanel = document.getElementById('cpt-login-modal-resetpw');
const modalScreen = document.querySelectorAll('.cpt-modal-screen');

function showLogin() {
  if (resetPasswordPanel) resetPasswordPanel.style.display = 'none';
  if (loginPanel) loginPanel.style.display = 'block';
  loginModal.style.display = 'grid';
  modalScreen[0].style.display = 'block';
}

function showResetPW() {
  if (resetPasswordPanel) resetPasswordPanel.style.display = 'block';
  if (loginPanel) loginPanel.style.display = 'none';
  loginModal.style.display = 'grid';
  modalScreen[0].style.display = 'block';
}


// Handles Login Link Clicks
const loggedIn = document.getElementById('cpt-login-modal-already-logged-in');
const loginLinks = document.querySelectorAll('.cpt-login-link, a[href*="#cpt-login"]');

if (loggedIn && loginLinks) {
  loginLinks.forEach(function(element) {
    element.innerHTML = 'Log Out';
  });
}

if (loginLinks) {
  loginLinks.forEach(function(element) {
    element.addEventListener('click', function(event) {
      event.preventDefault();
      showLogin();
    });
  });
}


// Displays the Login Modal on the Dashboard Page
if (!loggedIn && cpt_frontend_js_vars.postID == cpt_frontend_js_vars.dashboardID) showLogin();


// Displays the Login Modal Based on URL Query Parameters
const baseURL = [location.protocol, '//', location.host, location.pathname].join('');
const params = new URLSearchParams(location.search);

if (params.has('cpt_login')) {
  switch (params.get('cpt_login')) {
    case 'resetpw':
      showResetPW();
      break;
    case 'login':
    default:
      showLogin();
      break;
  }
}


// Handles Dismiss Button Clicks and Clears Query Parameters
const cptModal = document.querySelectorAll('.cpt-modal');

if (cptModal) {
  let i = 0;

  cptModal.forEach(function() {
    let thisModal   = cptModal[i];
    let thisScreen  = modalScreen[i];

    cptModal[i].querySelector('.cpt-modal-dismiss-button').addEventListener('click', function(event) {
      event.preventDefault();

      thisModal.style.display = 'none';
      thisScreen.style.display = 'none';

      // Removes the cpt_login, cpt_notice, and password set/reset query
      // parameters from the URL just in case the user tries to bookmark it or
      // copy and paste some reason.
      params.delete('cpt_login');
      params.delete('cpt_notice');
      params.delete('cpt_error');
      params.delete('key');
      params.delete('login');

      if (params.toString().length > 0) {
        history.replaceState({}, '', baseURL + '?' + params);
      } else {
        history.replaceState({}, '', baseURL);
      }
    });

    i++;
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


// Handles Switching Login Panels
const goToResetPassword = document.getElementById('cpt-login-go-to-resetpw');
const goToLogin = document.getElementById('cpt-login-go-to-login');

if (goToResetPassword) {
  goToResetPassword.addEventListener('click', function(event) {
    event.preventDefault();
    showResetPW();
  });
}

if (goToLogin) {
  goToLogin.addEventListener('click', function(event) {
    event.preventDefault();
    showLogin();
  });
}


// Handles the Magic Link
const codeLink = document.getElementById('cpt-login-code-link');
const pwLink = document.getElementById('cpt-password-link');
const forgotpwLink = document.getElementById('cpt-password-link');
const pwField = document.querySelector('#cpt-loginform .login-password');

const submitBtn = document.getElementById('cpt-login-modal-submit');
const submitBtnVal = submitBtn.value;

codeLink.addEventListener('click', function(event) {
  event.preventDefault();

  this.style.display = 'none';
  pwField.style.display = 'none';

  pwLink.style.display = 'block';

  submitBtn.value = 'Send Code';
  submitBtn.addEventListener('click', handleSubmitClick);
});

pwLink.addEventListener('click', function(event) {
  event.preventDefault();

  this.style.display = 'none';

  pwField.style.display = 'block';
  codeLink.style.display = 'block';

  submitBtn.value = submitBtnVal;
  submitBtn.removeEventListener('click', handleSubmitClick);
});

function handleSubmitClick(event) {
  event.preventDefault();
  let email = document.getElementById('cpt-login-modal-username').value;
  if (email) sendLoginCode(email);
}


// Sends the Login Code
const loginCodePanel = document.getElementById('cpt-login-code');
const loginCodeField = document.getElementById('cpt-check-login-code');

function sendLoginCode(email) {
  jQuery.ajax({
    type: 'POST',
    url: cpt_frontend_js_vars.ajaxurl,
    data: {
      _ajax_nonce: cpt_frontend_js_vars.nonce,
      action: 'send_login_code',
      email: email
    },
    beforeSend: function() {
      console.log('Sending …');
    },
    success: function(response) {
      // console.debug(response);
      loginPanel.style.display = 'none';
      resetPasswordPanel.style.display = 'none';

      loginCodePanel.style.display = 'grid';
      loginCodeField.addEventListener('change', handleSubmitLoginCode);
    },
    failure: function(error) {
      console.debug(error);
    }
  });
}

function handleSubmitLoginCode(event) {
  event.preventDefault();
  if (event.target.value.length == 8) checkLoginCode(event.target.value);
}

function checkLoginCode(code) {
  jQuery.ajax({
    type: 'POST',
    url: cpt_frontend_js_vars.ajaxurl,
    data: {
      _ajax_nonce: cpt_frontend_js_vars.nonce,
      action: 'check_login_code',
      email: email,
      code: code
    },
    beforeSend: function() {
    },
    success: function(response) {
      console.debug(response);
    },
    failure: function(error) {
      console.debug(error);
    }
  });
}
