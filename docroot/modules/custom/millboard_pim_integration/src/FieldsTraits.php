<?php

namespace Drupal\millboard_pim_integration;

use Drupal\acquia_dam\Entity\MediaSourceField;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductAttributeValue;
use Drupal\media\Entity\Media;

/**
 * Helpers for fields.
 */
trait FieldsTraits {
  use MillboardTermTraits;

  /**
   * Creating fields array.
   *
   * @param array $item
   *   Widen response item.
   * @param string $type
   *   Product/variation.
   * @param string $langcode
   *   The language code.
   *
   * @return array
   *   Fields array.
   */
  protected function fieldsArray(array $item, string $type, string $langcode) : array {
    // Load term of product type by product type id.
    $productTypeId = reset(\Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadByProperties(['field_product_type_id' => $item['product_type']['product_type_id']]));
    // Product categories.
    $productCategories = $this->getProductCategoryIds(reset($item['product_categories']));
    // Fields array.
    $fieldArr = [
      'type' => 'millboard_products',
      'title' => $item['name'],
      'field_product_id' => $item['product_id'],
      'uid' => 1,
      'field_product_type' => [$productTypeId->id()],
      'field_product_category' => $productCategories,
      'changed' => strtotime($item['last_updated_timestamp']),
      'created' => strtotime($item['created_date']),
    ];
    // Product variation sku and price.
    if ($type === 'variation') {
      $fieldArr['sku'] = $item['sku'];
      $fieldArr['price'] = new Price('0.00', 'USD');
    }
    else {
      $fieldArr['field_sku'] = $item['sku'];
    }

    foreach ($item['attributes'] as $attribute) {
      $pimAttributes = $this->pimAttributes();
      if (array_key_exists('kzrqvtzgzg9x', $pimAttributes) && $attribute['attribute_id'] == 'kzrqvtzgzg9x') {
          $temp = $attribute['values'];
      }
      if (array_key_exists($attribute['attribute_id'], $pimAttributes) && $attribute['values']) {
        $pimAttribute = $pimAttributes[$attribute['attribute_id']];
        if ($pimAttributes[$attribute['attribute_id']]['import'] == TRUE && empty($pimAttribute['commerce_attribute']) && empty($pimAttribute['asset_type']) && empty($pimAttribute['vocabulary']) && empty($pimAttribute['lang_code']) && $pimAttribute['field'] != 'status') {
          $fieldArr[$pimAttribute['field']] = $attribute['values'][0];
          if (isset($pimAttribute['format'])) {
            $fieldArr[$pimAttribute['field']] = [];
            $fieldArr[$pimAttribute['field']]['value'] = $attribute['values'][0];
            $fieldArr[$pimAttribute['field']]['format'] = $pimAttribute['format'];
          }
        }
        // Drupal commerce attributes.
        if ($pimAttributes[$attribute['attribute_id']]['import'] && isset($pimAttribute['commerce_attribute']) && $type === 'variation') {
          $fieldArr[$pimAttribute['field']] = $this->checkExistingAttributeId($attribute['values'][0], $pimAttribute['commerce_attribute']);
          if ($langcode === 'fr-fr') {
            if ($pimAttribute['commerce_attribute'] == 'color_fr') {
              $fieldArr['title'] = $attribute['values'][0];
            }
          }
          else {
            if ($pimAttribute['commerce_attribute'] == 'colour') {
              $fieldArr['title'] = $attribute['values'][0];
            }
          }
        }
        // Material vocabulary.
        if ($pimAttributes[$attribute['attribute_id']]['import'] && isset($pimAttribute['vocabulary'])) {
          $fieldArr[$pimAttribute['field']] = [$this->getTidByName($attribute['values'][0], $pimAttribute['vocabulary'])];
        }
        // DAM assets.
        if ($pimAttributes[$attribute['attribute_id']]['import'] && isset($pimAttribute['asset_type'])) {
          $fieldArr[$pimAttribute['field']] = $this->getMediaIds($attribute['values'], $pimAttribute['asset_type']);
        }

       if($pimAttributes[$attribute['attribute_id']] == 'kzrqvtzgzg9x') {
         $che = $pimAttributes[$attribute['attribute_id']];
        }
   
        // RRP Fields.
        if ($pimAttributes[$attribute['attribute_id']]['import'] && isset($pimAttribute['lang_code'])) {
          if ($langcode == $pimAttribute['lang_code'] && $pimAttribute['field'] != 'status') {
            $fieldArr[$pimAttribute['field']] = $attribute['values'][0];
            if (isset($pimAttribute['format'])) {
              $fieldArr[$pimAttribute['field']] = [];
              $fieldArr[$pimAttribute['field']]['value'] = $attribute['values'][0];
              $fieldArr[$pimAttribute['field']]['format'] = $pimAttribute['format'];
            }
            else {
              $fieldArr[$pimAttribute['field']] = [];
              $fieldArr[$pimAttribute['field']]['value'] = $attribute['values'][0];
            }
          }
          if ($langcode == $pimAttribute['lang_code'] && $pimAttribute['field'] == 'status' && strtolower($attribute['values'][0]) == 'no') {
            $fieldArr[$pimAttribute['field']] = FALSE;
          }
        }
      }
    }

    if($item["product_id"] == 'qmkrq6mlzprb'){
      $fieldArr = $fieldArr;
    }

    return $fieldArr;
  }

