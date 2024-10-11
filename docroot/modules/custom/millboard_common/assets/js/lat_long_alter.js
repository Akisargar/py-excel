(function ($, Drupal, once) {
  Drupal.behaviors.latLongAlter = {
    attach: function (context, settings) {

      /* Add help text below Gelocation field, having Google Maps in form display. */
      var geolocationLatInput = once("geolocation_lat_input", ".geolocation-input-latitude", context);
      if (geolocationLatInput.length > 0) {
        geolocationLatInput.forEach(function (singleEle, index) {
          if ($(singleEle).siblings('.form-item__description').length <= 0) {
            var latHelpText = '<div class="form-item__description">';
            latHelpText = latHelpText + 'Enter either in decimal <em class="placeholder">51.47879</em> or sexagesimal format <em class="placeholder">51° 28\' 43.644"</em>';
            latHelpText = latHelpText + '</div>';
            $(singleEle).after(latHelpText);
          }
        });
      }

      var geolocationLongInput = once("geolocation_long_input", ".geolocation-input-longitude", context);
      if (geolocationLongInput.length > 0) {
        geolocationLongInput.forEach(function (singleEle, index) {
          if ($(singleEle).siblings('.form-item__description').length <= 0) {
            var longHelpText = '<div class="form-item__description">';
            longHelpText = longHelpText + 'Enter either in decimal <em class="placeholder">-0.010677</em> or sexagesimal format <em class="placeholder">-0° 38.4372"</em>';
            longHelpText = longHelpText + '</div>';
            $(singleEle).after(longHelpText);
          }
        });
      }
    }
  };
})(jQuery, Drupal, once)
