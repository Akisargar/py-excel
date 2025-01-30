<?php

namespace Drupal\millboard_georedirect\EventSubscriber;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to disable caching for specific URLs.
 */
class CacheSubscriber implements EventSubscriberInterface, ContainerInjectionInterface {

  /**
   * The page cache kill switch service.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $pageCacheKillSwitch;

  /**
   * CacheSubscriber constructor.
   *
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $pageCacheKillSwitch
   *   The page cache kill switch service.
   */
  public function __construct(KillSwitch $pageCacheKillSwitch) {
    $this->pageCacheKillSwitch = $pageCacheKillSwitch;
  }

  /**
   * {@inheritdoc}
   */
  public function onKernelRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $current_path = $request->getPathInfo();

    // List of paths to disable caching for.
    $disable_cache_paths = [
      '/',
      '/en-gb',
      '/en-us',
      '/en-ie',
      '/en-au',
    ];

    // Check if the current path is in the list of paths to disable caching.
    if (in_array($current_path, $disable_cache_paths)) {
      // Disable caching for the specified URLs.
      $response = new Response();
      $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
      $response->headers->set('Pragma', 'no-cache');
      $response->headers->set('Expires', '0');
      $this->pageCacheKillSwitch->trigger();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Define the event and priority for the caching control.
    return [KernelEvents::REQUEST => 'onKernelRequest'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Create the instance of the class with the required service injected.
    return new static(
          $container->get('page_cache_kill_switch')
      );
  }

}
