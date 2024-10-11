<?php

namespace Drupal\millboard_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Order Sample Filters' Block.
 *
 * @Block(
 *   id = "order_sample_filters_block",
 *   admin_label = @Translation("Order Sample Filters Block"),
 *   category = @Translation("Order Sample Filters Block"),
 * )
 */
class OrderSampleFiltersBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected FormBuilder $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('form_builder')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = $this->formBuilder->getForm('Drupal\millboard_common\Form\OrderSampleFilters');
    return $build;
  }

}
