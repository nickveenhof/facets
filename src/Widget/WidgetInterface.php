<?php
/**
 * @file
 * Contains Drupal\facets\Widget\WidgetInterface.
 */

namespace Drupal\facets\Widget;

use Drupal\facets\FacetInterface;

/**
 * Interface describing the widgets.
 */
interface WidgetInterface {

  /**
   * Add facet info to the query using the selected query type.
   *
   * @return mixed
   *   A boolean
   */
  public function execute();

  /**
   * Builds the widget for rendering.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   The facet we need to build.
   *
   * @return array
   *   A renderable array.
   */
  public function build(FacetInterface $facet);

  /**
   * Pick the preferred query type for this widget.
   *
   * @param string[] $query_types
   *   An array keyed with query type name and it's plugin class to load.
   *
   * @return string
   *   The query type plugin class to load.
   */
  public function getQueryType($query_types);

}
