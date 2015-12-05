<?php

/**
 * @file
 * Contains \Drupal\facets\FacetSource\FacetSourceInterface.
 */

namespace Drupal\facets\FacetSource;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;

/**
 * Describes a source for facet items.
 *
 * A facet source is used to abstract the data source where facets can be added
 * to. A good example of this is a search api view. There are other possible
 * facet data sources, these all implement the FacetSourceInterface.
 *
 * @see plugin_api
 */
interface FacetSourceInterface {

  /**
   * Adds a configuration form for this facet source.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\facets\FacetInterface $facet
   * @param \Drupal\facets\FacetSource\FacetSourceInterface $facet_source
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet, FacetSourceInterface $facet_source);

  /**
   * Fill in facet data in to the configured facets.
   *
   * @param \Drupal\facets\FacetInterface[] $facets
   *
   * @return mixed
   */
  public function fillFacetsWithResults($facets);

  /**
   * Returns the path where a facet should link to.
   *
   * @return string
   */
  public function getPath();

  /**
   * Get the allowed query types for a given facet for the facet source.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *
   * @return array of allowed query types
   *
   * @throws \Drupal\facets\Exception\Exception
   */
  public function getQueryTypesForFacet(FacetInterface $facet);

  /**
   * Returns true if the Facet source is being rendered in the current request.
   *
   * This function will define if all facets for this facet source are shown
   * when facet source visibility: "being rendered" is configured in the facet
   * visibility settings.
   *
   * @return boolean
   */
  public function isRenderedInCurrentRequest();

  /**
   * Returns an array of fields that are defined on the datasource.
   *
   * This returns an array of fields that are defined on the source. This array
   * is keyed by the field's machine name and has values of the field's label.
   *
   * @return array
   */
  public function getFields();

  /**
   * Sets the search keys, or query text, submitted by the user.
   *
   * @param string $keys
   *   The search keys, or query text, submitted by the user.
   *
   * @return self
   *   An instance of this class.
   */
  public function setSearchKeys($keys);

  /**
   * Gets the search keys, or query text, submitted by the user.
   *
   * @return string
   *   The search keys, or query text, submitted by the user.
   */
  public function getSearchKeys();

}
