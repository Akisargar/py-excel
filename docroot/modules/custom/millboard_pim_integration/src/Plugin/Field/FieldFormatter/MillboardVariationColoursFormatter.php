<?php

namespace Drupal\millboard_pim_integration\Plugin\Field\FieldFormatter;

use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\millboard_pim_integration\MillboardProductManager;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'millboard_variation_colours' formatter.
 *
 * @FieldFormatter(
 *   id = "millboard_variation_colours",
 *   label = @Translation("Millboard Variation Colours"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MillboardVariationColoursFormatter extends EntityReferenceFormatterBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Millboard product manager.
   *
   * @var \Drupal\millboard_pim_integration\MillboardProductManager
   */
  protected $productManager;

  /**
   * The RouteMatch service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * The product name from the URL.
   *
   * @var string
   */
  protected $productName;

  /**
   * The variation name from the URL.
   *
   * @var string
   */
  protected $variationName;

  /**
   * The sku from the URL.
   *
   * @var string
   */
  protected $sku;

  /**
   * The width from the URL.
   *
   * @var string
   */
  protected $productAlias;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\millboard_pim_integration\MillboardProductManager $product_manager
   *   The Millboard PIM product manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The RouteMatch service.
   * @param \Drupal\path_alias\AliasManagerInterface $path_alias_manager
   *   The path alias manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, MillboardProductManager $product_manager, RouteMatchInterface $route_match, AliasManagerInterface $path_alias_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->productManager = $product_manager;
    $this->routeMatch = $route_match;
    $this->pathAliasManager = $path_alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $plugin_id,
        $plugin_definition,
        $configuration['field_definition'],
        $configuration['settings'],
        $configuration['label'],
        $configuration['view_mode'],
        $configuration['third_party_settings'],
        $container->get('entity_type.manager'),
        $container->get('millboard_pim_integration.product_manager'),
        $container->get('current_route_match'),
        $container->get('path_alias.manager')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $this->productAlias = $this->routeMatch->getParameter('arg_0');

    $productInternalPath = $this->pathAliasManager->getPathByAlias('/' . $this->productAlias);
    $params = Url::fromUserInput($productInternalPath)->getRouteParameters();
    $productId = $params['commerce_product'];
    $product = Product::load($productId);

    $this->productName = strtolower($product->getTitle());
    $this->variationName = str_replace('-', ' ', $this->routeMatch->getParameter('arg_1'));
    $this->sku = $this->routeMatch->getParameter('arg_2');
    if (!($this->fieldDefinition->getTargetEntityTypeId() === 'commerce_product_variation')) {
      return $elements;
    }

    $pdp_base_path = $this->productManager->snakeToKebabCase($this->viewMode);

    $variations = $this->productManager->loadVariationsByProductName($this->productName);
    // Return the elements from view only if no other variation found.
    if (empty($variations)) {
      return $elements;
    }
    // Create a link for each variation with same width.
    foreach ($variations as $variation) {
      $sku = '';
      $width = '';
      if (!$variation->isPublished()) {
        continue;
      }
      if ($variation->hasField('sku')) {
        $sku = $variation->get('sku')->getValue()[0]['value'];
      }
      if (strpos($this->sku, '126')) {
        $width = "126";
      }
      elseif (strpos($this->sku, '176')) {
        $width = "176";
      }
      if ($width != '') {
        if (!strpos($sku, $width)) {
          continue;
        }
      }
      $variation_name = $variation->getTitle();
      // Dynamic components from user input.
      $dynamicComponents = [
        $this->productAlias,
        str_replace(' ', '-', strtolower($variation->getTranslation($langcode)->getTitle())),
        $sku,
      ];
      // Create the URL object using fromUserInput.
      $url = Url::fromUserInput("/{$pdp_base_path}/{$dynamicComponents[0]}/{$dynamicComponents[1]}/{$dynamicComponents[2]}");

      // Adding a selected class in the current page.
      if ($sku === $this->sku) {
        $url->setOptions([
          'attributes' => [
            'class' => [
              'product-colour',
              str_replace(' ', '_', strtolower($variation->getTitle())),
              'selected',
            ],
          ],
        ]);
      }
      else {
        $url->setOptions([
          'attributes' => [
            'class' => [
              'product-colour',
              str_replace(' ', '_', strtolower($variation->getTitle())),
            ],
          ],
        ]);
      }
      $element = [
        '#markup' => Link::fromTextAndUrl($variation_name, $url)->toString(),
        '#value' => $variation->getTitle(),
      ];

      array_push($elements, $element);
    }
    // // Add the current variation as text.
    $element = [
      '#markup' => '<h5 class="' . str_replace(' ', '_', $this->variationName) . '">' . ucwords($this->variationName) . '</h5>',
    ];

    usort($elements, function ($a, $b) {
      // Convert to lowercase for case-insensitive sorting.
        $valueA = strtolower($a['#value']);
        $valueB = strtolower($b['#value']);

        return strcmp($valueA, $valueB);
    });

    // Add the element to the first position.
    array_unshift($elements, $element);
    return $elements;
  }

}
