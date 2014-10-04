<?php

/**
 * @file
 * Contains \Drupal\facet_api\QueryTypeBase.
 */

namespace Drupal\facet_api;


abstract class QueryTypeBase implements QueryTypeInterface {

  /**
   * The adapter associated with facet being queried.
   *
   * @var FacetapiAdapter
   */
  protected $adapter;

  /**
   * The facet definition as returned by facet_api_facet_load().
   *
   * @var array
   */
  protected $facet;

  /**
   * Constructs a FacetapiQueryType object.
   *
   * @param FacetapiAdapter $adapter
   *   The adapter object associated with facet being queried.
   * @param array $facet
   *   The facet definition as returned by facet_api_facet_load().
   */
  public function __construct(FacetapiAdapter $adapter, array $facet) {
    $this->adapter = $adapter;
    $this->facet = $facet;
  }

  /**
   * Adds additional information to the array active items.
   *
   * Active facet items are stored in the FacetapiAdapter::activeItems property
   * as associative arrays. See the docblock for the structure. Queries such as
   * ranges can add additional info such as the "start" and "end" values for
   * more efficient processing of facet data.
   *
   * @param array $item
   *   The active item. See FacetapiAdapter::activeItems for the structure of
   *   the active item array.
   *
   * @return array
   *   An associative array addition information to add to the active item.
   */
  public function extract(array $item) {
    return array();
  }

  /**
   * Convenience method to get the facet's global and per relam settings.
   *
   * @param string|array $realm
   *   The machine readable name of the realm or an array containing the realm
   *   definition. Pass NULL to return the facet's global settings.
   *
   * @return stdClass
   *   An object containing the settings.
   *
   * @see FacetapiFacet::getSettings()
   */
  public function getSettings($realm = NULL) {
    return $this->adapter->getFacet($this->facet)->getSettings($realm);
  }

  /**
   * Returns the facet's active items.
   *
   * @return array
   *   The facet's active items. See FacetapiAdapter::activeItems for the
   *   structure of the active item array.
   *
   * @see FacetapiAdapter::activeItems
   */
  public function getActiveItems() {
    return $this->adapter->getActiveItems($this->facet);
  }
} 