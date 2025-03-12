<?php

namespace Drupal\google_places\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Google Places API key.
 */
class GooglePlacesConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['google_places.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_places_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('google_places.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Places API Key'),
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('Enter your Google Places API Key.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('google_places.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
