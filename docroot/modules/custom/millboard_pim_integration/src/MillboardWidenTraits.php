<?php

namespace Drupal\millboard_pim_integration;

/**
 * Helpers for Widen APIs.
 */
trait MillboardWidenTraits {

  /**
   * Widen API URL.
   *
   * @return string|null
   *   Return API URL.
   */
  protected function apiUrl() : string {
    if (!\Drupal::config('millboard_pim_integration.widen_api_settings')->get('widen_api_url')) {
      \Drupal::logger('millboard_pim_integration')->error('Missing Widen API URL.');
    }
    return \Drupal::config('millboard_pim_integration.widen_api_settings')->get('widen_api_url');
  }

  /**
   * Http client headers.
   *
   * @return array
   *   Return headers array.
   */
  protected function headers() : array {
    if (!\Drupal::config('millboard_pim_integration.widen_api_settings')->get('widen_api_token')) {
      \Drupal::logger('millboard_pim_integration')->error('Missing Widen API TOken.');
    }
    return [
      "Content-Type" => "application/json",
      "Authorization" => 'Bearer ' . \Drupal::config('millboard_pim_integration.widen_api_settings')->get('widen_api_token'),
    ];
  }

}
