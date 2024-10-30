<?php

namespace Drupal\millboard_pim_integration;

use Drupal\Component\Serialization\Json;

/**
 * Helpers for get product Widen APIs.
 */
trait ProductTraits {
  use MillboardWidenTraits;

  /**
   * Get Product.
   *
   * @param srint $product_id
   *   The widen colloective product id.
   *
   * @return array
   *   The product.
   */
  protected function getProduct(string $product_id) : array {
    $client = \Drupal::httpClient();
    $url = $this->apiUrl() . '/products/' . $product_id;
    try {
      // Make the HTTP request.
      $response = $client->request('GET', $url, ['headers' => $this->headers()]);
      // Check the HTTP status code.
      if ($response->getStatusCode() != 200) {
        \Drupal::logger('millboard_pim_integration')->error('HTTP request failed with status code: ' . $response->getStatusCode());
      }
      // Decode the JSON response.
      $result = Json::decode($response->getBody());
      return $result ? $result : [];
    }
    catch (\Exception $e) {
      \Drupal::logger('millboard_pim_integration')->error('An exception occurred: ' . $e->getMessage());
    }
  }

}