  /**
   * Get product category ids.
   *
   * @param array $categories
   *   Product categories.
   *
   * @return array
   *   Term ids
   */
  protected function getProductCategoryIds(array $categories) : array {
    $tids = [];
    if (empty($categories)) {
      return [];
    }
    // Load term of product category by product category id.
    array_walk_recursive($categories, function ($value, $key) use (&$tids) {
      if ($key == 'product_category_id') {
        $term = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadByProperties([
          'field_product_category_id' => $value,
          'vid' => 'product_category',
        ]);
        $tids[] = reset($term)->id();
      }
    });
    return $tids;
  }

  /**
   * Get media IDs.
   *
   * @param array $asset_ids
   *   DAM media asset ids.
   * @param string $bundle
   *   DAM media bundle.
   *
   * @return array
   *   Media ids.
   */
  protected function getMediaIds(array $asset_ids, string $bundle) {
    $mids = [];

    foreach ($asset_ids as $asset_id) {
      // Check if media already exists.
      if ($this->checkExistingAssets($asset_id)) {
        $mids[] = $this->checkExistingAssets($asset_id);
      }
      else {
        if ($bundle == 'acquia_dam_image_video_asset') {
          $bundle = 'acquia_dam_image_asset';
          $client = \Drupal::service('acquia_dam.client.factory')->getSiteClient();
          $assetType = $client->getAsset($asset_id)['metadata']['fields']['asset_type'][0];
          if ($assetType && strtolower($assetType) == 'film') {
            $bundle = 'acquia_dam_video_asset';
          }
        }
        // Create media.
        $media = Media::create([
          'bundle' => $bundle,
          MediaSourceField::SOURCE_FIELD_NAME => [
            'asset_id' => $asset_id,
          ],
        ]);
        $media->save();

        if ($media) {
          $mids[] = $media->id();
        }
      }
    }
    return $mids;
  }

  /**
   * Check assets that already exist in Drupal.
   *
   * @param string $id
   *   Assets id.
   *
   * @return int|null
   *   Media id.
   */
  protected function checkExistingAssets(string $id) : ?int {
    // Assets that already exist in Drupal.
    $entity_id = \Drupal::database()
      ->select('media__acquia_dam_asset_id', 'ad')
      ->fields('ad', ['entity_id'])
      ->condition('ad.acquia_dam_asset_id_asset_id', $id)
      ->execute()
      ->fetchField();

    return $entity_id ? $entity_id : NULL;
  }

  /**
   * Check term that already exist in Drupal.
   *
   * @param string $name
   *   The colour name.
   * @param string $attribute
   *   The attribute name.
   *
   * @return int
   *   Attribute name id.
   */
  protected function checkExistingAttributeId(string $name, string $attribute) : int {
    $productAttribute = \Drupal::service('entity_type.manager')
      ->getStorage('commerce_product_attribute_value')->loadByProperties([
        'attribute' => $attribute,
        'name' => $name,
      ]);
    if (!empty($productAttribute)) {
      return reset($productAttribute)->id();
    }
    else {
      $productAttribute = ProductAttributeValue::create([
        'attribute' => $attribute,
        'name' => $name,
      ]);
      $productAttribute->save();
      return $productAttribute->id();
    }
  }

