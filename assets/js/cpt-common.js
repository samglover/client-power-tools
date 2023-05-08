(function($) {
  // Expanders
  $(document).ready(function(){
    // When using the expander, every .cpt-click-to-expand should be followed by
    // a .cpt-this-expands, so that the node list indexes match up.
    let expanderButtons = document.querySelectorAll('.cpt-click-to-expand');
    let buttonText      = [];
    let expandableDivs  = document.querySelectorAll('.cpt-this-expands');

    if (expanderButtons) {
      for (let i = 0; i < expanderButtons.length; i++) {
        buttonText[i] = expanderButtons[i].innerHTML;

        $(expanderButtons[i]).click(function(event) {
          event.preventDefault();
          if ($(expanderButtons[i]).hasClass('open')){
            expanderButtons[i].classList.remove('open');
            expandableDivs[i].classList.remove('open');
          } else {
            for (let i = 0; i < expanderButtons.length; i++) {
              expanderButtons[i].classList.remove('open');
              expanderButtons[i].innerHTML = buttonText[i];
              expandableDivs[i].classList.remove('open');
            }
            expanderButtons[i].classList.add('open');
            expandableDivs[i].classList.add('open');
          }

          // This adds/removes the *required* attribute based on form visibility.
          let formElements = expandableDivs[i].querySelectorAll('form input, form select, form textarea');

          if (expandableDivs[i].classList.contains('open')) {
            if (!expandableDivs[i].classList.contains('cpt-nav-tabs-submenu')) {
              expanderButtons[i].innerHTML = 'Cancel';
            }
            formElements.forEach(function(element) {
              if (element.dataset.required == 'true') {
                element.setAttribute('required', '');
                element.setAttribute('aria-required', 'true');
              }
            });
          } else {
            expanderButtons[i].innerHTML = buttonText[i];
            formElements.forEach(function(element){
              if (element.dataset.required == 'true') {
                element.removeAttribute('required', '');
                element.removeAttribute('aria-required', 'true');
              }
            });
          }
        });
      }
    }
  });


  // Adjust anchor targets.
  $(document).ready(function(){
    let target = $(location.hash);
    if (target.length > 0) {
      let adminBar  = $('#wpadminbar').outerHeight();
      let offset    = target.offset();
      let scrollTo  = offset.top - (20 + adminBar);

      $('html, body').animate({ scrollTop: scrollTo });
    }
  });
})(jQuery);
