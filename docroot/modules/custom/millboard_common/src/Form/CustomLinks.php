<?php

namespace Drupal\millboard_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The footer social links configuration form.
 */
class CustomLinks extends ConfigFormBase {

  /**
   * The language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames(): array {
    return ['millboard_common.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'custom_links';
  }

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('millboard_common.settings');
    $languages = $this->languageManager->getLanguages();
    $social_medias = $this->socialMediaTypes();
    $form['social_media_links'] = [
      '#type' => 'details',
      '#title' => 'Social Media links',
    ];
    $form['hubspot_footer_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Hubspot Footer Form configuration'),
      '#description' => $this->t('Configuration for the hubspot form.'),
      '#weight' => '10',
    ];
    $form['footer_region_name'] = [
      '#type' => 'details',
      '#title' => $this->t('Global Footer Region Name'),
      '#description' => $this->t('Configuration for the global footer region name.'),
      '#weight' => '10',
    ];
    foreach ($languages as $language) {
      $form['social_media_links'][$language->getName()] = [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => ucfirst($language->getName()),
      ];
      $form['hubspot_footer_form'][$language->getName()] = [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => ucfirst($language->getName()),
      ];
      $form['footer_region_name'][$language->getName()] = [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => ucfirst($language->getName()),
      ];
      foreach ($social_medias as $social_media) {
        $form['social_media_links'][$language->getName()][$social_media] = [
          '#type' => 'fieldset',
          '#title' => ucfirst($social_media),
          '#description' => $this->t('Enter the @media link details to be attached in footer.', ['@media' => $social_media]),
        ];
        $form['social_media_links'][$language->getName()][$social_media]['url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('URL'),
          '#description' => $this->t('Enter the @media URL.', ['@media' => $social_media]),
          '#default_value' => $config->get('social_media_links')[$language->getName()][$social_media]['url'] ?? NULL,
        ];
        $form['social_media_links'][$language->getName()][$social_media]['aria_label'] = [
          '#type' => 'textfield',
          '#title' => $this->t('ARIA label'),
          '#description' => $this->t('Enter the @media ARIA label.', ['@media' => $social_media]),
          '#default_value' => $config->get('social_media_links')[$language->getName()][$social_media]['aria_label'] ?? NULL,
        ];
        $form['hubspot_footer_form'][$language->getName()]['region'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Region'),
          '#description' => $this->t('The region of the form.'),
          '#default_value' => $config->get('hubspot')[$language->getName()]['region'],
        ];
        $form['hubspot_footer_form'][$language->getName()]['portalId'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Portal ID'),
          '#description' => $this->t('The portal ID of the form.'),
          '#default_value' => $config->get('hubspot')[$language->getName()]['portalId'],
        ];
        $form['hubspot_footer_form'][$language->getName()]['formId'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Form ID'),
          '#description' => $this->t('The Form ID of the form.'),
          '#default_value' => $config->get('hubspot')[$language->getName()]['formId'],
        ];
        $form['footer_region_name'][$language->getName()]['region_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Region Name'),
          '#description' => $this->t('The name of the region to be displayed in the footer.'),
          '#default_value' => $config->get('footer_region_name')[$language->getName()]['region_name'] ?? 'United Kingdom',
        ];
      }
    }
    $form['order_a_sample'] = [
      '#type' => 'details',
      '#title' => $this->t('Order a Sample'),
      '#description' => $this->t('Enter Order sample page link'),
      '#weight' => '9',
    ];
    $form['order_a_sample']['order_a_sample_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $config->get('order_a_sample') ?? NULL,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('millboard_common.settings');
    $social_medias = $this->socialMediaTypes();
    $languages = $this->languageManager->getLanguages();
    foreach ($languages as $language) {
      foreach ($social_medias as $social_media) {
        $config->set('social_media_links.' . $language->getName() . '.' . $social_media . '.' . 'url', $form_state->getValue($language->getName())[$social_media]['url']);
        $config->set('social_media_links.' . $language->getName() . '.' . $social_media . '.' . 'aria_label', $form_state->getValue($language->getName())[$social_media]['aria_label']);
        $config->set('hubspot.' . $language->getName() . '.region', $form_state->getValue($language->getName())['region']);
        $config->set('hubspot.' . $language->getName() . '.portalId', $form_state->getValue($language->getName())['portalId']);
        $config->set('hubspot.' . $language->getName() . '.formId', $form_state->getValue($language->getName())['formId']);
        $config->set('footer_region_name.' . $language->getName() . '.region_name', $form_state->getValue($language->getName())['region_name']);
      }
    }
    $config->set('order_a_sample', $form_state->getValue('order_a_sample_link'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * The social media types array.
   *
   * @return array
   *   The social media type.
   */
  private function socialMediaTypes(): array {
    return ['facebook', 'twitter', 'youtube', 'instagram', 'pinterest', 'linkedin'];
  }

}
