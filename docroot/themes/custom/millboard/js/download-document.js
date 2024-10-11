(function ($, Drupal) {
  'use strict';

  //Fix accessibility for Download document
  Drupal.behaviors.downloads = {
    attach: function (context, setting) {
      const text = document.querySelectorAll('.coh-download-link-text');
      
      if (text) {
        text.forEach((item, index) => { 
          
          var linkText = item.innerText;
          if(item.nextElementSibling){
            item.nextElementSibling.innerText = linkText
          }
        })
      }
    }
  }
})(jQuery, Drupal);