<?php

/**
 * @file
 * Contains Drupal\facet_api\Facet.
 */

namespace Drupal\facet_api;

/**
 * Wrapper around the facet definition with methods that build render arrays.
 *
 * Thic class contain methods that assist in render array generation and stores
 * additional context about how and what generated the render arrays for
 * consumption by the widget plugins. Objects can also be used as if they are
 * the facet definitions returned by facet_api_facet_load().
 *
 * @TODO: Remove ArrayAccess dependency...  'Cause we like object!
 */
class Facet implements \ArrayAccess {

  /**
   * The FacetapiAdapter object this class was instantiated from.
   *
   * @var FacetapiAdapter
   */
  protected $adapter;

  /**
   * The facet definition as returned by facet_api_facet_load().
   *
   * This is the array acted on by the ArrayAccess interface methods so the
   * object can be used as if it were the facet definition array.
   *
   * @var array
   */
  protected $facet;

  /**
   * The base render array used as a starting point for rendering.
   *
   * @var array
   */
  protected $build = array();

  /**
   * Constructs a FacetapiAdapter object.
   *
   * Sets the adapter and facet definitions.
   *
   * @param FacetapiAdapter $adapter
   *   he FacetapiAdapter object this class was instantiated from.
   * @param array $facet
   *   The facet definition as returned by facet_api_facet_load().
   */
  public function __construct(FacetapiAdapter $adapter, array $facet) {
    $this->adapter = $adapter;
    $this->facet = $facet;
  }

  /**
   * Implements ArrayAccess::offsetExists().
   */
  public function offsetExists($offset) {
    return isset($this->facet[$offset]);
  }

  /**
   * Implements ArrayAccess::offsetGet().
   */
  public function offsetGet($offset) {
    return isset($this->facet[$offset]) ? $this->facet[$offset] : NULL;
  }

  /**
   * Implements ArrayAccess::offsetSet().
   */
  public function offsetSet($offset, $value) {
    if (NULL === $offset) {
      $this->facet[] = $value;
    }
    else {
      $this->facet[$offset] = $value;
    }
  }

  /**
   * Implements ArrayAccess::offsetUnset().
   */
  public function offsetUnset($offset) {
    unset($this->facet[$offset]);
  }

  /**
   * Returns the FacetapiAdapter object this class was instantiated from.
   *
   * @return FacetapiAdapter
   *   The adapter object.
   */
  public function getAdapter() {
    return $this->adapter;
  }

  /**
   * Returns the facet definition as returned by facet_api_facet_load().
   *
   * @return array
   *   An array containing the facet definition.
   */
  public function getFacet() {
    return $this->facet;
  }

  /**
   * Returns the base render array used as a starting point for rendering.
   *
   * @return array
   *   The base render array.
   */
  public function getBuild() {
    return $this->build;
  }

  /**
   * Returns realm specific or global settings for a facet.
   *
   * @param string|array $realm
   *   The machine readable name of the realm or an array containing the realm
   *   definition. Pass NULL to return the facet's global settings.
   *
   * @return
   *   An object containing the settings.
   *
   * @see FacetapiAdapter::getFacetSettings()
   * @see FacetapiAdapter::getFacetSettingsGlobal()
   */
  public function getSettings($realm = NULL) {
    if ($realm && !is_array($realm)) {
      $realm = facet_api_realm_load($realm);
    }
    $method = ($realm) ? 'getFacetSettings' : 'getFacetSettingsGlobal';
    return $this->adapter->$method($this->facet, $realm);
  }

  /**
   * Build the facet's render array for the realm.
   *
   * Executes the filter plugins to modify the base render array, then passes
   * the filtered array to the widget plugin. The widget plugin is executed to
   * finalize the build if the filtered array contains items. Otherwise the
   * empty behavior plugin is executed to finalize the build.
   *
   * @param array $realm
   *   The realm definition as returned by facet_api_realm_load().
   * @param FacetapiFacetProcessor $processor
   *   The processor object.
   *
   * @return array
   *   The facet's render array keyed by the FacetapiWidget::$key property.
   */
  public function build(array $realm, FacetapiFacetProcessor $processor) {
    $settings = $this->getSettings($realm);

    // Get the base render array used as a starting point for the widget.
    $this->build = $processor->getBuild();

    // Execute the filter plugins.
    // @todo Defensive coding here for filters?
    $enabled_filters = array_filter($settings->settings['filters'], 'facet_api_filter_disabled_filters');
    uasort($enabled_filters, 'drupal_sort_weight');
    foreach ($enabled_filters as $filter_id => $filter_settings) {
      if ($class = ctools_plugin_load_class('facet_api', 'filters', $filter_id, 'handler')) {
        $filter_plugin = new $class($filter_id, $this->adapter, $settings);
        $this->build = $filter_plugin->execute($this->build);
      }
      else {
        watchdog('facet_api', 'Filter %name not valid.', array('%name' => $filter_id), WATCHDOG_ERROR);
      }
    }

    // Instantiate and initialize the widget plugin.
    // @todo Add defensive coding here for widgets?
    $widget_name = $settings->settings['widget'];
    if ($class = ctools_plugin_load_class('facet_api', 'widgets', $widget_name, 'handler')) {
      $widget_plugin = new $class($widget_name, $realm, $this, $settings);
      $widget_plugin->init();
    }
    else {
      watchdog('facet_api', 'Widget %name not valid.', array('%name' => $widget_name), WATCHDOG_ERROR);
      return array();
    }

    if ($this->build) {
      // Execute the widget plugin and get the finalized render array.
      $widget_plugin->execute();
      $build = $widget_plugin->getBuild();
    }
    else {
      // Instantiate the empty behavior plugin.
      $id = $settings->settings['empty_behavior'];
      $class = ctools_plugin_load_class('facet_api', 'empty_behaviors', $id, 'handler');
      $empty_plugin = new $class($settings);
      // Execute the empty behavior plugin.
      $build = $widget_plugin->getBuild();
      $build[$this['field alias']] = $empty_plugin->execute();
    }

    // If the element is empty, unset it.
    if (!$build[$this['field alias']]) {
      unset($build[$this['field alias']]);
    }

    // Add JavaScript settings by merging with the others already set.
    $merge_settings['facet_api']['facets'][] = $widget_plugin->getJavaScriptSettings();
    drupal_add_js($merge_settings, 'setting');

    // Return render array keyed by the FacetapiWidget::$key property.
    return array($widget_plugin->getKey() => $build);
  }

}
