(function ($, Drupal, once) {
  Drupal.behaviors.sampleBasket = {
    attach: function (context, settings) {

      // Reset basket if basket languagage does not match the region.
      var basketLanguage = localStorage.getItem('basket_language');
      if (!basketLanguage || drupalSettings.path.currentLanguage !== basketLanguage) {
        localStorage.clear('basket');
        // Set the new language for basket.
        basketLanguage = drupalSettings.path.currentLanguage;
        localStorage.setItem('basket_language', basketLanguage);
      }

      let currentURL = window.location.pathname;
      if (currentURL.indexOf('/thank-you') > 0) {
        let queryParameter = location.search;
        if (queryParameter.indexOf('token')) {
          localStorage.clear('basket');
        }
      }
      // Basket state
      let basket = JSON.parse(localStorage.getItem('basket')) || [];
      let basketItem = $('.basket-items');
      let basketOverlay = $('#basket-overlay');// Function to update the basket display
      function updateBasketDisplay() {
        // Clear existing basket display
        basketItem.empty();

        $(".order-sample-card").removeClass('selected');
        // Display the basket contents
        for (let i = 0; i < basket.length; i++) {
          // Create a remove button with a data attribute to store the index
          let remove_button = '<button class="remove-from-basket-btn" data-basket-index="' + i + '">' + Drupal.t('Remove') + '</button>';
          let product_html = '<div class="inner-wrapper coh-style-basket-card-style" id="' + basket[i].productID + '">';
          product_html += '<div class="product-left"> <div class="product-image"><img src="' + basket[i].image + '" /></div><div class="product-remove">' + remove_button + '</div></div>';
          if (basket[i].productType === drupalSettings.cladding_translation) {
            product_html += '<div class="product-right"> <div class="product-type coh-style-f37-ginger-light coh-style-paragraph-default">' + drupalSettings.cladding_translation + '</div>';
          }
          else if(basket[i].productType === drupalSettings.decking_translation) {
            product_html += '<div class="product-right"> <div class="product-type coh-style-f37-ginger-light coh-style-paragraph-default">' + drupalSettings.decking_translation + '</div>';
          }
          product_html += '<h4 class="product-title coh-style-heading-4-size-statements-regular">' + basket[i].name + '</h4>';
          product_html += '<div class="product-colour"><span class="colour coh-style-paragraph-large coh-style-f37-ginger-regular">' + basket[i].colour + ' - ' + basket[i].width + '</span><span class="free-text coh-style-paragraph-regular">' + Drupal.t('FREE') + '</span></div>';
          product_html += '<div class="product-remove coh-style-paragraph-regular">' + remove_button + '</div></div></div>';
          let item = '<li>' + product_html + '</li>';
          // Append the item to the basket display
          // basketItem.append(item);
          if (basket[i].productType === drupalSettings.cladding_translation) {
            // basketItem.append(item);
            $('.cladding .basket-items').append(item);
          }
          else if(basket[i].productType === drupalSettings.decking_translation) {
            // basketItem.append(item);
            $('.decking .basket-items').append(item);          
          }
          $('.checkout-basket-wrapper .basket-items').append(item);
          $(".order-sample-card#"+basket[i].productID).addClass('selected');
        }

        // Click handler for "Remove from Basket" button
        setTimeout(function () {
          var removeBasketBtn = once("remove_basket_btn", ".remove-from-basket-btn", context);
          if (removeBasketBtn.length > 0) {
            removeBasketBtn.forEach(function (eachRemoveBtn, index) {
              $(eachRemoveBtn).click(function() {
                // Get the index of the item to remove
                let indexToRemove = $(this).data('basket-index');
                // Remove the item from the basket
                basket.splice(indexToRemove, 1);
                // Update the basket display
                updateBasketDisplay();
                // Save updated basket to local storage
                localStorage.setItem('basket', JSON.stringify(basket));
                // remove the error msg if any.
                $('.error-message').remove();
              });
            });
          }
        }, 50);

        // Show/hide the basket overlay
        if(basket.length > 0) {
          var langcode = 'en-gb';
          var urlSplit = window.location.pathname.split('/');
          $('#basket-overlay').show();
          if ($.inArray(1, urlSplit)) {
            langcode = urlSplit[1];
          }
          var checkout_button = '<a class="checkout-btn coh-link link--cta coh-style-primary-button" href=/' + langcode + '/basket>' + Drupal.t('confirm sample request') + '</a>';
          var text = '<div class="note-text coh-style-f37-ginger-light coh-style-paragraph-default">' + Drupal.t('Please note you can only order a combined maximum order of 3 Decking and 3 Cladding samples at any given time.' + '</div>');
          $('.checkout').html(checkout_button);
          $('.modal-sample-footer').removeClass('is-hidden');
          $('.top-text').html(text);
          $('.menu-item--basket').html('<span class="menu-item--basket--count">' + basket.length + '</span>' + Drupal.t('Basket'));

        } else {
          let empty_text = '<div class="empty-text"><h5>' + Drupal.t('Your basket is empty.') + '</h5><p class="coh-style-paragraph-regular">' + Drupal.t('Add samples from our product pages and they will appear here.') + '</p>'
          // $('#basket-overlay').hide();
          $('.checkout').html('');
          $('#basket-overlay').hide();
          $('.modal-sample-footer').addClass('is-hidden');
          $('.top-text').html(empty_text);
          $('.menu-item--basket').html(Drupal.t('Basket'));
        }
        let add_to_basket_btn = $('.add-to-basket-btn.pdp');
        if(add_to_basket_btn.length > 0) {
        // Get product information
          let productName = $('.add-to-basket-btn').data('product-name');
          let colour = $('.add-to-basket-btn').data('attribute-colour');
          let width = $('.add-to-basket-btn').data('attribute-width');
          // Check if the product is already in the basket
          let existingProductIndex = basket.findIndex(function (item) {
            if(item.name === productName && item.colour === colour && item.width === width) {
              return item.name;
            }
          });
          if(existingProductIndex !== -1)  {
            add_to_basket_btn.html(Drupal.t('Remove Sample'));
          } else {
            add_to_basket_btn.html(Drupal.t('Add Free Sample'));
          }
        }

        if (($('.decking .basket-items li').length + $('.cladding .basket-items li').length) >= 3) { 
          $("#block-millboard-views-block-order-sample-listing-order-samplecladding .order-sample-card").each(function() {
              if (!$(this).hasClass('selected')) {
                 $(this).addClass('disable');
              }
          });
          $("#block-millboard-views-block-order-sample-listing-order-sample-decking .order-sample-card").each(function() {
            if (!$(this).hasClass('selected')) {
               $(this).addClass('disable');
            }
          });
        } else {
          $("#block-millboard-views-block-order-sample-listing-order-samplecladding .order-sample-card , #block-millboard-views-block-order-sample-listing-order-sample-decking .order-sample-card").each(function() {
            $(this).removeClass('disable');
          });
          $("#block-millboard-views-block-order-sample-listing-order-sample-decking .order-sample-card").each(function() {
            $(this).removeClass('disable');
          });
        }
      }

      // Initialize basket display on page load
      updateBasketDisplay();
      // Click handler for "Add to Basket" button
      var addToBasketButton = $(once('button-basket', '.add-to-basket-btn', context));
      addToBasketButton.click(function (e) {
        e.preventDefault();
        // Get product information
        let productType = $(this).data('product-type');
        let productID = $(this).data('product-id');
        let productName = $(this).data('product-name');
        let colour = $(this).data('attribute-colour');
        let image = $(this).data('image-thumb');
        let width = $(this).data('attribute-width');
        basketOverlay.show().addClass('basket-overlay-visible');
        $('#basket-overlay-backdrop').fadeIn(500).addClass('basket-overlay-backdrop-visible');
        basketOverlay.show();
        $('#basket-overlay-backdrop').show();
        $('body').addClass('no-scroll');
        // remove the error msg if any.
        $('.error-message').remove();
        // Check if the product is already in the basket
        let existingProductIndex = basket.findIndex(function (item) {
          if(item.name === productName && item.colour === colour && item.width === width) {
            return item.name;
          }
        });

        if (existingProductIndex !== -1) {
          // Product already in the basket, update quantity or handle accordingly
          if($(this).hasClass('pdp')) {
            basket.splice(existingProductIndex, 1);
            basketOverlay.show().removeClass('basket-overlay-visible');
        
            $('#basket-overlay-backdrop').removeClass('basket-overlay-backdrop-visible');

            basketOverlay.hide();
            $('#basket-overlay-backdrop').hide();
            let errorMessage = '<div class="error-message-remove-product coh-style-container-wide"><span class="msg-text-basket coh-style-paragraph-regular">' + Drupal.t("Sample removed from basket") + '</span><button class="error-dismiss">Dismiss</button></div>';
            $('header').after(errorMessage);
            $('#basket-overlay-backdrop').fadeIn(500).addClass('basket-overlay-backdrop-visible');
            $('#basket-overlay-backdrop').show();
            setTimeout(function(){
              $('.error-message-remove-product').remove();
              $('#basket-overlay-backdrop').fadeOut(500).removeClass('basket-overlay-backdrop-visible');
              $('#basket-overlay-backdrop').hide();
              $('body').removeClass('no-scroll');
            }, 3000);
          } else {
            if (basket && productID) {
              const productIdToRemove = productID;
              const indexToRemove = basket.findIndex(item => item.productID === productIdToRemove);
              if (indexToRemove !== -1) {
                  basket.splice(indexToRemove, 1);
                  localStorage.setItem('basket', JSON.stringify(basket));
              }
            }
          }
          updateBasketDisplay();
          localStorage.setItem('basket', JSON.stringify(basket));
          return;
        }
        let errorMessage = '';
        // Check if the basket has reached the maximum limit (3)
        if (basket.length >= 3) {
          let deckingProduct = 0;
          let claddingProduct = 0;

          $.each(basket, function(id, item) {
            if (item.productType === drupalSettings.decking_translation) {
              deckingProduct = deckingProduct + 1;
            }
            if (item.productType === drupalSettings.cladding_translation) {
              claddingProduct = claddingProduct + 1;
            }
          });

          if (basket.length >= 3) {
            errorMessage = "<span class='err-msg-text-basket'>" + Drupal.t('You can only add up to 3 samples to the basket.') + "</span><button class='error-dismiss'>Dismiss</button>";
            if($(".error-message").length === 0){
              $('.model-sample-body').prepend("<div class='error-message coh-style-paragraph-default coh-style-f37-ginger-light'></div>");
            }
            $('.error-message').html(errorMessage);
            return;
          }
        }

        basket.push({
          productType: productType,
          productID: productID,
          name: productName,
          colour: colour,
          image: image,
          width: width,
        });
        // Update the basket display
        updateBasketDisplay();
        // Save basket to local storage
        localStorage.setItem('basket', JSON.stringify(basket));
      });
      // Optionally, implement "Close Basket Overlay" functionality
      $('#close-basket-overlay-btn').off('click').on('click',function () {
        // Close the overlay
        $('#basket-overlay').toggleClass('basket-overlay-visible');
        $('#basket-overlay-backdrop').fadeOut(500).removeClass('basket-overlay-backdrop-visible');
        $('body').removeClass('no-scroll');
        // clear the error msg as well
        $('.error-message').remove();
        // $('#basket-overlay').;
      });
      // Show basket on header basket menu icon.
      $('.menu-item--basket').off('click').on('click',function (e) {
        e.preventDefault();
        // Update the basket display
        updateBasketDisplay();
        // remove the error msg if any.
        $('.error-message').remove();
        $('#basket-overlay').show().addClass('basket-overlay-visible');
        $('body').addClass('no-scroll');
        $('#basket-overlay-backdrop').fadeIn(500).addClass('basket-overlay-backdrop-visible');
      });

      // Added empty text in the basket page.
      let basketWrapper = $('.checkout-basket-wrapper');
      if (basketWrapper.length > 0) {
        if ($('.checkout-basket-wrapper .model-sample-body .basket-items').children().length === 0) {
          $('.checkout-basket-wrapper .model-sample-body').append('<div class="top-text coh-style-f37-ginger-light coh-style-paragraph-default coh-style-f37-ginger-light"><div class="empty-text"><h5>Your basket is empty.</h5><p class="coh-style-paragraph-regular">Add samples from our product pages and they will appear here.</p></div></div>');
        }
      }
    }
  };

  Drupal.behaviors.infoToggle = {
    attach: function (context, settings) {
      $(document).ready(function() {
        var deckerText = $('.total-decking-elements').text();
        var claddingText = $('.total-cladding-elements').text();
        var currentLang = drupalSettings.path.currentLanguage;
  
        if ($('.total-decking-elements').length && $('.total-cladding-elements').length) {
          if (currentLang == 'de-de') {
            $('.product-view-filter-header').text(
              'Es werden ' + deckerText + ' Terrassenoptioen sowie ' + claddingText + ' Fassadenoptionen angezeigt'
            );
          } else if (currentLang == 'fr-fr') {
            $('.product-view-filter-header').text(
              'Affichage de ' + deckerText + ' échantillons de terrasses et de ' + claddingText + ' échantillons de bardage'
            );
          } else {
            $('.product-view-filter-header').text(
              'Showing ' + deckerText + ' Decking Samples and ' + claddingText + ' Cladding Samples'
            );
          }
        } else {
          if ($('.total-decking-elements').length) {
            if (currentLang == 'de-de') {
              $('.product-view-filter-header').text('Es werden ' + deckerText + ' Terrassenoptioen');
            } else if (currentLang == 'fr-fr') {
              $('.product-view-filter-header').text('Affichage de ' + deckerText + ' échantillons de terrasses');
            } else {
              $('.product-view-filter-header').text('Showing ' + deckerText + ' Decking Samples');
            }
          }
          if ($('.total-cladding-elements').length) {
            if (currentLang == 'de-de') {
              $('.product-view-filter-header').text('Es werden ' + claddingText + ' Fassadenoptionen angezeigt');
            } else if (currentLang == 'fr-fr') {
              $('.product-view-filter-header').text('Affichage de ' + claddingText + ' échantillons de bardage');
            } else {
              $('.product-view-filter-header').text('Showing ' + claddingText + ' Cladding Samples');
            }
          }
        }
  
        if (currentLang == 'de-de') {
          $('.product-view-filter-header').append('<h4>Selektieren Sie 3 Muster indem Sie die Farben anklicken</h4>');
        } else if (currentLang == 'fr-fr') {
          $('.product-view-filter-header').append('<h4>Sélectionnez 3 échantillons en cliquant sur les couleurs</h4>');
        } else {
          $('.product-view-filter-header').append('<h4>Select 3 samples</h4>');
        }
      });

      $('.order-sample-cards .views-row').each(function() {
        var dataId = $(this).children('.order-sample-card').attr('data-id');
        var Id = $(this).children('.order-sample-card').attr('id');
        var width = $(this).children('.order-sample-card').attr('board-width');
        var productType = $(this).children('.order-sample-card').attr('data-product');
    
        if (dataId && Id && width && productType) {
            var selector = 'div[board-width="' + width + '"][data-product="' + productType + '"] .product-list .product-colour[data-id="' + Id + '"]';
              if ($(selector).length === 0) {
                var newItem = $('<span>')
                    .text('Product ID: ' + dataId)
                    .attr('data-id', Id)
                    .attr('board-width', width)
                    .attr('color-id', dataId)
                    .attr('data-type', productType)
                    .addClass(dataId.replace(/-/g, '_'))
                    .addClass('product-colour')
                    .css('cursor', 'pointer');
                $('div[board-width="' + width + '"][data-product="' + productType + '"] .product-list').append(newItem);
            }
          }
        });

        $('.order-sample-cards .views-row').each(function() {
          var Id = $(this).children('.order-sample-card').attr('id');
          $(this).find('.product-colour').each(function() {
              var productId = $(this).attr('data-id');
              if (Id === productId) {                  
                $(this).addClass('selected');
              }
          });
        });

      $('.order_sample_card_content .info').off('click').on('click', function() {
        $('.product-info').addClass('hidden').removeClass('active');
        $(this).parent().find('.product-info').removeClass('hidden ').addClass('active');
      }) 
      
      $('.order_sample_card_content .product-list .product-colour').off('click').on('click', function() {
          var colorId = $(this).attr('color-id');
          var productId = $(this).attr('data-type');
          var Id = $(this).attr('data-id');
  
          $('.product-info').addClass('hidden').removeClass('active');
          $('div[data-product="' + productId + '"][data-id^="' + colorId + '"][id = "' + Id + '"] .product-info')
            .removeClass('hidden')
            .addClass('active');
      });

      $('.product-info .close-pop-up').off('click').on('click', function() {
        $('.product-info').addClass('hidden').removeClass('active');
      }) 
      
      $('.model-sample-body').on('click', '.error-dismiss', function() {
        $(this).closest('.error-message').hide();
      });
    }
  };

})(jQuery, Drupal, once);
