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
 * Drush commands for product category.
 */
class MillboardProductCategoryCommand extends DrushCommands {
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
   * Constructs a new MillboardProductCategoryCommand instance.
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
   * Create product categories from the PIM.
   *
   * @param string|null $parent_product_cat_id
   *   The parent product category id.
   * @param int|null $pid
   *   The parent term id.
   *
   * @usage pim:create-product-categories
   *   Create product Categories.
   *
   * @command pim:create-product-categories
   * @aliases pim-cpc
   */
  public function createProductCategories(?string $parent_product_cat_id = NULL, ?int $pid = NULL) : void {
    // Parent product cat id of widen collective.
    $parent_product_cat_id = $parent_product_cat_id ? '?parent_product_category_id=' . $parent_product_cat_id : NULL;
    $url = $this->apiUrl() . '/product-categories' . $parent_product_cat_id;
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
          $this->processTerms($result['items'], $pid, $result['total_count']);
        }
        else {
          $this->logger()->error('Missing "items" key in the API response.');
        }
      }
      else {
        $this->logger()->error('HTTP request failed with status code: ' . $response->getStatusCode());
      }
    }
    catch (\Exception $e) {
      $this->logger()->error('An exception occurred: ' . $e->getMessage());
    }
  }

  /**
   * Process product type terms.
   *
   * @param array $items
   *   The items array.
   * @param int|Null $pid
   *   The parent term id.
   * @param int $count
   *   Total result count.
   */
  protected function processTerms(array $items, ?int $pid, int $count): void {
    $start = time();
    $fail_count = 0;
    $success_count = 0;
    $fail_items = [];
    $success_items = [];
    foreach ($items as $item) {
      if (!$this->checkExistingTerm($item['product_category_id'], 'product_category', 'field_product_category_id')) {
        // Create term of product category.
        $term = Term::create([
          'name' => $item['name'],
          'vid' => 'product_category',
          'field_product_category_id' => $item['product_category_id'],
          'parent' => ['target_id' => $pid],
        ]);
        $term->save();
        if ($term->id()) {
          // Add child terms of product category.
          $this->createProductCategories($item['product_category_id'], $term->id());
          $success_items[$item['product_category_id']] = $item['name'];
          $success_count++;
          $this->logger()->success(dt('Created @name product category terms', ['@name' => $item['name']]));
        }
        else {
          $fail_items[$item['product_category_id']] = $item['name'];
          $fail_count++;
          $this->logger()->error(dt('Failed to create @name product category terms', ['@name' => $item['name']]));
        }
        $count++;
      }
    }
    $this->logger()->success(dt('Created @success_count out of @total_count product category terms.', [
      '@success_count' => $success_count,
      '@total_count' => $count,
    ]));
    $this->loggerFactory->info('Created @success_count out of @total_count product category terms. Details: @array', [
      '@success_count' => $success_count,
      '@total_count' => $count,
      '@array' => json_encode($success_items, TRUE),
    ]);
    if ($fail_count) {
      $this->logger()->error(dt('Failed to create @fail_count out of @total_count product category terms.', [
        '@fail_count' => $fail_count,
        '@total_count' => $count,
      ]));
      $this->loggerFactory->info('Failed to create @fail_count out of @total_count product category terms. Details: @array', [
        '@fail_count' => $fail_count,
        '@total_count' => $count,
        '@array' => json_encode($fail_items, TRUE),
      ]);
    }
    $this->output()->writeln("Execution time: " . (time() - $start) . " seconds");
  }

}