  /**
   * Widen collective product attributes.
   *
   * @return array
   *   Product attributes.
   */
  protected function pimAttributes() : array {
    $attributes = [
      "dscvrzbxf7jn" => [
        "name" => "Active",
        'import' => FALSE,
      ],
      "bkqnm8jdbsk8" => [
        "name" => "Boards per m2",
        'field' => 'field_boards_per_m2',
        'import' => TRUE,
      ],
      "zsqbpvxdxgrv" => [
        "name" => "Boards per ft2",
        'field' => 'field_boards_per_ft2',
        'import' => TRUE,
      ],
      "hbvfrpbmtblz" => [
        "name" => "Body Copy (US)",
        'field' => 'field_body_copy',
        'import' => TRUE,
        'lang_code' => 'en-us',
        'format' => 'filtered_html',
      ],
      "xqqdt2bd6r7b" => [
        "name" => "Body Copy (FR)",
        'field' => 'field_body_copy',
        'import' => TRUE,
        'lang_code' => 'fr-fr',
        'format' => 'filtered_html',
      ],
      "kzrqvtzgzg9x" => [
        "name" => "Body Copy",
        'field' => 'field_body_copy',
        'import' => TRUE,
      ],
      "slcwgjxttrhk" => [
        "name" => "CAD File Downloads",
        'field' => 'field_cad_file_downloads',
        'import' => TRUE,
        "asset_type" => "acquia_dam_pdf_asset",
      ],
      "ln9bcrkhcqmx" => [
        "name" => "Certification Documents",
        'field' => 'field_certification_documents',
        'import' => TRUE,
        "asset_type" => "acquia_dam_pdf_asset",
      ],
      "pwmmmm5rsl5q" => [
        "name" => "Colour",
        'field' => 'attribute_colour',
        'commerce_attribute' => 'colour',
        'import' => TRUE,
      ],
      'sdrdfr9q9glk' => [
        "name" => 'Colour (FR)',
        "field" => 'attribute_color_fr',
        "commerce_attribute" => 'color_fr',
        'import' => TRUE,
      ],
      "9jk5h6jlfb7r" => [
        "name" => "Commercial Warranty",
        'field' => 'field_commercial_warranty',
        'import' => TRUE,
      ],
      "95h9qrtqfvbp" => [
        "name" => "Country of Origin",
        'field' => 'field_country_of_origin',
        'import' => TRUE,
      ],
      "f9qcbqbk9j2l" => [
        "name" => "Dimensions (inches)",
        'field' => 'field_dimensions_inches',
        'import' => TRUE,
      ],
      "nch7vmhnkgzs" => [
        "name" => "Dimensions (mm)",
        'field' => 'field_dimensions',
        'import' => TRUE,
      ],
      "xkmcr2qmplsv" => [
        "name" => "EAN",
        'field' => 'field_ean',
        'import' => TRUE,
      ],
      "vvr9gbwlhrrd" => [
        "name" => "Fire Rating",
        'field' => 'field_fire_rating',
        'import' => TRUE,
      ],
      "sm5zt6xgdkpq" => [
        "name" => "Fixings per Board",
        'field' => 'field_fixings_per_board',
        'import' => TRUE,
      ],
      "l5bsbhb9smsw" => [
        "name" => "Installation Guide",
        'field' => 'field_installation_guide',
        "asset_type" => "acquia_dam_pdf_asset",
        'import' => TRUE,
      ],
      "kggjhq2zrs8l" => [
        "name" => "Installation Guide (FR)",
        'field' => 'field_installation_guide_fr',
        "asset_type" => "acquia_dam_pdf_asset",
        'import' => TRUE,
      ],
      "kqhsb5lg5vxd" => [
        "name" => "Installed Width (inches)",
        "field" => 'attribute_installed_width_inches',
        "commerce_attribute" => 'installed_width_inches',
        'import' => TRUE,
      ],
      "dnzp6sxjtq2v" => [
        "name" => "Installed Width (mm)",
        'field' => 'attribute_installed_width',
        'commerce_attribute' => 'installed_width',
        'import' => TRUE,
      ],
      "qljspvmnxb2r" => [
        "name" => "Intro Copy",
        'field' => 'field_intro_copy',
        'import' => TRUE,
        'lang_code' => 'en-gb',
      ],
      "qljspvmnxb2r" => [
        "name" => "Intro Copy",
        'field' => 'field_intro_copy',
        'import' => TRUE,
        'lang_code' => 'en-ie',
      ],
      "wcn5t9jvmxsj" => [
        "name" => "Intro Copy",
        'field' => 'field_intro_copy',
        'import' => TRUE,
        'lang_code' => 'en-us',
      ],
      "spfmrwtqdwdd" => [
        "name" => "Intro Copy (FR)",
        "field" => "field_intro_copy",
        'import' => TRUE,
        'lang_code' => 'fr-fr',
      ],
      "xdztvqzcdvsq" => [
        "name" => "Lifestyle Images",
        'field' => 'field_lifestyle_images',
        'import' => TRUE,
        "asset_type" => "acquia_dam_image_asset",
      ],
      "mbzp7nrgmvhz" => [
        "name" => "Lifestyle Videos",
        'field' => 'field_lifestyle_videos',
        'import' => TRUE,
        "asset_type" => "acquia_dam_video_asset",
      ],
      "xvlvwb7vhfnc" => [
        "name" => "Material",
        'field' => 'field_material',
        'import' => TRUE,
        'vocabulary' => 'material',
      ],
      "ddvwhsdnb5bh" => [
        "name" => "Pack Size",
        'field' => 'field_pack_size',
        'import' => TRUE,
      ],
      "vnprsfccfpqd" => [
        "name" => "PDP Inspiration (8 max.)",
        'field' => 'field_pdp_inspiration',
        'import' => TRUE,
        "asset_type" => "acquia_dam_image_asset",
      ],
      "xhpn8vftd9cm" => [
        "name" => "PDP Product Images (4 max.)",
        'field' => 'field_pdp_product_images',
        'import' => TRUE,
        "asset_type" => "acquia_dam_image_video_asset",
      ],
      "l2qcpwmbnxwt" => [
        "name" => "Product Images",
        'field' => 'field_product_images',
        'import' => TRUE,
        "asset_type" => "acquia_dam_image_asset",
      ],
      "lhnjrnqtrswv" => [
        "name" => "Recycled Content",
        'field' => 'field_recycled_content',
        'import' => TRUE,
      ],
      "v7dsbsglrpx2" => [
        "name" => "Region",
        'import' => FALSE,
      ],
      "sztjdznbbfwh" => [
        "name" => "Residential Warranty",
        'field' => 'field_residential_warranty',
        'import' => TRUE,
      ],
      "pvxpkzgd6t9h" => [
        "name" => "RRP per m2 (£)",
        'field' => 'field_rrp_per_m2',
        'import' => TRUE,
        'lang_code' => 'en-gb',
      ],
      "gg872pnfbkdj" => [
        "name" => "RRP per m2 (IRE)",
        'field' => 'field_rrp_per_m2',
        'import' => TRUE,
        'lang_code' => 'en-ie',
      ],
      "sbs8tkpkx5zk" => [
        "name" => "RRP per m2 (USD)",
        'field' => 'field_rrp_per_m2',
        'import' => TRUE,
        'lang_code' => 'en-us',
      ],
      "c6snpcqc2smt" => [
        "name" => "RRP per Unit (£)",
        'field' => 'field_rrp_per_unit',
        'import' => TRUE,
        'lang_code' => 'en-gb',
      ],
      "swnkgs5sfffq" => [
        "name" => "RRP per Unit (IRE)",
        'field' => 'field_rrp_per_unit',
        'import' => TRUE,
        'lang_code' => 'en-ie',
      ],
      "cgpdrvhvbp5d" => [
        "name" => "RRP per Unit (USD)",
        'field' => 'field_rrp_per_unit',
        'import' => TRUE,
        'lang_code' => 'en-us',
      ],
      "sbs8tkpkx5zk" => [
        "name" => "MSRP per ft2 (USD)",
        "field" => 'field_msrp_per_ft2_usd',
        'import' => TRUE,
        'lang_code' => 'en-us',
      ],
      "cgpdrvhvbp5d" => [
        "name" => "MSRP per Unit (USD)",
        "field" => 'field_msrp_per_unit_usd',
        'import' => TRUE,
        'lang_code' => 'en-us',
      ],
      "2wmcdlzzd8lj" => [
        "name" => 'RRP per Unit (€)',
        "field" => 'field_rrp_per_unit_eu',
        "import" => TRUE,
        "lang_code" => 'fr-fr',
      ],
      "mpqptbkvfgww" => [
        "name" => 'RRP per m2 (€)',
        "field" => 'field_rrp_per_m2_eu',
        "import" => TRUE,
        "lang_code" => "fr-fr",
      ],
      "dbq2tp6rsnf8" => [
        "name" => "Size",
        'field' => 'field_size',
        'import' => TRUE,
      ],
      "jdgrzmqztspz" => [
        "name" => "Size Ounce",
        'field' => 'field_size_ounce',
        'import' => TRUE,
        'lang_code' => 'en-us',
      ],
      "kcnrv6mgd2mh" => [
        "name" => "Specification Sheet",
        'field' => 'field_specification_sheet',
        'import' => TRUE,
        "asset_type" => "acquia_dam_pdf_asset",
      ],
      "zhfkc9fd5kqg" => [
        'name' => "Specification Sheet (FR)",
        'field' => 'field_specification_sheet_fr',
        'import' => TRUE,
        "asset_type" => "acquia_dam_pdf_asset",
        "lang_code" => 'fr-fr',
      ],
      "j7vt5xqqzwmq" => [
        "name" => "Studio Imagery",
        'field' => 'field_studio_imagery',
        'import' => TRUE,
        "asset_type" => "acquia_dam_image_asset",
      ],
      "qwhdcttmqg6n" => [
        "name" => "Style",
        'field' => 'field_style',
        'import' => TRUE,
      ],
      "ns7rmctbfzgh" => [
        "name" => "Swatch",
        'field' => 'field_swatch',
        'import' => TRUE,
        "asset_type" => "acquia_dam_image_asset",
      ],
      "tcdrsvqfpq5l" => [
        "name" => "Training Videos",
        'field' => 'field_training_videos',
        'import' => TRUE,
        "asset_type" => "acquia_dam_video_asset",
      ],
      "wdbqjhnzxhmw" => [
        "name" => "Visual Width (inches)",
        'import' => FALSE,
      ],
      "rjxbdcplplcz" => [
        "name" => "Visual Width (mm)",
        'field' => 'attribute_visual_width',
        'commerce_attribute' => 'visual_width',
        'import' => TRUE,
      ],
      "v6dk8mfg5b6v" => [
        "name" => "Weight (kg)",
        'field' => 'field_weight',
        'import' => TRUE,
      ],
      "ncchgsfjwd5q" => [
        "name" => "Weight (lb)",
        'field' => 'field_weight_lb',
        'import' => TRUE,
      ],
      "zdfvnjgqkmv5" => [
        "name" => "Weight per Board",
        'field' => 'field_weight_per_board',
        'import' => TRUE,
      ],
      "xspvxjkmcj2p" => [
        "name" => "Width (inches)",
        'field' => 'attribute_width_inches',
        'commerce_attribute' => 'width_inches',
        'import' => TRUE,
      ],
      "rwqzbpfk5rps" => [
        "name" => "Width (mm)",
        'field' => 'attribute_width',
        'commerce_attribute' => 'width',
        'import' => TRUE,
      ],
      "zdfvnjgqkmv5" => [
        "name" => "Weight per m2 (kg)",
        'field' => 'field_weight_per_m2_kg',
        'import' => TRUE,
      ],
      "gl59tchdvz2g" => [
        "name" => "Weight per ft2 (lb)",
        "field" => 'field_weight_per_ft2_lb',
        'import' => TRUE,
      ],
      "8xd7ftjrrlfm" => [
        "name" => "Sample SKU",
        'field' => 'field_sample_sku',
        'import' => TRUE,
      ],
      "kbm8gszfvrgx" => [
        "name" => "Available in GB",
        'field' => 'status',
        'import' => TRUE,
        'lang_code' => 'en-gb',
      ],
      "9jl5hshhmssw" => [
        "name" => "Available in IE",
        'field' => 'status',
        'import' => TRUE,
        'lang_code' => 'en-ie',
      ],
      "xfqvlzcdn2kp" => [
        "name" => "Available in US",
        'field' => 'status',
        'import' => TRUE,
        'lang_code' => 'en-us',
      ],
    ];
    return $attributes;
  }

}
