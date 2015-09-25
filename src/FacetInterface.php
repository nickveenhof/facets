<?php
/**
 * @file
 * Contains  Drupal\facetapi\FacetInterface
 */

namespace Drupal\facetapi;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\facetapi\Result\ResultInterface;

interface FacetInterface extends ConfigEntityInterface {


  /**
   * Get field identifier.
   *
   * @return mixed
   */
  public function getFieldIdentifier();

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
   * Get the result for the facet.
   *
   * @return mixed
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
   * Get the name of the searcher.
   *
   * @return mixed
   */
  public function getSearcherName();

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

}