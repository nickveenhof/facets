<?php

namespace Drupal\facetapi\Processor;


use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;

class ProcessorPluginBase implements ProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    // By default, there should be no config form.
    return [];
  }

}
