"use strict";

/**
 * @file
 * script.js
 */

(function ($, Drupal) {
  Drupal.behaviors.videoAccordion = {
    attach: function attach(context, settings) {
      var accordionBtns = context.querySelectorAll(".accordion-button");
      accordionBtns.forEach(function (accordion) {
        accordion.setAttribute("aria-expanded", "false");
        accordion.setAttribute("role", "button");
        accordion.setAttribute("aria-controls", "accordion-content");
        accordion.setAttribute("tabindex", "0");
        var defaultTabindex = -1;
        var aElements = context.querySelectorAll(".accordion-content a");
        aElements.forEach(function (aElement) {
          aElement.setAttribute("tabindex", defaultTabindex);
        });
        accordion.onclick = function () {
          var _this = this;
          this.classList.toggle("is-open");
          var content = this.nextElementSibling;
          content.setAttribute("role", "region");
          if (content.style.maxHeight) {
            content.style.maxHeight = null;
            accordion.setAttribute("aria-expanded", "false");
            accordion.focus();
          } else {
            content.style.maxHeight = content.scrollHeight * 2 + "px";
            accordion.setAttribute("aria-expanded", "true");
            content.focus();
          }
          var aElements = content.querySelectorAll("a");
          if (aElements) {
            aElements.forEach(function (aElement) {
              aElement.setAttribute("tabindex", _this.classList.contains("is-open") ? "0" : "-1");
            });
          }
        };
        accordion.addEventListener("keydown", function (event) {
          if (event.code === "Enter") {
            this.click();
          }
        });
      });
    }
  };
})(jQuery, Drupal);
