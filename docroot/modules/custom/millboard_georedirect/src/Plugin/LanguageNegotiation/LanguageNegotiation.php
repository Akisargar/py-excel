<?php

namespace Drupal\millboard_georedirect\Plugin\LanguageNegotiation;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\language\LanguageNegotiationMethodBase;
use Drupal\millboard_georedirect\CookieManager;
use Drupal\millboard_georedirect\GeoDetection;
use Drupal\millboard_georedirect\RegionLanguageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from the user preferences.
 *
 * @LanguageNegotiation(
 *   id = \Drupal\millboard_georedirect\Plugin\LanguageNegotiation\LanguageNegotiation::METHOD_ID,
 *   weight = -4,
 *   name = @Translation("Millboard IP"),
 *   description = @Translation("Follow the IP language preference.")
 * )
 */
class LanguageNegotiation extends LanguageNegotiationMethodBase implements ContainerFactoryPluginInterface {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'millboard-ip';

  /**
   * The GeoDetection service.
   *
   * @var \Drupal\millboard_georedirect\GeoDetection
   */
  protected $geoDetection;

  /**
   * The CookieManager service.
   *
   * @var \Drupal\millboard_georedirect\CookieManager
   */
  protected $cookieManager;

  /**
   * The LanguageManager service.
   *
   * @var \Drupal\millboard_georedirect\RegionLanguageManager
   */
  protected $regionLanguageManager;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The route admin context to determine whether a route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The page cache disabling policy.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $pageCacheKillSwitch;

  /**
   * Constructs a LanguageNegotiation object.
   *
   * @param \Drupal\millboard_georedirect\GeoDetection $geoDetection
   *   The GeoDetection service.
   * @param \Drupal\millboard_georedirect\CookieManager $cookieManager
   *   The CookieManager service.
   * @param \Drupal\millboard_georedirect\RegionLanguageManager $regionLanguageManager
   *   The LanguageManager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend to be used.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The route admin context service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $pageCacheKillSwitch
   *   Killswitch policy object.
   */
  public function __construct(GeoDetection $geoDetection, CookieManager $cookieManager, RegionLanguageManager $regionLanguageManager, CacheBackendInterface $cacheBackend, AdminContext $admin_context, KillSwitch $pageCacheKillSwitch) {
    $this->geoDetection = $geoDetection;
    $this->cookieManager = $cookieManager;
    $this->regionLanguageManager = $regionLanguageManager;
    $this->cacheBackend = $cacheBackend;
    $this->adminContext = $admin_context;
    $this->pageCacheKillSwitch = $pageCacheKillSwitch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $container->get('millboard_georedirect.geo_detection'),
          $container->get('millboard_georedirect.cookie_manager'),
          $container->get('millboard_georedirect.region_language_manager'),
          $container->get('cache.default'),
          $container->get('router.admin_context'),
          $container->get('page_cache_kill_switch')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    // Set the default language code.
    $langcode = $this->regionLanguageManager::DEFAULT_LANGUAGE_CODE;

    // Get the user's region.
    $region = strtolower($this->geoDetection->getUserCountry());

    // Set the cookie for the region.
    $this->cookieManager->setCookie("region", $region);

    // Retrieve the list of country codes.
    $countryCodes = $this->regionLanguageManager->getCountryCodes();

    // Check if the language is available for the given region.
    if (isset($countryCodes[$region])) {
      // Set the cookie for the language preference.
      $this->cookieManager->setCookie("language_preference", $countryCodes[$region]);

      $langcode = $countryCodes[$region];
    }
    else {
      // Set the cookie for the default language.
      $this->cookieManager->setCookie("language_preference", $langcode);
    }

    // Trigger the page cache kill switch.
    $this->pageCacheKillSwitch->trigger();

    // Return the determined language code.
    return $langcode;
  }

}
