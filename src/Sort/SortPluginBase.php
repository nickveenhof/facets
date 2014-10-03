<?php
/**
 * @file
 * Provides \Drupal\facetapi\SortPluginBase;
 */

namespace Drupal\facetapi\Sort;

use Drupal\Component\Plugin\PluginBase;
use Drupal\facetapi\Sort\SortInterface;

class SortPluginBase extends PluginBase implements SortInterface {

  private $weight;

  /**
   * Returns the id of the facetapi sort.
   *
   * @return mixed
   */
  public function getId() {
    return $this->pluginDefinition['id'];
  }

  /**
   * Returns the label of the facetapi sort.
   *
   * @return string
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * Returns the description of the facetapi sort.
   *
   * @return string
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

  /**
   * Gets the weight of the facetapi sort.
   *
   * @return int|string
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * Sets the weight of the facetapi sort.
   *
   * @param int $weight
   * @return $this
   */
  public function setWeight($weight) {
    $this->weight = $weight;

    return $this;
  }

  public function sort(array $a, array $b) {
    //Nothing to do here...
  }

}
