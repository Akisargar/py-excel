/**
 * @file
 * script.js
 */

(function ($, Drupal) {
  'use strict';

  function updatePageCount(slickElement, pageCountInfo, currentSlide) {
    slickElement.on(
      'init reInit afterChange',
      function (event, slick, currentSlide = 0, nextSlide) {
        let i = (currentSlide ? currentSlide : 0) + 1;
        if (slick.slideCount < 10 && i < 10) {
          pageCountInfo.text('0' + i + '/' + '0' + slick.slideCount);
        }
        else {
          if (i < 10) {
            pageCountInfo.text('0' + i + '/' + slick.slideCount);
          }
          else {
            pageCountInfo.text(i + '/' + slick.slideCount);
          }
        }
      },
    );
  }

  // create progress bar for carousel
  function progressBar($this, slickElement, context, slickDirection) {
    let progressBar = $this.find('.progress', context);
    let progressBarLabel = $this.find('.slider__label', context);

    slickElement.on('init', function (event, slick) {
      var slideCount = $(this).find(
        '.slick-track .slick-slide:not(.slick-cloned)',
        context,
      ).length;
      var calc = (1 / slideCount) * 100;
      if (slickDirection === 'vertical') {
        progressBar
          .css('background-size', '100% ' + calc + '%')
          .attr('aria-valuenow', calc);
      }
      else {
        progressBar
          .css('background-size', calc + '% 100%')
          .attr('aria-valuenow', calc);
      }
      progressBarLabel.text(calc + '% completed');
    });

    slickElement.on(
      'beforeChange',
      function (event, slick, currentSlide, nextSlide) {
        var calc = ((nextSlide + 1) / slick.slideCount) * 100;
        if (slickDirection === 'vertical') {
          progressBar
            .css('background-size', '100% ' + calc + '%')
            .attr('aria-valuenow', calc);
        }
        else {
          progressBar
            .css('background-size', calc + '% 100%')
            .attr('aria-valuenow', calc);
        }
        progressBarLabel.text(calc + '% completed');
      },
    );
  }

  // check if collection carousel is visible
  function compInFocus(comp) {
    const windowTop = $(window).scrollTop();
    const windowBottom = windowTop + $(window).height();
    const compTop = $(comp).offset().top;
    const compBottom = compTop + $(comp).height();
    return compTop <= windowTop && windowBottom <= compBottom;
  }

  // Add animations to collection carousel
  function addAnimations(item) {
    setTimeout(() => {
      item.find('.swatch-image').addClass('animate-swatch');
    }, 1000);
    item
      .find('.collection-uploader-wrapper')
      .find('img')
      .parent()
      .closest('div').find('img')
      .addClass('animate-image');
    item
      .find('.collection-uploader-wrapper')
      .find('video')
      .addClass('animate-image');
    item.find('.description-wrapper').addClass('animate-image');
  }

  // remove animations to collection carousel on slide change
  function removeAnimations(item) {
    item.find('.swatch-image').removeClass('animate-swatch');
    item
      .find('.collection-uploader-wrapper')
      .find('img')
      .parent()
      .closest('div').find('img')
      .removeClass('animate-image');
    item
      .find('.collection-uploader-wrapper')
      .find('video')
      .removeClass('animate-image');
    item.find('.description-wrapper').removeClass('animate-image');
  }

  // Variables for Collection Scroller to handle custom up and down scroll
  let lastScrollTop = 0;
  var flag = false
  var slideNewIndex = 0;

  // Scroll handler function for Collection scroller
  var scrollHandler = function () {

    const slider = $('.coh-collection-scroller');
    if (slider && slider.length > 0) {
      const compVisible = compInFocus(slider);
      if (compVisible) {
        var slideCount = slider.find('.slick-track .slick-slide:not(.slick-cloned)').length;

        // Calculate the scroll position
        var scrollPos = window.scrollY || document.documentElement.scrollTop;
        var position = (scrollPos / window.innerHeight).toFixed(1);
        // Update the Slick slider based on the scroll position
        var slideIndex = Math.round(position);
        var dataIndex = slider.find('.slick-current').attr('data-slick-index');
        // Check for scroll up or down
        if (scrollPos > lastScrollTop) {
          if (slideNewIndex < slideCount - 1) {
            var difference = slideIndex - position;

            // Chcek for difference to move slide on two points (lowers the time between two slide change)
            if (difference == 0 ) {
              // set flag so slide doesnot get change on same value
              if (!flag) {
                slideNewIndex = parseInt(dataIndex) + 1;
                setTimeout(() => {
                  slider.find('.coh-collection-scroller-wrapper-inner').slick('slickGoTo', slideNewIndex);
                }, 100)
                flag = true
              }
            } else {
              flag = false
            }
          }
        } else {
          if (slideNewIndex < slideCount && slideNewIndex > 0) {
            var difference = slideIndex - position;
            // Chcek for difference to move slide on two points (lowers the time between two slide change)
            if (difference == 0 ) {
              // set flag so slide doesnot get change on same value
              if (!flag) {
                slideNewIndex = parseInt(dataIndex) - 1;
                setTimeout(() => {
                  slider.find('.coh-collection-scroller-wrapper-inner').slick('slickGoTo', slideNewIndex);
                }, 100)
                flag = true
              }
            } else {
              flag = false
            }
          }
        }
        lastScrollTop = scrollPos;
      }
    }
  };

  // Handled scroll Eventlisterner for collectionScroller
  window.addEventListener('scroll', scrollHandler);


  Drupal.behaviors.collectionScroller = {
    attach: function (context, setting) {

      // Initialised slick carousel for collections
      const slider = $('.coh-collection-scroller');
      if (slider && slider.length > 0) {
        slider.find('.coh-collection-scroller-wrapper-inner').not('.slick-initialized').slick({
          dots: false,
          infinite: false,
          speed: 0,
          autoplay: false,
          slidesToShow: 1,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          pauseOnHover: true,
        })
          .on(
            'afterChange',
            function (event, slick, currentSlide = 1, nextSlide) {
              // adding classes to image and description for animations
              var slideItem = document.querySelectorAll('.coh-collection-scroller-wrapper-inner .slick-slide');
              slideItem.forEach(function (item) {
                if ($(item).hasClass('slick-active')) {
                  addAnimations($(item));
                } else {
                  removeAnimations($(item));
                }
              });
              // adding slick count on pager
              slider.each(function () {
                let i = (currentSlide ? currentSlide : 0) + 1;
                let pageCountInfo = $(this).find('.page-count', context);
                pageCountInfo.text('0' + i + '/' + '0' + slick.slideCount);
              });
            },
          );
        var activeSlide = document.querySelector('.coh-collection-scroller-wrapper-inner .slick-active');
        var slideCount = slider.find('.slick-track .slick-slide:not(.slick-cloned)').length;
        if (activeSlide) {
          addAnimations($(activeSlide))
          let pageCountInfo = $(activeSlide).find('.page-count');
          pageCountInfo.text('0' + "1" + '/' + '0' + slideCount);
        }
        var pinDuration = slideCount * 700
        var scrollController = new ScrollMagic.Controller();
        var scrollScene = new ScrollMagic.Scene({
        triggerElement: '.coh-collection-scroller-wrapper-inner',
        triggerHook: "onLeave", duration: pinDuration
        })
        .setPin('.coh-collection-scroller-wrapper-inner')
        .addTo(scrollController)

        $('.slick-slide').each(function (index) {
          var slideIndex = $(this).attr("data-slick-index")
          // Hanlde for tab
          $(this).on('keydown', function (event) {
            if(event.keyCode == 9) {
              // For shift + tab
              if(event.shiftKey) {
                setTimeout(() => {
                  $(this).parents(".slick-slider").slick("slickGoTo", slideIndex - 1);
                }, 1000)

              } else {
              // For Only tab
                setTimeout(() => {
                  slideNewIndex = slideIndex
                  $(this).parents(".slick-slider").slick("slickGoTo", slideIndex + 1);
                }, 100)
              }
            }
          })
        });
      }
    },
  };

  Drupal.behaviors.discoveryCarousel = {
    attach: function (context, setting) {
      $('.coh-discovery-carousel', context).each(function () {
        let slickElement = $(this)
          .find('.millboard-carousel-wrapper', context)
          .find('.coh-slider-container-inner', context);
        let pageCountInfo = $(this).find('.page-count', context);
        updatePageCount(slickElement, pageCountInfo);
        progressBar($(this), slickElement, context);

        $(this).find('.millboard-carousel-wrapper', context).append("<div class='coh-slider-nav-bottom'> " + "<button type='button' class='slick-prev coh-style-slider-navigation-left'>" + "Prev" + "</button>" + "<button class='slick-next coh-style-slider-navigation-right'>" + "</button>" +  "</div>");

        setTimeout(() => {
          slickElement.slick({
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            useTransform: false,
            adaptiveHeight: true,
            prevArrow: $('.slick-prev'),
            nextArrow: $('.slick-next'),
          });
        }, 100)

        var flag = false;

        // Hanlde accessibility for discovery Carousel
        setTimeout(() => {

          var buttonPrevious = $('.coh-slider-nav-bottom .slick-prev')
          var buttonNext= $('.coh-slider-nav-bottom .slick-next')

          buttonPrevious.attr('tabindex', -1)

          var slickItem = $(this).children().find('.slick-slide')

          slickElement.each((index, item) => {
            $(this).children().find('a').on('keydown', function(e) {
              if(e.keyCode == 9) {
                if(e.shiftKey) {
                  setTimeout(() => {
                    if($(item).hasClass('slick-active')) {
                      var index = $(item).attr("data-slick-index");
                      if(index != 0) {
                        buttonPrevious.focus()
                      }
                    }
                  }, 100)
                }
              }
            })

            $(this).children().find('a').on('focus', () =>{
              flag = true
            })
          })



          buttonNext.on("keydown", function (e) {
            slickItem.each((index, item) => {
              var index = $(item).attr("data-slick-index");
              if(e.keyCode == 9) {
                if($(item).hasClass('slick-active')) {
                  setTimeout(() => {
                    if(!flag){
                      $(item).children().find('a').focus();
                    }
                  }, 10)
                }
              }
              // Handle last slide
              if (e.key === "Enter") {
                setTimeout(() => {
                  if($(item).hasClass('slick-active')) {
                    flag =  false
                  // For tab while moving to last slide
                    buttonPrevious.attr('tabindex', 0)
                    if(index == slickItem.length - 1){
                      $(item).children().find('a').focus();
                      buttonNext.attr('tabindex', -1)
                    }
                  }
                }, 500)
              }
            })
          })

          buttonPrevious.on("keydown", function (e) {
            slickItem.each((index, item) => {
              var index = $(item).attr("data-slick-index");

              if (e.key === "Enter") {
                setTimeout(() => {
                  if($(item).hasClass('slick-active')) {
                    buttonNext.attr('tabindex', 0)
                    if(index == 0) {
                      $(item).children().find('a').focus();
                      buttonPrevious.attr('tabindex', -1)
                    }
                  }
                }, 500)
              }
            })
          })
        }, 1000)
      });
    },
  };

  //Handle slider pagination for Staff profile card slider
  Drupal.behaviors.staffProfile = {
    attach: function (context, setting) {
      const slider = $('.coh-staff-profile-carousel');
      if (slider && slider.length) {
        slider.each(function () {
          let slickElement = $(this)
            .find('.millboard-carousel-wrapper', context)
            .find('.coh-slider-container-inner', context);
          let pageCountInfo = $(this).find('.page-count', context);
          updatePageCount(slickElement, pageCountInfo);
          progressBar($(this), slickElement, context);
        });
      }
    },
  };

  Drupal.behaviors.productHeroBannerMobile = {
    attach: function (context, setting) {
      if ($('.coh-style-product-image-gallery-mobile').length > 0) {
        $('.coh-style-product-image-gallery-mobile', context).each(function () {
          let slickElement = $(this).find('.gallery-image-slider', context);
          let pageCountInfo = $(this).find('.page-count', context);
          updatePageCount(slickElement, pageCountInfo);
          progressBar($(this), slickElement, context);

          slickElement.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            useTransform: false,
            variableWidth: true,
            infinite: true,
            arrows: false
          });
        });
      }
    },
  };

  Drupal.behaviors.historyCarousel = {
    attach: function (context, setting) {

      // Initialised slick carousel for collections
      const slider = $('.coh-history-carousel', context);

      if (slider && slider.length) {
        slider.each(function () {
          let slickElement = $(this).find('.millboard-carousel-wrapper', context);
          let slickDirection = 'vertical';
          let progressBarElement = $(this).find('.coh-carousel-inner-wrapper', context);
          progressBar($(this), slickElement, context, slickDirection);

          slickElement.slick({
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            useTransform: false,
            arrows: true,
            loop: false
          });

          progressBarElement.prepend("<span class='progress-year-start'>" + $(this).find('.slick-track .slick-slide', context).first().find('.coh-heading.year').text() + "</span>");
          progressBarElement.append("<span class='progress-year-end'>" + $(this).find('.slick-track .slick-slide', context).last().find('.coh-heading.year').text() + "</span>");

        });
      }

    },
  };

  Drupal.behaviors.productCarousel = {
    attach: function (context, setting) {
      const slider = $('.coh-product-carousel', context);
      if (slider && slider.length) {
        slider.each(function () {
          let slickElement = $(this).find('.millboard-carousel-wrapper', context).find('.coh-slider-container-inner', context);
          let pageCountInfo = $(this).find('.page-count', context);
          updatePageCount(slickElement, pageCountInfo);
          progressBar($(this), slickElement, context);

          slickElement.slick({
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            useTransform: false,
            arrows: true,
            loop: false
          });
        });
      }
    },
  };

  Drupal.behaviors.galleryPopup = {
    attach: function (context, setting) {
      if ($('.gallery-popup-slider').length > 0) {
        var parent = $(".gallery-popup-slider");
        var slickElement = parent.find("#popup_image_items");
        var pageCountInfo = parent.find(".page-count");
        updatePageCount(slickElement, pageCountInfo);

        var progressBar = parent.find(".progress");
        var progressBarLabel = parent.find(".slider__label");

        slickElement.on("init", function (event, slick) {
          var slideCount = parent.find(".slick-track .slick-slide").length;
          var calc = (1 / slideCount) * 100;
          progressBar
            .css("background-size", calc + "% 100%")
            .attr("aria-valuenow", calc);
          progressBarLabel.text(calc + "% completed");
        });

        slickElement.on(
          "beforeChange",
          function (event, slick, currentSlide, nextSlide) {
            var calc = ((nextSlide + 1) / slick.slideCount) * 100;
            progressBar
              .css("background-size", calc + "% 100%")
              .attr("aria-valuenow", calc);
            progressBarLabel.text(calc + "% completed");
          }
        );

        slickElement.slick({
          fade: true,
          prevArrow:
            '<button type="button" class="slick-prev" aria-label="Previous"></button>',
          nextArrow:
            '<button type="button" class="slick-next" aria-label="Next"></button>',
          speed: 200,
        });

        let activeImage = $(".active-image");
        let slideIndex = activeImage.parent().parent().attr("data-slick-index");
        slickElement.slick("slickGoTo", slideIndex);

        $(document).on("keydown", function (e) {
          if (e.key === "ArrowLeft") {
            slickElement.slick("slickPrev");
          } else if (e.key === "ArrowRight") {
            slickElement.slick("slickNext");
          }
        });
      }

    },
  };

})(jQuery, Drupal);
