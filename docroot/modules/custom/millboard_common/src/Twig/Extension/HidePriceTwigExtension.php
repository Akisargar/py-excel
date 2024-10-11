<?php

namespace Drupal\millboard_common\Twig\Extension;

use Drupal\Core\Config\ConfigFactoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * A class providing Drupal Twig hidePrice extension.
 */
class HidePriceTwigExtension extends AbstractExtension {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a CustomTwigExtension object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('hide_price', [$this, 'getMillboardHidePrice']),
    ];
  }

  /**
   * Gets the hide price from configuration.
   *
   * @return string
   *   The hide price.
   */
  public function getMillboardHidePrice() {
    return $this->configFactory->get('millboard_common.settings')->get('hide_price') ?? 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'millboard_common.hide_price';
  }

}
