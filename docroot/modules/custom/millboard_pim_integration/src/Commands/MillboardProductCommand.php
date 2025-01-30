<?php

namespace Drupal\millboard_pim_integration\Commands;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_store\Entity\Store;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\millboard_pim_integration\FieldsTraits;
use Drupal\millboard_pim_integration\MillboardTermTraits;
use Drupal\millboard_pim_integration\MillboardWidenTraits;
use Drupal\millboard_pim_integration\ProductTraits;
use Drupal\pathauto\PathautoState;
use Drush\Commands\DrushCommands;
use GuzzleHttp\ClientInterface;

/**
 * Drush commands for Millboard PIM products.
 */
class MillboardProductCommand extends DrushCommands {
  use FieldsTraits;
  use MillboardTermTraits;
  use MillboardWidenTraits;
  use ProductTraits;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;
  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Logger Factory.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $loggerFactory;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new MillboardProductCommand instance.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(ClientInterface $http_client, EntityTypeManagerInterface $entity_type_manager, Connection $connection, LoggerChannelFactoryInterface $logger_factory, LanguageManagerInterface $language_manager) {
    parent::__construct();
    $this->httpClient = $http_client;
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->loggerFactory = $logger_factory->get('millboard_pim_integration');
    $this->languageManager = $language_manager;
  }

