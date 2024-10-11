<?php

namespace Drupal\millboard_galleries\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\AliasManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Gallery popup.
 */
class GalleryPopup extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected AliasManager $pathAliasManager;

  /**
   * Constructs a new ContentEntityCloneBase.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\path_alias\AliasManager $pathAliasManager
   *   The path alias manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AliasManager $pathAliasManager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->pathAliasManager = $pathAliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('path_alias.manager')
    );
  }

  /**
   * Provide the content for the controller.
   *
   * @param mixed|null $project_target_id
   *   The view exposed filter value for the type of project.
   * @param mixed|null $collection
   *   The view exposed filter value for the collection.
   * @param mixed|null $colour
   *   The color in the view exposed filter.
   * @param mixed|null $location_target_id
   *   The location in the view exposed filter.
   * @param mixed|null $sort_by
   *   The sorting field in the view exposed filter.
   * @param mixed|null $sort_order
   *   The sorting order in the view exposed filter.
   * @param mixed|null $media_id
   *   The current media ID where the popup will be loaded.
   *
   * @return array
   *   The render array for the template.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function millboardGalleryPopup(
    mixed $project_target_id = NULL,
    mixed $collection = NULL,
    mixed $colour = NULL,
    mixed $location_target_id = NULL,
    mixed $sort_by = NULL,
    mixed $sort_order = NULL,
    mixed $media_id = NULL,
  ): array {
    $view = $this->entityTypeManager->getStorage('view')
      ->load('millboard_image_gallery')
      ->getExecutable();
    $view->initDisplay();
    $view->setDisplay('gallery_block_full');
    $view->setExposedInput($this->configureExposedFilter(
      $project_target_id,
      $collection,
      $colour,
      $location_target_id,
      $sort_by,
      $sort_order
    ));
    $view->execute();
    $results = $view->result;
    $image_url = [];
    foreach ($results as $result) {
      $is_active = FALSE;
      $source = $result->_relationship_entities['field_gallery_images']->getSource();
      $url = $source->getMetadata($result->_relationship_entities['field_gallery_images'], 'embeds')['OriginalSizewithDownload']['url'];
      if ($media_id === $result->media_field_data_node__field_gallery_images_mid) {
        $is_active = TRUE;
      }
      $image_url[] = [
        'url' => $url,
        'is_active' => $is_active,
      ];
    }
    return [
      '#theme' => 'gallery_popup',
      '#image_urls' => $image_url,
      '#classes' => 'millboard-gallery-popup',
    ];
  }

  /**
   * Get the content of the inspiration Gallery popup.
   *
   * @param int $content_id
   *   The content id of the entity type.
   * @param int $media_id
   *   The media id of the selected image.
   * @param string $language_id
   *   The language id.
   *
   * @return array
   *   The render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function inspirationGalleryPopupView(
    int $content_id,
    int $media_id,
    string $language_id,
  ): array {
    /** @var \Drupal\commerce_product\Entity\ProductVariation $content */
    $content = $this->entityTypeManager->getStorage('commerce_product_variation')->load($content_id);
    $image_url = [];
    $image_thumbnail = $product_type = $product_alias = $attribute_colour = $attribute_width = $variation_url = $product_title = $width = $attribute_width_inches = $attribute_colour_fr = '';
    $view = $this->entityTypeManager->getStorage('view')
      ->load('inspiration_gallery')
      ->getExecutable();
    $view->initDisplay();
    $view->setDisplay('inspiration_image_block_all');
    $view->setArguments([$content_id]);
    $view->execute();
    $results = $view->result;
    foreach ($results as $result) {
      $is_active = FALSE;
      $source = $result->_relationship_entities['field_pdp_inspiration']->getSource();
      $url = $source->getMetadata($result->_relationship_entities['field_pdp_inspiration'], 'embeds')['OriginalSizewithDownload']['url'];
      if ($media_id === $result->media_field_data_commerce_product_variation__field_pdp_inspi_1) {
        $is_active = TRUE;
      }
      $image_url[] = [
        'url' => $url,
        'is_active' => $is_active,
      ];
    }
    $is_commerce_product = FALSE;
    // Checking if it is a product variation entity then loading the content.
    if ($content->getEntityType()->id() == 'commerce_product_variation') {
      $is_commerce_product = TRUE;
      // Getting the Variation Product type.
      if ($content->hasField('field_product_type')) {
        $product_type =
            isset($content->field_product_type->referencedEntities()[0]) ?
              $content->field_product_type->referencedEntities()[0]->getTranslation($language_id)->getName() :
              NULL;
      }
      // Getting the product title for the variation.
      if ($content->hasField('product_id')) {
        $product_id = isset($content->product_id->referencedEntities()[0]) ?
          $content->product_id->referencedEntities()[0]->id() :
            NULL;
        $product_title = isset($content->product_id->referencedEntities()[0]) ?
          $content->product_id->referencedEntities()[0]->getTitle() :
          NULL;
        $product_alias = $this->pathAliasManager->getAliasByPath('/product/' . $product_id);
      }
      // Getting attribute color.
      if ($content->getAttributeValue('attribute_colour')) {
        // Add the attribute_colour to the variables.
        if ($language_id === 'fr-fr') {
          $attribute_colour = $content->getAttributeValue('attribute_color_fr')->getName();
        }
        else {
          $attribute_colour = $content->getAttributeValue('attribute_colour')->getName();
        }
      }
      // Getting the thumbnail image for the variation.
      if ($content->hasField('field_swatch')) {
        $image_thumbnail =
          isset($content->field_swatch->referencedEntities()[0]) ?
            $content->field_swatch->referencedEntities()[0]->getSource()
              ->getMetadata($content->field_swatch->referencedEntities()[0], 'thumbnails')['125px']['url'] :
            NULL;
      }
      // Getting the width of the product variation.
      if ($content->getAttributeValue('attribute_width')) {
        // Add the attribute_width to the variables.
        $attribute_width = $content->getAttributeValue('attribute_width')->getName();
        $attribute_width_inches = $content->getAttributeValue('attribute_width_inches')->getName();
      }
      if ($product_type === 'Cladding') {
        $attribute_width = $content->getAttributeValue('attribute_installed_width')->getName();
        $attribute_width_inches = $content->getAttributeValue('attribute_installed_width_inches')->getName();
      }
      $sku = '';
      if ($content->hasField('sku')) {
        $sku = $content->get('sku')->getValue()[0]['value'];
      }
      $variation_url = '/' . $language_id . '/composite-decking-collections' . $product_alias . '/' . str_replace(' ', '-', strtolower($content->getAttributeValue('attribute_colour')->getName())) . '/' . $sku;
    }
    $width = $language_id == 'en-us' ? $attribute_width_inches : $attribute_width;
    return [
      '#theme' => 'gallery_popup',
      '#image_urls' => $image_url,
      '#classes' => 'inspiration-gallery-popup',
      '#variation_product_type' => $product_type,
      '#product_title' => $product_title,
      '#attribute_colour' => $attribute_colour,
      '#variation_url' => $variation_url,
      '#variation_image_thumbnail' => $image_thumbnail,
      '#attribute_width' => $attribute_width,
      '#attribute_width_inches' => $attribute_width_inches,
      '#is_commerce_product' => $is_commerce_product,
      '#language' => $language_id,
      '#width' => $width,
      '#cache' => [
        'contexts' => [
          'languages',
          'url.path',
        ],
        'tags' => $content->getCacheTags(),
      ],
    ];
  }

  /**
   * Get the content of the inspiration Gallery popup.
   *
   * @param mixed $entity_type
   *   The type of the entity from where the data will be fetched.
   * @param mixed $field_name
   *   The field name from where the image content will be fetched.
   * @param mixed $content_id
   *   The content id of the entity type.
   * @param mixed $media_id
   *   The media id for which the popup will be opened.
   *
   * @return array
   *   The render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function inspirationGalleryPopupField(
    mixed $entity_type,
    mixed $field_name,
    mixed $content_id,
    mixed $media_id,
  ): array {
    /** @var \Drupal\commerce_product\Entity\ProductVariation $content */
    $content = $this->entityTypeManager->getStorage($entity_type)->load($content_id);
    $popup_contents = [];
    $image_url = [];
    if ($content->hasField($field_name)) {
      $popup_contents = $content->$field_name->referencedEntities();
    }
    foreach ($popup_contents as $popup_content) {
      $is_active = FALSE;
      $source = $popup_content->getSource();
      $url = $source->getMetadata($popup_content, 'embeds');
      if (isset($url['OriginalSizewithDownload'])) {
        $url = $url['OriginalSizewithDownload']['url'];
      }
      elseif (isset($url['video_player_with_download'])) {
        $url = $url['video_poster']['url'];
      }
      if ($media_id === $popup_content->id()) {
        $is_active = TRUE;
      }
      $image_url[] = [
        'url' => $url,
        'is_active' => $is_active,
      ];
    }
    return [
      '#theme' => 'gallery_popup',
      '#image_urls' => $image_url,
      '#classes' => 'inspiration-gallery-popup',
      '#cache' => [
        'contexts' => [
          'languages',
          'url.path',
        ],
        'tags' => $content->getCacheTags(),
      ],
    ];
  }

  /**
   * Get the configured value of the exposed filter.
   *
   * @param mixed|null $project_target_id
   *   The view exposed filter value for the type of project.
   * @param mixed|null $collection
   *   The view exposed filter value for the collection.
   * @param mixed|null $colour
   *   The color in the view exposed filter.
   * @param mixed|null $location_target_id
   *   The location in the view exposed filter.
   * @param mixed|null $sort_by
   *   The sorting field in the view exposed filter.
   * @param mixed|null $sort_order
   *   The sorting order in the view exposed filter.
   *
   * @return array
   *   The exposed filter array.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  private function configureExposedFilter(
    mixed $project_target_id = NULL,
    mixed $collection = NULL,
    mixed $colour = NULL,
    mixed $location_target_id = NULL,
    mixed $sort_by = 'field_publish_date_value',
    mixed $sort_order = 'DESC',
  ): array {
    $exposed_filter = [];
    if (!is_null($project_target_id) && $project_target_id !== 'no_data') {
      $exposed_filter['field_type_of_project_target_id'] = $project_target_id;
    }
    if (!is_null($collection) && $collection !== 'no_data') {
      $exposed_filter['collection'] = $collection;
    }
    if (!is_null($colour) && $colour !== 'no_data') {
      $exposed_filter['colour'] = $colour;
    }
    if (!is_null($location_target_id) && $location_target_id !== 'no_data') {
      $exposed_filter['field_location_target_id'] = $location_target_id;
    }
    if (!is_null($sort_by) && $sort_by !== 'no_data') {
      $exposed_filter['sort_by'] = $sort_by;
    }
    if (!is_null($sort_order) && $sort_order !== 'no_data') {
      $exposed_filter['sort_order'] = $sort_order;
    }
    return $exposed_filter;
  }

}
