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
   * Processing stage: pre_query.
   */
  const STAGE_PRE_QUERY= 'pre_query';

  /**
   * Processing stage: post_query.
   */
  const STAGE_POST_QUERY = 'post query';

  /**
   * Processing stage: build.
   */
  const STAGE_BUILD = 'build';

  /**
   * Adds a configuration form for this processor
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\facetapi\FacetInterface $facet
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet);

}
