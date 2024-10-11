(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.hideFilterOptions = {
    attach: function (context, settings) {

      function updateFieldOptions(singleFormEle, conditionsObj, selectFields = {}, checkboxFields = {}) {

        var arrayIndex = 0;

        /* Create query objects for checkbox fields. */
        $.each(checkboxFields, function (drupalName, singleField) {
          if ($(singleFormEle).find('input[name^=' + singleField + ']').length > 0) {
            var fieldOptions = [];
            $.each($(singleFormEle).find('input[name^=' + singleField + ']'), function (i, checkbox) {
              fieldOptions.push($(checkbox).val());
            });

            /* Pass the field as query object. */
            if (fieldOptions.length > 0) {
              conditionsObj['query_fields'][arrayIndex] = {
                type: 'checkbox',
                element_name: singleField,
                name: drupalName,
                values: fieldOptions,
              };
              arrayIndex++;
            }
          }
        });

        /* Create query objects for select fields. */
        $.each(selectFields, function (drupalName, singleField) {
          if ($(singleFormEle).find('select[name=' + singleField + ']').length > 0) {
            var fieldOptions = [];
            $.each($(singleFormEle).find('select[name=' + singleField + ']'), function (i, select) {
              $.each($(select).find('option'), function (key, selectOption) {
                if ($(selectOption).val() != 'all' && $(selectOption).val() != 'All' && $(selectOption).val() != '' && $(selectOption).val() != undefined) {
                  fieldOptions.push($(selectOption).val());
                }
              });
            });

            /* Pass the field as query object. */
            if (fieldOptions.length > 0) {
              conditionsObj['query_fields'][arrayIndex] = {
                type: 'select',
                element_name: singleField,
                name: drupalName,
                values: fieldOptions,
              };
              arrayIndex++;
            }
          }
        });

        /* Remove trailing and leading slashes */
        var reqPath = drupalSettings.path.pathPrefix.replace(/\/+$/, '');
        reqPath = reqPath.replace(/^\/+/, '');
        reqPath = '/' + reqPath + '/entity-results/count';

        $.ajax({
          url: reqPath,
          type: "POST",
          data: conditionsObj,
          dataType: "json",
          success: function (result) {
            if (result && result.length > 0) {

              /* Update each element based on result. */
              $.each(result, function (key, eleResult) {
                if (eleResult.type && eleResult.value_results && eleResult.element_name) {

                  /* Hide checkboxes. */
                  if (eleResult.type == "checkbox") {
                    $.each(eleResult.value_results, function (fieldVal, resultCount) {
                      if (resultCount <= 0) {
                        $(singleFormEle).find('input[name^=' + eleResult.element_name + '][value="' + fieldVal + '"]').parent('.form-item').remove();
                      }
                    });
                  }

                  /* Hide select options */
                  if (eleResult.type == "select") {
                    $.each(eleResult.value_results, function (fieldVal, resultCount) {
                      if (resultCount <= 0) {
                        $(singleFormEle).find('select[name=' + eleResult.element_name + '] option[value="' + fieldVal + '"]').remove();
                      }
                    });
                  }
                }
              });
            }
          },
        });
      }

      /* Check if correct settings are passed. */
      if (drupalSettings.view_name && drupalSettings.view_extra_conditions) {

        /* Get the List filter form. */
        var listFilterForm = once("list_filter_form", "#case-study-filters-form", context);
        if (listFilterForm.length > 0) {

          var conditionsObj = drupalSettings.view_extra_conditions;

          /* Add fields for which query will be applied. */
          conditionsObj['query_fields'] = {};

          /* Create Checkbox fields array. */
          var checkboxFields = {};
          checkboxFields['field_type_of_project'] = 'field_type_of_project_target_id';
          checkboxFields['field_location'] = 'field_location_target_id';

          /* Create Select fields array. */
          var selectFields = {};
          selectFields['field_collection'] = 'collection';
          selectFields['field_colour'] = 'colour';

          listFilterForm.forEach(function (singleFormEle, index) {
            updateFieldOptions(singleFormEle, conditionsObj, selectFields, checkboxFields);

            /* Update colour field on collection change. */
            var collectionSel = "#" + $(singleFormEle).attr('id') + " select[name=collection]";
            var collectionField = once("collection_field_change", collectionSel, context);
            if (collectionField.length > 0) {

              $(collectionField).change(function() {

                var conditionsObj = drupalSettings.view_extra_conditions;
                /* Add fields for which query will be applied. */
                conditionsObj['query_fields'] = {};

                /* Create Colour field select array. */
                var selectFields = {};
                selectFields['field_colour'] = 'colour';

                setTimeout(function () {
                  updateFieldOptions(singleFormEle, conditionsObj, selectFields);
                }, 2000);
              });
            }
          });
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings, once)
