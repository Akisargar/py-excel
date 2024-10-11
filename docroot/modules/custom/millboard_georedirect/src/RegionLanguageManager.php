<?php

namespace Drupal\millboard_georedirect;

use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manages languages and their related data.
 */
class RegionLanguageManager {

  // Define constants for default country and language codes.
  const DEFAULT_COUNTRY_CODE = 'GB';
  const DEFAULT_LANGUAGE_CODE = 'en-gb';

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new LanguageManager instance.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The Symfony RequestStack service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The Language Manager service for Drupal.
   */
  public function __construct(RequestStack $requestStack, LanguageManagerInterface $languageManager) {
    $this->requestStack = $requestStack;
    // Inject the Language Manager service.
    $this->languageManager = $languageManager;
  }

  /**
   * Get an array of supported regions.
   *
   * @return array
   *   An array of supported regions in lowercase.
   */
  public function getSupportedRegions() {
    // Use the injected Language Manager service.
    $languages = $this->languageManager->getLanguages();

    $supportedRegions = [];

    if (empty($languages)) {
      // Return an empty array if no languages are found.
      return $supportedRegions;
    }

    foreach ($languages as $language) {
      $languageCode = $language->getId();

      // Extract the region from the language code,
      // assuming it follows the pattern "{langcode}-{region}".
      $matches = [];
      if (preg_match('/^(\w+)-(\w+)$/', $languageCode, $matches)) {
        $region = $matches[2];
        $supportedRegions[] = $region;
      }
    }

    // Convert the supported regions to lowercase.
    $supportedRegions = array_map('strtolower', $supportedRegions);

    return $supportedRegions;
  }

  /**
   * Get an array of country codes associated with regions.
   *
   * @return array
   *   An array where region codes are keys and language codes are values.
   */
  public function getCountryCodes() {
    // Use the injected Language Manager service.
    $languages = $this->languageManager->getLanguages();

    $countryCodes = [];

    if (empty($languages)) {
      // Return an empty array if no languages are found.
      return $countryCodes;
    }

    foreach ($languages as $language) {
      $languageCode = $language->getId();

      // Extract the region from the language code,
      // assuming it follows the pattern "{langcode}-{region}".
      $matches = [];
      if (preg_match('/^(\w+)-(\w+)$/', $languageCode, $matches)) {
        $region = $matches[2];
        $countryCodes[$region] = $languageCode;
      }
    }

    return $countryCodes;
  }

}
