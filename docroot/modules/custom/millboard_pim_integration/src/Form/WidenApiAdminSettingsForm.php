<?php

namespace Drupal\millboard_pim_integration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for the Widen API integration.
 */
class WidenApiAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'millboard_widen_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['millboard_pim_integration.widen_api_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load the configuration.
    $config = $this->configFactory->get('millboard_pim_integration.widen_api_settings');

    // Widen API URL field.
    $form['widen_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widen API URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('widen_api_url') ?: "",
      '#description' => $this->t('Please enter the Widen API URL.'),
    ];

    // Bearer Token field.
    $form['widen_api_token'] = [
      '#type' => 'password',
      '#title' => $this->t('Bearer Token'),
      '#required' => TRUE,
      '#default_value' => $config->get('widen_api_token') ?: "",
      '#description' => $this->t('Please enter the Bearer Token.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the form values.
    $values = $form_state->getValues();

    // Save the configuration.
    $this->configFactory->getEditable('millboard_pim_integration.widen_api_settings')
      ->set('widen_api_url', $values['widen_api_url'])
      ->set('widen_api_token', $values['widen_api_token'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
