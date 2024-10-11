(function ($, Drupal, drupalSettings) {
    'use strict';

    Drupal.behaviors.banner = {
        attach: function(context) {
            var elements = once('banner', 'body', context);
            $(elements).each(function () {
                    var ajaxSettings = {
                        url: '/'+drupalSettings.path.currentLanguage+'/millboard-georedirect/banner',
                        selector: '#redirect-banner'
                    };
                    var myAjaxObject = Drupal.ajax(ajaxSettings);
                    myAjaxObject.execute();
                });
        }
    };

})(jQuery, Drupal, drupalSettings);
