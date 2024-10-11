<?php

namespace Drupal\millboard_google_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Config form Google Analytics settings.
 */
class GoogleAnalyticsConfiguration extends ConfigFormBase {

  /**
   * The language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'millboard_google_analytics.settings';

  /**
   * The constructor for the class.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'millboard_google_analytics_config_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $languages = $this->languageManager->getLanguages();
    $form['google_analytics_ids'] = [
      '#type' => 'details',
      '#title' => $this->t('Google Analytics Ids'),
      '#description' => $this->t('The google analytics IDs for different sites.'),
    ];
    foreach ($languages as $language) {
      $form['google_analytics_ids'][$language->getName()] = [
        '#type' => 'details',
        '#title' => $language->getName(),
        '#tree' => TRUE,
      ];
      $form['google_analytics_ids'][$language->getName()]['id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('ID'),
        '#description' => $this->t('Please enter the GA ID for the @language site.', ['@language' => $language->getName()]),
        '#default_value' => $config->get('google_analytics_id')[$language->getName()] ?? NULL,
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $languages = $this->languageManager->getLanguages();
    foreach ($languages as $language) {
      $config->set('google_analytics_id.' . $language->getName(), ($form_state->getValue($language->getName())['id']));
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
