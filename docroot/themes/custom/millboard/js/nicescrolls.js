/**
 * @file
 * script.js
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.nicescrollscripts = {
    attach: function (context, setting) {
      // selectors
      const find_installers_results_wrapper = $(
        once('nice-scroll', '.find-installers-results-wrapper', context),
      );
      // nicescroll init

      $("div[id^='ascrail']").remove();
      find_installers_results_wrapper.niceScroll({
        cursorfixedheight: 80,
        cursorcolor: '#62554D',
        autohidemode: false,
        disableoutline: true,
        preservenativescrolling: true,
        railoffset: !0;
      });

      $(window).scroll(function () {
        if ($('.secondary-header-container').hasClass('hide')) {
          window.dispatchEvent(new Event('resize'));
          $('.find-installers-results-wrapper').getNiceScroll().resize();
        }
      });
    },
  };
})(jQuery, Drupal);
