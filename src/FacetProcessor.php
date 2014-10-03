<?php
/**
 * Contains Drupal\facetapi\FacetProcessor
 */

namespace Drupal\facetapi;


/**
 * Builds base render array used as a starting point for rendering.
 *
 * The processor constructs the base render array used by widgets across all
 * realms. It is responsible for mapping the raw data returned by the index to
 * human readable values, processing hierarchical data, and building the query
 * strings for each facet item via the adapter's url processor plugin.
 */
class FacetProcessor {

  /**
   * An array of human readable values keyed by their raw index value.
   *
   * @var array
   */
  protected $map = array();

  /**
   * The facet being processed.
   *
   * @var Facet
   */
  protected $facet;

  /**
   * The base render array used as a starting point for rendering.
   *
   * @var array
   */
  protected $build = array();

  /**
   * Array of children keyed by their active parent's index value.
   *
   * @var array
   */
  protected $activeChildren = array();

  /**
   * Constructs a FacetapiAdapter object.
   *
   * Stores the facet being processed.
   *
   * @param Facet $facet
   *   The facet being processed.
   */
  public function __construct(Facet $facet) {
    $this->facet = $facet;
  }

  /**
   * Builds the base render array used as a starting point for rendering.
   *
   * This method takes the following actions:
   * - Maps index values to their human readable values.
   * - Processes hierarchical data.
   * - Builds each facet item's query string variables.
   */
  public function process() {
    $this->build = array();

    // Only initializes facet if a query type plugin is registered for it.
    // NOTE: We don't use the chaining pattern so the methods can be tested.
    if ($this->facet->getAdapter()->getFacetQuery($this->facet->getFacet())) {
      $this->build = $this->initializeBuild($this->build);
      $this->build = $this->mapValues($this->build);
      if ($this->build) {
        $settings = $this->facet->getSettings();
        if (!$settings->settings['flatten']) {
          $this->build = $this->processHierarchy($this->build);
        }
        $this->processQueryStrings($this->build);
      }
    }
  }

  /**
   * Helper function to get the facet's active items.
   *
   * @return array
   *   The facet's active items. See the FacetapiAdapter::getActiveItems()
   *   return value for the structure of the array.
   *
   * @see FacetapiAdapter::getActiveItems()
   */
  public function getActiveItems() {
    return $this->facet->getAdapter()->getActiveItems($this->facet->getFacet());
  }

  /**
   * Gets an active item's children.
   *
   * @param string $value
   *   The index value of the item.
   *
   * @return array
   *   The active item's childen.
   */
  public function getActiveChildren($value) {
    return (isset($this->activeChildren[$value])) ? $this->activeChildren[$value] : array();
  }

  /**
   * Gets the initialized render array.
   */
  public function getBuild() {
    return $this->build;
  }

  /**
   * Maps a facet's index value to a human readable value displayed to the user.
   *
   * @param string $value
   *   The raw value passed through the query string.
   *
   * @return string
   *   The mapped value.
   */
  public function getMappedValue($value) {
    return (isset($this->map[$value])) ? $this->map[$value] : array('#markup' => $value);
  }

  /**
   * Initializes the facet's render array.
   *
   * @return array
   *   The initialized render array containing:
   *   - #markup: The value displayed to the user.
   *   - #path: The href of the facet link.
   *   - #html: Whether #markup is HTML. If TRUE, it is assumed that the data
   *     has already been properly been sanitized for display.
   *   - #indexed_value: The raw value stored in the index.
   *   - #count: The number of items in the result set.
   *   - #active: An integer flagging whether the facet is active or not.
   *   - #item_parents: An array of the parent index values.
   *   - #item_children: References to the child render arrays.
   */
  protected function initializeBuild() {
    $build = array();

    // Build array defaults.
    $defaults = array(
      '#markup' => '',
      '#path' => $this->facet->getAdapter()->getSearchPath(),
      '#html' => FALSE,
      '#indexed_value' => '',
      '#count' => 0,
      '#active' => 0,
      '#item_parents' => array(),
      '#item_children' => array(),
    );

    // Builds render arrays for each item.
    $adapter = $this->facet->getAdapter();
    $build = $adapter->getFacetQuery($this->facet->getFacet())->build();

    // Invoke the alter callbacks for the facet.
    foreach ($this->facet['alter callbacks'] as $callback) {
      $callback($build, $adapter, $this->facet->getFacet());
    }

    // Iterates over the render array and merges in defaults.
    foreach (element_children($build) as $value) {
      $item_defaults = array(
        '#markup' => $value,
        '#indexed_value' => $value,
        '#active' => $adapter->itemActive($this->facet['name'], $value),
      );
      $build[$value] = array_merge($defaults, $item_defaults, $build[$value]);
    }

    return $build;
  }

