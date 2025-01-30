/**
 * @file
 * script.js
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.primary_hero_component = {
    attach: function (context, setting) {
      AOS.init({
        duration: 500,
        once: false,
      });

      if ($('#primary-hero-banner').length) {
        if ($(window).width() >= 768) {
          const hero = document.getElementById('primary-hero-banner');

          function updateClipPath() {
            const viewportHeight = window.innerHeight;
            const heroRect = hero.getBoundingClientRect();

            // Calculate the distance of the hero component from the viewport top
            const distanceFromTop = heroRect.top;

            // Calculate the percentage of visibility
            const visibilityPercentage = Math.min(1, Math.max(0, 1 - (distanceFromTop / viewportHeight)));

            // Introduce a threshold to limit the adjustment
            const threshold = 0.49; // Adjust this value as needed
            const adjustedVisibility = Math.max(0, visibilityPercentage - threshold) / (1 - threshold);

            // Ensure that finalClipPath is applied when visibility is between 0.9 and 1
            const finalClipPathThreshold = 0.9;
            const currentClipPath = visibilityPercentage >= finalClipPathThreshold
              ? 'polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%)'
              : `polygon(${14.5 - 15 * adjustedVisibility}% 0%, ${85.5 + 15 * adjustedVisibility}% 0%, ${85.5 + 15 * adjustedVisibility}% 100%, ${14.5 - 15 * adjustedVisibility}% 100%)`;

            // Apply the new clip-path value
            hero.style.clipPath = currentClipPath;
          }

          function handleScroll() {
            requestAnimationFrame(updateClipPath);
          }

          window.addEventListener('scroll', handleScroll);
          window.addEventListener('resize', handleScroll);
          document.addEventListener('DOMContentLoaded', handleScroll);
        }
      }
    },
  };

  Drupal.behaviors.productsMenu = {
    attach: function (context, settings) {
      // Check if the window width is 1024 or above
      if ($(window).width() >= 1024) {
        // Function to hide menus
        function hideMenus() {
          // Select all menu items and product items
          const menuItems = $(
            '.nav__our-products--decking + .menu-level-3-wrapper, .nav__our-products--decking + .menu-level-3-wrapper + img, .nav__our-products--cladding + .menu-level-3-wrapper, .nav__our-products--cladding + .menu-level-3-wrapper + img',
          );
          const productItems = $(
            '.nav__our-products--decking, .nav__our-products--cladding',
          );

          // Remove 'show' class and set display to 'none' for menu items
          menuItems.removeClass('show').css('display', 'none');

          // Remove 'border' and 'move' classes for product items
          productItems.removeClass('border move');
        }

        // Function to show a specific menu
        function showMenu(spanSelector, menuSelector, imgSelector) {
          // Hide all menus and images
          hideMenus();

          // Select the specific menu items and images
          const menuItems = $(`${menuSelector}, ${imgSelector}`);

          // Add 'show' class and set display and border for the selected items
          menuItems.addClass('show').css('display', 'block');
          $(spanSelector).addClass('border');
        }

        // Function to set menu behavior
        function setMenuBehavior(selector, menuFunction) {
          // Attach a click event to the specified selector
          $(selector)
            .off('click')
            .on('click', function (e) {
              // Hide all menus and show the specified menu
              e.preventDefault();
              hideMenus();
              menuFunction();
            });
        }

        // Set menu behavior for the 'decking' product
        setMenuBehavior('.nav__our-products--decking', () =>
          showMenu(
            '.nav__our-products--decking',
            '.nav__our-products--decking + .menu-level-3-wrapper',
            '.nav__our-products--decking + .menu-level-3-wrapper + img',
          ),
        );

        // Set menu behavior for the 'cladding' product
        setMenuBehavior('.nav__our-products--cladding', () =>
          showMenu(
            '.nav__our-products--cladding',
            '.nav__our-products--cladding + .menu-level-3-wrapper',
            '.nav__our-products--cladding + .menu-level-3-wrapper + img',
          ),
        );

        // Initially show the 'decking' menu
        showMenu(
          '.nav__our-products--decking',
          '.nav__our-products--decking + .menu-level-3-wrapper',
          '.nav__our-products--decking + .menu-level-3-wrapper + img',
        );
      } else {
        // Get the span elements with classes .nav__our-products--decking and .nav__our-products--cladding
        const deckingSpan = document.querySelector(
          '.nav__our-products--decking',
        );
        const claddingSpan = document.querySelector(
          '.nav__our-products--cladding',
        );

        // Get the respective div elements with class menu-level-3-wrapper
        const deckingMenu = deckingSpan.nextElementSibling;
        const claddingMenu = claddingSpan.nextElementSibling;

        function toggleMenuVisibility(menu, parentLi) {
          if (menu.style.display === 'block') {
            menu.style.display = 'none';
            parentLi.classList.remove('is-expanded');
            parentLi.classList.add('is-collapsed');
          } else {
            menu.style.display = 'block';
            parentLi.classList.remove('is-collapsed');
            parentLi.classList.add('is-expanded');
          }
        }

        // Add click event listeners to the span elements
        $('.nav__our-products--decking', context)
          .off('click')
          .on('click', function (e) {
            e.preventDefault();
            toggleMenuVisibility(deckingMenu, deckingSpan.parentElement);
          });

        $('.nav__our-products--cladding', context)
          .off('click')
          .on('click', function (e) {
            e.preventDefault();
            toggleMenuVisibility(claddingMenu, claddingSpan.parentElement);
          });
      }
    },
  };

  Drupal.behaviors.header = {
    attach: function (context, setting) {
      let lastScrollTop = 0;
      const delta = 50;

      $(window).scroll(function () {
        let currentScrollPos = $(this).scrollTop();

        if (Math.abs(lastScrollTop - currentScrollPos) <= delta) {
          return;
        }

        if (currentScrollPos > lastScrollTop) {
          // Scroll down
          $('.secondary-header-container').addClass('hide');
          $('.primary-header').addClass('sticky');
        } else {
          // Scroll up
          $('.secondary-header-container').removeClass('hide');
          $('.primary-header').removeClass('sticky');
        }

        lastScrollTop = currentScrollPos;
      });

      // add sticky product details to the header on product page
      if ($('body:has(.coh-style-millboard-product-page)').length) {
        const scrollTriggerDiv = $('.product-hero');
        const hiddenDiv = $('.product-attributes-wrapper');
        let lastScrollPos = 0;

        $(window).scroll(function() {
          let st = $(this).scrollTop();

          if (Math.abs(lastScrollPos - st) <= delta) {
            return;
          }

          // Check if scrolling down
          if (st > lastScrollPos) {
            // Scrolling down, show the div after scrollTriggerDiv is finished scrolling
            if (st > scrollTriggerDiv.offset().top + scrollTriggerDiv.height()) {
              hiddenDiv.addClass('product-attributes-wrapper--show');
            }
          } else {
            // Scrolling up, hide the div
            hiddenDiv.removeClass('product-attributes-wrapper--show');
          }

          lastScrollPos = st;
        });
      }
    },
  };
  Drupal.behaviors.removePipeSign = {
    attach: function (context, setting) {
      let display_center = jQuery('.coh-paragraph.directory-hero__display-center').length
      let type_of_installer = jQuery('.coh-paragraph.directory-hero__distributors').length
      if(display_center == 0 || type_of_installer == 0) {
          jQuery('.coh-inline-element.separator').remove()
      }
    }
  };
  Drupal.behaviors.socialLinks = {
    attach: function (context, settings) {
      $(".social-links li:has(a[href=''])").remove();
    }
  };
  Drupal.behaviors.sampleOrderForm = {
    attach: function (context, setting) {
      let basket = JSON.parse(localStorage.getItem('basket')) || [];
      let sampleNames = '';
      $('.order-a-sample-submit #edit-actions-submit').on('click', function (params) {
        if ($('#edit-address-address-line1').val() === '' &&
          $('#edit-address-postal-code').val() === '' &&
          $('#edit-address-locality').val() === ''
        ) {
          let addressWrapper =$('#address-address-wrapper');
          if (addressWrapper.hasClass('hidden')) {
            addressWrapper.removeClass('hidden');
          }
        }
      });
      $.each( basket, function( key, item ) {
        let SampleOrderCheckboxes = $('.sample-name-checkbox .form-checkbox');
        $.each(SampleOrderCheckboxes ,function (id, inputElements) {
          let input = inputElements.labels['0'].innerText;
          if ((item.productType === 'Terrasse' || item.productType === 'Decking') && item.width.indexOf('126mm') >= 0) {
            if (input === item.name + ' ' + 'SB' + ' ' + item.colour) {
              inputElements.checked = true;
              // eslint-disable-next-line @typescript-eslint/no-unused-vars
              sampleNames = sampleNames + item.name + ' ' + 'SB' + ' ' + item.colour + ';';
            }
          }
          else {
            if (input === item.name + ' ' + item.colour) {
              inputElements.checked = true;
              sampleNames = sampleNames + item.name + ' ' + item.colour + ';';
            }
          }
        });
        let projectTypeCheckboxes = $('.project-type-checkbox .form-checkbox');
        $.each(projectTypeCheckboxes, function (id, inputElement) {
          if (inputElement.labels[0].innerText.indexOf(item.productType) > 0) {
            inputElement.checked = true;
          }
        });
      });
      let sampleNamesHubspot = $('.sample-names-hubspot');
      sampleNamesHubspot.prop('value', sampleNames);
      sampleNamesHubspot.css('display', 'none');
      $('.sample-name-checkbox').css('display', 'none');
      $('.website-order-code #edit-website-order-code').css('display', 'none');
    },
  };

  Drupal.behaviors.mapAndLocate = {
    attach: function (context, setting) {

      // Get a reference to the scrollable section
      const scrollableSections = document.querySelectorAll('.find-installers-results-wrapper');

      scrollableSections.forEach((scrollableSection)=>{
        let isScrolling = false;
        // Add a scroll event listener to the scrollable section
        scrollableSection.addEventListener('scroll', function(e) {
          e.preventDefault();
          if (!isScrolling) {
            // Disable body scroll
            document.body.style.overflow = 'hidden';
          }
          // Set a timeout to revert the body overflow to 'auto' after a short delay
          clearTimeout(isScrolling);
          isScrolling = setTimeout(function () {
            document.body.style.overflow = 'auto';
            isScrolling = false;
          }, 300); // You can adjust the delay according to your preference
        });
      });

      // add additional class to empty results container.
      if($('.find-installers-results-row').find('.views-field-nothing').length === 0){
        $('.find-installers-results-row').find('.find-installers-content').addClass('find-installers-content-content-none');
        $('.find-installers-results-row').addClass('find-installers-results-row-content-none');
        /* $('.coh-style-millboard-find-installers-view-style').find('.coh-style-filter-and-sort-button').attr("disabled","true").addClass('filter-and-sort-button-disabled'); */
      }else{
        $('.find-installers-results-row').find('.find-installers-content').removeClass('find-installers-content-content-none');
        $('.find-installers-results-row').removeClass('find-installers-results-row-content-none');
        /* $('.coh-style-millboard-find-installers-view-style').find('.coh-style-filter-and-sort-button').removeAttr("disabled").removeClass('filter-and-sort-button-disabled'); */
      }
    },
  };
     Drupal.behaviors.stackAdaptTracking = {
    attach: function (context, settings) {
      $('#edit-actions-submit', context).once('stackAdaptTracking').on('click', function (event) {
        // StackAdapt script
        !function (s, a, e, v, n, t, z) {
          if (s.saq) return;
          n = s.saq = function () {
            n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
          };
          if (!s._saq) s._saq = n;
          n.push = n;
          n.loaded = !0;
          n.version = '1.0';
          n.queue = [];
          t = a.createElement(e);
          t.async = !0;
          t.src = v;
          z = a.getElementsByTagName(e)[0];
          z.parentNode.insertBefore(t, z);
        }(window, document, 'script', 'https://tags.srv.stackadapt.com/events.js');

        saq('conv', 'm6E9iqGcYXZM0E5CUVfInt');

        // NoScript equivalent for tracking
        $('<img>', {
          src: 'https://tags.srv.stackadapt.com/conv?cid=m6E9iqGcYXZM0E5CUVfInt',
          width: 1,
          height: 1,
          style: 'display:none;'
        }).appendTo('body');
      });
    }
  };

  Drupal.behaviors.stackAdaptAddToBasket = {
    attach: function (context, settings) {
      $('.order_sample_card_image .add-to-basket-btn', context).once('stackAdaptAddToBasket').on('click', function (event) {
        event.preventDefault(); // Prevents default action if necessary

        // StackAdapt script
        !function (s, a, e, v, n, t, z) {
          if (s.saq) return;
          n = s.saq = function () {
            n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
          };
          if (!s._saq) s._saq = n;
          n.push = n;
          n.loaded = !0;
          n.version = '1.0';
          n.queue = [];
          t = a.createElement(e);
          t.async = !0;
          t.src = v;
          z = a.getElementsByTagName(e)[0];
          z.parentNode.insertBefore(t, z);
        }(window, document, 'script', 'https://tags.srv.stackadapt.com/events.js');

        saq('conv', '2sYLJs0Nc5guHcANqJqPiU');

        // NoScript equivalent for tracking
        $('<img>', {
          src: 'https://tags.srv.stackadapt.com/conv?cid=2sYLJs0Nc5guHcANqJqPiU',
          width: 1,
          height: 1,
          style: 'display:none;'
        }).appendTo('body');
      });
    }
  };

  // Add the functionality for custom page title
  document.addEventListener("DOMContentLoaded", function () {
    // Check for the custom title in the product page div
    const productPage = document.querySelector('.coh-style-millboard-product-page');

    if (productPage && productPage.getAttribute('data-custom-title')) {
      const customTitle = productPage.getAttribute('data-custom-title');

      // Set the page <title> to custom title
      document.title = customTitle;
    }
  });

})(jQuery, Drupal);
