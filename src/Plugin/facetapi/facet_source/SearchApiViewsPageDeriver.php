<?php

/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facet_api\facet_source\SearchApiViewsPageDeriver.
 */

namespace Drupal\facetapi\Plugin\facetapi\facet_source;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives a facet source plugin definition for every search api view.
 *
 * @see \Drupal\facetapi\Plugin\facetapi\facet_source\SearchApiViewsPage
 */
class SearchApiViewsPageDeriver implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * List of derivative definitions.
   *
   * @var array
   */
  protected $derivatives = array();

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $deriver = new static();

    /** @var $entity_manager \Drupal\Core\Entity\EntityManagerInterface */
    $entity_manager = $container->get('entity.manager');
    $deriver->setEntityManager($entity_manager);

    /** @var \Drupal\Core\StringTranslation\TranslationInterface $translation */
    $translation = $container->get('string_translation');
    $deriver->setStringTranslation($translation);

    return $deriver;
  }

  /**
   * Retrieves the entity manager.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   *   The entity manager.
   */
  public function getEntityManager() {
    return $this->entityManager ?: \Drupal::entityManager();
  }

  /**
   * Sets the entity manager.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   *
   * @return $this
   */
  public function setEntityManager(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinition($derivative_id, $base_plugin_definition) {
    $derivatives = $this->getDerivativeDefinitions($base_plugin_definition);
    return isset($derivatives[$derivative_id]) ? $derivatives[$derivative_id] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $base_plugin_id = $base_plugin_definition['id'];

    if (!isset($this->derivatives[$base_plugin_id])) {
      $plugin_derivatives = array();
      /** @var \Drupal\Core\Entity\EntityStorageInterface $views_storage */
      $views_storage = $this->entityManager->getStorage('view');
      $all_views = $views_storage->loadMultiple();

      /** @var \Drupal\views\Entity\View $view */
      foreach ($all_views as $view) {
        // Hardcoded usage of search api views, for now.
        if (strpos($view->get('base_table'), 'search_api_index') !== FALSE) {
          $displays = $view->get('display');
          foreach ($displays as $name => $display_info) {
            if($display_info['display_plugin'] == "page"){
              $machine_name = $view->id() . PluginBase::DERIVATIVE_SEPARATOR . $name;

              $plugin_derivatives[$machine_name] = array(
                  'id' => $base_plugin_id . PluginBase::DERIVATIVE_SEPARATOR . $machine_name,
                  'label' => $this->t('Search api view: %view_name, display: %display_title', ['%view_name' => $view->label(), '%display_title' => $display_info['display_title']]),
                  'description' => $this->t('Provides a facet source.'),
                  'view_id' => $view->id(),
                  'view_display' => $name,
                ) + $base_plugin_definition;

              $sources[] = $this->t('Search api view: ' . $view->label() . ' display: ' . $display_info['display_title']);
            }
          }
        }
      }
      uasort($plugin_derivatives, array($this, 'compareDerivatives'));

      $this->derivatives[$base_plugin_id] = $plugin_derivatives;
    }
    return $this->derivatives[$base_plugin_id];
  }

  /**
   * Compares two plugin definitions according to their labels.
   *
   * @param array $a
   *   A plugin definition, with at least a "label" key.
   * @param array $b
   *   Another plugin definition.
   *
   * @return int
   *   An integer less than, equal to, or greater than zero if the first
   *   argument is considered to be respectively less than, equal to, or greater
   *   than the second.
   */
  public function compareDerivatives(array $a, array $b) {
    return strnatcasecmp($a['label'], $b['label']);
  }

}
