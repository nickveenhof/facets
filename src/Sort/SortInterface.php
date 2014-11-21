<?php

/**
 * @file
 * Contains \Drupal\facet_api\Sort\SortInterface.
 */

namespace Drupal\facet_api\Sort;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for image effects.
 *
 * @see plugin_api
 */
interface SortInterface extends PluginInspectionInterface {

  /**
   * Returns the id of the facet_api sort.
   *
   * @return mixed
   */
  public function getId();

  /**
   * Returns the label of the facet_api sort.
   *
   * @return string
   */
  public function getLabel();

  /**
   * Returns the description of the facet_api sort.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Returns the weight of the facet_api sort.
   *
   * @return int|string
   *   Either the integer weight of the facet_api sort, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for this facet_api sort.
   *
   * @param int $weight
   *   The weight for this facet_api sort.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Function that actually sorts the facet_api results.
   *
   * @param array $a
   * @param array $b
   * @return mixed
   */
  public function sort(array $a, array $b);

}
