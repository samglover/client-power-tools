// Admin Delete-Client Warning
let deleteClientLink    = document.querySelector('#cpt-delete-client-link');

let cptAdminModal       = document.querySelector('.cpt-admin-modal');
let adminModalScreen    = document.querySelector('.cpt-admin-modal-screen');
let deleteCancelButton  = document.querySelector('.cpt-cancel-delete-client');

if (deleteClientLink) {
  deleteClientLink.addEventListener('click', function() {
    cptAdminModal.style.display = 'grid';
    adminModalScreen.style.display = 'block';
  });
}

if (deleteCancelButton) {
  deleteCancelButton.addEventListener('click', function() {
    cptAdminModal.style.display = 'none';
    adminModalScreen.style.display = 'none';
  });
}
