<?php

/**
 * @file
 * Contains \Drupal\facetapi\Processor\ProcessorInterface.
 */

namespace Drupal\facetapi\Processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;

/**
 * Describes a processor
 */
interface ProcessorInterface {

  /**
   * Adds a configuration form for this processor
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\facetapi\FacetInterface $facet
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet);

}
