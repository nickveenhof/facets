<?php

/**
 * @file
 * Contains \Drupal\facetapi\Form\FacetSourceConfigForm.
 */

namespace Drupal\facetapi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for creating and editing facet source config overrides.
 */
class FacetSourceConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facet_source_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['facetapi.facet_source'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('facetapi.facet_source');

    $form['description'] = [
      '#markup' => $this->t('Saving this form will create a configuration override for this specific facet source. Not doing so will make sure that Facet API uses the default settings. This is an advanced feature and unless you are fully aware of why you\'re creating this configuration. With a default configuration of Facet API you can leave the default configuration.')
    ];

    $facet_source_id = $this->getRequest()->get('source_id');
    $form['facet_source_id'] = [
      '#type' => 'hidden',
      '#value' => $facet_source_id,
    ];

    // Filter key setting.
    $form['filter_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filter key'),
      '#size' => 20,
      '#maxlength' => 255,
      '#default_value' => (!is_null($config->get('overrides.' . $facet_source_id . '.filter_key')) ? $config->get('overrides.' . $facet_source_id . '.filter_key') : $config->get('filter_key')),
      '#description' => $this->t('They key used for filtering in the URL, defaults to f. You should change this to something else if you expect to have multiple facet sources on one page.'),
    ];

    // The parent's form build method will add a save button.
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The parent's submitForm adds a message.
    parent::submitForm($form, $form_state);

    $configKey = 'overrides.' . $form_state->getValue('facet_source_id') . '.';

    $config = $this->config('facetapi.facet_source');
    $config->set($configKey. 'filter_key', $form_state->getValue('filter_key'));
    $config->save();
  }
}
