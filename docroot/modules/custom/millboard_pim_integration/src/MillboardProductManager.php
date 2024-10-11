<?php

namespace Drupal\millboard_pim_integration;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines a service for managing products in the Millboard PIM integration.
 */
class MillboardProductManager {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a MillboardPIMProductManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Load variations for a given product name.
   *
   * @param string $productName
   *   The name of the product.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface[]
   *   An array of product variations.
   */
  public function loadVariationsByProductName($productName) {
    // Load the product by name.
    $product = $this->loadProductByName($productName);

    // Check if the product is found.
    if ($product) {
      // Get the IDs of all variations for the product.
      $variationIds = $product->getVariationIds();

      // Load all variations for the product.
      $variations = $this->entityTypeManager->getStorage('commerce_product_variation')->loadMultiple($variationIds);

      return $variations;
    }

    return [];
  }

  /**
   * Load a product by name.
   *
   * @param string $productName
   *   The name of the product.
   *
   * @return \Drupal\commerce_product\Entity\ProductInterface|null
   *   The loaded product entity or NULL if not found.
   */
  protected function loadProductByName($productName) {
    $query = $this->entityTypeManager->getStorage('commerce_product')
      ->getQuery()
      ->condition('title', $productName)
      ->range(0, 1)
      ->accessCheck(FALSE);

    $productIds = $query->execute();

    if (!empty($productIds)) {
      $productId = reset($productIds);
      return $this->entityTypeManager->getStorage('commerce_product')->load($productId);
    }

    return NULL;
  }

  /**
   * Convert a string from snake_case to kebab-case.
   *
   * @param string $input
   *   The input string in snake_case.
   *
   * @return string
   *   The converted string in kebab-case.
   */
  public function snakeToKebabCase($input) {
    // Replace underscores (_) with hyphens (-)
    $kebabCase = str_replace('_', '-', $input);
    // Convert to lowercase.
    $kebabCase = strtolower($kebabCase);
    return $kebabCase;
  }

}
