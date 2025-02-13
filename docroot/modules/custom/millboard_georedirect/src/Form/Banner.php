<?php

namespace Drupal\millboard_georedirect\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the Banner form.
 */
class Banner extends FormBase {

  /**
   * The cookie manager service.
   *
   * @var \Drupal\millboard_georedirect\CookieManager
   */
  protected $cookieManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\millboard_georedirect\RegionLanguageManager
   */
  protected $regionLangManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The logger factory.
   *
   * @var Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructor for the Banner form.
   *
   * @param \Drupal\millboard_georedirect\CookieManager $cookieManager
   *   The cookie manager service.
   * @param \Drupal\millboard_georedirect\RegionLanguageManager $regionLangManager
   *   The language manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Locale\CountryManagerInterface $countryManager
   *   The country manager service.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The alias manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct($cookieManager, $regionLangManager, RequestStack $requestStack, CountryManagerInterface $countryManager, PathValidatorInterface $path_validator, AliasManagerInterface $alias_manager, LanguageManagerInterface $language_manager) {
    $this->cookieManager = $cookieManager;
    $this->regionLangManager = $regionLangManager;
    $this->requestStack = $requestStack;
    $this->countryManager = $countryManager;
    $this->pathValidator = $path_validator;
    $this->aliasManager = $alias_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * Creates a new instance of the Banner form with dependency injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return \Drupal\millboard_georedirect\Form\Banner
   *   A new instance of the Banner form.
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('millboard_georedirect.cookie_manager'),
          $container->get('millboard_georedirect.region_language_manager'),
          $container->get('request_stack'),
          $container->get('country_manager'),
          $container->get('path.validator'),
          $container->get('path_alias.manager'),
          $container->get('language_manager')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'millboard_georedirect_banner';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $region = $this->cookieManager->getCookie("region");
    $current_language = $this->languageManager->getCurrentLanguage()->getId();

    // Add wrapper to Banner.
    $form['localization-banner-wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div class="localization-banner-wrapper">',
      '#suffix' => '</div>',
    ];

    $question = $this->t('Are you in the right place?');
    $button_text = $this->t('Continue');

    if ($current_language == 'en-au') {
      $question = $this->t('Are you looking for our Australian distributor?');
      $button_text = $this->t('Click here');
    }

    // Display the message with the user's region.
    $form['localization-banner-wrapper']['text_markup'] = [
      '#type' => 'markup',
      '#markup' => '<div class="coh-banner-heading">' . $question . '</div>',
    ];

    if (!$current_language == 'en-au') {
      // Display the dropdown with supported countries.
      $form['localization-banner-wrapper']['country'] = [
        '#type' => 'select',
        '#options' => $this->getCountryList(),
        '#default_value' => $region,
      ];
    }

    $form['localization-banner-wrapper']['banner-button-wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div class="banner-button-wrapper">',
      '#suffix' => '</div>',
    ];

    // "Continue" button to redirect to the user's region.
    $form['localization-banner-wrapper']['banner-button-wrapper']['continue'] = [
      '#type' => 'submit',
      '#value' => $button_text,
      '#attributes' => [
        'class' => ['coh-style-primary-button'],
      ],
    ];

    // "Close" button to close the banner.
    $form['localization-banner-wrapper']['banner-button-wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('X'),
      '#attributes' => [
        'class' => ['coh-style-close-button'],
      ],
      '#ajax' => [
        'callback' => [$this, 'closeBannerCallback'],
      ],
    ];

    // ADD FOR MOBILE VIEW STYLES.
    $form['banner-dropdown-wrapper-mobile'] = [
      '#type' => 'container',
      '#prefix' => '<div class="banner-dropdown-wrapper-mobile">',
      '#suffix' => '</div>',
    ];

    $form['banner-dropdown-wrapper-mobile']['continue'] = [
      '#type' => 'submit',
      '#value' => $button_text,
      '#attributes' => [
        'class' => ['coh-style-primary-button'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set Banner consent.
    $this->cookieManager->setCookie('banner_consent', 1);

    // Get Region and country code.
    $region = $form_state->getValue('country');
    $countryCodes = $this->regionLangManager->getCountryCodes();
    $current_language = $this->languageManager->getCurrentLanguage()->getId();

    // Get current request from previous url.
    $previousUrl = $this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER');

    $oldLangCode = $this->getLangCodeFromUrl($previousUrl);

    // Extract the path from the URL.
    $urlParts = parse_url($previousUrl);
    $path = $urlParts['path'] ?? '';

    // Replace the old language code with the new one.
    $newPath = str_replace('/' . $oldLangCode . '/', '/' . $countryCodes[$region] . '/', $path);

    // Reconstruct the modified URL.
    $newUrl = $urlParts['scheme'] . '://' . $urlParts['host'] . $newPath;

    if ($current_language == 'en-au') {
      $newUrl = 'https://www.conceptmaterials.com.au/';
    }

    // Create a TrustedRedirectResponse to redirect to the full URL.
    $response = new TrustedRedirectResponse($newUrl);
    $form_state->setResponse($response);
  }

  /**
   * Callback to close the banner and set a cookie.
   */
  public function closeBannerCallback() {
    // Remvoe the banner on close button click.
    $response = new AjaxResponse();
    $response->addCommand(new RemoveCommand('#redirect-banner'));
    return $response;
  }

  /**
   * Get supported countries.
   */
  public function getCountryList() {
    $options = [];
    $countryCodes = $this->regionLangManager->getCountryCodes();
    $country_list = $this->countryManager->getList();
    foreach ($countryCodes as $key => $countryCode) {
      $options[$key] = $country_list[strtoupper($key)]->render();
      $countryCode = $countryCode;
    }
    return $options;
  }

  /**
   * Extracts the language code from a given URL.
   *
   * @param string $url
   *   The URL from which to extract the language code.
   *
   * @return string|null
   *   The extracted language code, or null if not found.
   */
  public function getLangCodeFromUrl($url) {
    // Parse the URL.
    $urlParts = parse_url($url);

    // Extract the path from the URL.
    $path = $urlParts['path'] ?? '';

    // Use regular expression to extract the language code.
    if (preg_match('#/([a-z]{2}-[a-z]{2})/#', $path, $matches)) {
      $languageCode = $matches[1];
      return $languageCode;
    }

    // Language code not found in the URL.
    return NULL;
  }

}
