(function($) {
  'use strict';
  $(function() {
    $('[data-toggle="offcanvas"]').on("click", function() {
      $('.sidebar-offcanvas').toggleClass('active')
    });
  });
})(jQuery);

function showLoader() {
  document.getElementById('loader').style.display = 'flex';
}

// Hide loader
function hideLoader() {
  document.getElementById('loader').style.display = 'none';
}