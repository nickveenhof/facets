<?php
/**
 * @file
 * Contains  Drupal\facetapi\FacetInterface
 */

namespace Drupal\facetapi;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;
use Drupal\facetapi\Result\ResultInterface;

interface FacetInterface extends ConfigEntityInterface {

  /**
   * Sets the facet's widget plugin id.
   *
   * @param string  $widget
   * @return $this
   */
  public function setWidget($widget);

  /**
   * Returns the facet's widget plugin id.
   *
   * @return string
   */
  public function getWidget();

  /**
   * Get field identifier.
   *
   * @return mixed
   */
  public function getFieldIdentifier();

  /**
   * Set field identifier.
   *
   * @return mixed
   */
  public function setFieldIdentifier($field_identifier);

  /**
   * Get the field alias used to identify the facet in the url.
   *
   * @return mixed
   */
  public function getFieldAlias();

  /**
   * Get the field name of the facet as used in the index.
   *
   * @TODO: Check if fieldIdentifier can be used as well!
   *
   * @return mixed
   */
  public function getName();
  /**
   * Sets an item with value to active.
   *
   * @param $value
   */
  public function setActiveItem($value);

  /**
   * Get all the active items in the facet.
   *
   * @return mixed
   */
  public function getActiveItems();

  /**
   * Check if a value is active.
   *
   * @param string $value
   * @return bool
   */
  public function isActiveValue($value);

  /**
   * Get the result for the facet.
   *
   * @return ResultInterface[] $results
   */
  public function getResults();

  /**
   * Sets the reuslts for the facet.
   *
   * @param ResultInterface[] $results
   */
  public function setResults(array $results);


  /**
   * Get the query type plugin name.
   *
   * @return mixed
   */
  public function getQueryType();

  /**
   * Get the plugin name for the url processor.
   *
   * @return mixed
   */
  public function getUrlProcessorName();

  /**
   * Retrieves an option.
   *
   * @param string $name
   *   The name of an option.
   * @param mixed $default
   *   The value return if the option wasn't set.
   *
   * @return mixed
   *   The value of the option.
   *
   * @see getOptions()
   */
  public function getOption($name, $default = NULL);

  /**
   * Retrieves an array of all options.
   *
   * @return array
   *   An associative array of option values, keyed by the option name.
   */
  public function getOptions();

  /**
   * Sets an option.
   *
   * @param string $name
   *   The name of an option.
   * @param mixed $option
   *   The new option.
   *
   * @return $this
   */
  public function setOption($name, $option);

  /**
   * Sets the index's options.
   *
   * @param array $options
   *   The new index options.
   *
   * @return $this
   */
  public function setOptions(array $options);


  /**
   * Gets the facet manager plugin id
   * @return string
   */
  public function getManagerPluginId();

  /**
   * Sets a string representation of the Facet source plugin.
   *
   * This is usually the name of the Search-api view.
   *
   * @param string $facet_source
   * @return $this
   */
  public function setFacetSource($facet_source);

  /**
   * Returns the Facet source.
   * @return string
   */
  public function getFacetSource();

  /**
   * Load the facet sources for this facet.
   *
   * @param bool|TRUE $only_enabled
   * @return FacetSourceInterface[]
   */
  public function getFacetSources($only_enabled = TRUE);

  /**
   * Get the path to which the facet should link.
   *
   * @param string $path
   */
  public function setPath($path);

  /**
   * Get the path to which the facet should link.
   *
   * @return NULL|string
   */
  public function getPath();

  /**
   * Returns an array of processors with their configuration.
   *
   * @return array
   */
  public function getProcessorConfigs();

  /**
   * Sets the processors with their config.
   *
   * @param array $processor_config
   */
  public function setProcessorConfigs($processor_config = []);

}
