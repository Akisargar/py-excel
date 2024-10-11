<?php

namespace Drupal\millboard_pim_integration;

use Drupal\taxonomy\Entity\Term;

/**
 * Helpers for taxonomy terms.
 */
trait MillboardTermTraits {

  /**
   * Check term that already exist in Drupal.
   *
   * @param string $id
   *   Widen collective term id.
   * @param string $vid
   *   The vocabulary id.
   * @param stfing $field_name
   *   The field name.
   *
   * @return bool
   *   Product exit or not.
   */
  protected function checkExistingTerm(string $id, string $vid, $field_name) : bool {

    // Term that already exist in Drupal.
    $properties = [];
    $term = "";
    if (!empty($field_name)) {
      $properties[$field_name] = $id;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);

    return !empty($term) ? TRUE : FALSE;
  }

  /**
   * Find term by name and vid.
   *
   * @param mixed $name
   *   Term name.
   * @param string $vid
   *   Term vid.
   *
   * @return int
   *   Term id.
   */
  protected function getTidByName($name, string $vid) : int {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);
    if (!empty($term)) {
      return $term->id();
    }
    else {
      $term = Term::create([
        'name' => $name,
        'vid' => $vid,
      ]);
      $term->save();
      return $term->id();
    }
  }

}
