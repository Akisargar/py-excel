(function ($, Drupal) {
  Drupal.behaviors.googlePlacesAutocomplete = {
    attach: function (context, settings) {
      var locationInput = document.getElementById('location-autocomplete');
      if (locationInput) {
        var autocomplete = new google.maps.places.Autocomplete(locationInput);

        autocomplete.addListener('place_changed', function () {
          var place = autocomplete.getPlace();
          if (!place.geometry) {
            console.log("No details available for input: '" + locationInput.value + "'");
            return;
          }

          // console.log("Selected Place: ", place);
          var addressLine1Field = document.getElementById("address_line_1");
          var addressLine2Field = document.getElementById("address_line_2");
          var zipcodeField = document.getElementById("zipcode");
          var cityTownField = document.getElementById("city_town");
          var countryField = document.getElementById("country");
          var countryFieldselect = document.getElementById("country_select");
          var stateField =  document.getElementById("state");

          var addressInfo = {
            address_line_1: "",
            address_line_2: "",
            zipcode: "",
            city_town: "",
            state: "",
            country: ""
          };

          if (place.address_components) {
            place.address_components.forEach(function (component) {
              if (component.types.includes("subpremise")) {
                addressInfo.address_line_1 = component.long_name;
              } else if (component.types.includes("premise")) {
                addressInfo.address_line_1 += (addressInfo.address_line_1 ? ", " : "") + component.long_name;
              } else if (component.types.includes("street_number")) {
                addressInfo.address_line_1 += (addressInfo.address_line_1 ? ", " : "") + component.long_name;
              } else if (component.types.includes("route")) {
                addressInfo.address_line_1 += (addressInfo.address_line_1 ? ", " : "") + component.long_name;
              } else if (component.types.includes("sublocality")) {
                addressInfo.address_line_2 = component.long_name;
              } else if (component.types.includes("locality") || component.types.includes("postal_town")) {
                addressInfo.city_town = component.long_name;
              } else if (component.types.includes("administrative_area_level_1")) {
                addressInfo.state = component.long_name;
              } else if (component.types.includes("postal_code")) {
                addressInfo.zipcode = component.long_name;
              } else if (component.types.includes("country")) {
                addressInfo.country = component.long_name;
              }
            });
          }

          function updateField(field, value) {
            if (field) {
              setTimeout(() => {
                field.focus();
                field.value = value;
              }, 50);
            }
          }

          updateField(addressLine1Field, addressInfo.address_line_1);
          updateField(addressLine2Field, addressInfo.address_line_2);
          updateField(zipcodeField, addressInfo.zipcode);
          updateField(cityTownField, addressInfo.city_town);
          updateField(stateField, addressInfo.state);
          updateField(countryField, addressInfo.country);

          function updateSelectField(field, value) {
            if (field) {
              field.focus();
              if ($(field).hasClass("select2-hidden-accessible")) {
                $(field).val(value).trigger('change');
              } else {
                field.value = value;
              }
              $(field).parent().click();
            }
          }
          updateSelectField(countryFieldselect, addressInfo.country);
        });
      }
    }
  };
})(jQuery, Drupal);