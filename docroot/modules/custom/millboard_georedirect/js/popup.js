(function ($, Drupal) {
    Drupal.behaviors.customPopUp = {
      attach: function (context, settings) {
        $.get('/'+drupalSettings.path.currentLanguage+'/millboard-georedirect/banner', function (data) {
        $('#redirect-banner').html(data.html).fadeIn();
        });

        document.addEventListener('click', function (event) {
          if (event.target.matches('.popup-wrapper .coh-style-close-button')) {
            document.getElementById('redirect-banner').style.display = 'none';
          }
        });
      }
    };
  })(jQuery, Drupal);
  