<?php

namespace Drupal\millboard_common\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * List Filters Form.
 */
class ListFiltersForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   */
  public function __construct(RequestStack $request, protected CurrentPathStack $currentPath, protected AliasManagerInterface $aliasManager) {
    $this->request = $request;
    $this->currentPath = $currentPath;
    $this->aliasManager = $aliasManager;
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
      $container->get('path_alias.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_study_filters_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $query_parameters = $this->request->getCurrentRequest()->query->all();
    $form['sort_combine'] = [
      '#type' => 'details',
      '#title' => $this->t('Sort By'),
      '#open' => FALSE,
    ];
    $form['sort_combine']['sort_bef_combine'] = [
      "#type" => "radios",
      "#options" => [
        "field_publish_date_value_ASC" => $this->t("Date Asc"),
        "field_publish_date_value_DESC" => $this->t("Date Desc"),
      ],
      "#default_value" => !empty($query_parameters) && array_key_exists('sort_bef_combine', $query_parameters) ? $query_parameters['sort_bef_combine'] : 'field_publish_date_value_DESC',
    ];
    $form['type_of_project'] = [
      '#type' => 'details',
      '#title' => $this->t('Type of project'),
      '#open' => FALSE,
    ];
    $form['type_of_project']['field_type_of_project_target_id'] = [
      '#type' => 'checkboxes',
      '#options' => _millboard_common_term_options('projects'),
      '#size' => NULL,
      '#default_value' => !empty($query_parameters) && array_key_exists('field_type_of_project_target_id', $query_parameters) ? array_values($query_parameters['field_type_of_project_target_id']) : [],
    ];
    $form['collection_colour'] = [
      '#type' => 'details',
      '#title' => $this->t('Collection/Colour'),
      '#open' => FALSE,
    ];
    $form['collection_colour']['collection'] = [
      '#title' => $this->t('Collection'),
      '#type' => 'select',
      '#options' => _millboard_common_products_options(),
      '#size' => NULL,
      '#default_value' => !empty($query_parameters) && array_key_exists('collection', $query_parameters) ? $query_parameters['collection'] : 'all',
      '#ajax' => [
        'callback' => '::filterColourOptions',
        'wrapper' => 'edit-filter-colours',
      ],
    ];
    $form['collection_colour']['colour'] = [
      '#title' => $this->t('Colour'),
      '#type' => 'select',
      '#options' => ['all' => $this->t('- All -')],
      '#size' => NULL,
      '#default_value' => !empty($query_parameters) && array_key_exists('colour', $query_parameters) ? $query_parameters['colour'] : 'all',
      '#prefix' => '<div id="edit-filter-colours">',
      '#suffix' => '</div>',
    ];

    if (array_key_exists('collection', $query_parameters) && $query_parameters['collection'] != 'all') {
      $form['collection_colour']['colour']['#options'] = _millboard_common_product_variation_options($query_parameters['collection'], TRUE);
    }

    if ($form_state->getValue('collection') && $form_state->getValue('collection') != 'all') {
      $form['collection_colour']['colour']['#options'] = _millboard_common_product_variation_options($form_state->getValue('collection'), TRUE);
    }
    $form['location'] = [
      '#type' => 'details',
      '#title' => $this->t('Location'),
      '#open' => FALSE,
    ];
    $form['location']['field_location_target_id'] = [
      '#type' => 'checkboxes',
      '#options' => _millboard_common_term_options('location'),
      '#size' => NULL,
      '#default_value' => !empty($query_parameters) && array_key_exists('field_location_target_id', $query_parameters) ? array_values($query_parameters['field_location_target_id']) : [],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply'),
      '#attributes' => [
        'class' => ['coh-style-primary-button'],
      ],
    ];

    if ($query_parameters) {
      $form['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Clear All'),
        '#submit' => ['::ClearAll'],
        '#attributes' => [
          'class' => ['coh-style-secondary-button'],
        ],
      ];
    }
    $form['#cache'] = ['contexts' => ['url.path', 'url.query_args']];

    // Show selected filters count.
    $form['#attached']['library'][] = 'millboard_common/millboard_common.filter_count';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $current_path = $this->aliasManager->getPathByAlias($this->currentPath->getPath());
    $query_parameters = [
      'query' => [
        'collection' => $values['collection'],
        'colour' => $values['colour'],
        'sort_bef_combine' => $values['sort_bef_combine'],
      ],
    ];
    if (array_filter($values['field_type_of_project_target_id'])) {
      $query_parameters['query']['field_type_of_project_target_id'] = array_filter($values['field_type_of_project_target_id']);
    }
    if (array_filter($values['field_location_target_id'])) {
      $query_parameters['query']['field_location_target_id'] = array_filter($values['field_location_target_id']);
    }
    $url = Url::fromUserInput($current_path, $query_parameters);
    $form_state->setRedirectUrl($url);
  }

  /**
   * AJAX callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   Form field.
   */
  public function filterColourOptions(array $form, FormStateInterface $form_state): array {
    return $form['collection_colour']['colour'];
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
    $current_path = $this->aliasManager->getPathByAlias($this->currentPath->getPath());
    $form_state->setRedirectUrl(Url::fromUserInput($current_path));
  }

}
