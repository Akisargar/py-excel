<?php

namespace Drupal\millboard_pim_integration\EventSubscriber;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects specific URLs in Millboard PIM Integration module.
 */
class MillboardProductRedirectSubscriber implements EventSubscriberInterface {


  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAliasManager;


  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new MillboardProductRedirectSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\path_alias\AliasManagerInterface $pathAliasManager
   *   The path alias manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AliasManagerInterface $pathAliasManager, LanguageManagerInterface $languageManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->pathAliasManager = $pathAliasManager;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 100];
    return $events;
  }

  /**
   * Redirects specific URLs to new paths.
   */
  public function onKernelRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();
    // Extract language code and product alias from the path.
    if (preg_match('|^/([^/]+)/([^/]+)$|', $path, $matches)) {
      $languageCode = $matches[1];
      $productAlias = $matches[2];

      // Load the language entity using the language manager.
      $language = $this->languageManager->getLanguage($languageCode);

      // Check if the language is valid.
      if (!is_null($language) && $language->getId()) {
        // Load the product entity using the alias.
        $commerceProduct = $this->pathAliasManager->getPathByAlias("/$productAlias");

        // Check if the product exists and is of type "commerce_product".
        if ($commerceProduct && preg_match('|^/product/(\d+)$|', $commerceProduct, $productMatches)) {
          // Get the commerce product entity.
          $productId = $productMatches[1];
          $product = Product::load($productId);

          // Check if the loaded entity is a valid commerce product.
          if ($product instanceof ProductInterface) {
            // The product is valid, and you can perform actions.
            // Check if the product has the "field_product_type" field.
            if ($product && $product->hasField('field_product_type')) {
              $productType = $product->get('field_product_type');
              $productTypeId = $productType->getValue();

              // Load the taxonomy term using the EntityTypeManager.
              $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($productTypeId[0]["target_id"]);

              // Check if the term belongs to the specified vocabulary.
              if ($term && $term->bundle() == "product_type") {
                // The term was loaded successfully.
                $term_name = strtolower($term->getName());
                // Check if the product type is "decking" or "cladding".
                if ($term_name == 'decking') {
                  $newPath = '/' . $language->getId() . '/composite-decking-collections';
                  $this->redirect($event, $newPath);
                }
                elseif ($term_name == 'cladding') {
                  $newPath = '/' . $language->getId() . '/composite-cladding-collections';
                  $this->redirect($event, $newPath);
                }
              }
            }
          }
        }
      }
    }

    // This redirection for the regional product pages.
    if (
      preg_match('/^\/([a-z]{2}-[a-z]{2})\/composite-siding-collections\/([^\/]+)\/([^\/]+)\/([A-Z0-9]+)$/', $path, $matches)
    ) {
      $languageCode = $this->languageManager->getCurrentLanguage()->getId();

      switch ($languageCode) {
        case "en-gb":
        case "en-ie":
          $productVariation = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
            'sku' => $matches[4],
          ]);
          $attribute_color_id = reset($productVariation)->get('attribute_colour')->getValue()[0]['target_id'];
          $color_name = $this->entityTypeManager->getStorage('commerce_product_attribute_value')->load($attribute_color_id)->getName();
          $color_url_component = strtolower(str_replace(' ', '-', $color_name));
          $newPath = '/' . $languageCode . '/composite-cladding-collections/' . $matches[2] . '/' . $color_url_component . '/' . $matches[4];
          $this->redirect($event, $newPath);
          break;

        case 'fr-fr':
          $productVariation = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
            'sku' => $matches[4],
          ]);
          $attribute_fr_color_id = reset($productVariation)->getTranslation('fr-fr')->get('attribute_color_fr')->getValue()[0]['target_id'];
          $color_fr_name = $this->entityTypeManager->getStorage('commerce_product_attribute_value')->load($attribute_fr_color_id)->getName();
          $color_url_component = strtolower(str_replace(' ', '-', $color_fr_name));
          $newPath = '/' . $languageCode . '/composite-bardage-collections/' . $matches[2] . '/' . $color_url_component . '/' . $matches[4];
          $this->redirect($event, $newPath);
          break;
      }
    }
    if (preg_match('/^\/([a-z]{2}-[a-z]{2})\/composite-bardage-collections\/([^\/]+)\/([^\/]+)\/([A-Z0-9]+)$/', $path, $matches)) {
      $languageCode = $this->languageManager->getCurrentLanguage()->getId();
      $productVariation = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
        'sku' => $matches[4],
      ]);
      $attribute_color_id = reset($productVariation)->get('attribute_colour')->getValue()[0]['target_id'];
      $color_name = $this->entityTypeManager->getStorage('commerce_product_attribute_value')->load($attribute_color_id)->getName();
      $color_url_component = strtolower(str_replace(' ', '-', $color_name));
      switch ($languageCode) {
        case "en-gb":
        case "en-ie":
          $newPath = '/' . $languageCode . '/composite-cladding-collections/' . $matches[2] . '/' . $color_url_component . '/' . $matches[4];
          $this->redirect($event, $newPath);
          break;

        case "en-us":
          $newPath = '/' . $languageCode . '/composite-siding-collections/' . $matches[2] . '/' . $color_url_component . '/' . $matches[4];
          $this->redirect($event, $newPath);
          break;
      }
    }
    if (preg_match('/^\/([a-z]{2}-[a-z]{2})\/composite-terrasse-collections\/([^\/]+)\/([^\/]+)\/([A-Z0-9]+)$/', $path, $matches)) {
      $languageCode = $this->languageManager->getCurrentLanguage()->getId();
      switch ($languageCode) {
        case "en-gb":
        case "en-ie":
        case "en-us":
          $productVariation = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
            'sku' => $matches[4],
          ]);
          $attribute_color_id = reset($productVariation)->get('attribute_colour')->getValue()[0]['target_id'];
          $color_name = $this->entityTypeManager->getStorage('commerce_product_attribute_value')->load($attribute_color_id)->getName();
          $color_url_component = strtolower(str_replace(' ', '-', $color_name));
          $newPath = '/' . $languageCode . '/composite-decking-collections/' . $matches[2] . '/' . $color_url_component . '/' . $matches[4];
          $this->redirect($event, $newPath);
          break;
      }
    }
    if (preg_match('/^\/([a-z]{2}-[a-z]{2})\/composite-cladding-collections\/([^\/]+)\/([^\/]+)\/([A-Z0-9]+)$/', $path, $matches)) {
      $languageCode = $this->languageManager->getCurrentLanguage()->getId();
      if ($languageCode == "en-us") {
        $newPath = '/' . $languageCode . '/composite-siding-collections/' . $matches[2] . '/' . $matches[3] . '/' . $matches[4];
        $this->redirect($event, $newPath);
      }
      switch ($languageCode) {
        case 'en-us':
          $newPath = '/' . $languageCode . '/composite-siding-collections/' . $matches[2] . '/' . $matches[3] . '/' . $matches[4];
          $this->redirect($event, $newPath);
          break;

        case 'fr-fr':
          $productVariation = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
            'sku' => $matches[4],
          ]);
          $attribute_fr_color_id = reset($productVariation)->getTranslation('fr-fr')->get('attribute_color_fr')->getValue()[0]['target_id'];
          $color_fr_name = $this->entityTypeManager->getStorage('commerce_product_attribute_value')->load($attribute_fr_color_id)->getName();
          $color_url_component = strtolower(str_replace(' ', '-', $color_fr_name));
          $newPath = '/' . $languageCode . '/composite-bardage-collections/' . $matches[2] . '/' . $color_url_component . '/' . $matches[4];
          $this->redirect($event, $newPath);
          break;
      }
    }
    if (preg_match('/^\/([a-z]{2}-[a-z]{2})\/composite-decking-collections\/([^\/]+)\/([^\/]+)\/([A-Z0-9]+)$/', $path, $matches)) {
      $languageCode = $this->languageManager->getCurrentLanguage()->getId();
      if ($languageCode == 'fr-fr') {
        $productVariation = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
          'sku' => $matches[4],
        ]);
        $attribute_fr_color_id = reset($productVariation)->getTranslation('fr-fr')->get('attribute_color_fr')->getValue()[0]['target_id'];
        $color_fr_name = $this->entityTypeManager->getStorage('commerce_product_attribute_value')->load($attribute_fr_color_id)->getName();
        $color_url_component = strtolower(str_replace(' ', '-', $color_fr_name));
        $newPath = '/' . $languageCode . '/composite-terrasse-collections/' . $matches[2] . '/' . $color_url_component . '/' . $matches[4];
        $this->redirect($event, $newPath);
      }
    }

    // This redirection is for the old product urls.
    if (
      preg_match('/^\/([a-z]{2}-[a-z]{2})\/composite-cladding-collections\/([^\/]+)\/([^\/]+)\/(\d+mm)$/', $path, $matches) ||
      preg_match('/^\/([a-z]{2}-[a-z]{2})\/composite-decking-collections\/([^\/]+)\/([^\/]+)\/(\d+mm)$/', $path, $matches)
    ) {
      $decking = FALSE;
      $cladding = FALSE;
      $productWidth = $this->entityTypeManager->getStorage('commerce_product_attribute_value')
        ->loadByProperties(['name' => $matches[4]]);
      $productWidth = reset($productWidth);
      $width_id = $productWidth->id();
      $productVariationTitle = ucwords(str_replace("-", " ", $matches[3]));
      if (str_contains($path, 'composite-decking-collections')) {
        $productVariations = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
          'title' => $productVariationTitle,
          'attribute_width' => $width_id,
        ]);
        $decking = TRUE;
      }
      elseif (str_contains($path, 'composite-cladding-collections')) {
        $productVariations = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
          'title' => $productVariationTitle,
          'attribute_installed_width' => $width_id,
        ]);
        $cladding = TRUE;
      }
      if (!empty($productVariations)) {
        foreach ($productVariations as $productVariation) {
          $product_title = $productVariation->getProduct()->title->getValue()[0]['value'];
          $sku = $productVariation->get('sku')->getValue()[0]['value'];
          $product_name = explode("-", $matches[2]);
          if (str_contains(strtolower($product_title), $product_name[0])) {
            $languageCode = $this->languageManager->getCurrentLanguage()->getId();
            if ($cladding) {
              if ($languageCode == "en-us") {
                $newPath = '/' . $languageCode . '/composite-siding-collections/' . $matches[2] . '/' . $matches[3] . '/' . $sku;
              }
              else {
                $newPath = '/' . $languageCode . '/composite-cladding-collections/' . $matches[2] . '/' . $matches[3] . '/' . $sku;
              }
            }
            elseif ($decking) {
              $newPath = '/' . $languageCode . '/composite-decking-collections/' . $matches[2] . '/' . $matches[3] . '/' . $sku;
            }
            $this->redirect($event, $newPath);
          }
        }
      }
    }
  }

  /**
   * Performs the redirect.
   */
  private function redirect(RequestEvent $event, $newPath) {
    // Generate the URL using Drupal's Url class.
    $url = Url::fromUserInput($newPath, ['absolute' => TRUE])->toString();

    // Use TrustedRedirectResponse for redirection.
    $response = new RedirectResponse($url, 301);
    $event->setResponse($response);
  }

}
