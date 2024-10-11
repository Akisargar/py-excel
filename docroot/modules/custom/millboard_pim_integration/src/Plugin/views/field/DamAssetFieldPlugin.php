<?php

namespace Drupal\millboard_pim_integration\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A plugin used as get DAM asset thumbnail URL.
 *
 * @ViewsField("dam_asset_field_plugin")
 */
class DamAssetFieldPlugin extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a new MyBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function query() {}

  /**
   * Renders the field.
   *
   * @param \Drupal\views\ResultRow $values
   *   The values retrieved from a single row of a view's query result.
   *
   * @return string|NUll
   *   The DAM asset thumbnail url
   */
  public function render(ResultRow $values): ?string {
    if (empty($values)) {
      return NULL;
    }
    $variation_id = $values->commerce_product_variation_field_data_commerce_product__vari_2;
    $product_variation = $this->entityTypeManager->getStorage('commerce_product_variation')->load($variation_id);
    // Check if the product variation has a product images associated with it.
    if (is_object($product_variation)) {
      if ($product_variation->get('field_swatch')->target_id) {
        $media = $this->entityTypeManager->getStorage('media')->load($product_variation->get('field_swatch')->target_id);
        $source = $media->getSource();
        if (isset($source) && 'acquia_dam_asset:image' == $source->getPluginId()) {
          if (isset($source->getMetadata($media, 'thumbnails')['125px']['url'])) {
            return $source->getMetadata($media, 'thumbnails')['125px']['url'];
          }
        }
      }
    }
    return NULL;
  }

}
