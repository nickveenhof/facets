<?php

/**
 * @file
 * Contains \Drupal\facetapi\QueryTypeInterface.
 */

namespace Drupal\facetapi;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * This interface was called FacetapiQueryTypeInterface but the name would then no longer be consistent
 * with the current best practises.
 *
 * In D7 this was the interface implemented by all query type plugins.
 *
 * Facet API does not perform any facet calculations on it's own. The query type
 * plugin system provides Facet API with a consistent way to tell backends what
 * type of query to execute in order to return the appropriate data required
 * for the faceted navigation display.
 *
 * Query type plugins are implemented by the backends and perform the
 * alterations of their internal search engine query mechanisms to execute the
 * filters and retrieve facet data. For example, modules that integrate with
 * Apache Solr will set the necessary params for faceting, whereas modules that
 * extend the core Search module will add SQL joins, filter clauses, and COUNT
 * queries in order to implement faceting.
 *
 * Although the actual method of querying the search engine is vastly different
 * per backend, Facet API operates under the assumption that the types of
 * queries are the same. For example, a "term" query is assumed to be a straight
 * filter, whereas a "range" query is assumed to be a search between two values.
 * Although the common query types such as "term" and "date" should be available
 * to all backends, it is expected that some backends will have additional query
 * types based on capability. For example, backends integrating with the Apache
 * Solr engine might have a "geospatial" query type that modules integrating
 * with the core Search won't have.
 *
 * All functions and comments in this class have currently been copy/pasted verbatim
 * and then tweaked to provide a skeleton that describes what the D7 version used to handle.
 */
interface QueryTypeInterface extends ConfigEntityInterface {
  /**
   * Returns the query type associated with the plugin.
   *
   * Query types must be standard across all backends. For example, the common
   * "term" query type must execute the same type of query for backends that
   * integrate with Apache Solr, the core Search module, or any other search
   * engine that implementing modules connect to.
   *
   * It is recommended that the strings returned by this method contain only
   * lowercase letters with optional underscores.
   *
   * @return string
   *   The query type.
   */
  static public function getType();

  /**
   * Alters the backend's native query object to execute the facet query.
   *
   * As an example, modules that integrate with Apache Solr will set the
   * necessary params for faceting, whereas modules that extend the core Search
   * module will add SQL joins, filter clauses, and COUNT queries in order to
   * implement faceting.
   *
   * @param mixed $query
   *   The backend's native query object.
   */
  public function execute($query);

  /**
   * Gets data from the server and adds values to the facet's render array.
   *
   * At a minimum this method should add the index values returned by the
   * search server as keys containing associative arrays with the "#count" key.
   * The end result will be an array structured like the one below:
   *
   * <code>
   * $build = array(
   *   'index-value-1' => array('#count' => 3),
   *   'index-value-2' => array('#count' => 19),
   *   'index-value-3' => array('#count' => 82),
   *   ...
   * );
   * </code>
   *
   * See the return of the FacetapiFacetProcessor::initializeBuild() for all
   * possible values that could be populated. Query type plugins such as "date"
   * types will populte the #item_children, #item_parents, and #active keys in
   * addition to #count.
   *
   * @return array
   *   The initialized render array. For all possible values of the structure of
   *   the array, see the FacetapiFacetProcessor::initializeBuild() docblock.
   *   Usually only the #count key is used.
   *
   * @see FacetapiFacetProcessor::initializeBuild()
   */
  public function build();
} 