<?php

namespace Drupal\millboard_pim_integration\Commands;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\millboard_pim_integration\MillboardTermTraits;
use Drupal\millboard_pim_integration\MillboardWidenTraits;
use Drupal\taxonomy\Entity\Term;
use Drush\Commands\DrushCommands;
use GuzzleHttp\ClientInterface;

/**
 * Drush commands for product type.
 */
class MillboardProductTypeCommand extends DrushCommands {
  use MillboardTermTraits;
  use MillboardWidenTraits;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Logger Factory.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new MillboardProductTypeCommand instance.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(ClientInterface $http_client, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct();
    $this->httpClient = $http_client;
    $this->loggerFactory = $logger_factory->get('millboard_pim_integration');
  }

  /**
   * Create product types from the PIM.
   *
   * @usage pim:create-product-types
   *   Create product types.
   *
   * @command pim:create-product-types
   * @aliases pim-cpt
   */
  public function createProductTypes() : void {
    $url = $this->apiUrl() . '/product-types';
    try {
      // Make the HTTP request.
      $response = $this->httpClient->request('GET', $url, ['headers' => $this->headers()]);
      // Check the HTTP status code.
      if ($response->getStatusCode() == 200) {
        // Decode the JSON response.
        $result = Json::decode($response->getBody());

        if ($result['total_count'] == 0) {
          $this->output()->writeln("No Records.");
          return;
        }
        // Check if 'items' key exists in the response.
        if (isset($result['items'])) {
          // Process terms.
          $this->processTerms($result['items'], $result['total_count']);
        }
        else {
          $this->logger()->error('Missing "items" key in the API response.');
          $this->loggerFactory->error('Missing "items" key in the API response.');
        }
      }
      else {
        $this->logger()->error('HTTP request failed with status code: ' . $response->getStatusCode());
        $this->loggerFactory->error('HTTP request failed with status code: ' . $response->getStatusCode());
      }
    }
    catch (\Exception $e) {
      $this->logger()->error('An exception occurred: ' . $e->getMessage());
      $this->loggerFactory->error('An exception occurred: ' . $e->getMessage());
    }
  }

  /**
   * Process product type terms.
   *
   * @param array $items
   *   The items array.
   * @param int $count
   *   Total result count.
   */
  protected function processTerms(array $items, int $count): void {
    $start = time();
    $fail_count = 0;
    $success_count = 0;
    $fail_items = [];
    $success_items = [];
    foreach ($items as $item) {
      if (!$this->checkExistingTerm($item['product_type_id'], 'product_type', 'field_product_type_id')) {
        // Create term of product type.
        $term = Term::create([
          'name' => $item['name'],
          'vid' => 'product_type',
          'field_product_type_id' => $item['product_type_id'],
        ]);
        $term->save();
        if ($term->id()) {
          $success_items[$item['product_type_id']] = $item['name'];
          $success_count++;
          $this->logger()->success(dt('Created @name product type terms', ['@name' => $item['name']]));
        }
        else {
          $fail_items[$item['product_type_id']] = $item['name'];
          $fail_count++;
          $this->logger()->error(dt('Failed to create @name product type terms', ['@name' => $item['name']]));
        }
      }
    }
    $this->logger()->success(dt('Created @success_count out of @total_count product type terms.', [
      '@success_count' => $success_count,
      '@total_count' => $count,
    ]));
    $this->loggerFactory->info('Created @success_count out of @total_count product type terms. Details: @array', [
      '@success_count' => $success_count,
      '@total_count' => $count,
      '@array' => json_encode($success_items, TRUE),
    ]);
    if ($fail_count) {
      $this->logger()->error(dt('Failed to create @fail_count out of @total_count product type terms.', [
        '@fail_count' => $fail_count,
        '@total_count' => $count,
      ]));
      $this->loggerFactory->info('Failed to create @fail_count out of @total_count product type terms. Details: @array', [
        '@fail_count' => $fail_count,
        '@total_count' => $count,
        '@array' => json_encode($fail_items, TRUE),
      ]);
    }
    $this->output()->writeln("Execution time: " . (time() - $start) . " seconds");
  }

}
