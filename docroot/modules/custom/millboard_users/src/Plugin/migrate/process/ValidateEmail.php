<?php

namespace Drupal\millboard_users\Plugin\migrate\process;

use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks if the mail syntax is correct.
 *
 * @code
 *   mail:
 *     plugin: validate_email
 *     source: email
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "validate_email"
 * )
 */
class ValidateEmail extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The email validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidator
   */
  protected $emailValidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EmailValidator $email_validator,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->emailValidator = $email_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = trim($value);
    if (!$this->emailValidator->isValid($value)) {
      throw new MigrateException(sprintf('%s is not a valid mail.', var_export($value, TRUE)));
    }
    return $value;
  }

}
