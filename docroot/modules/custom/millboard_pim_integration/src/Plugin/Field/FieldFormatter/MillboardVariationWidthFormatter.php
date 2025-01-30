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
 * Plugin implementation of the 'millboard_variation_width' formatter.
 *
 * @FieldFormatter(
 *   id = "millboard_variation_width",
 *   label = @Translation("Millboard Variation Width"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MillboardVariationWidthFormatter extends EntityReferenceFormatterBase {

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
    // Create a link for each variation with the same width.
    foreach ($variations as $variation) {
      $variation = $variation->getTranslation($langcode);
      // Allow only variants with the same name.
      if (strcasecmp($variation->getTitle(), $this->variationName)) {
        continue;
      }
      $sku = '';
      if ($variation->hasField('sku')) {
        $sku = $variation->get('sku')->getValue()[0]['value'];
      }
      $attribute_id = $variation->get($this->fieldDefinition->getName())->getValue()[0]['target_id'];
      if ($this->fieldDefinition->getName() === 'attribute_installed_width') {
        $width_inches_attribute_id = $variation->get('attribute_installed_width_inches')->getValue()[0]['target_id'];
      }
      else {
        $width_inches_attribute_id = $variation->get('attribute_width_inches')->getValue()[0]['target_id'];
      }
      if (empty($width_inches_attribute_id)) {
        $width_inches_attribute_id = "141";
      }
      if (empty($attribute_id)) {
        $attribute_id = '1';
      }
      $productWidthInchesValue = $this->entityTypeManager->getStorage('commerce_product_attribute_value')
        ->loadByProperties(['attribute_value_id' => $width_inches_attribute_id]);
      // Load the product attribute value by properties.
      $productAttributeValues = $this->entityTypeManager
        ->getStorage('commerce_product_attribute_value')
        ->loadByProperties(['attribute_value_id' => $attribute_id]);
      $url = $width = $width_inches = '';
      // Extract the first item from the loaded values.
      if (!empty($productAttributeValues)) {
        $productAttributeValue = reset($productAttributeValues);
        // Get the width display name, E.g.: 176mm, 126mm.
        $width = $productAttributeValue->getName();
        $productWidthInchesValue = reset($productWidthInchesValue);
        // Get the width inches display name, E.g.: 7", 5".
        $width_inches = $productWidthInchesValue->getName();
        // Dynamic components from user input.
        $dynamicComponents = [
          $this->productAlias,
          str_replace(' ', '-', strtolower($variation->getTitle())),
          $sku,
        ];
        // Create the URL object using fromUserInput.
        $url = Url::fromUserInput("/{$pdp_base_path}/{$dynamicComponents[0]}/{$dynamicComponents[1]}/{$dynamicComponents[2]}");
        $url->setOptions([
          'attributes' => [
            'class' => ['coh-style-primary-button', 'width'],
          ],
        ]);
        // Adding a selected class in the current page.
        if (!strcasecmp($sku, $this->sku)) {
          $url->setOptions([
            'attributes' => [
              'class' => ['coh-style-primary-button', 'width', 'selected'],
            ],
          ]);
        }
      }
      if ($langcode == 'en-us') {
        $width = $width_inches;
      }
      $element = [
        '#markup' => Link::fromTextAndUrl($width, $url)->toString(),
      ];
      $elements[] = $element;
    }
    return $elements;
  }

}
