(function ($, Drupal, once) {
  Drupal.behaviors.blogListing = {
    attach: function (context, settings) {
      if (window.location.search) {
        const target = $(once('blog-listing-block-1', '#block-millboard-views-block-blog-listing-block-1', context));
        if (target.length) {
          $('html,body').animate({
            scrollTop: target.offset().top - 164
          }, 1000);
          return;
        }
      }
    }
  };
})(jQuery, Drupal, once)