  /**
   * Maps the IDs to human readable values via the facet's mapping callback.
   *
   * @param array $build
   *   The initialized render array.
   *
   * @return array
   *   The initialized render array with mapped values. See the return of
   *   FacetProcessor::initializeBuild() for the structure of the return
   *   array.
   */
  protected function mapValues(array $build) {
    if ($this->facet['map callback']) {
      // Get available items and active items, invoke the map callback only when
      // there are values to map.
      // NOTE: array_merge() doesn't work here when the values are numeric.
      if ($values = array_unique(array_keys($build + $this->getActiveItems()))) {
        $this->map = call_user_func($this->facet['map callback'], $values, $this->facet['map options']);
        // Normalize all mapped values to a two element array.
        foreach ($this->map as $key => $value) {
          if (!is_array($value)) {
            $this->map[$key] = array();
            $this->map[$key]['#markup'] = $value;
            $this->map[$key]['#html'] = FALSE;
          }
          if (isset($build[$key])) {
            $build[$key]['#markup'] = $this->map[$key]['#markup'];
            $build[$key]['#html'] = !empty($this->map[$key]['#html']);
          }
        }
      }
    }
    return $build;
  }

  /**
   * Processes hierarchical relationships between the facet items.
   *
   * @param array $build
   *   The initialized render array.
   *
   * @return array
   *   The initialized render array with processed hierarchical relationships.
   *   See the return of FacetProcessor::initializeBuild() for the
   *   structure of the return array.
   */
  protected function processHierarchy(array $build) {

    // Builds the hierarchy information if the hierarchy callback is defined.
    if ($this->facet['hierarchy callback']) {
      $parents = $this->facet['hierarchy callback'](array_keys($build));
      foreach ($parents as $value => $parents) {
        foreach ($parents as $parent) {
          if (isset($build[$parent]) && isset($build[$value])) {
            // Use a reference so we see the updated data.
            $build[$parent]['#item_children'][$value] = &$build[$value];
            $build[$value]['#item_parents'][$parent] = $parent;
          }
        }
      }
    }

    // Tests whether parents have an active child.
    // @todo: Can we make this more efficient?
    do {
      $active = 0;
      foreach ($build as $value => $item) {
        if ($item['#active'] && !empty($item['#item_parents'])) {
          // @todo Can we build facets with multiple parents? Core taxonomy
          // form cannot, so we will need a check here.
          foreach ($item['#item_parents'] as $parent) {
            if (!$build[$parent]['#active']) {
              $active = $build[$parent]['#active'] = 1;
            }
          }
        }
      }
    } while ($active);

    // Since the children are copied to their parent's "#item_parents" property
    // during processing, we have to filter the original child items from the
    // top level of the hierarchy.
    return array_filter($build, 'facetapi_filter_top_level_children');
  }

  /**
   * Initializes the render array's query string variables.
   *
   * @param array &$build
   *   The initialized render array.
   */
  protected function processQueryStrings(array &$build) {
    foreach ($build as $value => &$item) {
      $values = array($value);
      // Calculate paths for the children.
      if (!empty($item['#item_children'])) {
        $this->processQueryStrings($item['#item_children']);
        // Merges the childrens' values if the item is active so the children
        // are deactivated along with the parent.
        if ($item['#active']) {
          $values = array_merge(facetapi_get_child_values($item['#item_children']), $values);
        }
      }
      // Stores this item's active children so we can deactivate them in the
      // current search block as well.
      $this->activeChildren[$value] = $values;

      // Formats path and query string for facet item, sets theme function.
      $item['#path'] = $this->getFacetPath($values, $item['#active']);
      $item['#query'] = $this->getQueryString($values, $item['#active']);
    }
  }

  /**
   * Helper function that returns the path for a facet item.
   *
   * @param array $values
   *   An array containing the item's values being added to or removed from the
   *   query string dependent on whether or not the item is active.
   * @param int $active
   *   An integer flagging whether the item is active or not.
   *
   * @return string
   *   The facet path.
   *
   * @see FacetapiAdapter::getFacetPath()
   */
  public function getFacetPath(array $values, $active) {
    return $this->facet
      ->getAdapter()
      ->getFacetPath($this->facet->getFacet(), $values, $active);
  }

  /**
   * Helper function that returns the query string variables for a facet item.
   *
   * @param array $values
   *   An array containing the item's values being added to or removed from the
   *   query string dependent on whether or not the item is active.
   * @param int $active
   *   An integer flagging whether the item is active or not.
   *
   * @return array
   *   An array containing the query string variables.
   *
   * @see FacetapiAdapter::getQueryString()
   */
  public function getQueryString(array $values, $active) {
    return $this->facet
      ->getAdapter()
      ->getQueryString($this->facet->getFacet(), $values, $active);
  }
}
