<?php

/**
 * @file
 * Contains \Drupal\facetapi\Sort\SortInterface.
 */

namespace Drupal\facetapi\Sort;

use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Defines the interface for image effects.
 *
 * @see plugin_api
 */
interface SortInterface extends PluginInspectionInterface {

  /**
   * Returns the id of the facetapi sort.
   *
   * @return mixed
   */
  public function getId();

  /**
   * Returns the label of the facetapi sort.
   *
   * @return string
   */
  public function getLabel();

  /**
   * Returns the description of the facetapi sort.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Returns the weight of the facetapi sort.
   *
   * @return int|string
   *   Either the integer weight of the facetapi sort, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for this facetapi sort.
   *
   * @param int $weight
   *   The weight for this facetapi sort.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Function that actually sorts the facetapi results.
   *
   * @param array $a
   * @param array $b
   * @return mixed
   */
  public function sort(array $a, array $b);

}
