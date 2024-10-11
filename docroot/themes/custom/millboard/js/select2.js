/**
 * @file
 * script.js
 */

(function ($, Drupal) {
    'use strict';
    Drupal.behaviors.select2 = {
      attach: function (context, setting) {
        // Initialise global country switcher select elements for desktop and mobile
        $('.coh-style-select2--desktop select').select2({
          minimumResultsForSearch: Infinity,
          dropdownParent: $('.coh-style-select2--desktop')
        });

        $('.coh-style-select2--mobile select').select2({
          minimumResultsForSearch: Infinity,
          dropdownParent: $('.coh-style-select2--mobile')
        });

        // Initialise all select elements used in the view exposed forms for filter and sort
        $('.coh-style-exposed-filters-container select').select2({
          minimumResultsForSearch: Infinity,
          dropdownParent: $('.coh-style-exposed-filters-container form')
        });

        // Add/Remove overlay when the country switcher dropdown is clicked
        $('.lang-dropdown-select-element').off('select2:open select2:close').on('select2:open select2:close', function (e) {
          $('#overlay').toggleClass('overlay--show', e.type === 'select2:open');

          if ($(window).width() >= 1024) {
            $('body').toggleClass('no-scroll', e.type === 'select2:open');
          } else {
            setTimeout(function () {
              $('#overlay').toggleClass('overlay--show', e.type === 'select2:open');
              $('.mobile-nav-bottom').toggleClass('mobile-nav-bottom--expanded', e.type === 'select2:open');
              }, 100);
          }
        });

        //Initialised select for Localization banner
        $('.millboard-georedirect-banner select').select2({
          minimumResultsForSearch: Infinity,
          dropdownParent: $('.millboard-georedirect-banner')
        });

        // hubspot form select2() init.
        setTimeout(function () {
        $('body')
        .find('select.hs-input')
        .each((i, el) => {
          let dropdownParent = el.closest('div.hs-fieldtype-select');
          $(el).select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: dropdownParent,
          });
          // hiding the actual select on change.
          $(el).on('change', function () {
            $(this)
              .closest('select.hs-input')
              .addClass('is-placeholder select2-hidden-accessible');
          });
        });
        }, 1000);
      }
    }
  })(jQuery, Drupal);
