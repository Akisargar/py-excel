<?php

/**
 * @file
 * This file is for the raising token.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function millboard_common_token_info() {
  $types = [];
  $types['custom-link'] = [
    'name' => t("Custom Link"),
    'description' => t("Group of all the social media links and custom links."),
  ];
  $social_medias = [
    'facebook',
    'twitter',
    'youtube',
    'instagram',
    'pinterest',
    'linkedin',
  ];
  $links = [];
  foreach ($social_medias as $social_media) {
    $links[$social_media . '-url'] = [
      'name' => t('@name url', ['@name' => ucfirst($social_media)]),
    ];
    $links[$social_media . '-aria-label'] = [
      'name' => t('@name aria_label', ['@name' => ucfirst($social_media)]),
    ];
  }
  $links['order-a-sample-link'] = [
    'name' => t('Order a sample link'),
  ];
  $links['hubspot_form_region'] = [
    'name' => t('Hubspot Form Region'),
  ];
  $links['hubspot_form_portalId'] = [
    'name' => t('Hubspot Form Portal ID'),
  ];
  $links['hubspot_form_formId'] = [
    'name' => t('Hubspot Form Form ID'),
  ];
  $links['footer_region_name'] = [
    'name' => t('Footer Region Name'),
  ];
  return [
    'types' => $types,
    'tokens' => [
      'custom-link' => $links,
    ],
  ];
}

/**
 * Implements hook_tokens().
 */
function millboard_common_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata): array {
  $replacements = [];
  $config = \Drupal::service('config.factory')->get('millboard_common.settings');
  $languageManager = \Drupal::service('language_manager');
  $language = $languageManager->getCurrentLanguage()->getName();
  $language_id = $languageManager->getCurrentLanguage()->getId();
  if ($type == 'custom-link') {
    foreach ($tokens as $name => $original) {
      $replacements[$original] = match ($name) {
        'facebook-url' => $config->get('social_media_links')[$language]['facebook']['url'] ?? NULL,
        'facebook-aria-label' => $config->get('social_media_links')[$language]['facebook']['aria_label'] ?? NULL,
        'twitter-url' => $config->get('social_media_links')[$language]['twitter']['url'] ?? NULL,
        'twitter-aria-label' => $config->get('social_media_links')[$language]['twitter']['aria_label'] ?? NULL,
        'youtube-url' => $config->get('social_media_links')[$language]['youtube']['url'] ?? NULL,
        'youtube-aria-label' => $config->get('social_media_links')[$language]['youtube']['aria_label'] ?? NULL,
        'instagram-url' => $config->get('social_media_links')[$language]['instagram']['url'] ?? NULL,
        'instagram-aria-label' => $config->get('social_media_links')[$language]['instagram']['aria_label'] ?? NULL,
        'pinterest-url' => $config->get('social_media_links')[$language]['pinterest']['url'] ?? NULL,
        'pinterest-aria-label' => $config->get('social_media_links')[$language]['pinterest']['aria_label'] ?? NULL,
        'linkedin-url' => $config->get('social_media_links')[$language]['linkedin']['url'] ?? NULL,
        'linkedin-aria-label' => $config->get('social_media_links')[$language]['linkedin']['aria_label'] ?? NULL,
        'order-a-sample-link' => '/' . $language_id . $config->get('order_a_sample') ?? NULL,
        'hubspot_form_region' => $config->get('hubspot')[$language]['region'] ?? NULL,
        'hubspot_form_portalId' => $config->get('hubspot')[$language]['portalId'] ?? NULL,
        'hubspot_form_formId' => $config->get('hubspot')[$language]['formId'] ?? NULL,
        'footer_region_name' => $config->get('footer_region_name')[$language]['region_name'] ?? NULL,
        default => '',
      };
    }
  }
  return $replacements;
}
