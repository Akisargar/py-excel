<?php

namespace Drupal\millboard_pim_integration\EventSubscriber;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects specific URLs in Millboard PIM Integration module.
 */
class MillboardPimIntegrationRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * Constructs a new MillboardPimIntegrationRedirectSubscriber object.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $pathAliasManager
   *   The path alias manager.
   */
  public function __construct(AliasManagerInterface $pathAliasManager) {
    $this->pathAliasManager = $pathAliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 100];
    return $events;
  }

  /**
   * Redirects specific URLs to new paths.
   */
  public function onKernelRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();
    if (preg_match('|^/([^/]+)/composite-cladding-collections/([^/]+)/([^/]+)/([^/]+)$|', $path, $matches)) {
      return;
    }
    if (preg_match('|^/([^/]+)/composite-decking-collections/([^/]+)/([^/]+)/([^/]+)$|', $path, $matches)) {
      return;
    }

    // Check if the path contains "composite-decking-collections"
    // or "composite-cladding-collections".
    if (strpos($path, 'composite-decking-collections') !== FALSE || strpos($path,
                'composite-cladding-collections') !== FALSE) {
      // Check if the path matches the specified patterns.
      if (preg_match('|^/([^/]+)/composite-cladding-collections/([^/]+)/([^/]+)$|', $path, $matches)) {
        $newPath = "/{$matches[1]}/composite-cladding-collections/{$matches[2]}/{$matches[3]}/181mm";
        $this->redirect($event, $newPath, $matches[2]);
      }
      elseif (preg_match('|^/([^/]+)/composite-decking-collections/([^/]+)/([^/]+)$|', $path, $matches)) {
        $newPath = "/{$matches[1]}/composite-decking-collections/{$matches[2]}/{$matches[3]}/176mm";
        $this->redirect($event, $newPath, $matches[2]);
      }
    }
  }

  /**
   * Performs the redirect.
   */
  private function redirect(RequestEvent $event, $newPath, $productAlias) {
    // Load the product entity using the alias.
    $commerceProduct = $this->pathAliasManager->getPathByAlias("/$productAlias");

    // Check if the product exists and is of type "commerce_product".
    if ($commerceProduct && preg_match('|^/product/(\d+)$|', $commerceProduct, $productMatches)) {
      // Get the commerce product entity.
      $productId = $productMatches[1];
      $product = Product::load($productId);

      // Check if the loaded entity is a valid commerce product.
      if ($product instanceof ProductInterface) {
        // Generate the URL using Drupal's Url class.
        $url = Url::fromUserInput($newPath, ['absolute' => TRUE])->toString();

        // Use TrustedRedirectResponse for redirection.
        $response = new RedirectResponse($url, 301);
        $event->setResponse($response);
      }
    }
  }

}
