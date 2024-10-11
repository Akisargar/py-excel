<?php

namespace Drupal\millboard_common\Form;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
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
 * List Filters Form.
 */
class OrderSampleFilters extends FormBase {

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
   * {@inheritdoc}
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request service.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   An alias manager to find the alias for the current system path.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(RequestStack $request, protected CurrentPathStack $currentPath, protected AliasManagerInterface $aliasManager, EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager) {
    $this->request = $request;
    $this->currentPath = $currentPath;
    $this->aliasManager = $aliasManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
          $container->get('request_stack'),
          $container->get('path.current'),
          $container->get('path_alias.manager'),
          $container->get('entity_type.manager'),
          $container->get('language_manager'),
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'order_sample_filters';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $query_parameters = $this->request->getCurrentRequest()->query->all();

    // Get options for 'Cladding' and 'Decking' terms
    // from the 'product_type' vocabulary.
    $vocabulary = 'product_type';
    $term_names = ['Decking', 'Cladding'];
    $product_type = _millboard_common_get_taxonomy_term_options($vocabulary, $term_names);
    $product_type = $this->hideUnusedProductTypes($product_type);
    // $tids = $product_type ? array_keys($product_type) : [];
    // Prepare default values for the checkboxes.
    $default_value = [];
    $default_value = ['all', '141' , '131'];
    if (!empty($query_parameters['product_type']) && is_array($query_parameters['product_type'])) {
      // Sanitize each value if it is expected to be an array of strings.
      $default_value = array_map([Xss::class, 'filter'], $query_parameters['product_type']);
    }

    $form['product_type'] = [
      "#type" => "checkboxes",
      '#options' => $product_type,
      '#size' => NULL,
      '#default_value' => $default_value,
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'wrapper' => 'ajax-form-wrapper',
      ],
      '#prefix' => '<div class="product-type-wrapper"><h4>Filters: </h4>',
      '#suffix' => '</div>',
    ];

    $form['#prefix'] = '<div id="ajax-form-wrapper">';
    $form['#suffix'] = '</div><div class="product-view-filter-header"></div>';
    $form['#cache'] = ['contexts' => ['url.path', 'url.query_args']];

    // Show selected filters count.
    $form['#attached']['library'][] = 'millboard_common/millboard_common.filter_count';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * AJAX callback to handle form submission.
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $form = $form;
    $response = new AjaxResponse();
    $values = $form_state->getValues();
    $current_path = $this->aliasManager->getPathByAlias($this->currentPath->getPath());
    $url = Url::fromUserInput($current_path, [
      'query' => [
        'product_type' => $values['product_type'] ?? [],
      ],
    ])->toString();

    // Add a RedirectCommand to the response.
    $response->addCommand(new RedirectCommand($url));
    return $response;
  }

  /**
   * Clear All.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function clearAll(array $form, FormStateInterface $form_state) : void {
    $form = $form;
    $current_path = $this->aliasManager->getPathByAlias($this->currentPath->getPath());
    $form_state->setRedirectUrl(Url::fromUserInput($current_path));
  }

  /**
   * Hide unused options for Product type.
   *
   * @param array $options
   *   The field options.
   *
   * @return array
   *   Returns used options array.
   */
  private function hideUnusedProductTypes(array $options) : array {
    $usedOptions = [];

    if (empty($options)) {
      return $usedOptions;
    }

    $langId = $this->languageManager->getCurrentLanguage()->getId();
    $entityQuery = $this->entityTypeManager->getStorage('commerce_product')->getQuery();
    $entityQuery->accessCheck(TRUE);
    $entityQuery->condition('langcode', $langId, '=');

    foreach ($options as $optionVal => $optionLabel) {
      if (is_string($optionVal) && strtolower($optionVal) == 'all') {
        $usedOptions[$optionVal] = $optionLabel;
        continue;
      }
      $fieldEntityQuery = clone $entityQuery;
      $fieldEntityQuery->condition('field_product_type', [$optionVal], 'IN');
      $entityCount = $fieldEntityQuery->count()->execute();
      if ($entityCount <= 0) {
        continue;
      }
      $usedOptions[$optionVal] = $optionLabel;
    }

    return $usedOptions;
  }

}
