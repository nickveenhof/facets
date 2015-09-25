<?php

/**
 * @file
 * Contains \Drupal\node\ContextProvider\NodeRouteContext.
 */

namespace Drupal\facetapi\ContextProvider;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class FacetContextProvider implements ContextProviderInterface {

  use StringTranslationTrait;

  protected $facetStorage;

  public function __construct(EntityManagerInterface $entityManager) {
    $this->facetStorage = $entityManager->getStorage('facetapi_facet');
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
      $context = new Context(new ContextDefinition('entity:facetapi_facet'));
      $context->setContextValue($facet);
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
    foreach ($facets as $facet) {
      $context = new Context(
        new ContextDefinition('entity:facetapi_facet', $facet->label())
      );
      $context->setContextValue($facet);
      $contexts[$facet->uuid()] = $context;
    }

    return $contexts;
  }


}
