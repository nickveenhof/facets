<?php

/**
 * @file
 * Contains \Drupal\facetapi\FacetSource\FacetSourceInterface.
 */

namespace Drupal\facetapi\FacetSource;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;

/**
 * Describes a source for facet items.
 *
 * A facet source is used to abstract the datasource where facets can be added
 * to. A good example of this is a search api view. There are other possible
 * facet data sources, these all implement the FacetSourceInterface.
 * @see plugin_api
 */
interface FacetSourceInterface {

  /**
   * Returns an array of possible query types associated with this data source.
   *
   * @return array
   */
  public function getAllowedQueryTypes();

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
   * Adds a configuration form for this facet source.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\facetapi\FacetInterface $facet
   * @param \Drupal\facetapi\FacetSource\FacetSourceInterface $facet_source
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet, FacetSourceInterface $facet_source);

}
