<?php

namespace Drupal\millboard_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller to return view entity results.
 */
class ViewEntityResultsController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ContentEntityCloneBase.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Returns the results count for each option.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request data.
   *
   * @return array
   *   Entity count result array.
   */
  public function getEntityResultsCount(Request $request) {

    $result = [];

    // Return empty result array if
    // request array is empty.
    if (empty($request->request->all())) {
      return new JsonResponse($result);
    }

    $extraConditions = $request->request->all();

    // Build partial reusable entity query.
    $entityQuery = $this->buildEntityQuery($extraConditions);

    // Apply field conditions using
    // above partial entity query.
    if (!empty($entityQuery)) {

      // Get the query fields
      // and apply conditions.
      if (isset($extraConditions['query_fields']) && !empty($extraConditions['query_fields'])) {
        foreach ($extraConditions['query_fields'] as $singleQryField) {

          // Build result array.
          $fieldResult = $singleQryField;
          unset($fieldResult['values']);
          $fieldResult['value_results'] = [];

          // Check results for each option.
          foreach ($singleQryField['values'] as $value) {
            $fieldEntityQuery = clone $entityQuery;
            $fieldEntityQuery->condition($singleQryField['name'], [$value], 'IN');
            $entityCount = $fieldEntityQuery->count()->execute();
            $fieldResult['value_results'][$value] = $entityCount;
          }

          // Pass field result to main
          // result array.
          if (!empty($fieldResult['value_results'])) {
            $result[] = $fieldResult;
          }
        }
      }
    }
    return new JsonResponse($result);
  }

  /**
   * Build partial entity query.
   *
   * @param array $extraConditions
   *   An associative array for extra entity query conditions.
   *
   * @return array|object
   *   Returns partial entity query or empty array.
   */
  private function buildEntityQuery(array $extraConditions) {
    $entityQuery = [];
    if (!empty($extraConditions) && isset($extraConditions['entity_type']) && !empty($extraConditions['entity_type'])) {

      $entityQuery = $this->entityTypeManager->getStorage($extraConditions['entity_type'])->getQuery();
      $entityQuery->accessCheck(TRUE);

      // Apply default conditions.
      if (isset($extraConditions['default_fields']) && !empty($extraConditions['default_fields'])) {
        foreach ($extraConditions['default_fields'] as $defaultFieldName => $defaultFieldData) {
          $opr = isset($defaultFieldData['value']) && is_array($defaultFieldData['value']) ? 'IN' : '=';
          $entityQuery->condition($defaultFieldName, $defaultFieldData['value'] ?? '', $opr);
        }
      }
    }
    return $entityQuery;
  }

}
