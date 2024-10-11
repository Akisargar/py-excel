/**
 * @file
 * script.js
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.hubspotformscripts = {
    attach: function (context, setting) {

      setTimeout(function () {
        const radioElementNo = $('.hs_is_warranty_registration_address_same_as_contact_address input[value="No"]')
        const radioElementContractor = $('.hs_how_were_products_installed input[value="Contractor"]')

        function checkForInputs(element) {
          element.forEach((el) => {
            var input = el.querySelector('.input')
            var value = input.children[0].value
            if(value !== ''){
              var inputLabel = el.querySelectorAll('label')
              inputLabel.forEach((label) => {
                if(!$(label).hasClass('label-focused')) {
                  label.classList.add('label-focused')
                }
              })
            }
            input.children[0].addEventListener(
              'focus',
              (event) => {
                event.currentTarget.parentElement.classList.add('input-focused');
                event.currentTarget.parentElement.classList.remove(
                  'input-blurred',
                );
                event.currentTarget.parentElement.classList.remove(
                  'input-blurred-empty',
                );

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

            input.children[0].addEventListener(
              'blur',
              (event) => {
                event.currentTarget.parentElement.classList.remove(
                  'input-focused',
                );
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.remove('label-focused');
                if (event.currentTarget.value.length === 0) {
                  event.currentTarget.parentElement.classList.add(
                    'input-blurred-empty',
                  );
                  event.currentTarget.parentElement.parentElement
                    .querySelector('label')
                    .classList.add('label-blurred-empty');
                } else {
                  event.currentTarget.parentElement.classList.add(
                    'input-blurred',
                  );
                  event.currentTarget.parentElement.parentElement
                    .querySelector('label')
                    .classList.add('label-blurred');
                }
              },
              true,
            );
          });

          document.body.addEventListener('mousedown', function() {
            element.forEach((el) => {
              el.classList.add('hs-fieldtype-text-using-mouse');
            });

            element.forEach((el) => {
              el.classList.add('hs-fieldtype-textarea-using-mouse');
            });
          })

          // Re-enable focus styling when Tab is pressed
          document.body.addEventListener('keydown', function(event) {
            if (event.key === "Tab") {
              element.forEach((el) => {
                el.classList.remove('hs-fieldtype-text-using-mouse');
              })

              element.forEach((el) => {
                el.classList.remove('hs-fieldtype-textarea-using-mouse');
              });
            }
          });

        }

        radioElementNo.on('click', function() {
          setTimeout(function () {
            var hs_fieldtype_text = context.querySelectorAll(
              'div.hs-fieldtype-text',
            );

            var hs_fieldtype_textarea = context.querySelectorAll(
              'div.hs-fieldtype-textarea',
            );

            checkForInputs(hs_fieldtype_textarea)
            checkForInputs(hs_fieldtype_text)

          }, 10)
        })

        radioElementContractor.on('click', function() {
          setTimeout(function () {
            var hs_fieldtype_text = context.querySelectorAll(
              'div.hs-fieldtype-text',
            );

            var hs_fieldtype_textarea = context.querySelectorAll(
              'div.hs-fieldtype-textarea',
            );

            checkForInputs(hs_fieldtype_textarea)
            checkForInputs(hs_fieldtype_text)

          }, 10)
        })

        // selecting all of the hubspot field wrapper divs.
        var hs_fieldtype_text = context.querySelectorAll(
          'div.hs-fieldtype-text',
        );
        var hs_fieldtype_textarea = context.querySelectorAll(
          'div.hs-fieldtype-textarea',
        );
        var hs_fieldtype_phonenumber = document.querySelectorAll(
          'div.hs-fieldtype-phonenumber',
        );
        var hs_fieldtype_date = document.querySelectorAll(
          'div.hs-fieldtype-date',
        );
        var hs_fieldtype_file = document.querySelectorAll(
          'div.hs-fieldtype-file',
        );
        var hs_form_radio = context.querySelectorAll('li.hs-form-radio');
        var hs_submit = document.querySelectorAll('.hs-submit');
        var hs_form_checkbox = document.querySelectorAll('.hs-form-checkbox');
        var hs_form_boolean_checkbox = document.querySelectorAll('.hs-form-booleancheckbox');

        const fileInput = document.querySelectorAll('input[type="file"]');

        hs_form_checkbox.forEach((el) => {
          el.prepend(el.firstElementChild.firstElementChild);
        });

        hs_form_boolean_checkbox.forEach((el) => {
          el.prepend(el.firstElementChild.firstElementChild);
        });



        hs_fieldtype_text.forEach((el) => {
          el.appendChild(el.firstElementChild);
          el.children[1].children[0].addEventListener(
            'focus',
            (event) => {
              event.currentTarget.parentElement.classList.add('input-focused');
              event.currentTarget.parentElement.classList.remove(
                'input-blurred',
              );
              event.currentTarget.parentElement.classList.remove(
                'input-blurred-empty',
              );

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
          el.children[1].children[0].addEventListener(
            'blur',
            (event) => {
              event.currentTarget.parentElement.classList.remove(
                'input-focused',
              );
              event.currentTarget.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-focused');
              if (event.currentTarget.value.length === 0) {
                event.currentTarget.parentElement.classList.add(
                  'input-blurred-empty',
                );
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred-empty');
              } else {
                event.currentTarget.parentElement.classList.add(
                  'input-blurred',
                );
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred');
              }
            },
            true,
          );
        });

        hs_fieldtype_date.forEach((el) => {
          el.appendChild(el.firstElementChild);
          el.children[1].children[0].children[0].addEventListener(
            'focus',
            (event) => {
              event.currentTarget.parentElement.parentElement.classList.add(
                'input-focused',
              );
              event.currentTarget.parentElement.parentElement.classList.remove(
                'input-blurred',
              );
              event.currentTarget.parentElement.parentElement.classList.remove(
                'input-blurred-empty',
              );

              event.currentTarget.parentElement.parentElement.parentElement
                .querySelector('label')
                .classList.add('label-focused');
              event.currentTarget.parentElement.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-blurred-empty');
              event.currentTarget.parentElement.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-blurred');
            },
            true,
          );
          el.children[1].children[0].children[0].addEventListener(
            'blur',
            (event) => {
              event.currentTarget.parentElement.parentElement.classList.remove(
                'input-focused',
              );
              event.currentTarget.parentElement.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-focused');
              if (event.currentTarget.value.length === 0) {
                event.currentTarget.parentElement.parentElement.classList.add(
                  'input-blurred-empty',
                );
                event.currentTarget.parentElement.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred-empty');
              } else {
                event.currentTarget.parentElement.parentElement.classList.add(
                  'input-blurred',
                );
                event.currentTarget.parentElement.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred');
              }
            },
            true,
          );
        });

        hs_fieldtype_file.forEach((el) => {
          el.appendChild(el.children[1]);
        });

        hs_form_radio.forEach((el) => {
          let radio_input = el.children[0].children[0];
          el.prepend(radio_input);
        });

        hs_fieldtype_textarea.forEach((el) => {
          el.appendChild(el.firstElementChild);
          el.children[1].children[0].addEventListener(
            'focus',
            (event) => {
              event.currentTarget.parentElement.classList.add('input-focused');
              event.currentTarget.parentElement.classList.remove(
                'input-blurred',
              );
              event.currentTarget.parentElement.classList.remove(
                'input-blurred-empty',
              );

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
          el.children[1].children[0].addEventListener(
            'blur',
            (event) => {
              event.currentTarget.parentElement.classList.remove(
                'input-focused',
              );
              event.currentTarget.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-focused');
              if (event.currentTarget.value.length === 0) {
                event.currentTarget.parentElement.classList.add(
                  'input-blurred-empty',
                );
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred-empty');
              } else {
                event.currentTarget.parentElement.classList.add(
                  'input-blurred',
                );
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred');
              }
            },
            true,
          );
        });

        hs_fieldtype_phonenumber.forEach((el) => {
          el.appendChild(el.firstElementChild);
          el.children[1].children[0].addEventListener(
            'focus',
            (event) => {
              event.currentTarget.parentElement.classList.add('input-focused');
              event.currentTarget.parentElement.classList.remove(
                'input-blurred',
              );
              event.currentTarget.parentElement.classList.remove(
                'input-blurred-empty',
              );

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
          el.children[1].children[0].addEventListener(
            'blur',
            (event) => {
              event.currentTarget.parentElement.classList.remove(
                'input-focused',
              );
              event.currentTarget.parentElement.parentElement
                .querySelector('label')
                .classList.remove('label-focused');
              if (event.currentTarget.value.length === 0) {
                event.currentTarget.parentElement.classList.add(
                  'input-blurred-empty',
                );
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred-empty');
              } else {
                event.currentTarget.parentElement.classList.add(
                  'input-blurred',
                );
                event.currentTarget.parentElement.parentElement
                  .querySelector('label')
                  .classList.add('label-blurred');
              }
            },
            true,
          );
        });

        hs_submit.forEach((el) => {
          el.children[1]
            .querySelectorAll('input[type=submit]')
            .forEach((elem) => {
              elem.classList.add('coh-style-primary-button');
            });
        });

        // adding accesibility to datepicker UI.
        $('.pika-title').attr('tabindex', '3');
        $('.pika-table').attr('tabindex', '4');

        // selecting the select
        const active_hubspot_select = document.querySelectorAll(
          '.hs-fieldtype-select .select2-selection',
          context,
        );

          function selectValidationlogic(el) {
            if (
              el.querySelector('.select2-selection__rendered').innerHTML ===
                'Please Select'
            ) {
              if (
                $(el)
                  .parents('.hs-fieldtype-select')
                  .find('ul.hs-error-msgs').length === 0
              ) {
                $(el)
                  .closest('div.input')
                  .after('<ul class="no-list hs-error-msgs inputs-list" role="alert"><li><label class="hs-error-msg hs-main-font-element">' + Drupal.t('Please select an option from the dropdown menu.') + '</label></li></ul>',
                  );
              }
            } else {
              // removing the validation msg if some option is selected/
              $(el)
              .parents('.hs-fieldtype-select')
              .find('ul.hs-error-msgs').remove();
            }
          }

        // on tabbing show validation error.
        active_hubspot_select.forEach((el) => {
          $('body').on(
            'keydown',
            el,
            function (e) {
              if (e.which == 9 && document.activeElement === el) {
                selectValidationlogic(el);
              }
            },
          );
        });

        active_hubspot_select.forEach((el) => {
          $(el).mousedown(
            function (e) {
              if (e.which == 1) {
                selectValidationlogic(el);
              }
            },
          );
        });

        // Let the document know when the mouse is being used
        document.body.addEventListener('mousedown', function() {
          hs_fieldtype_text.forEach((el) => {
            el.classList.add('hs-fieldtype-text-using-mouse');
          });
          hs_fieldtype_phonenumber.forEach((el) => {
            el.classList.add('hs-fieldtype-phonenumber-using-mouse');
          });
          hs_fieldtype_textarea.forEach((el) => {
            el.classList.add('hs-fieldtype-textarea-using-mouse');
          });
          hs_form_checkbox.forEach((el) => {
            el.classList.add('hs-fieldtype-checkbox-using-mouse');
          });
          hs_form_boolean_checkbox.forEach((el) => {
            el.classList.add('hs-fieldtype-checkbox-using-mouse');
          });
          active_hubspot_select.forEach((el) => {
            el.classList.add('hs-fieldtype-select-using-mouse');
          });
        });

        // Re-enable focus styling when Tab is pressed
        document.body.addEventListener('keydown', function(event) {
          if (event.key === "Tab") {
            hs_fieldtype_text.forEach((el) => {
              el.classList.remove('hs-fieldtype-text-using-mouse');
            });
            hs_fieldtype_phonenumber.forEach((el) => {
              el.classList.remove('hs-fieldtype-phonenumber-using-mouse');
            });
            hs_fieldtype_textarea.forEach((el) => {
              el.classList.remove('hs-fieldtype-textarea-using-mouse');
            });
            hs_form_checkbox.forEach((el) => {
              el.classList.remove('hs-fieldtype-checkbox-using-mouse');
            });
            hs_form_boolean_checkbox.forEach((el) => {
              el.classList.remove('hs-fieldtype-checkbox-using-mouse');
            });
            active_hubspot_select.forEach((el) => {
              el.classList.remove('hs-fieldtype-select-using-mouse');
            });
          }
        });
      }, 1000);
    },
  };
})(jQuery, Drupal);