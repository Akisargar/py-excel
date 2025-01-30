<?php

namespace Drupal\millboard_georedirect\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\millboard_georedirect\GeoDetection;
use Drupal\millboard_georedirect\RegionLanguageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for handling the banner display.
 */
class BannerController extends ControllerBase {

  /**
   * The region language manager.
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
   * The route admin context to determine whether a route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The GeoDetection service.
   *
   * @var \Drupal\millboard_georedirect\GeoDetection
   */
  protected $geoDetection;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $langugaeManager;

  /**
   * Constructs a new BannerController.
   *
   * @param \Drupal\millboard_georedirect\RegionLanguageManager $regionLangManager
   *   The language manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The route admin context service.
   * @param \Drupal\millboard_georedirect\GeoDetection $geoDetection
   *   The geodetection service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $langugaeManager
   *   The langugae manager service.
   */
  public function __construct(RegionLanguageManager $regionLangManager, RequestStack $requestStack, AdminContext $admin_context, GeoDetection $geoDetection, LanguageManagerInterface $langugaeManager) {
    $this->regionLangManager = $regionLangManager;
    $this->requestStack = $requestStack;
    $this->adminContext = $admin_context;
    $this->geoDetection = $geoDetection;
    $this->langugaeManager = $langugaeManager;
  }

  /**
   * Creates an instance of the BannerController.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   *
   * @return static
   *   A new instance of BannerController.
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('millboard_georedirect.region_language_manager'),
          $container->get('request_stack'),
          $container->get('router.admin_context'),
          $container->get('millboard_georedirect.geo_detection'),
          $container->get('language_manager'),
      );
  }

  /**
   * Displays the banner and handles redirection.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response for displaying the banner.
   */
  public function banner() {

    $request = $this->requestStack->getCurrentRequest();
    $countryCodes = $this->regionLangManager->getCountryCodes();
    $userActualRegion = $request->cookies->get("region");
    $siteCurrentlanguage = $this->langugaeManager->getCurrentLanguage()->getId();

    if ($siteCurrentlanguage == 'en-au') {
      $html = '
      <div class="background-overlay"></div>
      <div class="popup-wrapper">
        <div class="popup-content">
          <div class="coh-row-inner coh-ce-cpt_2_column_layout-wrapper container-wrapper">
            <div class="coh-column coh-visible-xs coh-col-xs-12 coh-visible-sm coh-col-sm-6 coh-visible-lg coh-col-lg-6 left-container">
              <div class="coh-container ssa-component coh-component text-wrapper">
                <img src="/themes/custom/millboard/images/concept-materials-logo-01@2x.png" width="1024" height="576" alt="" loading="lazy" data-once="lazyload-once" class="coh-lazy-loaded logo" data-was-processed="true">
                <br/>
                <p class="text-align-center p1">Concept Materials provides Australians with the best choice in high-performing, premium-quality building materials that live up to the demands of architects, builders, and people who use the space every day.</p>
                <br/>
                <p class="text-align-center p1 coh-style-paragraph-regular" style="padding-bottom: 15px;"><strong>Contact Details</strong></p>
                <p class="coh-style-paragraph-regular" style="padding-bottom: 15px;">1300 626 273</p>
                <p class="coh-style-paragraph-regular" style="padding-bottom: 15px;"><a href="mailto:info@conceptmaterials.com.au">info@conceptmaterials.com.au</a></p>
                <a class="coh-style-primary-button-with-millboard-green-bg" href="https://conceptmaterials.com.au/">VISIT SITE</a>
              </div>
            </div>
            <div class="coh-column coh-visible-xs coh-col-xs-12 coh-visible-sm coh-col-sm-6 coh-visible-lg coh-col-lg-6 image-container">
              <div class="coh-container ssa-component coh-component">
                <img src="/themes/custom/millboard/images/Concept-Products3-1024x768-1@2x.png" width="1024" height="576" alt="" loading="lazy" data-once="lazyload-once" class="coh-lazy-loaded" data-was-processed="true">
              </div>
            </div>
          </div>
          <span id="close-popup" class="coh-style-close-button"></span>
        </div>
      </div>';
      return new JsonResponse(['html' => $html]);
    }

    if ($request->cookies->has("banner_consent")) {
      // Return an empty AjaxResponse.
      return new AjaxResponse();
    }

    if (!in_array($userActualRegion, $this->regionLangManager->getSupportedRegions())) {
      return new AjaxResponse();
    }

    if ($request->cookies->has("language_preference") && $siteCurrentlanguage && $request->cookies->has("region")) {
      if ($request->cookies->get("language_preference") === $siteCurrentlanguage && $siteCurrentlanguage === $countryCodes[$request->cookies->get("region")]) {
        // Return an empty AjaxResponse.
        return new AjaxResponse();
      }
    }

    $response = new AjaxResponse();
    $modal_form = $this->formBuilder()->getForm('Drupal\millboard_georedirect\Form\Banner');

    $response->addCommand(new HtmlCommand('#redirect-banner', $modal_form));
    return $response;
  }

}
