"use strict";

(function ($, Drupal) {
  'use strict';

  //Fix accessibility for Download document
  Drupal.behaviors.downloads = {
    attach: function attach(context, setting) {
      var text = document.querySelectorAll('.coh-download-link-text');
      if (text) {
        text.forEach(function (item, index) {
          var linkText = item.innerText;
          if (item.nextElementSibling) {
            item.nextElementSibling.innerText = linkText;
          }
        });
      }
    }
  };
})(jQuery, Drupal);