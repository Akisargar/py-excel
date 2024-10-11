<?php

namespace Drupal\millboard_sap_configuration\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush command for sending the mail.
 */
class MillboardSendMail extends DrushCommands {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The Mail Manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * The sap configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $sapConfiguration;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $factory;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * Constructs a new MillboardProductCommand instance.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   The mail manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $factory
   *   The logger factory service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(MailManagerInterface $mailManager, ConfigFactoryInterface $configFactory, LanguageManagerInterface $languageManager, LoggerChannelFactoryInterface $factory, FileSystemInterface $file_system) {
    $this->mailManager = $mailManager;
    $this->configFactory = $configFactory;
    $this->languageManager = $languageManager;
    $this->loggerFactory = $factory;
    $this->fileSystem = $file_system;
    $this->sapConfiguration = $this->configFactory->get('millboard_sap_configuration.settings');
    parent::__construct();
  }

  /**
   * Command to send the mail for csv order list.
   *
   * @usage mbrd:send_order_mail
   *   Send mail with the order list.
   *
   * @command mbrd:send_order_mail
   * @aliases mbrd-som
   */
  public function sendOrderMail() : void {
    $params = [];
    $success = "The CSV exported has been mailed to the configured email ID.";
    $notice = "There was no order data to be exported in the past hour and hence has not been mailed.";

    // Check if the mail body value is set.
    if (isset($this->sapConfiguration->get('mail_body')['value']) && !empty($this->sapConfiguration->get('mail_body')['value'])) {
      $params['message'] = $this->sapConfiguration->get('mail_body')['value'];
    }

    // Set the subject.
    $params['subject'] = $this->sapConfiguration->get('mail_subject');

    // Get the directory path.
    $directoryPath = 'public://views_data_export/sample_order_csv_export_data_export_1';

    // Get a list of files in the directory.
    $files = scandir($directoryPath);

    // Remove '.' and '..' from the list.
    $files = array_diff($files, ['.', '..']);

    // Sort files by modification time in descending order.
    foreach ($files as $file) {
      $filePaths[$file] = filemtime("$directoryPath/$file");
    }
    arsort($filePaths);

    // Get the latest file.
    $latestFile = key($filePaths);

    // Get the file path.
    $filePath = "$directoryPath/$latestFile";

    // Get the file URL using the Drupal file system service.
    $fileUrl = $this->fileSystem->realpath($filePath);

    // Read the file content.
    $mail_content = file_get_contents($fileUrl);

    if (!empty($mail_content)) {
      $params = [
        'attachments' => [
                [
                  'filecontent' => $mail_content,
                  'filename' => $latestFile,
                  'filemime' => 'text/csv',
                ],
        ],
        'message' => $this->sapConfiguration->get('mail_body')['value'] ?? '',
        'subject' => $params['subject'],
      ];
      $language = $this->languageManager->getCurrentLanguage()->getId();
      $this->mailManager->mail('millboard_sap_configuration', 'millboard_sap_configuration', $this->sapConfiguration->get('sap_mail_id'), $language, $params, NULL, TRUE);
      $this->loggerFactory->get('millboard_sap_configuration')->notice($success);
      $this->logger()->success($success);
    }
    else {
      $this->loggerFactory->get('millboard_sap_configuration')->notice($notice);
      $this->logger()->notice($notice);
    }
  }

  /**
   * Command to send the mail for csv order list FR.
   *
   * @usage mbrd:send_order_mail:fr
   *   Send mail with the order list.
   *
   * @command mbrd:send_order_mail:fr
   * @aliases mbrd-som-fr
   */
  public function sendOrderMailFr() : void {
    $params = [];
    $success = "The CSV exported for FR has been mailed to the configured email ID.";
    $notice = "There was no order data to be exported in the past hour and hence has not been mailed.";
    if ($this->sapConfiguration->get('fr_mail_body') !== NULL && !empty($this->sapConfiguration->get('fr_mail_body'))) {
      $params['message'] = $this->sapConfiguration->get('fr_mail_body');
    }
    $params['subject'] = $this->sapConfiguration->get('fr_mail_subject');
    // Get the file URL using file_create_url.
    $fileUrl = 'public://views_data_export/sample_order_csv_export_data_export_2/Sample-Orders-FR.csv';
    $mail_content = file_get_contents($fileUrl);
    if (!empty($mail_content)) {
      $params = [
        'attachments' => [
                [
                  'filecontent' => $mail_content,
                  'filename' => 'Sample-Orders.csv',
                  'filemime' => 'text/csv',
                ],
        ],
        'message' => $params['message'],
        'subject' => $params['subject'],
      ];
      $language = $this->languageManager->getCurrentLanguage()->getId();
      $this->mailManager->mail('millboard_sap_configuration', 'millboard_sap_configuration', $this->sapConfiguration->get('fr_mail_id'), $language, $params, NULL, TRUE);
      $this->loggerFactory->get('millboard_sap_configuration')->notice($success);
      $this->logger()->success($success);
    }
    else {
      $this->loggerFactory->get('millboard_sap_configuration')->notice($notice);
      $this->logger()->notice($notice);
    }
  }

}
