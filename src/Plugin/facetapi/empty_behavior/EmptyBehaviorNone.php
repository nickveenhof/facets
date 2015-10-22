<?php

/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facetapi\empty_behavior\EmptyBehaviorNone
 */

namespace Drupal\facetapi\Plugin\facetapi\empty_behavior;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\EmptyBehavior\EmptyBehaviorInterface;
use Drupal\facetapi\FacetInterface;

/**
 * @FacetApiEmptyBehavior(
 *   id = "none",
 *   label = @Translation("Do not display facet"),
 *   description = @Translation("Do not display a facet when no results"),
 * )
 */
class EmptyBehaviorNone implements EmptyBehaviorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(array $config) {
    return [];
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {
    return false;
  }

}
