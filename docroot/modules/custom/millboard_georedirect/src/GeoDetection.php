<?php

namespace Drupal\millboard_georedirect;

use Drupal\Core\Cache\CacheBackendInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides geolocation services for determining the user's country.
 */
class GeoDetection {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  /**
   * Cache backend instance.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The language manager.
   *
   * @var \Drupal\millboard_georedirect\RegionLanguageManager
   */
  protected $regionLanguageManager;

  /**
   * Constructs a GeoDetection object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The Symfony RequestStack service for retrieving the current request.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend service for caching geolocation data.
   * @param \Drupal\millboard_georedirect\RegionLanguageManager $regionLanguageManager
   *   The language manager service.
   */
  public function __construct(RequestStack $requestStack, CacheBackendInterface $cacheBackend, RegionLanguageManager $regionLanguageManager) {
    $this->requestStack = $requestStack;
    $this->cacheBackend = $cacheBackend;
    $this->regionLanguageManager = $regionLanguageManager;
  }

  /**
   * Get the user's country based on their geolocation.
   *
   * @return string
   *   The two-letter country code (e.g., 'US') of the user's country.
   */
  public function getUserCountry() {
    $request = $this->requestStack->getCurrentRequest();

    $country_code = NULL;

    // Check if the X-Geo-Country header is present.
    if ($request->headers->has('X-Geo-Country')) {
      $country_code = $request->headers->get('X-Geo-Country');
      return $country_code;
    }

    // Set the default value if the country code is empty or not available.
    if (empty($country_code)) {
      $country_code = $this->regionLanguageManager::DEFAULT_COUNTRY_CODE;
    }

    return $country_code;
  }

}
