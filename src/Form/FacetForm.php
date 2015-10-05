<?php

/**
 * @file
 * Contains \Drupal\facetapi\Form\FacetForm.
 */

namespace Drupal\facetapi\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetApiException;
use Drupal\search_api\IndexInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for creating and editing search servers.
 */
class FacetForm extends EntityForm {

  /**
   * The facet storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $facetStorage;

  /**
   * Constructs a FacetForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->facetStorage = $entity_manager->getStorage('facetapi_facet');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');
    return new static($entity_manager);
  }

  /**
   * Retrieves the facet storage controller.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The facet storage controller.
   */
  protected function getFacetStorage() {
    return $this->facetStorage ?: \Drupal::service('entity.manager')->getStorage('facetapi_facet');
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // If the form is being rebuilt, rebuild the entity with the current form
    // values.
    if ($form_state->isRebuilding()) {
      $this->entity = $this->buildEntity($form, $form_state);
    }

    $form = parent::form($form, $form_state);

    /** @var \Drupal\facetapi\FacetInterface $facet */
    $facet = $this->getEntity();

    // Set the page title according to whether we are creating or editing the
    // server.
    if ($facet->isNew()) {
      $form['#title'] = $this->t('Add facet');
    }
    else {
      $form['#title'] = $this->t('Edit facet %label', array('%label' => $facet->label()));
    }

    $search_api_index = $form_state->get('search_api_index');
    $this->buildEntityForm($form, $form_state, $facet, $search_api_index);

    return $form;
  }

  /**
   * Builds the form for the basic server properties.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   *   The server that is being created or edited.
   * @param \Drupal\search_api\IndexInterface $search_api_index
   *   The search index we're creating a facet for.
   */
  public function buildEntityForm(array &$form, FormStateInterface $form_state, FacetInterface $facet, IndexInterface $search_api_index) {

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Facet name'),
      '#description' => $this->t('Enter the displayed name for the facet.'),
      '#default_value' => $facet->label(),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $facet->id(),
      '#maxlength' => 50,
      '#required' => TRUE,
      '#machine_name' => array(
        'exists' => array($this->getFacetStorage(), 'load'),
        'source' => array('name'),
      ),
    );

    $form['field_identifier'] = [
      '#type' => 'select',
      '#options' => $this->getIndexedFields($search_api_index),
      '#title' => $this->t('Facet field'),
      '#description' => $this->t('Choose the indexed field.'),
      '#required' => TRUE,
      '#default_value' => $facet->getFieldIdentifier()
    ];

    $form['status'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Only enabled facets can be displayed.'),
      '#default_value' => $facet->status(),
    );
  }

  /**
   * Gets all indexed fields for this search index.
   *
   * @param \Drupal\search_api\IndexInterface $search_index
   *   The search index we're creating a facet for.
   * @return array
   *   An array of all indexed fields.
   */
  protected function getIndexedFields(IndexInterface $search_api_index) {
    $indexed_fields = [];

    foreach ($search_api_index->getDatasources() as $datasource_id => $datasource) {
      $fields = $search_api_index->getFieldsByDatasource($datasource_id);
      foreach ($fields as $field) {
        $indexed_fields[$field->getFieldIdentifier()] = $field->getLabel();
      }
    }
    return $indexed_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    /** @var \Drupal\facetapi\FacetInterface $facet */
    $facet = $this->getEntity();

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\facetapi\FacetInterface $facet */
    $facet = $this->getEntity();

    return $facet;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Only save the facet if the form doesn't need to be rebuilt.
    if (!$form_state->isRebuilding()) {
      try {
        $facet = $this->getEntity();
        $facet->save();
        drupal_set_message($this->t('The facet was successfully saved.'));
        $form_state->setRedirect('entity.search_api_index.facets', array('search_api_index' => $facet->getSearchApiIndex()));
      }
      catch (FacetApiException $e) {
        $form_state->setRebuild();
        watchdog_exception('facetapi', $e);
        drupal_set_message($this->t('The facet could not be saved.'), 'error');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.facetapi_facet.delete_form', array('facetapi_facet' => $this->getEntity()->id()));
  }

}
