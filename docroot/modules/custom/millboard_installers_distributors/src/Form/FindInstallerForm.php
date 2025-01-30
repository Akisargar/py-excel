<?php

namespace Drupal\millboard_installers_distributors\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Creates a form to find installers.
 */
class FindInstallerForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The geolocation geocoder manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $geocoderManager;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request service.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   An alias manager to find the alias for the current system path.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $geocoderManager
   *   The geolocation geocoder manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request,
    protected CurrentPathStack $currentPath,
    protected AliasManagerInterface $aliasManager,
    ConfigFactoryInterface $config_factory,
    PluginManagerInterface $geocoderManager,
    LanguageManagerInterface $languageManager,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request;
    $this->currentPath = $currentPath;
    $this->aliasManager = $aliasManager;
    $this->configFactory = $config_factory;
    $this->geocoderManager = $geocoderManager;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('path.current'),
      $container->get('path_alias.manager'),
      $container->get('config.factory'),
      $container->get('plugin.manager.geolocation.geocoder'),
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'millboard_installers_distributors_find_installer';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('geolocation_google_maps.settings');

    $request = $this->getRequest();

    $form['#cache'] = ['max-age' => 0];

    $form['country_uselocation_wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div class="country_uselocation_wrapper">',
      '#suffix' => '</div>',
    ];

    $form['country_uselocation_wrapper']['get_location'] = [
      '#type' => 'button',
      '#value' => $this->t('Use my location'),
      '#attributes' => [
        'id' => 'use-location',
        'class' => [
          'coh-style-tertiary-button-with-underline',
          'use-location-button',
        ],
      ],
      '#prefix' => '<div class="get_location_submit_wrapper">',
      '#suffix' => '</div>',
    ];

    $default_postcode = '';
    if ($request->query->get('postcode')) {
      $default_postcode = $request->query->get('postcode');
    }
    elseif ($request->query->get('location')) {
      $default_postcode = $request->query->get('location');
    }

    // Post code input.
    $form['search_location_field_wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div class="search-location-field-wrapper coh-style-search-location-custom-style">',
      '#suffix' => '</div>',
    ];

    $form['search_location_field_wrapper']['postcode'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->t('Enter town or postcode'),
        'id' => "edit-postcode",
      ],
      '#cache' => [
        'contexts' => [
          'url.query_args:location',
        ],
      ],
      '#default_value' => $default_postcode,
    ];

    $default_distance = 30;
    $current_language = $this->languageManager->getCurrentLanguage()->getId();
    if ($current_language == "en-us") {
      $default_distance = 500;
    }

    $proximity = $request->query->get('proximity');

    if (isset($proximity)) {
      $string = $proximity;

      $pattern = '/<=(.*?)mi/';

      preg_match($pattern, $string, $matches);

      // Perform the regex match.
      if (isset($matches[1])) {
        $default_distance = (int) $matches[1];
      }
    }

    $form['search_location_field_wrapper']['distance'] = [
      '#type' => 'hidden',
      '#default_value' => $default_distance,
    ];

    // Hidden fields for latitude and longitude.
    $form['search_location_field_wrapper']['latitude'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'latitude',
      ],
    ];

    $form['search_location_field_wrapper']['longitude'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'longitude',
      ],
    ];

    $form['search_location_field_wrapper']['submit']['#prefix'] = '<div class="findinstaller-postcode-search">';
    $form['search_location_field_wrapper']['submit']['#suffix'] = '</div>';

    // Error message for location.
    $error = $request->query->get('error', '');

    if (!empty($error)) {
      switch ($error) {
        case 'ERR_DETERMINING_LOCATION':
          $error = 'Please enable location services on your browser or device to use your location, or enter a town or postcode manually.';
          break;

        case 'INVALID_POSTCODE':
          $error = 'Please enter a valid postcode or location.';
          break;
      }
    }

    $form['search_location_field_wrapper']['get_location']['#markup'] = "<p class='error geoerror'>$error</p>";

    $form['search_location_field_wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('SEARCH'),
      '#attributes' => [
        'class' => [
          'search_submit',
          'coh-style-primary-button-with-icon',
          'where-to-buy-submit',
        ],
      ],
    ];

    // Attaching library to fetch user location.
    $form['#attached']['library'][] = 'millboard_installers_distributors/user_location';
    $form['#attached']['drupalSettings']['google_map_api_key'] = $config->get('google_map_api_key');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $current_path = $this->aliasManager->getPathByAlias($this->currentPath->getPath());

    $url = NULL;
    switch (TRUE) {
      case !empty($values['latitude']) && !empty($values['longitude']):
        $lat = $values['latitude'];
        $lng = $values['longitude'];

        $postcode = $this->reverseGeocode($lat, $lng);
        if (empty($postcode)) {
          // Current url with error message.
          $url = Url::fromUserInput($current_path, [
            'query' => [
              'error' => 'ERR_DETERMINING_LOCATION',
            ],
          ]);

          break;
        }

        $url = Url::fromUserInput($current_path, [
          'query' => [
            'location' => $postcode,
            'proximity' => $lat . ',' . $lng . '<=' . $values['distance'] . 'mi',
          ],
        ]);

        break;

      case !empty($values['postcode']):
        $loc = $this->geocodePostalCode($values['postcode']);
        if (empty($loc)) {
          // Current url with error message.
          $url = Url::fromUserInput($current_path, [
            'query' => [
              'error' => 'INVALID_POSTCODE',
            ],
          ]);

          break;
        }

        $lat = $loc['lat'];
        $lng = $loc['lng'];
        $url = Url::fromUserInput($current_path, [
          'query' => [
            'location' => $values['postcode'],
            'proximity' => $lat . ',' . $lng . '<=' . $values['distance'] . 'mi',
          ],
        ]);

        break;

      default:
        $url = Url::fromUserInput($current_path, [
          'query' => [
            'error' => 'INVALID_POSTCODE',
          ],
        ]);
    }

    $form_state->setRedirectUrl($url);
  }

  /**
   * Geocode a postal code using the Geolocation module's geocoder.
   *
   * @param string $postcode
   *   The postal code to geocode.
   *
   * @return array|null
   *   The latitude and longitude coordinates of the postal code.
   */
  public function geocodePostalCode(string $postcode): ?array {
    // Get the Google Geocoding API geocoder plugin.
    $geocoder = $this->geocoderManager->getGeocoder('google_geocoding_api');

    // Perform geocoding for the provided postal code.
    $geocode = $geocoder->geocode($postcode);

    if (!empty($geocode) && array_key_exists('location', $geocode)
      && array_key_exists('lat', $geocode['location']) && array_key_exists('lng', $geocode['location'])) {
      return [
        'lat' => $geocode['location']['lat'],
        'lng' => $geocode['location']['lng'],
      ];
    }

    return NULL;
  }

  /**
   * Reverse geocode a lat and long using the Geolocation module's geocoder.
   *
   * @param string $lat
   *   The latitude.
   * @param string $lng
   *   The longitude.
   *
   * @return string|null
   *   The postal code of the latitude and longitude.
   */
  private function reverseGeocode(string $lat, string $lng) {
    $geocoder = $this->geocoderManager->getGeocoder('google_geocoding_api');

    $geocode = $geocoder->reverseGeocode($lat, $lng);

    if (array_key_exists('atomics', $geocode) || array_key_exists('elements', $geocode)) {
      $data = $geocode['atomics'] ?? $geocode['elements'];
      return $data['postalCode'] ?? NULL;
    }

    return NULL;
  }

}
