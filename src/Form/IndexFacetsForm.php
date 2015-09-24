<?php

/**
 * @file
 * Contains \Drupal\facetapi\Form\IndexFacetsForm.
 */

namespace Drupal\facetapi\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for configuring the processors of a search index.
 */
class IndexFacetsForm extends EntityForm {

  /**
   * The index being configured.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $entity;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs an IndexFacetsForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, \Drupal\facetapi\Adapter\AdapterPluginManager $adapter_manager) {
    $this->entityManager = $entity_manager;
    $this->adapterManager = $adapter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');
    /** @var \Drupal\facetapi\Adapter\AdapterPluginManager $adapter_manager */
    $adapter_manager = $container->get('plugin.manager.facetapi.adapter');
    return new static($entity_manager, $adapter_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormID() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $index = $this->entity;
    if (!$index->isServerEnabled()) {
      return array('#markup' => t('Since this index is at the moment disabled, no facets can be activated.'));
    }
    if (!$index->getServer()->supportsFeature('search_api_facets')) {
      return array('#markup' => t('This index uses a server that does not support facet functionality.'));
    }

    // Instantiates adapter, loads realm.
    $adapter = $this->adapterManager->getDefinition('search_api');

    // @todo inject realmManager to load realms. Look at the DataSourceDerivative
    $realm_name = $index->getServerId() . ':' . $index->id();

    return array('#markup' => t('Bla.'));

    //$realm = $this->searcherManager->getInstance();
    /*$realm = facetapi_realm_load($realm_name);
    // @todo inject facetManager to get Facet Info from Searchers
    $facet_info = facetapi_get_facet_info($searcher);

    $form['#facetapi'] = array(
      'adapter' => $adapter,
      'realm' => $realm,
      'facet_info' => $facet_info
    );

    $form['description'] = array(
      '#prefix' => '<div class="facetapi-realm-description">',
      '#markup' => filter_xss_admin($realm['description']),
      '#suffix' => "</div>\n",
    );

    $form['performance'] = array(
      '#prefix' => '<div class="facetapi-performance-note">',
      '#markup' => t('For performance reasons, you should only enable facets that you intend to have available to users on the search page.'),
      '#suffix' => "</div>\n",
    );

    $form['table'] = array(
      '#theme' => 'facetapi_realm_settings_table',
      '#facetapi' => &$form['#facetapi'],
      'operations' => array('#tree' => TRUE),
      'weight' => array('#tree' => TRUE),
    );

    // Builds "enabled_facets" options.
    $options = $default_value = array();
    foreach ($form['#facetapi']['facet_info'] as $facet_name => $facet) {
      $settings = $adapter->getFacetSettings($facet, $realm);
      $global_settings = $adapter->getFacetSettingsGlobal($facet);

      // Builds array of operations to use in the dropbutton.
      $operations = array();
      $operations[] = array(
        'title' => t('Configure display'),
        'href' => facetapi_get_settings_path($searcher, $realm['name'], $facet_name, 'edit')
      );
      if ($facet['dependency plugins']) {
        $operations[] = array(
          'title' => t('Configure dependencies'),
          'href' => facetapi_get_settings_path($searcher, $realm['name'], $facet_name, 'dependencies')
        );
      }
      if (facetapi_filters_load($facet_name, $searcher, $realm['name'])) {
        $operations[] = array(
          'title' => t('Configure filters'),
          'href' => facetapi_get_settings_path($searcher, $realm['name'], $facet_name, 'filters')
        );
      }
      $operations[] = array(
        'title' => t('Export configuration'),
        'href' => facetapi_get_settings_path($searcher, $realm['name'], $facet_name, 'export')
      );
      if (facetapi_is_overridden($settings) || facetapi_is_overridden($global_settings)) {
        $operations[] = array(
          'title' => t('Revert configuration'),
          'href' => facetapi_get_settings_path($searcher, $realm['name'], $facet_name, 'revert')
        );
      }

      $form['table']['operations'][$facet_name] = array(
        '#theme' => 'links__ctools_dropbutton',
        '#links' => $operations,
        '#attributes' => array(
          'class' => array('inline', 'links', 'actions', 'horizontal', 'right')
        ),
      );

      // Adds weight if sortable.
      if ($realm['sortable']) {

        $form['#facetapi']['facet_info'][$facet_name]['weight'] = $settings->settings['weight'];
        $form['table']['weight'][$facet_name] = array(
          '#type' => 'select',
          '#title' => t('Weight for @title', array('@title' => $facet['label'])),
          '#title_display' => 'invisible',
          '#options' => drupal_map_assoc(range(-50, 50)),
          '#default_value' => $settings->settings['weight'],
          '#attributes' => array('class' => array('facetapi-facet-weight')),
        );
      }

      $options[$facet_name] = '';
      $default_value[$facet_name] = (!$settings->enabled) ? 0 : $facet_name;
    }

    // Sorts by the weight appended above.
    uasort($form['#facetapi']['facet_info'], 'drupal_sort_weight');

    $form['table']['enabled_facets'] = array(
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $default_value,
    );

    // Checks whether block caching is enabled, sets description accordingly.
    if (!$disabled = (module_implements('node_grants') || !variable_get('block_cache', FALSE))) {
      $description = t('Configure the appropriate cache setting for facet blocks.');
    }
    else {
      $description = t(
        'To enable block caching, visit the <a href="@performance-page">performance page</a>.',
        array('@performance-page' => url('admin/config/development/performance', array('query' => array('destination' => current_path()))))
      );
    }
    $form['block_cache'] = array(
      '#type' => 'select',
      '#access' => ('block' == $realm_name),
      '#title' => t('Block cache settings'),
      '#disabled' => $disabled,
      '#options' => array(
        DRUPAL_NO_CACHE => t('Do not cache'),
        DRUPAL_CACHE_PER_ROLE | DRUPAL_CACHE_PER_PAGE => t('Per role'),
        DRUPAL_CACHE_PER_USER | DRUPAL_CACHE_PER_PAGE => t('Per user'),
      ),
      '#default_value' => variable_get('facetapi:block_cache:' . $searcher, DRUPAL_NO_CACHE),
      '#description' => $description,
    );

    $form['actions'] = array(
      '#type' => 'actions',
      '#weight' => 20,
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save configuration'),
    );

    $form['#submit'][] = 'facetapi_realm_settings_form_submit';

    return $form;
    */
  }


}
