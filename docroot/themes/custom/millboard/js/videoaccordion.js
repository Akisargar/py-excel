/**
 * @file
 * script.js
 */

(function ($, Drupal) {
  Drupal.behaviors.videoAccordion = {
    attach: function (context, settings) {
      const accordionBtns = context.querySelectorAll(".accordion-button");

      accordionBtns.forEach((accordion) => {
        accordion.setAttribute("aria-expanded", "false");
        accordion.setAttribute("role", "button");
        accordion.setAttribute("aria-controls", "accordion-content");
        accordion.setAttribute("tabindex", "0");

        const defaultTabindex = -1;
        const aElements = context.querySelectorAll(".accordion-content a");
          aElements.forEach((aElement) => {
            aElement.setAttribute("tabindex", defaultTabindex);
          });

        accordion.onclick = function () {
          this.classList.toggle("is-open");

          let content = this.nextElementSibling;
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

          const aElements = content.querySelectorAll("a");
            if (aElements) {
            aElements.forEach((aElement) => {
              aElement.setAttribute("tabindex", this.classList.contains("is-open") ? "0" : "-1");
            });
          }
        };
        accordion.addEventListener("keydown", function (event) {
          if (event.code === "Enter") {
            this.click();
          }
        });
      });
    },
  };
})(jQuery, Drupal);
