(function($) {
  $(document).ready(function() {
    // Get the value of the project type field when it changes.
    const editLinks = document.querySelectorAll('.cpt-edit-link');
    const editRowHome = document.getElementById('cpt-edit-row-home');
    const editRow = document.getElementById('cpt-edit-row');
    const editID = document.getElementById('edit_project_type_id');
    const editName = document.getElementById('edit_project_type');
    const editStages = document.getElementById('edit_project_type_stages');
    const editCancel = document.getElementById('edit-cancel');

    if (!editLinks || !editRow) return;

    editLinks.forEach(element => {
      element.addEventListener('click', function(event) {
        let row = $(event.target).parents('tr');
        let typeID = $(row).data('id');
        let typeName = $(row).data('name');
        let stages = $(row).data('stages');

        $(editID).val(typeID);
        $(editName).val(typeName);
        $(editStages).val(stages);
        
        $(row).after(editRow);
        $(editRow).show();
      });
    });

    editCancel.addEventListener('click', putBack);

    function putBack(event) {
      if (event) event.preventDefault();
      $(editRow).hide();
      $(editRowHome).append(editRow);
      $(editID).val('');
      $(editName).val('');
      $(editStages).val('');
    }
  });
})(jQuery);