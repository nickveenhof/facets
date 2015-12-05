<?php

/**
 * @file
 * Contains \Drupal\facets\ContextProvider\FacetContextProvider.
 */

namespace Drupal\facets\ContextProvider;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A provider for the core context system for facets.
 *
 * This provider is a provider for core's context system, it makes integration
 * with blocks, panels and other layout systems easy.
 */
class FacetContextProvider implements ContextProviderInterface {

  use StringTranslationTrait;

  protected $facetStorage;

  /**
   * Creates a new instance of the context provider.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->facetStorage = $entity_type_manager->getStorage('facets_facet');
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids = []) {
    $ids = $this->facetStorage->getQuery()
      ->condition('uuid', $unqualified_context_ids, 'IN')
      ->execute();
    $contexts = [];
    foreach ($this->facetStorage->loadMultiple($ids) as $facet) {
      $context = new Context(new ContextDefinition('entity:facets_facet'), $facet);
      $contexts[$facet->uuid()] = $context;
    }
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $facets = $this->facetStorage->loadMultiple();
    $contexts = [];

    /** @var \Drupal\facets\FacetInterface $facet */
    foreach ($facets as $facet) {
      $context = new Context(
        new ContextDefinition('entity:facets_facet', $facet->label()),
        $facet
      );
      $contexts[$facet->uuid()] = $context;
    }

    return $contexts;
  }

}
