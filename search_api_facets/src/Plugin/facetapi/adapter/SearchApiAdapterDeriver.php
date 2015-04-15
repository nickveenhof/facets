<?php
/**
 * Search api adapter deriver.
 */

namespace Drupal\search_api_facets\Plugin\facetapi\adapter;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\search_api\Entity\Index;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;


class SearchApiAdapterDeriver implements ContainerDeriverInterface {

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
   * Creates a new class instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the fetcher.
   * @param string $base_plugin_id
   *   The base plugin ID for the plugin ID.
   *
   * @return static
   *   Returns an instance of this fetcher.
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
   * Returns the definition of a derivative plugin.
   *
   * @param string $derivative_id
   *   The derivative id. The id must uniquely identify the derivative within a
   *   given base plugin, but derivative ids can be reused across base plugins.
   * @param mixed $base_plugin_definition
   *   The definition of the base plugin from which the derivative plugin
   *   is derived. It is maybe an entire object or just some array, depending
   *   on the discovery mechanism.
   *
   * @return array
   *   The full definition array of the derivative plugin, typically a merge of
   *   $base_plugin_definition with extra derivative-specific information. NULL
   *   if the derivative doesn't exist.
   */
  public function getDerivativeDefinition($derivative_id, $base_plugin_definition) {
    $derivatives = $this->getDerivativeDefinitions($base_plugin_definition);
    return isset($derivatives[$derivative_id]) ? $derivatives[$derivative_id] : NULL;
  }

  /**
   * Returns the definition of all derivatives of a base plugin.
   *
   * @param array $base_plugin_definition
   *   The definition array of the base plugin.
   *
   * @return array
   *   An array of full derivative definitions keyed on derivative id.
   *
   * @see getDerivativeDefinition()
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $base_plugin_id = $base_plugin_definition['id'];

    // Create derivatives based on indexes.
    if (!isset($this->derivatives[$base_plugin_id])) {
      $plugin_derivatives = array();
      $properties = array(
        'status' => TRUE,
      );
      foreach ($this->getEntityManager()->getStorage('search_api_index')->loadByProperties($properties) as $index_id => $index) {
        // We only support index entity types at the moment.
        $plugin_derivatives[$index_id] = array(
            'id' => $base_plugin_id . PluginBase::DERIVATIVE_SEPARATOR . $index_id,
            'index' => $index,
            'label' => $index->label(),
            'description' => $this->t(
              'Provides %entity_type searcher for facet api.',
              array('%entity_type' => $index->label())
            ),
          ) + $base_plugin_definition;
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