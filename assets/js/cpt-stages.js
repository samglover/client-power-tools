(function($) {
  $(document).ready(function() {
    // Get the value of the project type field when it changes.
    const { __ } = wp.i18n;
    const typesField = document.querySelector('select#project_type');
    const stagesField = document.querySelector('select#project_stage');

    if (!typesField || !stagesField) return;

    typesField.addEventListener('change', function(event) {
      let projectType = event.target.value;
      updateStages(projectType);
    });

    // Send the project type value to AJAX to update the stage select field.
    function updateStages(projectType) {
      $.ajax({
        type: 'POST',
        url: vars.ajaxurl,
        data: {
          _ajax_nonce: vars.nonce,
          action: 'cpt_update_stage_select',
          project_type: projectType
        },
        beforeSend: function() {
        },
        success: function(response) {
          // Update stages field values. If the same one exists as was checked before, select it.
          let currentStage = stagesField.value;
          stagesField.innerHTML = '';
          if (response.stages.length > 0) {
            response.stages.forEach(element => {
              let option = document.createElement('option');
              option.value = element;
              option.innerHTML = element;
              if (currentStage == element) option.selected = true;
              stagesField.append(option);
            });
          } else {
            let option = document.createElement('option');
            option.setAttribute('disabled', '');
            option.setAttribute('selected', '');
            option.setAttribute('value', '');
            option.innerHTML = __('Select another project type.', 'client-power-tools');
            option.selected = true;
            stagesField.append(option);
          }
        },
        failure: function(error) {
          console.debug(error);
        }
      });
    }
  });
})(jQuery);