"use strict";

/**
 * @file
 * script.js
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.nicescrollscripts = {
    attach: function attach(context, setting) {
      // selectors
      var find_installers_results_wrapper = $(once('nice-scroll', '.find-installers-results-wrapper', context));
      // nicescroll init
      $(document)
        .ajaxStop(function (){
          var loaderObject = $(".find-installers-results-load-more").first();
          if( $(".find-installers-results-load-more").length>1) {
            let listItem = $('.find-installers-results-wrapper').find(".find-installers-results-load-more");
            $(loaderObject).find('.nstaller-footer').html($(listItem).last().find('.nstaller-footer').html());
            $('.find-installers-results-wrapper').append(loaderObject);
            $('.find-installers-results-wrapper').find(".find-installers-results-load-more").first().show();
            $('.find-installers-results-wrapper').find(".find-installers-results-load-more").last().hide();
          }
        });
      $(document).ready(function(){
        $('#millboard-installers-distributors-find-installer').keydown(function(event){
          if(event.keyCode==13) {
            event.preventDefault();
            return false;
          }
        });
      });
      $("div[id^='ascrail']").remove();
      find_installers_results_wrapper.niceScroll({
        cursorfixedheight: 80,
        cursorcolor: '#62554D',
        autohidemode: false,
        disableoutline: true,
        preservenativescrolling: true
      });
      $(window).scroll(function () {
        if ($('.secondary-header-container').hasClass('hide')) {
          window.dispatchEvent(new Event('resize'));
          $('.find-installers-results-wrapper').getNiceScroll().resize();
        }
      });



    }
  };
})(jQuery, Drupal);
