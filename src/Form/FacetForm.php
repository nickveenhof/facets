<?php

/**
 * @file
 * Contains \Drupal\facetapi\Form\FacetForm.
 */

namespace Drupal\facetapi\Form;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for creating and editing search servers.
 */
class FacetForm extends EntityForm {

  /**
   * The server storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;


  protected $contextPluginManager;

  /**
   * Constructs a ServerForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\search_api\Backend\BackendPluginManager $backend_plugin_manager
   *   The backend plugin manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->storage = $entity_manager->getStorage('facetapi_facet');
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
   * Retrieves the server storage controller.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The server storage controller.
   */
  protected function getStorage() {
    return $this->storage ?: \Drupal::service('entity.manager')->getStorage('facetapi_facet');
  }

  /**
   * Returns the datasource plugin manager.
   *
   * @return \Drupal\search_api\Datasource\DatasourcePluginManager
   *   The datasource plugin manager.
   */
  protected function getContextPluginManager() {
    return $this->contextPluginManager ?: \Drupal::service('plugin.manager.facetapi.context');
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

    /** @var \Drupal\facetapi\FacetInterface $server */
    $facet = $this->getEntity();

    // Set the page title according to whether we are creating or editing the
    // server.
    if ($facet->isNew()) {
      $form['#title'] = $this->t('Add facet');
    }
    else {
      $form['#title'] = $this->t('Edit facet %label', array('%label' => $facet->label()));
    }

    $this->buildEntityForm($form, $form_state, $facet);

    return $form;
  }

  /**
   * Builds the form for the basic server properties.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   *   The server that is being created or edited.
   */
  public function buildEntityForm(array &$form, FormStateInterface $form_state, FacetInterface $facet) {
    $form['#attached']['library'][] = 'search_api/drupal.search_api.admin_css';

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
        'exists' => array($this->getStorage(), 'load'),
        'source' => array('name'),
      ),
    );
    $form['status'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Only enabled facets can be displayed.'),
      '#default_value' => $facet->status(),
    );

    $context_options = [];
    foreach ($this->getContextPluginManager()->getDefinitions() as $context_id => $definition) {
      $context_options[$context_id] = !empty($definition['label']) ? $definition['label'] : $context_id;
    }
    $form['contexts'] = [
      '#type' => 'select',
      '#title' => $this->t('Contexts'),
      '#description' => $this->t('Select one or more contexts this facet will react on.'),
      '#options' => $context_options,
      '#default_value' => $facet->getContextIds(),
      '#multiple' => TRUE,
      '#required' => TRUE,
      '#ajax' => [
        'trigger_as' => ['name' => 'contexts_configure'],
        'callback' => '::buildAjaxDatasourceConfigForm',
        'wrapper' => 'facet-api-contexts-config-form',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];

    $form['context_configs'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'facet-api-contexts-config-form',
      ],
      '#tree' => TRUE,
    ];

    $form['context_config_configure_button'] = [
      '#type' => 'submit',
      '#name' => 'datasourcepluginids_configure',
      '#value' => $this->t('Configure'),
      '#limit_validation_errors' => [['datasources']],
      '#submit' => ['::submitAjaxDatasourceConfigForm'],
      '#ajax' => [
        'callback' => '::buildAjaxDatasourceConfigForm',
        'wrapper' => 'search-api-datasources-config-form',
      ],
      '#attributes' => ['class' => ['js-hide']],
    ];

    $this->buildContextForm($form, $form_state, $facet);
  }

  /**
   * Builds the configuration forms for all selected datasources.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The index begin created or edited.
   */
  public function buildContextForm(array &$form, FormStateInterface $form_state, FacetInterface $facet) {
    /**
     * @var FacetApiContext $context
     */
    foreach ($facet->getContextIds() as $context_id => $context) {
      // @todo Create, use and save SubFormState already here, not only in
      //   validate(). Also, use proper subset of $form for first parameter?
      if ($config_form = $datasource->buildConfigurationForm(array(), $form_state)) {
        $form['context_configs'][$context_id]['#type'] = 'details';
        $form['context_configs'][$context_id]['#title'] = $this->t('Configure the %datasource datasource', array('%datasource' => $context->getPluginDefinition()['label']));
        $form['context_configs'][$context_id]['#open'] = $facet->isNew();

        $form['context_configs'][$context_id] += $config_form;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    /** @var \Drupal\search_api\FacetInterface $facet */
    $facet = $this->getEntity();

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\facetapi\FacetInterface $server */
    $facet = $this->getEntity();

    return $facet;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Only save the server if the form doesn't need to be rebuilt.
    if (!$form_state->isRebuilding()) {
      try {
        $facet = $this->getEntity();
        $facet->save();
        drupal_set_message($this->t('The facet was successfully saved.'));
        $form_state->setRedirect('entity.facetapi_facet.canonical', array('facetapi_facet' => $facet->id()));
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
