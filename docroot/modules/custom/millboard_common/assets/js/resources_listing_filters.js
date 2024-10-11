(function ($, Drupal, once) {
  Drupal.behaviors.resourceListing = {
    attach: function (context, settings) {
      if (window.location.search) {
        const target = $(once('resource-listing-block-1', '#block-millboard-views-block-video-listing-block-1', context));
        if (target.length) {
          $('html,body').animate({
            scrollTop: target.offset().top - 234
          }, 1000);
          return;
        }
      }
    }
  };
})(jQuery, Drupal, once)
