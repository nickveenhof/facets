<?php

/**
 * @file
 * Contains \Drupal\facets\Form\FacetSourceEditForm.
 */

namespace Drupal\facets\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\Entity\FacetSource;

/**
 * Provides a form for editing facet sources.
 *
 * Configuration saved trough this form is specific for a facet source and can
 * be used by all facets on this facet source.
 */
class FacetSourceEditForm extends EntityForm {

  /**
   * Constructs a FacetSourceEditForm.
   */
  public function __construct() {
    $facet_source_storage = \Drupal::entityTypeManager()->getStorage('facets_facet_source');

    // Make sure we remove colons from the source id, those are disallowed in
    // the entity id.
    $source_id = $this->getRequest()->get('source_id');
    $source_id = str_replace(':', '__', $source_id);

    $facet_source = $facet_source_storage->load($source_id);

    if ($facet_source instanceof FacetSource) {
      $this->setEntity($facet_source);
    }
    else {

      // We didn't have a facet source config entity yet for this facet source
      // plugin, so we create it on the fly.
      $facet_source = new FacetSource(
        [
          'id' => $source_id,
          'name' => $this->getRequest()->get('source_id'),
        ],
        'facets_facet_source'
      );
      $facet_source->save();
      $this->setEntity($facet_source);
    }

    $this->setModuleHandler(\Drupal::moduleHandler());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facet_source_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\facets\FacetSourceInterface $facet_source */
    $facet_source = $this->getEntity();

    // Filter key setting.
    $form['filterKey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filter key'),
      '#size' => 20,
      '#maxlength' => 255,
      '#default_value' => $facet_source->getFilterKey(),
      '#description' => $this->t(
        'The key used in the url to identify the facet source.
        When using multiple facet sources you should make sure each facet source has a different filter key.'
      ),
    ];

    // The parent's form build method will add a save button.
    return parent::buildForm($form, $form_state);
  }

}
