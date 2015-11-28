<?php

/**
 * @file
 * Contains \Drupal\core_search_facetapi\Plugin\Search\NodeSearchFacets.
 */

namespace Drupal\core_search_facetapi\Plugin\Search;

use Drupal\Core\Config\Config;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Plugin\Search\NodeSearch;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles searching for node entities using the Search module index.
 */
class NodeSearchFacets extends NodeSearch {

  protected $facetSource;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Connection $database,
    EntityManagerInterface $entity_manager,
    ModuleHandlerInterface $module_handler,
    Config $search_settings,
    LanguageManagerInterface $language_manager,
    RendererInterface $renderer,
    AccountInterface $account = NULL,
    $facet_source_plugin_manager,
    $request_stack) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $database, $entity_manager, $module_handler, $search_settings, $language_manager, $renderer, $account);
    /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    if ($search_page = $request_stack->getMasterRequest()->attributes->get('entity')) {
      /** @var \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facet_source_plugin_manager */
      $this->facetSource = $facet_source_plugin_manager->createInstance('core_node_search:' . $search_page->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  static public function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('module_handler'),
      $container->get('config.factory')->get('search.settings'),
      $container->get('language_manager'),
      $container->get('renderer'),
      $container->get('current_user'),
      $container->get('plugin.manager.facetapi.facet_source'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function searchFormAlter(array &$form, FormStateInterface $form_state) {
    $parameters = $this->getParameters();
    $keys = $this->getKeywords();
    $used_advanced = !empty($parameters[self::ADVANCED_FORM]);
    if ($used_advanced) {
      $f = isset($parameters['f']) ? (array) $parameters['f'] : array();
      $defaults =  $this->parseAdvancedDefaults($f, $keys);
    }
    else {
      $defaults = array('keys' => $keys);
    }

    $form['basic']['keys']['#default_value'] = $defaults['keys'];

    // Add advanced search keyword-related boxes.
    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Advanced search'),
      '#attributes' => array('class' => array('search-advanced')),
      '#access' => $this->account && $this->account->hasPermission('use advanced search'),
      '#open' => $used_advanced,
    );
    $form['advanced']['keywords-fieldset'] = array(
      '#type' => 'fieldset',
      '#title' => t('Keywords'),
    );

    $form['advanced']['keywords'] = array(
      '#prefix' => '<div class="criterion">',
      '#suffix' => '</div>',
    );

    $form['advanced']['keywords-fieldset']['keywords']['or'] = array(
      '#type' => 'textfield',
      '#title' => t('Containing any of the words'),
      '#size' => 30,
      '#maxlength' => 255,
      '#default_value' => isset($defaults['or']) ? $defaults['or'] : '',
    );

    $form['advanced']['keywords-fieldset']['keywords']['phrase'] = array(
      '#type' => 'textfield',
      '#title' => t('Containing the phrase'),
      '#size' => 30,
      '#maxlength' => 255,
      '#default_value' => isset($defaults['phrase']) ? $defaults['phrase'] : '',
    );

    $form['advanced']['keywords-fieldset']['keywords']['negative'] = array(
      '#type' => 'textfield',
      '#title' => t('Containing none of the words'),
      '#size' => 30,
      '#maxlength' => 255,
      '#default_value' => isset($defaults['negative']) ? $defaults['negative'] : '',
    );

    $form['advanced']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Advanced search'),
      '#prefix' => '<div class="action">',
      '#suffix' => '</div>',
      '#weight' => 100,
    );

    // Only add node types and language filters when a facet source has facets.
    if (!$this->facetSource->hasFacets()) {
      // Add node types.
      $types = array_map(array('\Drupal\Component\Utility\Html', 'escape'), node_type_get_names());
      $form['advanced']['types-fieldset'] = array(
        '#type' => 'fieldset',
        '#title' => t('Types'),
      );
      $form['advanced']['types-fieldset']['type'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Only of the type(s)'),
        '#prefix' => '<div class="criterion">',
        '#suffix' => '</div>',
        '#options' => $types,
        '#default_value' => isset($defaults['type']) ? $defaults['type'] : array(),
      );

      $form['advanced']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Advanced search'),
        '#prefix' => '<div class="action">',
        '#suffix' => '</div>',
        '#weight' => 100,
      );

      // Add languages.
      $language_options = array();
      $language_list = $this->languageManager->getLanguages(LanguageInterface::STATE_ALL);
      foreach ($language_list as $langcode => $language) {
        // Make locked languages appear special in the list.
        $language_options[$langcode] = $language->isLocked() ? t('- @name -', array('@name' => $language->getName())) : $language->getName();
      }
      if (count($language_options) > 1) {
        $form['advanced']['lang-fieldset'] = array(
          '#type' => 'fieldset',
          '#title' => t('Languages'),
        );
        $form['advanced']['lang-fieldset']['language'] = array(
          '#type' => 'checkboxes',
          '#title' => t('Languages'),
          '#prefix' => '<div class="criterion">',
          '#suffix' => '</div>',
          '#options' => $language_options,
          '#default_value' => isset($defaults['language']) ? $defaults['language'] : array(),
        );
      }
    }
  }

}

