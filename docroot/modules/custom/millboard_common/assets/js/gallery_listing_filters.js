(function ($, Drupal, once) {
  Drupal.behaviors.galleryListing = {
    attach: function (context, settings) {
      if (window.location.search) {
        const target = $(once('gallery-listing-block-1', '#block-millboard-views-block-millboard-image-gallery-gallery-block', context));
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
