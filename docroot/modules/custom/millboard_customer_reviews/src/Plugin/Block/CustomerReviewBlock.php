<?php

namespace Drupal\millboard_customer_reviews\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Customer Review block.
 *
 * @Block(
 *   id = "customer_review_block",
 *   admin_label = @Translation("Customer Review block using FEEFO")
 * )
 */
class CustomerReviewBlock extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build(): array {
    return [
      '#theme' => 'customer_review__block',
    ];
  }

}
