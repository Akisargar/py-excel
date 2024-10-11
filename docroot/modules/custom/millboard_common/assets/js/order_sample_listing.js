(function ($, Drupal, once, drupalSettings) {
  Drupal.behaviors.sampleListing = {
    attach: function (context, settings) {
      let pricing = $('.order_sample_listing_block_container .order_sample_card_rrp');
      (drupalSettings.hide_price == 1)? pricing.remove() : pricing.show();
      var instances = $(context).find('.no-result-order-sample');
      // If there are at least two instances, hide the second one.
      if (instances.length >= 2) {
        instances[1].style.display = 'none';
      }
      // If there is only one instance, hide it.
      if (instances.length == 1) {
        instances[0].style.display = 'none';
      }
      if (window.location.search) {
        const target = $(once('sample-listing-block-1', '#block-millboard-views-block-order-sample-listing-order-sample-decking', context));
        if (target.length) {
          $('html,body').animate({
            scrollTop: target.offset().top - 357
          }, 1000);
          return;
        }
      }
    }
  };
})(jQuery, Drupal, once, drupalSettings)
