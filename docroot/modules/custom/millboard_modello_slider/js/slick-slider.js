(function ($, Drupal) {
    Drupal.behaviors.slickSlider = {
        attach: function (context, settings) {
            // Process each Linear-slider instance.
            $('.Linear-slider', context).each(function () {
                var $linearSlider = $(this);
                
                $linearSlider.find('.slider-for').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: false,
                    autoplay: true,
                    fade: false,
                    asNavFor: $linearSlider.find('.slider-nav')
                });
                
                $linearSlider.find('.slider-nav').slick({
                    slidesToShow: 8,
                    slidesToScroll: 1,
                    asNavFor: $linearSlider.find('.slider-for'),
                    dots: false,
                    focusOnSelect: true,
                    infinite: true,
                    arrows: true,
                    responsive: [
                        {
                            breakpoint: 1024, // Tablet breakpoint.
                            settings: {
                                slidesToShow: 7,
                            }
                        },
                        {
                            breakpoint: 768, // Mobile breakpoint.
                            settings: {
                                slidesToShow: 5,
                            }
                        }
                    ]
                });
            });

            $('.contour-slider', context).each(function () {
                var $contourSlider = $(this);
                
                $contourSlider.find('.slider-for').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: false,
                    autoplay: true,
                    fade: false,
                    asNavFor: $contourSlider.find('.slider-nav')
                });
                
                $contourSlider.find('.slider-nav').slick({
                    slidesToShow: 8,
                    slidesToScroll: 1,
                    asNavFor: $contourSlider.find('.slider-for'),
                    dots: false,
                    focusOnSelect: true,
                    infinite: true,
                    arrows: true,
                    responsive: [
                        {
                            breakpoint: 1024, // Tablet breakpoint.
                            settings: {
                                slidesToShow: 7,
                            }
                        },
                        {
                            breakpoint: 768, // Mobile breakpoint.
                            settings: {
                                slidesToShow: 5,
                            }
                        }
                    ]
                });
            });
            $('.Linear-slider').show();
            $('.contour-slider').show();
        }
    };
})(jQuery, Drupal);
