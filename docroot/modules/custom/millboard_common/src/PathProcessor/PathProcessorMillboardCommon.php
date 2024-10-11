<?php

namespace Drupal\millboard_common\PathProcessor;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\path_alias\AliasManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Path processor to make updates in path URLs.
 */
final class PathProcessorMillboardCommon implements OutboundPathProcessorInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected AliasManager $pathAliasManager;

  /**
   * Constructs a new PathProcessorMillboardCommon instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\path_alias\AliasManager $pathAliasManager
   *   The path alias manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, Connection $connection, LanguageManagerInterface $languageManager, AliasManager $pathAliasManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->connection = $connection;
    $this->languageManager = $languageManager;
    $this->pathAliasManager = $pathAliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL): string {

    // Alter the Product variation link.
    // Check if the entity type is commerce_product and
    // the variation ID is numeric.
    if (!$this->isValidCommerceProduct($options)) {
      return $path;
    }

    $product = $options['entity'] ?? NULL;
    $productVariation = $this->entityTypeManager->getStorage('commerce_product_variation')
      ->load((int) $options['query']['v']);

    // Return same incoming path if Product or Product variation not found.
    if (empty($product) || empty($productVariation)) {
      return $path;
    }

    // Get the product type and build a new path based on it.
    $newPath = $this->buildNewPath($product, $productVariation);
    if (empty($newPath)) {
      return $path;
    }

    // Update options and return the new path.
    $this->updateOptions($options);
    return $newPath;

  }

  /**
   * Check if entity type is commerce_product & variation ID is numeric.
   *
   * @param array $options
   *   An associative array of additional options.
   *
   * @return bool
   *   Path is valid or not.
   */
  private function isValidCommerceProduct($options): bool {
    return !empty($options) && isset($options['entity_type']) && $options['entity_type'] == 'commerce_product' && isset($options['query']['v']) && is_numeric($options['query']['v']);
  }

  /**
   * Build a new path based on the product type.
   *
   * @param \Drupal\commerce_product\Entity\Product $product
   *   The Product object.
   * @param \Drupal\commerce_product\Entity\ProductVariation $productVariation
   *   The Variation object.
   *
   * @return string
   *   New standard path or empty string.
   */
  private function buildNewPath(Product $product, ProductVariation $productVariation): string {
    if (!$productVariation->hasField('field_product_type') || $productVariation->get('field_product_type')->isEmpty()) {
      return '';
    }

    // Get the Product type.
    $productTypeQry = $this->connection->select('taxonomy_term_field_data', 't');
    $productTypeQry->fields('t', ['name']);
    $productTypeQry->condition('t.tid', $productVariation->field_product_type->target_id);
    $productType = $productTypeQry->execute()->fetchCol();

    if (empty($productType)) {
      return '';
    }
    // Form new path based on a Product type.
    $newPath = '';
    $productTypeAttr = [
      'cladding' => [
        'path' => 'composite-cladding-collections',
        'color_attr_name' => 'colour',
        'sku' => 'sku',
      ],
      'decking' => [
        'path' => 'composite-decking-collections',
        'color_attr_name' => 'colour',
        'sku' => 'sku',
      ],
    ];
    $productType = strtolower($productType[0]);

    if (!isset($productTypeAttr[$productType])) {
      return '';
    }

    $currLang = $this->languageManager->getCurrentLanguage();
    $newPath = '/' . $productTypeAttr[$productType]['path'];

    // Check if the product has translated alias.
    $translatedAlias = $this->getTranslatedAlias($product, $currLang->getId());
    $newPath .= '/' . trim($translatedAlias, '/');

    $colorField = 'attribute_' . $productTypeAttr[$productType]['color_attr_name'];
    $skuValue = $productTypeAttr[$productType]['sku'];

    if (!$productVariation->hasField($colorField) || $productVariation->get($colorField)->isEmpty() || !$productVariation->hasField($skuValue) || $productVariation->get($skuValue)->isEmpty()) {
      return '';
    }

    $newPath .= '/' . strtolower(str_replace(' ', '-', $productVariation->get($colorField)->first()->entity->label()));
    $newPath .= '/' . $productVariation->get($skuValue)->getValue()[0]['value'];
    return $newPath;
  }

  /**
   * Update options with the new path and language settings.
   *
   * @param array $options
   *   An associative array of additional options.
   */
  private function updateOptions(&$options) {
    $currLang = $this->languageManager->getCurrentLanguage();
    if (isset($options['prefix'])) {
      $options['prefix'] = $currLang->getId() . '/';
    }
    if (isset($options['language'])) {
      $options['language'] = $currLang;
    }

    // Remove the variation id query param.
    unset($options['query']['v']);
  }

  /**
   * Returns correct alias based on language.
   *
   * @param \Drupal\commerce_product\Entity\Product $product
   *   The Product object.
   * @param string $langId
   *   Language id from a language object.
   *
   * @return string
   *   Translated path alias.
   */
  private function getTranslatedAlias(Product $product, string $langId): string {
    return $product->hasTranslation($langId)
      ? $this->pathAliasManager->getAliasByPath('/product/' . $product->id(), $langId)
      : $this->pathAliasManager->getAliasByPath('/product/' . $product->id(), $this->languageManager->getDefaultLanguage()->getId());
  }

}
