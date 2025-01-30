<?php

namespace Drupal\millboard_modello_slider\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block for displaying Millboard Modello slider data.
 *
 * @Block(
 *   id = "millboard_modello_slider_block",
 *   admin_label = @Translation("Millboard Modello-Linear Slider Block")
 * )
 */
class ModelloLinearSliderBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Define an array of image paths for the slider (top-image and nav-images)
    $images = [
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
      [
        'top_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.34.jpg',
        'nav_image' => '/modules/custom/millboard_modello_slider/img/linear/Design 1.24.jpg',
      ],
    ];

    $slider_html = '<div class="coh-style-container-small coh-style-inner-container-padding laying-pattern">
    <h3 class="text-align-center"><strong>' . t('LINEAR LAYING PATTERNS') . '</strong></h3>
    <p class="coh-style-green-dash" style="margin:0 auto;">&nbsp;</p>
    <p class="text-align-center p1">&nbsp;</p>
    <p class="text-align-center p1">' . t('With Modello, the possibilities are endless. By simply offsetting the same board, you can create a stunning array of over 50 unique patterns, each with its own distinctive character. From refined symmetry to bold, dynamic configurations. Modello empowers you to craft a design thats uniquely yours. Whether you choose Linear or Contour, this revolutionary product lets you bring your vision to life, redefining what decking can achieve.') . '</p>
    </div>';
    $slider_html .= '<div class="Linear-slider coh-container coh-style-container-wrapper"><div class="slick-top-image slider-for">';
    foreach ($images as $image) {
      $slider_html .= '<div><img src="' . $image['top_image'] . '" alt="Top Image"></div>';
    }
    $slider_html .= '</div>';

    $nav_html = '<div class="slick-nav-images slider-nav">';
    foreach ($images as $image) {
      $nav_html .= '<div class="nav-image"><img src="' . $image['nav_image'] . '" alt="Nav Image"></div>';
    }
    $nav_html .= '</div></div>';
    $nav_html .= '<div class="coh-style-container-small coh-style-inner-container-padding laying-pattern-footer">';
    $nav_html .= '<h3 class="text-align-center"><strong>' . t('OVER 50 POSSIBLE PATTERNS') . '</strong></h3>';
    $nav_html .= '<p class="text-align-center">&nbsp;</p>';
    $nav_html .= '<p class="text-align-center"><a class="coh-style-primary-button-with-millboard-green-bg" style="margin:0px auto;" href="#">' . t('GOT A QUESTION?') . '</a></p>';
    $nav_html .= '</div>';

    // Combine the top image slider and nav images.
    $slider_html .= $nav_html;

    return [
      '#markup' => $slider_html,
      '#attached' => [
        'library' => [
          'millboard_modello_slider/slider',
        ],
      ],
    ];
  }

}
