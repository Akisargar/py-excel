<?php

namespace Drupal\millboard_modello_slider\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a block for displaying Millboard Modello slider data.
 *
 * @Block(
 *   id = "millboard_modello_slider_block",
 *   admin_label = @Translation("Millboard Modello-Linear Slider Block")
 * )
 */
class ModelloLinearSliderBlock extends BlockBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Define an array of image paths for the slider (top-image and nav-images)
    $indices = [
      'Design 1.1', 'Design 1.2', 'Design 1.3', 'Design 1.4', 'Design 1.5', 'Design 1.6', 'Design 1.7', 'Design 1.11', 'Design 1.12', 'Design 1.13', 'Design 1.14', 'Design 1.15', 'Design 1.16', 'Design 1.17', 'Design 1.21',
      'Design 1.22', 'Design 1.23', 'Design 1.24', 'Design 1.25', 'Design 1.26', 'Design 1.27', 'Design 1.28', 'Design 1.31', 'Design 1.37', 'Design 1.38', 'Design 1.41', 'Design 1.42', 'Design 1.43', 'Design 1.44',
      'Design 1.45', 'Design 1.46', 'Design 1.47', 'Design 1.48', 'Design 1.51', 'Design 1.52', 'Design 1.53', 'Design 1.54', 'Design 1.55', 'Design 1.56', 'Design 1.57', 'Design 1.58', 'Design 1.61', 'Design 1.62',
      'Design 1.63', 'Design 1.64', 'Design 1.65', 'Design 1.66', 'Design 1.67', 'Design 1.71', 'Design 1.72',
    ];

    $images = array_map(function ($index) {
      return [
        'top_image' => "/modules/custom/millboard_modello_slider/img/linear/$index.jpg",
        'nav_image' => "/modules/custom/millboard_modello_slider/img/linear/$index.jpg",
      ];
    }, $indices);

    $slider_html = '<div class="coh-style-container-small coh-style-inner-container-padding laying-pattern">
    <h3 class="text-align-center"><strong>' . $this->t('LINEAR LAYING PATTERNS') . '</strong></h3>
    <p class="coh-style-green-dash" style="margin:0 auto;">&nbsp;</p>
    <p class="text-align-center p1">&nbsp;</p>
    <p class="text-align-center p1">' . $this->t('With Modello, the possibilities are endless. By simply offsetting the same board, you can create a stunning array of over 50 unique patterns, each with its own distinctive character. From refined symmetry to bold, dynamic configurations. Modello empowers you to craft a design thats uniquely yours. Whether you choose Linear or Contour, this revolutionary product lets you bring your vision to life, redefining what decking can achieve.') . '</p>
    </div>';
    $slider_html .= '<div class="Linear-slider coh-container coh-style-container-wrapper"><div class="slick-top-image slider-for">';
    foreach ($images as $image) {
      $slider_html .= '<div><img src="' . $image['top_image'] . '" alt="Top Image"></div>';
    }
    $slider_html .= '</div>';

    $nav_html = '<div class="slick-nav-images slider-nav">';
    foreach ($images as $image) {
      $nav_html .= '<div class="nav-image"><img src="' . $image['nav_image'] . '" width="100" height="100" alt="Nav Image"></div>';
    }
    $nav_html .= '</div></div>';
    $nav_html .= '<div class="coh-style-container-small coh-style-inner-container-padding laying-pattern-footer">';
    $nav_html .= '<h3 class="text-align-center"><strong>' . $this->t('OVER 50 POSSIBLE PATTERNS') . '</strong></h3>';
    $nav_html .= '<p class="text-align-center">&nbsp;</p>';
    $nav_html .= '<p class="text-align-center"><a class="coh-style-primary-button-with-millboard-green-bg" style="margin:0px auto;" href="#">' . $this->t('GOT A QUESTION?') . '</a></p>';
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
