<?php
/**
 * @file
 * Contains  Drupal\facets\FacetInterface.
 */

namespace Drupal\facets;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * The facet entity.
 */
interface FacetInterface extends ConfigEntityInterface {

  /**
   * Sets the facet's widget plugin id.
   *
   * @param string $widget
   *   The widget plugin id.
   *
   * @return $this
   *   Returns self
   */
  public function setWidget($widget);

  /**
   * Returns the facet's widget plugin id.
   *
   * @return string
   *   The widget plugin id.
   */
  public function getWidget();

  /**
   * Get field identifier.
   *
   * @return string
   *   The field identifier of this facet.
   */
  public function getFieldIdentifier();

  /**
   * Set field identifier.
   *
   * @param string $field_identifier
   *   The field identifier of this facet.
   *
   * @return $this
   *   Returns self.
   */
  public function setFieldIdentifier($field_identifier);

  /**
   * Get the field alias used to identify the facet in the url.
   *
   * @return string
   *   The field alias for the facet.
   */
  public function getFieldAlias();

  /**
   * Get the field name of the facet as used in the index.
   *
   * @TODO: Check if fieldIdentifier can be used as well!
   *
   * @return string
   *   The name of the facet.
   */
  public function getName();

  /**
   * Sets an item with value to active.
   *
   * @param string $value
   *   An item that is active.
   */
  public function setActiveItem($value);

  /**
   * Get all the active items in the facet.
   *
   * @return mixed
   *   An array containing all active items.
   */
  public function getActiveItems();

  /**
   * Checks if a value is active.
   *
   * @param string $value
   *   The value to be checked.
   *
   * @return bool
   *   Is an active value.
   */
  public function isActiveValue($value);

  /**
   * Get the result for the facet.
   *
   * @return \Drupal\facets\Result\ResultInterface[] $results
   *   The results of the facet.
   */
  public function getResults();

  /**
   * Sets the results for the facet.
   *
   * @param \Drupal\facets\Result\ResultInterface[] $results
   *   The results of the facet.
   */
  public function setResults(array $results);


  /**
   * Get the query type instance.
   *
   * @return string
   *   The query type plugin being used.
   */
  public function getQueryType();

  /**
   * Get the plugin name for the url processor.
   *
   * @return string
   *   The id of the url processor.
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
   *   Returns self.
   */
  public function setOption($name, $option);

  /**
   * Sets the index's options.
   *
   * @param array $options
   *   The new index options.
   *
   * @return $this
   *   Returns self.
   */
  public function setOptions(array $options);

  /**
   * Sets a string representation of the Facet source plugin.
   *
   * This is usually the name of the Search-api view.
   *
   * @param string $facet_source_id
   *   The facet source id.
   *
   * @return $this
   *   Returns self.
   */
  public function setFacetSourceId($facet_source_id);

  /**
   * Returns the Facet source id.
   *
   * @return string
   *   The id of the facet source.
   */
  public function getFacetSourceId();

  /**
   * Returns the plugin instance of a facet source.
   *
   * @return \Drupal\facets\FacetSource\FacetSourceInterface
   *   The plugin instance for the facet source.
   */
  public function getFacetSource();

  /**
   * Load the facet sources for this facet.
   *
   * @param bool|TRUE $only_enabled
   *   Only return enabled facet sources.
   *
   * @return \Drupal\facets\FacetSource\FacetSourceInterface[]
   *   An array of facet sources.
   */
  public function getFacetSources($only_enabled = TRUE);

  /**
   * Returns an array of processors with their configuration.
   *
   * @param bool|TRUE $only_enabled
   *   Only return enabled processors.
   *
   * @return \Drupal\facets\Processor\ProcessorInterface[]
   *   An array of processors.
   */
  public function getProcessors($only_enabled = TRUE);

  /**
   * Loads this facets processors for a specific stage.
   *
   * @param string $stage
   *   The stage for which to return the processors. One of the
   *   \Drupal\facets\Processor\ProcessorInterface::STAGE_* constants.
   * @param bool $only_enabled
   *   (optional) If FALSE, also include disabled processors. Otherwise, only
   *   load enabled ones.
   *
   * @return \Drupal\facets\Processor\ProcessorInterface[]
   *   An array of all enabled (or available, if if $only_enabled is FALSE)
   *   processors that support the given stage, ordered by the weight for that
   *   stage.
   */
  public function getProcessorsByStage($stage, $only_enabled = TRUE);

  /**
   * Sets the "only visible when facet source is visible" boolean flag.
   *
   * @param bool $only_visible_when_facet_source_is_visible
   *   A boolean flag indicating if the facet should be hidden on a page that
   *   does not show the facet source.
   *
   * @return $this
   *   Returns self.
   */
  public function setOnlyVisibleWhenFacetSourceIsVisible($only_visible_when_facet_source_is_visible);

  /**
   * Returns the "only visible when facet source is visible" boolean flag.
   *
   * @return bool
   *   True when the facet is only shown on a page with the facet source.
   */
  public function getOnlyVisibleWhenFacetSourceIsVisible();

}
