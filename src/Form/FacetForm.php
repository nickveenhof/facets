<?php

/**
 * @file
 * Contains \Drupal\facetapi\Form\FacetForm.
 */

namespace Drupal\facetapi\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager;
use Drupal\facetapi\Exception\Exception;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginManager;
use Drupal\facetapi\Processor\ProcessorPluginManager;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for creating and editing facets.
 */
class FacetForm extends EntityForm {

  /**
   * The facet storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $facetStorage;

  /**
   * The plugin manager for facet sources.
   *
   * @var \Drupal\facetapi\FacetSource\FacetSourcePluginManager
   */
  protected $facetSourcePluginManager;

  /**
   * The plugin manager for processors.
   *
   * @var \Drupal\facetapi\Processor\ProcessorPluginManager
   */
  protected $processorPluginManager;

  /**
   * The plugin manager for facet sources.
   *
   * @var \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager
   */
  protected $emptyBehaviorPluginManager;

  /**
   * Constructs a FacetForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity manager.
   * @param \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facetSourcePluginManager
   *   The plugin manager for facet sources.
   * @param \Drupal\facetapi\Processor\ProcessorPluginManager $processorPluginManager
   *   The plugin manager for processors.
   * @param \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager $emptyBehaviorPluginManager
   *   The plugin manager for empty behaviors.
   */
  public function __construct(EntityTypeManager $entity_type_manager, FacetSourcePluginManager $facetSourcePluginManager, ProcessorPluginManager $processorPluginManager, EmptyBehaviorPluginManager $emptyBehaviorPluginManager) {
    $this->facetStorage = $entity_type_manager->getStorage('facetapi_facet');
    $this->facetSourcePluginManager = $facetSourcePluginManager;
    $this->processorPluginManager = $processorPluginManager;
    $this->emptyBehaviorPluginManager = $emptyBehaviorPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManager $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    /** @var \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facet_source_plugin_manager */
    $facet_source_plugin_manager = $container->get('plugin.manager.facetapi.facet_source');

    /** @var \Drupal\facetapi\Processor\ProcessorPluginManager $processor_plugin_manager */
    $processor_plugin_manager = $container->get('plugin.manager.facetapi.processor');

    /** @var \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager $empty_behavior_plugin_manager */
    $empty_behavior_plugin_manager = $container->get('plugin.manager.facetapi.empty_behavior');

    return new static($entity_type_manager, $facet_source_plugin_manager, $processor_plugin_manager, $empty_behavior_plugin_manager);
  }

  /**
   * Gets the form entity.
   *
   * The form entity which has been used for populating form element defaults.
   * This method is defined on the \Drupal\Core\Entity\EntityFormInterface and
   * has the same contents there, we only extend to add the correct return type,
   * this makes IDE's smarter about the other places where we use
   * $this->getEntity().
   *
   * @return \Drupal\facetapi\FacetInterface
   *   The current form facet entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Retrieves the facet storage controller.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The facet storage controller.
   */
  protected function getFacetStorage() {
    return $this->facetStorage ?: \Drupal::service('entity_type.manager')->getStorage('facetapi_facet');
  }

  /**
   * Returns the facet source plugin manager.
   *
   * @return \Drupal\facetapi\FacetSource\FacetSourcePluginManager
   *   The facet source plugin manager.
   */
  protected function getFacetSourcePluginManager() {
    return $this->facetSourcePluginManager ?: \Drupal::service('plugin.manager.facetapi.facet_source');
  }

  /**
   * Returns the processor plugin manager.
   *
   * @return \Drupal\facetapi\Processor\ProcessorPluginManager
   *   The processor plugin manager.
   */
  protected function getProcessorPluginManager() {
    return $this->processorPluginManager ?: \Drupal::service('plugin.manager.facetapi.processor');
  }

  /**
   * Returns the empty behavior plugin manager.
   *
   * @return \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager
   *   The processor plugin manager.
   */
  protected function getEmptyBehaviorPluginManager() {
    return $this->emptyBehaviorPluginManager ?: \Drupal::service('plugin.manager.facetapi.empty_behavior');
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

    // Set the page title according to whether we are creating or editing the
    // facet.
    if ($this->getEntity()->isNew()) {
      $form['#title'] = $this->t('Add facet');
    }
    else {
      $form['#title'] = $this->t('Edit facet %label', ['%label' => $this->getEntity()->label()]);
    }

    $this->buildEntityForm($form, $form_state, $this->getEntity());

    return $form;
  }

