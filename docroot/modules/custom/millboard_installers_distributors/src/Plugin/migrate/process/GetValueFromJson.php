<?php

namespace Drupal\millboard_installers_distributors\Plugin\migrate\process;

use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Custom transformation of JSON to array and get a value.
 *
 * @MigrateProcessPlugin(
 *   id = "get_value_from_json",
 *   handle_multiples = TRUE
 * )
 *
 * @code
 * field_text:
 *   plugin: get_value_from_json
 *   source: json
 *   index:
 *     - und
 *     - 0
 *     - value
 * @endcode
 */
class GetValueFromJson extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $result = json_decode(trim($value), TRUE);
    // Check if json is valid.
    if (empty($result)) {
      throw new MigrateSkipProcessException(sprintf('%s is not a valid JSON.', var_export($value, TRUE)));
    }

    return NestedArray::getValue($result, $this->configuration['index']);
  }

}
