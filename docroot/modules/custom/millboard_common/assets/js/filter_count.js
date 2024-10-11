(function ($, Drupal, once) {
  Drupal.behaviors.filterCount = {
    attach: function (context, settings) {
      /* Get the List filter form. */
      var listFilterForm = once("count_list_filter_form", ".coh-style-exposed-filters-container form", context);
      if (listFilterForm.length > 0) {
        listFilterForm.forEach(function (singleFormEle, index) {
          var totalFilterCount = 0;
          var countRegex = new RegExp(/^.+\\(\\d+\\)$/);

          /* For all the details tab. */
          if ($(singleFormEle).find('details').length > 0) {
            $.each($(singleFormEle).find('details'), function (i, detailsEle) {
              if(!$(detailsEle).attr("id").includes("-sort-")) {
                var filterCount = 0;
                if ($(detailsEle).find('.form-item').length > 0) {
                  $.each($(detailsEle).find('.form-item'), function (i, formItem) {

                    /* Radio field count. */
                    filterCount = filterCount + $(formItem).find('input:radio:checked:not("[value=all], [value=All]")').length;

                    /* Checkbox field count. */
                    filterCount = filterCount + $(formItem).find('input:checkbox:checked:not("[value=all], [value=All]")').length;

                    /* Select field count. */
                    filterCount = filterCount + $(formItem).find('select :selected:not("[value=all], [value=All]")').length;
                    
                  });
                }

                /* Display count for each filter. */
                if (filterCount > 0) {
                  totalFilterCount = totalFilterCount + filterCount;
                  if ($(detailsEle).find('summary').length > 0) {
                    var summary = $(detailsEle).find('summary')[0];

                    var newText = '';
                    if (countRegex.test($(summary).text())) {
                      newText = $(summary).text().replace(/\$\$(.+?)\$\$/, '(' + filterCount + ')<span class="summary"></span>');
                    }
                    else {
                      newText = $(summary).text() + '(' + filterCount + ')<span class="summary"></span>';
                    }

                    if (newText) {
                      $(summary).html(newText);
                    }
                  }
                }
              }
            });
          }

          /* For all the direct filters without details tab. */
          if ($(singleFormEle).find('.form-item:not(fieldset, details, details .form-item)').length > 0) {
            $.each($(singleFormEle).find('.form-item:not(fieldset, details, details .form-item)'), function (i, directFilterItem) {

              var filterCount = 0;

              /* Radio field count. */
              filterCount = filterCount + $(directFilterItem).find('input:radio:checked:not("[value=all], [value=All]")').length;

              /* Checkbox field count. */
              filterCount = filterCount + $(directFilterItem).find('input:checkbox:checked:not("[value=all], [value=All]")').length;

              /* Select field count. */
              filterCount = filterCount + $(directFilterItem).find('select :selected:not("[value=all], [value=All]")').length;

              /* Display count for each filter. */
              if (filterCount > 0) {
                totalFilterCount = totalFilterCount + filterCount;
                if ($(directFilterItem).find('label').length > 0 && $(directFilterItem).hasClass('form-type-select')) {
                  var label = $(directFilterItem).find('label')[0];
                  var newText = '';
                  if (countRegex.test($(label).text())) {
                    newText = $(label).text().replace(/\$\$(.+?)\$\$/, '(' + filterCount + ')');
                  }
                  else {
                    newText = $(label).text() + '(' + filterCount + ')';
                  }

                  if (newText) {
                    $(label).text(newText);
                  }
                }
              }
            });
          }

          /* Display total count. */
          if (totalFilterCount > 0) {
            if ($(singleFormEle).find('input[id^=edit-reset]').length > 0) {

              /* Display total count with Reset button. */
              /* Clone original btn because changing orig btn text
              *  cause issues.
              *  Trigger click on orig btn when clicked cloned btn.
              */
              var orgResetBtn = $(singleFormEle).find('input[id^=edit-reset]')[0];
              var cloneResetBtn = $(orgResetBtn).clone();
              var newText = $(cloneResetBtn).val() + '(' + totalFilterCount + ')';
              $(cloneResetBtn).val(newText);
              $(orgResetBtn).hide();
              $(orgResetBtn).after($(cloneResetBtn));

              $(cloneResetBtn).click(function(e) {
                e.preventDefault();
                $(orgResetBtn).click();
              });
            }

            /* Display total count with Filter & Sort button. */
            if ($(singleFormEle).parents('.coh-view-contents, div[class*="coh-ce-cpt_view_block_with_filters"]').find('button.coh-style-filter-and-sort-button').length > 0) {
              var label = $(singleFormEle).parents('.coh-view-contents, div[class*="coh-ce-cpt_view_block_with_filters"]').find('button.coh-style-filter-and-sort-button')[0];
              var newText = '';
              if (countRegex.test($(label).text())) {
                newText = $(label).text().replace(/\$\$(.+?)\$\$/, '(' + totalFilterCount + ')');
              }
              else {
                newText = $(label).text() + '(' + totalFilterCount + ')';
              }

              if (newText) {
                $(label).text(newText);
              }

            }
          }
        });
      }
    }
  };
})(jQuery, Drupal, once)
