/**
 * @file
 * script.js
 */

(function ($, Drupal) {
    'use strict';
    Drupal.behaviors.webformscripts = {
      attach: function (context, setting) {
        // selectors
        const js_form_type_textfield = document.querySelectorAll(
          '.form-type-textfield',
        );
        const form_type_email = document.querySelectorAll('.form-type-email');
        const form_type_telephone = document.querySelectorAll('.form-type-tel');
        const form_type_textarea = document.querySelectorAll(
          '.form-type-textarea',
        );

        // selecting the select
        const active_webform_select = document.querySelectorAll(
          '.select2-selection',
        );

        // Select all forms on the page
        var forms = document.querySelectorAll('form');


        // altering the fields, labels and toggling classes.
        js_form_type_textfield.forEach((el) => {
          //append the firstElementChild to the parent node
          el.appendChild(el.firstElementChild);
          // toggle class when input field is in focus and blurred.
          el.children[0].addEventListener(
            'focus',
            (event) => {
              event.currentTarget.classList.add('input-focused');
              event.currentTarget.classList.remove('input-blurred');
              event.currentTarget.classList.remove('input-blurred-empty');
              if (
                event.currentTarget.parentElement.querySelector('label') !== null
              ) {
                event.currentTarget.parentElement
                  .querySelector('label')
                  .classList.add('label-focused');
                event.currentTarget.parentElement
                  .querySelector('label')
                  .classList.remove('label-blurred-empty');
                event.currentTarget.parentElement
                  .querySelector('label')
                  .classList.remove('label-blurred');
              }
            },
            true,
          );
          el.children[0].addEventListener(
            'blur',
            (event) => {
              event.currentTarget.classList.remove('input-focused');
              if (
                event.currentTarget.parentElement.querySelector('label') !== null
              ) {
                event.currentTarget.parentElement
                  .querySelector('label')
                  .classList.remove('label-focused');
              }
              if (event.currentTarget.value.length === 0) {
                event.currentTarget.classList.add('input-blurred-empty');
                if (
                  event.currentTarget.parentElement.querySelector('label') !==
                  null
                ) {
                  event.currentTarget.parentElement
                    .querySelector('label')
                    .classList.add('label-blurred-empty');
                }
              } else {
                event.currentTarget.classList.add('input-blurred');
                if (
                  event.currentTarget.parentElement.querySelector('label') !==
                  null
                ) {
                  event.currentTarget.parentElement
                    .querySelector('label')
                    .classList.add('label-blurred');
                }
              }
            },
            true,
          );
        });
        form_type_email.forEach((el) => {
          //append the firstElementChild to the parent node
          el.appendChild(el.firstElementChild);
          // toggle class when input field is in focus and blurred.
          el.children[0].addEventListener(
            'focus',
            (event) => {
              event.currentTarget.classList.add('input-focused');
              event.currentTarget.classList.remove('input-blurred');
              event.currentTarget.classList.remove('input-blurred-empty');
              event.currentTarget.parentElement
                .querySelector('label')
                .classList.add('label-focused');
              event.currentTarget.parentElement
                .querySelector('label')
                .classList.remove('label-blurred-empty');
              event.currentTarget.parentElement
                .querySelector('label')
                .classList.remove('label-blurred');
            },
            true,
          );
          el.children[0].addEventListener(
            'blur',
            (event) => {
              event.currentTarget.classList.remove('input-focused');
              event.currentTarget.parentElement
                .querySelector('label')
                .classList.remove('label-focused');
              if (event.currentTarget.value.length === 0) {
                event.currentTarget.classList.add('input-blurred-empty');
                event.currentTarget.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred-empty');
              } else {
                event.currentTarget.classList.add('input-blurred');
                event.currentTarget.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred');
              }
            },
            true,
          );
        });
        form_type_textarea.forEach((el) => {
          //append the firstElementChild to the parent node
          el.appendChild(el.firstElementChild);
          // toggle class when input field is in focus and blurred.
          el.children[0].children[0].addEventListener(
            'focus',
            (event) => {
              event.currentTarget.classList.add('input-focused');
              event.currentTarget.classList.remove('input-blurred');
              event.currentTarget.classList.remove('input-blurred-empty');

              event.currentTarget.parentElement.parentElement
                .querySelector('label')
                .classList.add('label-focused');
              event.currentTarget.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-blurred-empty');
              event.currentTarget.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-blurred');
            },
            true,
          );
          el.children[0].children[0].addEventListener(
            'blur',
            (event) => {
              event.currentTarget.classList.remove('input-focused');
              event.currentTarget.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-focused');
              if (event.currentTarget.value.length === 0) {
                event.currentTarget.classList.add('input-blurred-empty');
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred-empty');
              } else {
                event.currentTarget.classList.add('input-blurred');
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred');
              }
            },
            true,
          );
        });

        // Loop through each form
        forms.forEach(function (form) {
            // Select all anchor tags inside the current form
            var anchorTags = form.querySelectorAll('a');

            // Loop through the selected anchor tags (for demonstration purposes)
            anchorTags.forEach(function (anchor) {
                anchor.classList.add('coh-style-link-default');
            });
        });

        // Loop through each telephone field.
        form_type_telephone.forEach((el)=> {
          //append the firstElementChild to the parent node
          el.appendChild(el.firstElementChild);
          // toggle class when input field is in focus and blurred.
          el.children[0].addEventListener(
            'focus',
            (event) => {
              event.currentTarget.classList.add('input-focused');
              event.currentTarget.classList.remove('input-blurred');
              event.currentTarget.classList.remove('input-blurred-empty');
              event.currentTarget.parentElement
                .querySelector('label')
                .classList.add('label-focused');
              event.currentTarget.parentElement
                .querySelector('label')
                .classList.remove('label-blurred-empty');
              event.currentTarget.parentElement
                .querySelector('label')
                .classList.remove('label-blurred');
            },
            true,
          );
          el.children[0].addEventListener(
            'blur',
            (event) => {
              event.currentTarget.classList.remove('input-focused');
              event.currentTarget.parentElement
                .querySelector('label')
                .classList.remove('label-focused');
              if (event.currentTarget.value.length === 0) {
                event.currentTarget.classList.add('input-blurred-empty');
                event.currentTarget.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred-empty');
              } else {
                event.currentTarget.classList.add('input-blurred');
                event.currentTarget.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred');
              }
            },
            true,
          );
        });

        // Let the document know when the mouse is being used
        document.body.addEventListener('mousedown', function() {
          js_form_type_textfield.forEach((el) => {
            el.classList.add('form-type-textfield-using-mouse');
          });
          form_type_email.forEach((el) => {
            el.classList.add('form-type-email-using-mouse');
          });
          form_type_telephone.forEach((el) => {
            el.classList.add('form-type-telephone-using-mouse');
          });
          form_type_textarea.forEach((el) => {
            el.classList.add('form-type-textarea-using-mouse');
          });
          active_webform_select.forEach((el) => {
            el.classList.add('form-type-select-using-mouse');
          });
        });

        // Re-enable focus styling when Tab is pressed
        document.body.addEventListener('keydown', function(event) {
          if (event.key === "Tab") {
            js_form_type_textfield.forEach((el) => {
              el.classList.remove('form-type-textfield-using-mouse');
            });
            form_type_email.forEach((el) => {
              el.classList.remove('form-type-email-using-mouse');
            });
            form_type_telephone.forEach((el) => {
              el.classList.remove('form-type-telephone-using-mouse');
            });
            form_type_textarea.forEach((el) => {
              el.classList.remove('form-type-textarea-using-mouse');
            });
            active_webform_select.forEach((el) => {
              el.classList.remove('form-type-select-using-mouse');
            });
          }
        });

        // Edit Address link.
        $('.edit-address a', context).on('click', function() {
          var inputFields = document.querySelectorAll('#address-address-wrapper input');
          // Loop through the selected input fields
          inputFields.forEach(function(inputField) {
            // Get the current value of each input field
            var value = inputField.value;
            // Log the value to the console (you can perform any action with the value)
            if(value !== ''){
              if (!inputField.nextElementSibling.classList.contains('label-focused')) {
              inputField.nextElementSibling.classList.add('label-focused')
              }
            }
          });
        })
      },
    };
  })(jQuery, Drupal);
