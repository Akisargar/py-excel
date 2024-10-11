<?php

namespace Drupal\millboard_sap_configuration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The configuration form for the SAP Mail integration.
 */
class SapMailConfiguration extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'millboard_sap_configuration.settings';

  /**
   * {@inheritdoc}
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
    return 'sap_mail_configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config(static::SETTINGS);
    $mail_body = $fr_mail_body = '';
    if (isset($config->get('mail_body')['value']) && !empty($config->get('mail_body')['value'])) {
      $mail_body = $config->get('mail_body')['value'];
    }
    if ($config->get('fr_mail_body') !== NULL && !empty($config->get('fr_mail_body'))) {
      $fr_mail_body = $config->get('fr_mail_body');
    }
    $form['order_sample_mail_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Sample order Mail configuration.'),
    ];
    $form['order_sample_mail_configuration']['sap_mail'] = [
      '#type' => 'details',
      '#title' => $this->t('SAP mail configuration'),
    ];
    $form['order_sample_mail_configuration']['sap_mail']['sap_mail_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SAP E-mail id:'),
      '#default_value' => $config->get('sap_mail_id') ? $config->get('sap_mail_id') : 'website.export@Millboard.co.uk',
    ];

    $form['order_sample_mail_configuration']['sap_mail']['mail_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mail Subject'),
      '#default_value' => $config->get('mail_subject') ? $config->get('mail_subject') : $this->t('Millboard Sample Order List.'),
    ];

    $form['order_sample_mail_configuration']['sap_mail']['mail_body'] = [
      '#type' => 'text_format',
      '#title' => 'Mail Body',
      '#format' => 'full_html',
      '#default_value' => $mail_body ? $mail_body : $this->t('Please find the attached CSV.'),
    ];

    $form['order_sample_mail_configuration']['fr_order_sample'] = [
      '#type' => 'details',
      '#title' => $this->t('FR order Sample'),
    ];

    $form['order_sample_mail_configuration']['fr_order_sample']['fr_mail_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mail id'),
      '#default_value' => $config->get('fr_mail_id') ? $config->get('fr_mail_id') : NULL,
    ];

    $form['order_sample_mail_configuration']['fr_order_sample']['fr_mail_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mail Subject'),
      '#default_value' => $config->get('fr_mail_subject') ? $config->get('fr_mail_subject') : NULL,
    ];

    $form['order_sample_mail_configuration']['fr_order_sample']['fr_mail_body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Mail Body'),
      '#format' => 'full_html',
      '#default_value' => $fr_mail_body ? $fr_mail_body : $this->t('Please find the attached CSV.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::SETTINGS)
      ->set('sap_mail_id', $form_state->getValue('sap_mail_id'))
      ->set('mail_subject', $form_state->getValue('mail_subject'))
      ->set('mail_body', $form_state->getValue('mail_body')['value'])
      ->set('mail_body_format', $form_state->getValue('mail_body')['format'])
      ->set('fr_mail_id', $form_state->getValue('fr_mail_id'))
      ->set('fr_mail_subject', $form_state->getValue('fr_mail_subject'))
      ->set('fr_mail_body_format', $form_state->getValue('fr_mail_body')['format'])
      ->set('fr_mail_body', $form_state->getValue('fr_mail_body')['value'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}
