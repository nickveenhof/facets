<?php

/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FacetApiSearcher.
 */

namespace Drupal\facet_api\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API Searcher annotation object.
 *
 * @see \Drupal\facetapi\FacetApiSearcherManager
 * @see plugin_api
 *
 * @Annotation
 */
class FacetApiSearcher extends Plugin {

  /**
   * The machine readable name of the searcher.
   *
   * @var string
   */
  public $name;

  /**
   * The human readable name of the searcher displayed in the admin UI.
   *
   * @var string
   */
  public $label;

  /**
   * The adapter plugin ID associated with the searcher.
   *
   * @var string
   */
  public $adapter;

  /**
   * The URL processor plugin ID associated with the searcher.
   *
   * @var string
   */
  public $urlProcessor;

  /**
   * An array containing the types of content indexed by the searcher.
   * A type is usually an entity such as 'node', but it can be a non-entity
   * value as well.
   *
   * @var array
   */
  public $types;

  /**
   * The MENU_DEFAULT_LOCAL_TASK item which the admin UI page is added
   * to as a MENU_LOCAL_TASK. An empty string if the backend manages the admin
   * UI menu items internally.
   *
   * @var string
   */
  public $path;

  /**
   * TRUE if the searcher supports "missing" facets.
   *
   * @var boolean
   */
  public $supportFacetsMissing;

  /**
   * TRUE if the searcher supports the minimum facet count setting.
   *
   * @var boolean
   */
  public $supportFacetsMincount;

  /**
   * include default facets: TRUE if the searcher should include the facets
   * defined in facetapi_facetapi_facet_info() when indexing node content,
   * FALSE if they should be skipped.
   *
   * @var boolean
   */
  public $includeDefaultFacets;
}
