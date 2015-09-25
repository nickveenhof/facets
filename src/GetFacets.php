<?php
/**
 * @file
 * Contains \Drupal\facetapi\GetFacets.
 */

namespace Drupal\facetapi;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\facetapi\Entity\Facet;
use Drupal\search_api\DataType\DataTypePluginManager;
use Drupal\search_api\Entity\Index;

/**
 * Class getFacets
 */
class GetFacets {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The data type plugin manager.
   *
   * @var \Drupal\search_api\DataType\DataTypePluginManager
   */
  protected $dataTypePluginManager;

  /**
   * Constructs an IndexFieldsForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * Gets all search api fields that are exposed as facets.
   *
   * @return array
   */
  public function getFacets() {
    $search_indexes = $this->entityManager->getStorage('search_api_index')->loadMultiple();
    $facets = $this->entityManager->getStorage('facetapi_facet')->loadMultiple();

    $facet_info = [];

    /** @var Index $index */
    foreach ($search_indexes as $index) {
      foreach ($index->getDatasources() as $datasource_id => $datasource) {
        $fields = $index->getFieldsByDatasource($datasource_id, FALSE);
        foreach ($fields as $field) {

          /** @var Facet $facet_info */
          foreach ($facets as $facet_info) {
            if ($facet_info->getFieldIdentifier() == $field->getFieldIdentifier()) {
              $properties = array(
                'name' => $field->getLabel(),
                'label' => $field->getLabel(),
                'field_identifier' => $field->getFieldIdentifier(),
                'query_type_name' => 'search_api_term',
                'searcher_name' => "searcher",
              );

              $facet = Facet::create($properties);
              $facet_info[$index->getServer()->getBackendId()][$field->getLabel()] = $facet;
            }
          }
        }
      }

    }

    return $facet_info;
  }

}
