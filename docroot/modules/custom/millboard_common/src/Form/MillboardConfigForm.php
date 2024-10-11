<?php

namespace Drupal\millboard_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a configuration form for the Millboard settings.
 */
class MillboardConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['millboard_common.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'millboard_common_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('millboard_common.settings');

    $form['key_distributor'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Key Distributor'),
      '#default_value' => $config->get('key_distributor'),
      '#description' => $this->t('Enter key distributor information.'),
    ];

    $form['approved_installer'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Approved Installer'),
      '#default_value' => $config->get('approved_installer'),
      '#description' => $this->t('Enter approved installer information.'),
    ];

    $form['premier_distributor'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Premier Distributor'),
      '#default_value' => $config->get('premier_distributor'),
      '#description' => $this->t('Enter premier distributor information.'),
    ];
    $form['hide_price'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide price'),
      '#default_value' => $config->get('hide_price'),
      '#description' => $this->t('Hide price information.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('millboard_common.settings');
    $config->set('key_distributor', $form_state->getValue('key_distributor'));
    $config->set('approved_installer', $form_state->getValue('approved_installer'));
    $config->set('premier_distributor', $form_state->getValue('premier_distributor'));
    $config->set('hide_price', $form_state->getValue('hide_price'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
