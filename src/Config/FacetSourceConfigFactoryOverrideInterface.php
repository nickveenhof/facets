<?php

/**
 * @file
 * Contains \Drupal\facetapi\Config\FacetSourceConfigFactoryOverrideInterface.
 */

namespace Drupal\facetapi\Config;

use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;

/**
 * Defines the interface for a configuration factory facet source override object.
 */
interface FacetSourceConfigFactoryOverrideInterface extends ConfigFactoryOverrideInterface {

  /**
   * Gets the object used to override configuration data.
   *
   * @return \Drupal\facetapi\FacetSource\FacetSourceInterface
   *   The object used to override configuration data.
   */
  public function getFacetSource();

  /**
   * Sets the facet source to be used in configuration overrides.
   *
   * @param \Drupal\facetapi\FacetSource\FacetSourceInterface
   *   The object used to override configuration data.
   *
   * @return $this
   */
  public function setFacetSource(FacetSourceInterface $facetSource = NULL);

  /**
   * Get the override for given facet source and configuration name.
   *
   * @param string $facetSource
   *   Facet source.
   * @param string $name
   *   Configuration name.
   *
   * @return \Drupal\Core\Config\Config
   *   Configuration override object.
   */
  public function getOverride($facetSource, $name);

  /**
   * Returns the storage instance for a particular facet source.
   *
   * @param string $facetSource
   *   Facet source.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *   The storage instance for a particular facet source.
   */
  public function getStorage($facetSource);

  /**
   * Installs available configuration overrides for a given facet source.
   *
   * @param string $facetSource
   *   Facet source.
   */
  public function installFacetSourceOverrides($facetSource);

}
