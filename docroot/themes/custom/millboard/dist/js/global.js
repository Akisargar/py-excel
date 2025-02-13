"use strict";

/**
 * @file
 * script.js
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.primary_hero_component = {
    attach: function attach(context, setting) {
      AOS.init({
        duration: 500,
        once: false
      });
      if ($('#primary-hero-banner').length) {
        if ($(window).width() >= 768) {
          var updateClipPath = function updateClipPath() {
            var viewportHeight = window.innerHeight;
            var heroRect = hero.getBoundingClientRect();

            // Calculate the distance of the hero component from the viewport top
            var distanceFromTop = heroRect.top;

            // Calculate the percentage of visibility
            var visibilityPercentage = Math.min(1, Math.max(0, 1 - distanceFromTop / viewportHeight));

            // Introduce a threshold to limit the adjustment
            var threshold = 0.49; // Adjust this value as needed
            var adjustedVisibility = Math.max(0, visibilityPercentage - threshold) / (1 - threshold);

            // Ensure that finalClipPath is applied when visibility is between 0.9 and 1
            var finalClipPathThreshold = 0.9;
            var currentClipPath = visibilityPercentage >= finalClipPathThreshold ? 'polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%)' : "polygon(".concat(14.5 - 15 * adjustedVisibility, "% 0%, ").concat(85.5 + 15 * adjustedVisibility, "% 0%, ").concat(85.5 + 15 * adjustedVisibility, "% 100%, ").concat(14.5 - 15 * adjustedVisibility, "% 100%)");

            // Apply the new clip-path value
            hero.style.clipPath = currentClipPath;
          };
          var handleScroll = function handleScroll() {
            requestAnimationFrame(updateClipPath);
          };
          var hero = document.getElementById('primary-hero-banner');
          window.addEventListener('scroll', handleScroll);
          window.addEventListener('resize', handleScroll);
          document.addEventListener('DOMContentLoaded', handleScroll);
        }
      }
    }
  };
  Drupal.behaviors.productsMenu = {
    attach: function attach(context, settings) {
      // Check if the window width is 1024 or above
      if ($(window).width() >= 1024) {
        // Function to hide menus
        var hideMenus = function hideMenus() {
          // Select all menu items and product items
          var menuItems = $('.nav__our-products--decking + .menu-level-3-wrapper, .nav__our-products--decking + .menu-level-3-wrapper + img, .nav__our-products--cladding + .menu-level-3-wrapper, .nav__our-products--cladding + .menu-level-3-wrapper + img');
          var productItems = $('.nav__our-products--decking, .nav__our-products--cladding');

          // Remove 'show' class and set display to 'none' for menu items
          menuItems.removeClass('show').css('display', 'none');

          // Remove 'border' and 'move' classes for product items
          productItems.removeClass('border move');
        }; // Function to show a specific menu
        var showMenu = function showMenu(spanSelector, menuSelector, imgSelector) {
          // Hide all menus and images
          hideMenus();

          // Select the specific menu items and images
          var menuItems = $("".concat(menuSelector, ", ").concat(imgSelector));

          // Add 'show' class and set display and border for the selected items
          menuItems.addClass('show').css('display', 'block');
          $(spanSelector).addClass('border');
        }; // Function to set menu behavior
        var setMenuBehavior = function setMenuBehavior(selector, menuFunction) {
          // Attach a click event to the specified selector
          $(selector).off('click').on('click', function (e) {
            // Hide all menus and show the specified menu
            e.preventDefault();
            hideMenus();
            menuFunction();
          });
        }; // Set menu behavior for the 'decking' product
        setMenuBehavior('.nav__our-products--decking', function () {
          return showMenu('.nav__our-products--decking', '.nav__our-products--decking + .menu-level-3-wrapper', '.nav__our-products--decking + .menu-level-3-wrapper + img');
        });

        // Set menu behavior for the 'cladding' product
        setMenuBehavior('.nav__our-products--cladding', function () {
          return showMenu('.nav__our-products--cladding', '.nav__our-products--cladding + .menu-level-3-wrapper', '.nav__our-products--cladding + .menu-level-3-wrapper + img');
        });

        // Initially show the 'decking' menu
        showMenu('.nav__our-products--decking', '.nav__our-products--decking + .menu-level-3-wrapper', '.nav__our-products--decking + .menu-level-3-wrapper + img');
      } else {
        var toggleMenuVisibility = function toggleMenuVisibility(menu, parentLi) {
          if (menu.style.display === 'block') {
            menu.style.display = 'none';
            parentLi.classList.remove('is-expanded');
            parentLi.classList.add('is-collapsed');
          } else {
            menu.style.display = 'block';
            parentLi.classList.remove('is-collapsed');
            parentLi.classList.add('is-expanded');
          }
        }; // Add click event listeners to the span elements
        // Get the span elements with classes .nav__our-products--decking and .nav__our-products--cladding
        var deckingSpan = document.querySelector('.nav__our-products--decking');
        var claddingSpan = document.querySelector('.nav__our-products--cladding');

        // Get the respective div elements with class menu-level-3-wrapper
        var deckingMenu = deckingSpan.nextElementSibling;
        var claddingMenu = claddingSpan.nextElementSibling;
        $('.nav__our-products--decking', context).off('click').on('click', function (e) {
          e.preventDefault();
          toggleMenuVisibility(deckingMenu, deckingSpan.parentElement);
        });
        $('.nav__our-products--cladding', context).off('click').on('click', function (e) {
          e.preventDefault();
          toggleMenuVisibility(claddingMenu, claddingSpan.parentElement);
        });
      }
    }
  };
  Drupal.behaviors.header = {
    attach: function attach(context, setting) {
      var lastScrollTop = 0;
      var delta = 50;
      $(window).scroll(function () {
        var currentScrollPos = $(this).scrollTop();
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
        var scrollTriggerDiv = $('.product-hero');
        var hiddenDiv = $('.product-attributes-wrapper');
        var lastScrollPos = 0;
        $(window).scroll(function () {
          var st = $(this).scrollTop();
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
    }
  };
  Drupal.behaviors.removePipeSign = {
    attach: function attach(context, setting) {
      var display_center = jQuery('.coh-paragraph.directory-hero__display-center').length;
      var type_of_installer = jQuery('.coh-paragraph.directory-hero__distributors').length;
      if (display_center == 0 || type_of_installer == 0) {
        jQuery('.coh-inline-element.separator').remove();
      }
    }
  };
  Drupal.behaviors.socialLinks = {
    attach: function attach(context, settings) {
      $(".social-links li:has(a[href=''])").remove();
    }
  };
  Drupal.behaviors.sampleOrderForm = {
    attach: function attach(context, setting) {
      var basket = JSON.parse(localStorage.getItem('basket')) || [];
      var sampleNames = '';
      $('.order-a-sample-submit #edit-actions-submit').on('click', function (params) {
        if ($('#edit-address-address-line1').val() === '' && $('#edit-address-postal-code').val() === '' && $('#edit-address-locality').val() === '') {
          var addressWrapper = $('#address-address-wrapper');
          if (addressWrapper.hasClass('hidden')) {
            addressWrapper.removeClass('hidden');
          }
        }
      });
      $.each(basket, function (key, item) {
        var SampleOrderCheckboxes = $('.sample-name-checkbox .form-checkbox');
        $.each(SampleOrderCheckboxes, function (id, inputElements) {
          var input = inputElements.labels['0'].innerText;
          if ((item.productType === 'Terrasse' || item.productType === 'Decking') && item.width.indexOf('126mm') >= 0) {
            if (input === item.name + ' ' + 'SB' + ' ' + item.colour) {
              inputElements.checked = true;
              // eslint-disable-next-line @typescript-eslint/no-unused-vars
              sampleNames = sampleNames + item.name + ' ' + 'SB' + ' ' + item.colour + ';';
            }
          } else {
            if (input === item.name + ' ' + item.colour) {
              inputElements.checked = true;
              sampleNames = sampleNames + item.name + ' ' + item.colour + ';';
            }
          }
        });
        var projectTypeCheckboxes = $('.project-type-checkbox .form-checkbox');
        $.each(projectTypeCheckboxes, function (id, inputElement) {
          if (inputElement.labels[0].innerText.indexOf(item.productType) > 0) {
            inputElement.checked = true;
          }
        });
      });
      var sampleNamesHubspot = $('.sample-names-hubspot');
      sampleNamesHubspot.prop('value', sampleNames);
      sampleNamesHubspot.css('display', 'none');
      $('.sample-name-checkbox').css('display', 'none');
      $('.website-order-code #edit-website-order-code').css('display', 'none');
    }
  };
  Drupal.behaviors.mapAndLocate = {
    attach: function attach(context, setting) {
      // Get a reference to the scrollable section
      var scrollableSections = document.querySelectorAll('.find-installers-results-wrapper');
      scrollableSections.forEach(function (scrollableSection) {
        var isScrolling = false;
        // Add a scroll event listener to the scrollable section
        scrollableSection.addEventListener('scroll', function (e) {
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
      if ($('.find-installers-results-row').find('.views-field-nothing').length === 0) {
        $('.find-installers-results-row').find('.find-installers-content').addClass('find-installers-content-content-none');
        $('.find-installers-results-row').addClass('find-installers-results-row-content-none');
        /* $('.coh-style-millboard-find-installers-view-style').find('.coh-style-filter-and-sort-button').attr("disabled","true").addClass('filter-and-sort-button-disabled'); */
      } else {
        $('.find-installers-results-row').find('.find-installers-content').removeClass('find-installers-content-content-none');
        $('.find-installers-results-row').removeClass('find-installers-results-row-content-none');
        /* $('.coh-style-millboard-find-installers-view-style').find('.coh-style-filter-and-sort-button').removeAttr("disabled").removeClass('filter-and-sort-button-disabled'); */
      }
    }
  };

  Drupal.behaviors.disableRightClick = {
    attach: function (context, setting) {
      console.log('check');
      document.addEventListener('contextmenu', function (event) {
        if (event.target.tagName === 'IMG') {
          // Log the event to Google Tag Manager or Analytics
          window.dataLayer = window.dataLayer || [];
          window.dataLayer.push({
            'event': 'imageRightClick',
            'imageUrl': event.target.src,
          });
        }

        // Disable right-click on the entire page
        event.preventDefault();
      });
    }
  }

  Drupal.behaviors.searchFilters = {
    attach: function (context, setting) {
      if ($(".coh-style-clear-facets-block").length) {
        if ($("#block-search-content-type").length) {
          var activeItems = $(".facet-item .is-active");  
          activeItems.each(function() {
            console.log($(this).attr("data-drupal-facet-item-value"));
            if ($(".coh-style-clear-facets-block").find(`[data-drupal-facet-item-value='${$(this).attr('data-drupal-facet-item-value')}']`).length === 0) {
              $(".coh-style-clear-facets-block").prepend($(this).parent().clone());
            }
          });
        }
      }

      if ($("#block-search-content-type").length > 0) {
        var facetItems = $("#block-search-content-type .facet-item a:not(.is-active)");
        var currentPageUrl = window.location.pathname;
        var checkFacetFilter = currentPageUrl.split('/').slice(-2);
      
        if (facetItems.length > 0) {
          facetItems.each(function() {
            var facetUrl = $(this).attr("href").split('/').slice(-2);
      
            if (checkFacetFilter[0] === facetUrl[0]) {
              var newUrl = window.location.origin + currentPageUrl;
              if (!currentPageUrl.endsWith(facetUrl.join('/'))) {
                newUrl += '/' + facetUrl.join('/');
              }
              $(this).attr("href", newUrl);
            }
          });
        }
      }
    },
  };

  Drupal.behaviors.copyButton = {
    attach: function (context, setting) {
      $(document).ready(function() {
        $(".copy-text").on("click", function() {
            // Get the value of the button
            const url = $(this).attr("url");
            // Copy to clipboard using the Clipboard API
            navigator.clipboard.writeText(url).then(function() {
                console.log("URL copied to clipboard!");
            }).catch(function(error) {
                console.error("Failed to copy: ", error);
            });
        });
      });
    }
  }
  
  Drupal.behaviors.addMapMarker = {
    attach: function (context, settings) {
      if ($('.map-with-marker .geolocation-map-wrapper').length) {
        let mapId = $('.geolocation-map-wrapper').attr('id');
        let interval = setInterval(function () {
          let map = Drupal.geolocation.getMapById(mapId);
    
          if (map && map.googleMap) {
            clearInterval(interval);
            let location = {
              lat: $('.map-with-marker').data('lat'),
              lng: $('.map-with-marker').data('lng')
            };
    
            let title = $('.map-with-marker').data('title') || 'Default Marker Title';

            function addMarker(location, map, title) {
              let newMarker = new google.maps.Marker({
                position: new google.maps.LatLng(parseFloat(location.lat), parseFloat(location.lng)),
                icon: '/themes/custom/millboard/svg/map_marker.svg',
                title: title
              });
                
              newMarker.setMap(map.googleMap);
              map.mapMarkers = map.mapMarkers || [];
              map.mapMarkers.push(newMarker);

              let bounds = new google.maps.LatLngBounds();
              bounds.extend(newMarker.getPosition());
              map.googleMap.fitBounds(bounds);
            }
            addMarker(location, map, title);
          } else {
            console.log("Waiting for map to be initialized...");
          }
        }, 500);
      }
    }
  }

  Drupal.behaviors.imageHotpointsBehaviour = {
    attach: function (context, settings) {
      let hotpoint = $('.hotpoint label.coh-inline-element');

      if(hotpoint.length) {
        hotpoint.on("click",function() {
          var offset = $(this).offset();
          var viewportWidth = $(window).width();
          var distanceFromTopRight = {
              top: offset.top,
              right: viewportWidth - offset.left - $(this).outerWidth()
          };
          if(distanceFromTopRight.right < 190){
            let hotpoint_content = $(this).siblings(".hotpoint-content");
            hotpoint_content.css({
              'position': 'absolute',
              'right' : '100%',
              'transform-origin' : 'right top'
            })
          }
        })
      }
    }
  }

  Drupal.behaviors.whereToBuyFilterUpdate = {
    attach: function (context, settings) {
      var currentLanguage = drupalSettings.path.currentLanguage;
      if(currentLanguage == "en-us") {
        $(document).ready(function() {
          var currentPageParams = window.location.search;
          var distance = currentPageParams.split("%3C%3D")[1];
          distance = distance.replace(/\D/g, "");
          if($('#views-exposed-form-millboard-find-installers-where-to-buy').length && $('#edit-field-store-location-proximity').length) {
            $('#edit-field-store-location-proximity').val(distance);
            $('#views-exposed-form-millboard-find-installers-where-to-buy').trigger('change');
          }
        });
      }
    }
  }

  Drupal.behaviors.productManualHandler = {
    attach: function (context, settings) {
      const $productWrappers = $(context).find('.product-manual-wrapper');  
      $productWrappers.each(function () {
        const $wrapper = $(this);
        const $products = $wrapper.find('.product-manual > .product-swatch');
        const $realLifeImageContainer = $wrapper.find('.product-reallife-image');
        if ($products.length > 0) {
          const $firstProduct = $products.first();
          $firstProduct.addClass('selected-product');
          const $firstProductImage = $firstProduct.find('img.reallife-image');
          if ($firstProductImage.length) {
            const firstImageSrc = $firstProductImage.attr('src');
            const link = $firstProductImage.data('link') || '#';
            const text = $firstProductImage.data('sku') || 'No SKU available';
            const linkElement = `<a href="${link}" target="_self">${text}</a>`;
            const imgElement = `<img src="${firstImageSrc}" alt="${text}-Image">`;
            $realLifeImageContainer.html(imgElement + " " + linkElement);
          }
        }
        $products.on('click', function () {
          const $clickedProduct = $(this);
          $products.removeClass('selected-product');
          $clickedProduct.addClass('selected-product');
          const $selectedImage = $clickedProduct.find('img.reallife-image');
          const link = $selectedImage.data('link') || '#';
          const text = $selectedImage.data('sku') || 'No SKU available';
          const imgSrc = $selectedImage.attr('src');
          const imgElement = `<img src="${imgSrc}" alt="Selected Product Image">`;
          $realLifeImageContainer.html(imgElement);
          const linkElement = `<a href="${link}" target="_self">${text}</a>`;
          $realLifeImageContainer.append(linkElement);
        });
      });
    },
  };

  Drupal.behaviors.simpleTabSwitcher = {
    attach: function (context, settings) {
      // Select all tab links within the current context and ensure once per element.
      const tabLinks = once('simple-tab-init', '.tab-links [id^="simple-tab-"]', context);

      tabLinks.forEach((link) => {
        link.addEventListener('click', function (event) {
          event.preventDefault();

          // Remove 'active' class from all tab links and containers.
          tabLinks.forEach((l) => l.classList.remove('active'));
          document.querySelectorAll('.tab-container [id^="simple-tab-container-"]').forEach((container) => {
            container.classList.remove('active');
          });

          // Add 'active' class to the clicked link and corresponding container.
          this.classList.add('active');
          const containerId = this.id.replace('tab', 'tab-container');
          const container = document.getElementById(containerId);
          if (container) {
            container.classList.add('active');
          }
        });
      });

      // Set the first tab and container as active by default.
      if (tabLinks.length > 0) {
        tabLinks[0].classList.add('active');
        const firstContainer = document.getElementById('simple-tab-container-1');
        if (firstContainer) {
          firstContainer.classList.add('active');
        }
      }
    },
  };

  Drupal.behaviors.webformField = {
    attach: function (context, settings) {
      context.addEventListener("click", function (event) {
        const element = event.target.closest(".form-type-select");
        if (element) {
          const labelChild = element.querySelector("label.label-blurred-empty, label.label-blurred");
          if (labelChild && labelChild.classList.contains("label-blurred-empty")) {
            element.classList.remove("label-blurred-empty");
            labelChild.classList.remove("label-blurred-empty");
            labelChild.classList.add("label-blurred");
          }
        }
      });
    },
  };
  
})(jQuery, Drupal);