  /**
   * Create and update products from the PIM.
   *
   * @param int $offset
   *   Max 9999, Min 0, Default 0.
   * @param int $limit
   *   Max 100, Min 0, Default 10.
   *
   * @usage pim:create-products
   *   Create products.
   *
   * @command pim:create-products.
   * @aliases pim-cp
   */
  public function createProducts($offset = 0, $limit = 100) : void {
    // Create store.
    $this->createStore();
    $url = $this->apiUrl() . '/products/search';
    $filter = [["type" => "exclude_variants"]];
    $body = json_encode(["offset" => $offset, "limit" => $limit, "filters" => $filter]);
    try {
      // Make the HTTP request.
      $response = $this->httpClient->request('POST', $url, [
        'body' => $body,
        'http_errors' => FALSE,
        'headers' => $this->headers(),
      ]);
      // Check the HTTP status code.
      if ($response->getStatusCode() == 200) {
        // Decode the JSON response.
        $result = Json::decode($response->getBody());
        if ($result['total_count'] == 0) {
          $this->output()->writeln("No Records.");
          $this->loggerFactory->info("No Records.");
          return;
        }
        // Check if 'items' key exists in the response.
        if (isset($result['items'])) {
          // Process products.
          $this->processProducts($result['items'], $result['total_count'], $offset);
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
   * Process products.
   *
   * @param array $items
   *   The items array.
   * @param int $total_count
   *   Total count of result.
   * @param int $offset
   *   Max 9999, Min 0, Default 0.
   */
  protected function processProducts(array $items, int $total_count, int $offset) : void {
    $start = time();
    $count = count($items);
    $up_count = 0;
    $fail_count = 0;
    $success_count = 0;
    $fail_items = [];
    $success_items = [];
    $fail_up_items = [];
    $success_up_items = [];
    $fail_up_count = 0;
    $success_up_count = 0;
    $langcodes = array_keys($this->languageManager->getLanguages());

    // Exclude Board & Batten.
    $pids = ['7pmsmfvsknrm'];
    // Create only parent products.
    foreach ($items as $item) {
      if (!in_array($item['product_id'], $pids)) {
        if ($item['parent_product'] == NULL && !$this->checkExistingProduct($item['product_id'])) {
          $product = Product::create($this->fieldsArray($this->getProduct($item['product_id']), 'product', 'en-gb'));
          $product->save();
          if ($product->id()) {
            $product->path->pathauto = PathautoState::CREATE;
            foreach ($langcodes as $langcode) {
              if (!$product->hasTranslation($langcode)) {
                $productTranslate = Product::create($this->fieldsArray($this->getProduct($item['product_id']), 'product', $langcode));
                $product->addTranslation($langcode, $productTranslate->toArray());
              }
            }
            $product->save();
            $success_items[$item['product_id']] = $item['name'];
            $this->logger()->success(dt('Created @name product', ['@name' => $item['name']]));
            $success_count++;
          }
          else {
            $fail_items[$item['product_id']] = $item['name'];
            $fail_count++;
            $this->logger()->error(dt('Failed to create @name product', ['@name' => $item['name']]));
          }
        }
      }
      // Update exiting product.
      if ($item['parent_product'] == NULL && $this->checkExistingProduct($item['product_id'])) {
        /** @var \Drupal\commerce_product\Entity\Product $product **/
        $product = $this->entityTypeManager->getStorage('commerce_product')->load($this->checkExistingProduct($item['product_id']));
        foreach ($langcodes as $langcode) {
          foreach ($this->fieldsArray($this->getProduct($item['product_id']), 'product', $langcode) as $field => $values) {
            if ($product->hasTranslation($langcode)) {
              $product = $product->getTranslation($langcode);
              $product->set($field, $values);
            }
            else {
              $product = $product->addTranslation($langcode);
            }
          }
        }
        $product->save();
        if ($product->id()) {
          $success_up_items[$item['product_id']] = $item['name'];
          $this->logger()->success(dt('Updated @name product', ['@name' => $item['name']]));
          $success_up_count++;
        }
        else {
          $fail_up_items[$item['product_id']] = $item['name'];
          $fail_up_count++;
          $this->logger()->error(dt('Failed to update @name product', ['@name' => $item['name']]));
        }
        $up_count++;
      }
      $offset++;
    }
    // Check for more then 100.
    if ($offset < $total_count) {
      $this->createProducts($offset, 100);
    }
    $this->logger()->success(dt('Created @success_count out of @total_count products', [
      '@success_count' => $success_count,
      '@total_count' => $count,
    ]));
    $this->loggerFactory->info('Created @success_count out of @total_count products. Details: @array', [
      '@success_count' => $success_count,
      '@total_count' => $count,
      '@array' => json_encode($success_items, TRUE),
    ]);
    if ($fail_count) {
      $this->logger()->error(dt('Failed to create @fail_count out of @total_count products', [
        '@fail_count' => $fail_count,
        '@total_count' => $fail_count,
      ]));
      $this->loggerFactory->info('Failed to create @fail_count out of @total_count products. Details: @array', [
        '@fail_count' => $fail_count,
        '@total_count' => $count,
        '@array' => json_encode($fail_items, TRUE),
      ]);
    }
    $this->logger()->success(dt('Updated @success_count out of @up_count products.', [
      '@success_count' => $success_up_count,
      '@up_count' => $up_count,
    ]));
    $this->loggerFactory->info('Updated @success_count out of @total_count products. Details: @array', [
      '@success_count' => $success_up_count,
      '@total_count' => $up_count,
      '@array' => json_encode($success_up_items, TRUE),
    ]);
    if ($fail_up_count) {
      $this->logger()->error(dt('Failed to update @fail_count out of @total_count products.', [
        '@fail_count' => $fail_up_count,
        '@total_count' => $up_count,
      ]));
      $this->loggerFactory->info('Failed to update @fail_count out of @total_count products. Details: @array', [
        '@fail_count' => $fail_up_count,
        '@total_count' => $up_count,
        '@array' => json_encode($fail_up_items, TRUE),
      ]);
    }

    $this->output()->writeln("Execution time: " . (time() - $start) . " seconds");
  }

  /**
   * Create store.
   */
  protected function createStore() : void {
    $store = $this->entityTypeManager->getStorage('commerce_store')->loadByProperties(['type' => 'online']);
    // The store's address.
    $address = [
      'country_code' => 'GB',
      'address_line1' => '123 Street Drive',
      'locality' => 'Beverly Hills',
      'administrative_area' => 'CA',
      'postal_code' => 'W1A 0AX',
    ];

    // Check if store already exits.
    if (!empty($store) && reset($store)->id()) {
      $this->output()->writeln("Store Already Exits.");
      return;
    }
    // Create store.
    $store = Store::create([
      'type' => 'online',
      'uid' => 1,
      'name' => 'MillBoard',
      'mail' => 'admin@example.com',
      'address' => $address,
      'default_currency' => 'USD',
      'billing_countries' => ['GB'],
    ]);
    $store->save();
    if ($store->id()) {
      $this->loggerFactory->info('MillBoard Store Created.');
      $this->logger()->success("MillBoard Store Created.");
    }
    else {
      $this->loggerFactory->info('Failed to Create Store.');
      $this->logger()->error('Failed to Create Store.');
    }
  }

  /**
   * Check product that already exist in Drupal.
   *
   * @param string $id
   *   Widen collective product id.
   *
   * @return int|bool
   *   Product exit or not.
   */
  protected function checkExistingProduct(string $id) : ?int {
    // Product that already exist in Drupal.
    $productId = $this->connection
      ->select('commerce_product__field_product_id', 'p')
      ->fields('p', ['entity_id'])
      ->condition('p.field_product_id_value', $id)
      ->execute()
      ->fetchField();
    return $productId ? $productId : FALSE;
  }

}
