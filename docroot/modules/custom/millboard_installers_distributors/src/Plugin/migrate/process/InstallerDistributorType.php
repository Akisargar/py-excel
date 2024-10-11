<?php

namespace Drupal\millboard_installers_distributors\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Perform custom value transformations.
 *
 * @MigrateProcessPlugin(
 *   id = "installer_distributor_type"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * field_text:
 *   plugin: installer_distributor_type
 *   source: text
 * @endcode
 */
class InstallerDistributorType extends ProcessPluginBase {

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $fieldData = [];
    $values = explode(",", $value);
    foreach ($values as $key => $item) {
      if ($item === '1') {
        $fieldData[] = $instDistMap[$key];
      }
    }
    return $fieldData;
  }

}
