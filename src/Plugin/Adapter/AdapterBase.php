<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\AdapterBase.
 */

namespace Drupal\facetapi\Plugin;

use Drupal\facetapi\Plugin\AdapterInterface;

/**
 * Base class for Facet API adapters.
 *
 * @TODO: rewrite D7 comment block:
 * Adapters are responsible for abstracting interactions with the Search backend
 * that are necessary for faceted search. The adapter is also responsible for
 * retrieving facet information passed by the user via the a processor plugin
 * taking the appropriate action, whether it is checking dependencies for all
 * enabled facets or passing the appropriate query type plugin to the backend
 * so that it can execute the actual facet query.
 */
class AdapterBase implements AdapterInterface {

  /**
   * The searcher information as returned by facetapi_get_searcher_info().
   *
   * @var array
   */
  protected $info = array();

  /**
   * The search keys, or query text, submitted by the user.
   *
   * @var string
   */
  protected $keys;

  /**
   * An array of FacetapiFacet objects for facets being rendered.
   *
   * @var array
   *
   * @see FacetapiFacet
   */
  protected $facets = array();


}
