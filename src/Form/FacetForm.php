<?php

/**
 * @file
 * Contains \Drupal\facetapi\Form\FacetForm.
 */

namespace Drupal\facetapi\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetApiException;
use Drupal\facetapi\FacetSource\FacetSourcePluginManager;
use Drupal\facetapi\Processor\ProcessorInterface;
use Drupal\facetapi\Processor\ProcessorPluginManager;
use Drupal\facetapi\Widget\WidgetPluginManager;
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
   * The plugin manager for widgets.
   *
   * @var \Drupal\facetapi\Widget\WidgetPluginManager
   */
  protected $widgetPluginManager;

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
   * @var array
   */
  protected $facetSourcePlugins;

  /**
   * Constructs a FacetForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\facetapi\Widget\WidgetPluginManager $widgetPluginManager
   *   The plugin manager for widgets.
   * @param \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facetSourcePluginManager
   *   The plugin manager for facet sources.
   * @param \Drupal\facetapi\Processor\ProcessorPluginManager $processorPluginManager
   *   The plugin manager for processors.
   */
  public function __construct(EntityManagerInterface $entity_manager, WidgetPluginManager $widgetPluginManager, FacetSourcePluginManager $facetSourcePluginManager, ProcessorPluginManager $processorPluginManager) {
    $this->facetStorage = $entity_manager->getStorage('facetapi_facet');
    $this->widgetPluginManager = $widgetPluginManager;
    $this->facetSourcePluginManager = $facetSourcePluginManager;
    $this->processorPluginManager = $processorPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    /** @var \Drupal\facetapi\Widget\WidgetPluginManager $widget_plugin_manager */
    $widget_plugin_manager = $container->get('plugin.manager.facetapi.widget');

    /** @var \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facet_source_plugin_manager */
    $facet_source_plugin_manager = $container->get('plugin.manager.facetapi.facet_source');

    /** @var \Drupal\facetapi\Processor\ProcessorPluginManager $processor_plugin_manager */
    $processor_plugin_manager = $container->get('plugin.manager.facetapi.processor');

    return new static($entity_manager, $widget_plugin_manager, $facet_source_plugin_manager, $processor_plugin_manager);
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
   * Returns the widget plugin manager.
   *
   * @return \Drupal\facetapi\Widget\WidgetPluginManager
   *   The widget plugin manager.
   */
  protected function getWidgetPluginManager() {
    return $this->widgetPluginManager ?: \Drupal::service('plugin.manager.facetapi.widget');
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
    // facet.
    if ($facet->isNew()) {
      $form['#title'] = $this->t('Add facet');
    }
    else {
      $form['#title'] = $this->t('Edit facet %label', ['%label' => $facet->label()]);
    }

    $this->buildEntityForm($form, $form_state, $facet);

    return $form;
  }

  /**
   * Builds the form for editing and creating a facet.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   *   The server that is being created or edited.
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

    $facet_sources = $this->getFacetSources();
    $form['facet_source'] = [
      '#type' => 'select',
      '#title' => $this->t('Facet source'),
      '#description' => $this->t('Select the source where this facet can find its fields.'),
      '#options' => $facet_sources,
      '#default_value' => $facet->getFacetSource(),
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
      '#limit_validation_errors' => [['facet_source']],
      '#submit' => ['::submitAjaxFacetSourceConfigForm'],
      '#ajax' => [
        'callback' => '::buildAjaxFacetSourceConfigForm',
        'wrapper' => 'facetapi-facet-sources-config-form',
      ],
      '#attributes' => ['class' => ['js-hide']],
    ];
    $this->buildFacetSourceConfigForm($form, $form_state, $facet);

    $widget_options = [];
    foreach ($this->getWidgetPluginManager()->getDefinitions() as $widget_id => $definition) {
      $widget_options[$widget_id] = !empty($definition['label']) ? $definition['label'] : $widget_id;
    }
    $form['widget'] = [
      '#type' => 'select',
      '#title' => $this->t('Widget'),
      '#description' => $this->t('Select the widget used for displaying this facet.'),
      '#options' => $widget_options,
      '#default_value' => $facet->getWidget(),
      '#required' => TRUE,
      '#ajax' => [
        'trigger_as' => ['name' => 'widget_configure'],
        'callback' => '::buildAjaxWidgetConfigForm',
        'wrapper' => 'facet-api-widget-config-form',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];
    $form['widget_configs'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'facet-api-widget-config-form',
      ],
      '#tree' => TRUE,
    ];
    $form['widget_configure_button'] = [
      '#type' => 'submit',
      '#name' => 'widget_configure',
      '#value' => $this->t('Configure widget'),
      '#limit_validation_errors' => [['widget']],
      '#submit' => ['::submitAjaxWidgetConfigForm'],
      '#ajax' => [
        'callback' => '::buildAjaxWidgetConfigForm',
        'wrapper' => 'facet-api-widget-config-form',
      ],
      '#attributes' => ['class' => ['js-hide']],
    ];
    $this->buildWidgetConfigForm($form, $form_state, $facet);


    $form['processor_configs'] = [
      '#type' => 'details',
      '#title' => $this->t('Processors'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];
    foreach ($this->getProcessorPluginManager()->getDefinitions() as $id => $definition) {
      $form['processor_configs'][$id] = [
        '#type' => 'details',
        '#title' => $this->t('Processor: %id', ['%id' => $id]),
        '#open' => TRUE,
      ];
      $form['processor_configs'][$id]['processor_id'] = [
        '#title' => 'id',
        '#type' => 'hidden',
        '#value' => $id
      ];

      $form['processor_configs'][$id]['settings'] = [
        '#type' => 'container'
      ];

      /** @var ProcessorInterface $build_processor */
      $build_processor = $this->getProcessorPluginManager()->createInstance($id);
      $form['processor_configs'][$id]['settings'] = $build_processor->buildConfigurationForm($form, $form_state, $facet);
    }

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Only enabled facets can be displayed.'),
      '#default_value' => $facet->status(),
    ];
  }

  /**
   * Handles changes to the selected widgets.
   */
  public function buildAjaxWidgetConfigForm(array $form, FormStateInterface $form_state) {
    return $form['widget_configs'];
  }

  /**
   * Builds the configuration forms for all selected widgets.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   *   The facet begin created or edited.
   */
  public function buildWidgetConfigForm(array &$form, FormStateInterface $form_state, FacetInterface $facet) {
    $widget = $facet->getWidget();

    if (!is_null($widget) && $widget !== '') {
      $widget_instance = $this->getWidgetPluginManager()->createInstance($widget);
      // @todo Create, use and save SubFormState already here, not only in
      //   validate(). Also, use proper subset of $form for first parameter?
      $config = $this->config('facetapi.facet.' . $facet->id());
      if ($config_form = $widget_instance->buildConfigurationForm([], $form_state, ($config instanceof Config) ? $config : null )) {
        $form['widget_configs']['#type'] = 'details';
        $form['widget_configs']['#title'] = $this->t('Configure the %widget widget', ['%widget' => $this->getWidgetPluginManager()->getDefinition($widget)['label']]);
        $form['widget_configs']['#open'] = $facet->isNew();

        $form['widget_configs'] += $config_form;
      }
      else {
        $form['widget_configs']['#type'] = 'container';
        $form['widget_configs']['#open'] = true;
        $form['widget_configs']['widget_information_dummy'] = [
          '#type' => 'hidden',
          '#value' => '1',
          '#default_value' => '1',
        ];
      }
    }
  }

  /**
   * Builds the configuration forms for all possible facet sources.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   *   The facet being updated or created.
   */
  public function buildFacetSourceConfigForm(array &$form, FormStateInterface $form_state, FacetInterface $facet) {
    $facet_source_id = $facet->getFacetSource();

    if (!is_null($facet_source_id) && $facet_source_id !== '') {
      /** @var \Drupal\facetapi\FacetSource\FacetSourceInterface $facet_source */
      $facet_source = $this->getFacetSourcePluginManager()->createInstance($facet_source_id);

      if ($config_form = $facet_source->buildConfigurationForm([], $form_state, $facet, $facet_source)) {
        $form['facet_source_configs'][$facet_source_id]['#type'] = 'details';
        $form['facet_source_configs'][$facet_source_id]['#title'] = $this->t('Configure %plugin facet source', ['%plugin' => $facet_source->getPluginDefinition()['label']]);
        $form['facet_source_configs'][$facet_source_id]['#open'] = TRUE;

        $form['facet_source_configs'][$facet_source_id] += $config_form;
      }
    }
  }

  /**
   * Form submission handler for the facet source subform.
   */
  public function submitAjaxFacetSourceConfigForm($form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Handles changes to the selected facet sources.
   */
  public function buildAjaxFacetSourceConfigForm(array $form, FormStateInterface $form_state) {
    return $form['facet_source_configs'];
  }

  /**
   * Form submission handler for the widget subform.
   */
  public function submitAjaxWidgetConfigForm($form, FormStateInterface $form_state) {
    $form_state->setRebuild();
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

    // Make sure the field identifier is copied from within the facet source
    // config to the facet object and saved there.
    $facet_source = $form_state->getValue('facet_source');
    $field_identifier = $form_state->getValue('facet_source_configs')[$facet_source]['field_identifier'];

    $facet->setFieldIdentifier($field_identifier);
    $facet->save();

    // Ensure that the caching of the view display is disabled, so the search
    // correctly returns the facets. This is a temporary fix, until the cache
    // metadata is correctly stored on the facet block.
    list(, $view_id, $display) = explode(':', $facet_source);
    $view = Views::getView($view_id);

    $display = &$view->storage->getDisplay($display);
    $display['display_options']['cache']['type'] = 'none';
    $view->storage->save();

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
        $form_state->setRedirect('facetapi.overview');
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
    $form_state->setRedirect('entity.facetapi_facet.delete_form', ['facetapi_facet' => $this->getEntity()->id()]);
  }

  /**
   * Gets the possible sources for faceted searches.
   *
   * @return array
   */
  protected function getFacetSources() {
    $sources = [];
    $facet_definitions = $this->facetSourcePluginManager->getDefinitions();

    foreach ($facet_definitions as $definition) {
      $sources[$definition['id']] = $definition['label'];
    }

    return $sources;
  }

}