  /**
   * Builds the form for editing and creating a facet.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   *   The facetapi facet entity that is being created or edited.
   */
  public function buildEntityForm(array &$form, FormStateInterface $form_state, FacetInterface $facet) {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facet name'),
      '#description' => $this->t('Enter the displayed name for the facet.'),
      '#default_value' => $facet->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $facet->id(),
      '#maxlength' => 50,
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => [$this->getFacetStorage(), 'load'],
        'source' => ['name'],
      ],
    ];

    $facet_sources = [];
    foreach ($this->getFacetSourcePluginManager()->getDefinitions() as $facet_source_id => $definition) {
      $facet_sources[$definition['id']] = !empty($definition['label']) ? $definition['label'] : $facet_source_id;
    }
    $form['facet_source_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Facet source'),
      '#description' => $this->t('Select the source where this facet can find its fields.'),
      '#options' => $facet_sources,
      '#default_value' => $facet->getFacetSourceId(),
      '#required' => TRUE,
      '#ajax' => [
        'trigger_as' => ['name' => 'facet_source_configure'],
        'callback' => '::buildAjaxFacetSourceConfigForm',
        'wrapper' => 'facetapi-facet-sources-config-form',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];
    $form['facet_source_configs'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'facetapi-facet-sources-config-form',
      ],
      '#tree' => TRUE,
    ];
    $form['facet_source_configure_button'] = [
      '#type' => 'submit',
      '#name' => 'facet_source_configure',
      '#value' => $this->t('Configure facet source'),
      '#limit_validation_errors' => [['facet_source_id']],
      '#submit' => ['::submitAjaxFacetSourceConfigForm'],
      '#ajax' => [
        'callback' => '::buildAjaxFacetSourceConfigForm',
        'wrapper' => 'facetapi-facet-sources-config-form',
      ],
      '#attributes' => ['class' => ['js-hide']],
    ];
    $this->buildFacetSourceConfigForm($form, $form_state);

    // Behavior for empty facets.
    $behavior_options = [];
    $empty_behavior = $facet->getFieldEmptyBehavior();
    foreach ($this->getEmptyBehaviorPluginManager()->getDefinitions() as $behavior_id => $definition) {
      $behavior_options[$behavior_id] = !empty($definition['label']) ? $definition['label'] : $behavior_id;
    }
    $form['empty_behavior'] = [
      '#type' => 'select',
      '#title' => t('Empty facet behavior'),
      '#default_value' => $empty_behavior ? $empty_behavior : 'none',
      '#options' => $behavior_options,
      '#description' => $this->t('The action to take when a facet has no items.'),
      '#required' => TRUE,
      '#ajax' => [
        'trigger_as' => ['name' => 'empty_behavior_configure'],
        'callback' => '::buildAjaxEmptyBehaviorConfigForm',
        'wrapper' => 'facet-api-empty-behavior-config-form',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];
    $form['empty_behavior_configs'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'facet-api-empty-behavior-config-form',
      ],
      '#tree' => TRUE,
    ];
    $form['empty_behavior_configure_button'] = [
      '#type' => 'submit',
      '#name' => 'empty_behavior_configure',
      '#value' => $this->t('Configure empty behavior'),
      '#limit_validation_errors' => [['empty_behavior']],
      '#submit' => ['::submitAjaxEmptyBehaviorConfigForm'],
      '#ajax' => [
        'callback' => '::buildAjaxEmptyBehaviorConfigForm',
        'wrapper' => 'facet-api-empty-behavior-config-form',
      ],
      '#attributes' => ['class' => ['js-hide']],
    ];
    $this->buildEmptyBehaviorConfigForm($form, $form_state);

    $form['only_visible_when_facet_source_is_visible'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the facet only when the facet source is also visible.'),
      '#description' => $this->t('If checked, the facet will only be rendered on pages where the facet source is being rendered too.  If not checked, the facet can be shown on every page.'),
      '#default_value' => $facet->getOnlyVisibleWhenFacetSourceIsVisible(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Only enabled facets can be displayed.'),
      '#default_value' => $facet->status(),
    ];
  }

  /**
   * Form submission handler for the facet source subform.
   */
  public function submitAjaxFacetSourceConfigForm($form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Form submission handler for the empty behavior subform.
   */
  public function submitAjaxEmptyBehaviorConfigForm($form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }


  /**
   * Handles changes to the selected facet sources.
   */
  public function buildAjaxFacetSourceConfigForm(array $form, FormStateInterface $form_state) {
    return $form['facet_source_configs'];
  }


  /**
   * Handles changes to the selected empty behavior.
   */
  public function buildAjaxEmptyBehaviorConfigForm(array $form, FormStateInterface $form_state) {
    return $form['empty_behavior_configs'];
  }

  /**
   * Builds the configuration forms for all possible facet sources.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   */
  public function buildFacetSourceConfigForm(array &$form, FormStateInterface $form_state) {
    $facet_source_id = $this->getEntity()->getFacetSourceId();

    if (!is_null($facet_source_id) && $facet_source_id !== '') {
      /** @var \Drupal\facetapi\FacetSource\FacetSourceInterface $facet_source */
      $facet_source = $this->getFacetSourcePluginManager()->createInstance($facet_source_id);

      if ($config_form = $facet_source->buildConfigurationForm([], $form_state, $this->getEntity(), $facet_source)) {
        $form['facet_source_configs'][$facet_source_id]['#type'] = 'details';
        $form['facet_source_configs'][$facet_source_id]['#title'] = $this->t('Configure %plugin facet source', ['%plugin' => $facet_source->getPluginDefinition()['label']]);
        $form['facet_source_configs'][$facet_source_id]['#open'] = TRUE;

        $form['facet_source_configs'][$facet_source_id] += $config_form;
      }
    }
  }

  /**
   * Builds the configuration forms for all the empty behaviors.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   */
  public function buildEmptyBehaviorConfigForm(array &$form, FormStateInterface $form_state) {
    $behavior_id = $this->getEntity()->getFieldEmptyBehavior();

    if (!is_null($behavior_id) && $behavior_id !== '') {
      $empty_behavior_instance = $this->getEmptyBehaviorPluginManager()->createInstance($behavior_id);
      if ($config_form = $empty_behavior_instance->buildConfigurationForm([], $form_state)) {
        $form['empty_behavior_configs']['#type'] = 'details';
        $form['empty_behavior_configs']['#title'] = $this->t('Configure the %behavior empty behavior', ['%behavior' => $this->emptyBehaviorPluginManager->getDefinition($behavior_id)['label']]);
        $form['empty_behavior_configs']['#open'] = $this->getEntity()->isNew();

        $form['empty_behavior_configs'] += $config_form;
      }
      else {
        $form['empty_behavior_configs']['#type'] = 'container';
        $form['empty_behavior_configs']['#open'] = TRUE;
        $form['empty_behavior_configs']['empty_behavior_information_dummy'] = [
          '#type' => 'hidden',
          '#value' => [],
          '#default_value' => '1',
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\facetapi\FacetInterface $facet */
    $facet = $this->getEntity();
    $is_new = $facet->isNew();

    if ($is_new) {
      // On facet creation, enable all locked processors by default, using their
      // default settings.
      $initial_settings = [];
      $stages = $this->getProcessorPluginManager()->getProcessingStages();
      $processors_definitions = $this->getProcessorPluginManager()->getDefinitions();

      foreach ($processors_definitions as $processor_id => $processor) {
        if (isset($processor['locked']) && $processor['locked'] == TRUE) {
          $weights = [];
          foreach($stages as $stage_id => $stage) {
            if(isset($processor['stages'][$stage_id])) {
              $weights[$stage_id] = $processor['stages'][$stage_id];
            }
          }
          $initial_settings[$processor_id] = array(
            'processor_id' => $processor_id,
            'weights' => $weights,
            'settings' => [],
          );
        }
      }
      $facet->setOption('processors', $initial_settings);
    }

    // Make sure the field identifier is copied from within the facet source
    // config to the facet object and saved there.
    $facet_source = $form_state->getValue('facet_source_id');
    $field_identifier = $form_state->getValue('facet_source_configs')[$facet_source]['field_identifier'];

    $facet->setFieldIdentifier($field_identifier);
    $facet->save();

    // Ensure that the caching of the view display is disabled, so the search
    // correctly returns the facets. This is a temporary fix, until the cache
    // metadata is correctly stored on the facet block. Only apply this when the
    // facet source type is actually something this is related to views.
    list($type,) = explode(':', $facet_source);

    if ($type === 'search_api_views') {
      list(, $view_id, $display) = explode(':', $facet_source);
    }

    if (isset($view_id)) {
      $view = Views::getView($view_id);

      $display = &$view->storage->getDisplay($display);
      $display['display_options']['cache']['type'] = 'none';
      $view->storage->save();
    }

    if ($is_new) {
      if (\Drupal::moduleHandler()->moduleExists('block')) {
        $message = $this->t('A new context for blocks is automatically created. Go to the <a href=":block_overview">Block overview page</a> and add a new "Facet block". If this is your first and only facet, just adding that block make it link to this facet, if you have addded more facets already, please make sure to select the correct Facet to render.', [':block_overview' => \Drupal::urlGenerator()->generateFromRoute('block.admin_display')]);
        drupal_set_message($message);
      }
    }

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

        // Ensure the new facet has a widget (when creating a new facet)
        // TODO: move this to the not-yet-existing add facet form, TBD: possibly fetch first widget available from widgetmanager instead of hardcoded (and store default widgetconfig in that case too).
        if(!$facet->getWidget()){
          $facet->setWidget('links');
          $facet->save();
          drupal_set_message(t('Facet %name has been created.', ['%name' => $facet->getName()]));
        }else{
          $facet->save();
          drupal_set_message(t('Facet %name has been updated.', ['%name' => $facet->getName()]));
        }

      }
      catch (Exception $e) {
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
    $form_state->setRedirect('entity.facetapi_facet.delete_form', ['facetapi_facet' => $this->getEntity()->id()]);
  }

}
