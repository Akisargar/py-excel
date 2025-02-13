<?php

namespace Drupal\millboard_modello_slider\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a block for displaying Millboard Modello slider data.
 *
 * @Block(
 *   id = "millboard_modello_contour_slider_block",
 *   admin_label = @Translation("Millboard Modello-Contour Slider Block")
 * )
 */
class ModelloContourSliderBlock extends BlockBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Define an array of image paths for the slider (top-image and nav-images)
    $indices = [
      'Design 11.1', 'Design 11.2', 'Design 11.3', 'Design 11.4', 'Design 11.5', 'Design 11.6', 'Design 11.7', 'Design 11.11', 'Design 11.12', 'Design 11.13', 'Design 11.14',
      'Design 11.15', 'Design 11.16', 'Design 11.17', 'Design 11.21', 'Design 11.22', 'Design 11.23', 'Design 11.24', 'Design 11.25', 'Design 11.26', 'Design 11.27',
      'Design 11.28', 'Design 11.31', 'Design 11.32', 'Design 11.33', 'Design 11.34', 'Design 11.35', 'Design 11.36', 'Design 11.37', 'Design 11.38', 'Design 11.41',
      'Design 11.42', 'Design 11.43', 'Design 11.44', 'Design 11.45', 'Design 11.46', 'Design 11.47', 'Design 11.48', 'Design 11.51', 'Design 11.52', 'Design 11.53',
      'Design 11.54', 'Design 11.55', 'Design 11.56', 'Design 11.57', 'Design 11.58', 'Design 11.61', 'Design 11.62', 'Design 11.63', 'Design 11.64', 'Design 11.65',
      'Design 11.66', 'Design 11.67', 'Design 11.71', 'Design 11.72',
    ];

    $images = array_map(function ($index) {
      return [
        'top_image' => "/modules/custom/millboard_modello_slider/img/contour/$index.jpg",
        'nav_image' => "/modules/custom/millboard_modello_slider/img/contour/$index.jpg",
      ];
    }, $indices);

    $slider_html = '<div class="coh-style-container-small coh-style-inner-container-padding laying-pattern">
      <h3 class="text-align-center"><strong>' . $this->t('CONTOUR LAYING PATTERNS') . '</strong></h3>
      <p class="coh-style-green-dash" style="margin:0 auto;">&nbsp;</p>
      <p class="text-align-center p1">&nbsp;</p>
      <p class="text-align-center p1">' . $this->t('With Modello, the possibilities are endless. By simply offsetting the same board, you can create a stunning array of over 50 unique patterns, each with its own distinctive character. From refined symmetry to bold, dynamic configurations. Modello empowers you to craft a design thats uniquely yours. Whether you choose Linear or Contour, this revolutionary product lets you bring your vision to life, redefining what decking can achieve.') . '</p>
      </div>';
    $slider_html .= '<div class="contour-slider coh-container coh-style-container-wrapper"><div class="slick-top-image slider-for">';
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
