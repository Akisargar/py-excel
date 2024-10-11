<?php

namespace Drupal\millboard_pim_integration\Commands;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\millboard_pim_integration\FieldsTraits;
use Drupal\millboard_pim_integration\MillboardTermTraits;
use Drupal\millboard_pim_integration\MillboardWidenTraits;
use Drupal\millboard_pim_integration\ProductTraits;
use Drush\Commands\DrushCommands;
use GuzzleHttp\ClientInterface;

/**
 * Drush commands for Millboard product variations.
 */
class MillboardProductVariationCommand extends DrushCommands {
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
   * Constructs a new MillboardProductVariationCommand instance.
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
   * Create product variations from the PIM.
   *
   * @param int $offset
   *   Max 9999, Min 0, Default 0.
   * @param int $limit
   *   Max 100, Min 0, Default 10.
   *
   * @usage pim:create-product-variations
   *   Create products.
   *
   * @command pim:create-product-variations
   * @aliases pim-cpv
   */
  public function createProductVariations($offset = 0, $limit = 100) : void {
    $url = $this->apiUrl() . '/products/search';
    $filter = [["type" => "exclude_parents"]];
    $body = json_encode([
      "offset" => $offset,
      "limit" => $limit,
      "filters" => $filter,
    ]);
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
          // Process product variations.
          $this->processProductVariations($result['items'], $result['total_count'], $offset);
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
   * Process product variations.
   *
   * @param array $items
   *   The items array.
   * @param int $total_count
   *   Total count of result.
   * @param int $offset
   *   Max 9999, Min 0, Default 0.
   */
  protected function processProductVariations(array $items, int $total_count, int $offset) {
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

    // Create only product variations.
    foreach ($items as $item) {
      // Exclude Board & Batten variations.
      $pids = ['tvlccflhsqzb', 'ds6splj62flp', 'cnkbjfvwpj7f', 'mrzxhgxm9g2s'];
      // Create only parent products.
      if ($item['parent_product'] && !in_array($item['product_id'], $pids)) {
        if (!$this->checkExistingProductVariation($item['product_id'])) {
          $product_var = ProductVariation::create($this->fieldsArray($this->getProduct($item['product_id']), 'variation', 'en-gb'));
          $product_var->save();
          if ($product_var->id()) {
            foreach ($langcodes as $langcode) {
              $product_var_translate = ProductVariation::create($this->fieldsArray($this->getProduct($item['product_id']), 'variation', $langcode));
              if (!$product_var->hasTranslation($langcode)) {
                $product_var->addTranslation($langcode, $product_var_translate->toArray());
              }
            }
            $product_var->save();
          }
          $product = $this->entityTypeManager->getStorage('commerce_product')->loadByProperties([
            'type' => 'millboard_products',
            'field_product_id' => $item['parent_product']['parent_product_id'],
          ]);
          /** @var \Drupal\commerce_product\Entity\Product $product **/
          $product = reset($product);
          $product->addVariation($product_var);
          $product->save();
          if ($product->id()) {
            $success_items[$item['product_id']] = $item['name'];
            $this->logger()->success(dt('Created @name product variation.', ['@name' => $item['name']]));
            $success_count++;
          }
          else {
            $fail_items[$item['product_id']] = $item['name'];
            $fail_count++;
            $this->logger()->error(dt('Failed to create @name product variation.', ['@name' => $item['name']]));
          }
        }
      }
      else {
        $fail_items[$item['product_id']] = $item['name'];
        $fail_count++;
        $this->logger()->notice(dt('Failed to create @name product variation.', ['@name' => $item['name']]));
      }
      // Update exiting product variations.
      if ($this->checkExistingProductVariation($item['product_id'])) {
        /** @var \Drupal\commerce_product\Entity\ProductVariation $variation **/
        $variation = $this->entityTypeManager->getStorage('commerce_product_variation')->load($this->checkExistingProductVariation($item['product_id']));
        $variation->set('sku', $item['sku']);
        foreach ($langcodes as $langcode) {
          foreach ($this->fieldsArray($this->getProduct($item['product_id']), 'variation', $langcode) as $field => $values) {
            if ($variation->hasTranslation($langcode)) {
              $variation = $variation->getTranslation($langcode);
              $variation->set($field, $values);
            }
            else {
              $variation = $variation->addTranslation($langcode);
            }
          }
        }
        $variation->save();
        if ($variation->id()) {
          $success_up_items[$item['product_id']] = $item['name'];
          $this->logger()->success(dt('Updated @name product variation.', ['@name' => $item['name']]));
          $success_up_count++;
        }
        else {
          $fail_up_items[$item['product_id']] = $item['name'];
          $fail_up_count++;
          $this->logger()->error(dt('Failed to update @name product variation.', ['@name' => $item['name']]));
        }
        $up_count++;
      }
      $offset++;
    }
    if ($offset < $total_count) {
      $this->createProductVariations($offset, 100);
    }
    $this->logger()->success(dt('Created @success_count out of @total_count product variations', [
      '@success_count' => $success_count,
      '@total_count' => $count,
    ]));
    $this->loggerFactory->info('Created @success_count out of @total_count product variations. Details: @array', [
      '@success_count' => $success_count,
      '@total_count' => $count,
      '@array' => json_encode($success_items, TRUE),
    ]);
    if ($fail_count) {
      $this->logger()->error(dt('Failed to create @fail_count out of @total_count product variations', [
        '@fail_count' => $fail_count,
        '@total_count' => $count,
      ]));
      $this->loggerFactory->info('Failed to create @fail_count out of @total_count product variations. Details: @array', [
        '@fail_count' => $fail_count,
        '@total_count' => $count,
        '@array' => json_encode($fail_items, TRUE),
      ]);
    }
    $this->logger()->success(dt('Updated @success_count out of @total_count product variations', [
      '@success_count' => $success_up_count,
      '@total_count' => $up_count,
    ]));
    $this->loggerFactory->info('Updated @success_count out of @total_count product variations. Details: @array', [
      '@success_count' => $success_up_count,
      '@total_count' => $up_count,
      '@array' => json_encode($success_up_items, TRUE),
    ]);
    if ($fail_up_count) {
      $this->logger()->success(dt('Updated @fail_count out of @total_count product variations', [
        '@fail_count' => $fail_up_count,
        '@total_count' => $up_count,
      ]));
      $this->loggerFactory->info('Failed to update @fail_count out of @total_count product variations. Details: @array', [
        '@fail_count' => $fail_up_count,
        '@total_count' => $up_count,
        '@array' => json_encode($fail_up_items, TRUE),
      ]);
    }
    $this->output()->writeln("Execution time: " . (time() - $start) . " seconds");
  }

  /**
   * Check product variation that already exist in Drupal.
   *
   * @param string $id
   *   Widen collective variation id.
   *
   * @return int|bool
   *   Product variation exit or not.
   */
  protected function checkExistingProductVariation(string $id) : ?int {
    // Product variation that already exist in Drupal.
    $variationId = $this->connection
      ->select('commerce_product_variation__field_product_id', 'pv')
      ->fields('pv', ['entity_id'])
      ->condition('pv.field_product_id_value', $id)
      ->execute()
      ->fetchField();
    return $variationId ? $variationId : FALSE;
  }

}
