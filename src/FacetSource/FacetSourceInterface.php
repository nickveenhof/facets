<?php

/**
 * @file
 * Contains \Drupal\facetapi\FacetSource\FacetSourceInterface.
 */

namespace Drupal\facetapi\FacetSource;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Exception;

/**
 * Describes a source for facet items.
 *
 * A facet source is used to abstract the data source where facets can be added
 * to. A good example of this is a search api view. There are other possible
 * facet data sources, these all implement the FacetSourceInterface.
 * @see plugin_api
 */
interface FacetSourceInterface {

  /**
   * Adds a configuration form for this facet source.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\facetapi\FacetInterface $facet
   * @param \Drupal\facetapi\FacetSource\FacetSourceInterface $facet_source
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet, FacetSourceInterface $facet_source);

  /**
   * Fill in facet data in to the configured facets.
   *
   * @param FacetInterface[] $facets
   * @return mixed
   */
  public function fillFacetsWithResults($facets);

  /**
   * Get the allowed query types for a given facet for the facet source.
   *
   * @param FacetInterface $facet
   * @return array of allowed query types
   * @throws Exception
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
   * @return mixed
   */
  public function getFields();

}
