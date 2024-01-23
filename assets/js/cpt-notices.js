// Handles Dismiss Button Clicks for Notices/Inline Modals
// (Not technically modals, but the code overlaps for efficiency.)
const cptInlineModal = document.querySelector('.cpt-notice');
const inlineModalDismiss = document.querySelector('.cpt-notice-dismiss');

if (cptInlineModal && inlineModalDismiss) {
  inlineModalDismiss.addEventListener('click', function() {
    cptInlineModal.classList.remove('visible');
  });
}
