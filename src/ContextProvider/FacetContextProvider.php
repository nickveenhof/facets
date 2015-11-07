<?php

/**
 * @file
 * Contains \Drupal\facetapi\ContextProvider\FacetContextProvider.
 */

namespace Drupal\facetapi\ContextProvider;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
/**
 *
 */
class FacetContextProvider implements ContextProviderInterface {

  use StringTranslationTrait;

  protected $facetStorage;

  /**
   * Create a new instance of the context provider.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->facetStorage = $entity_type_manager->getStorage('facetapi_facet');
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
      $context = new Context(new ContextDefinition('entity:facetapi_facet'), $facet);
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

    /** @var \Drupal\facetapi\FacetInterface $facet */
    foreach ($facets as $facet) {
      $context = new Context(
        new ContextDefinition('entity:facetapi_facet', $facet->label()),
        $facet
      );
      $contexts[$facet->uuid()] = $context;
    }

    return $contexts;
  }

}
