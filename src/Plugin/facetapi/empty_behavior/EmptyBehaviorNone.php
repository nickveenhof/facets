<?php

/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facetapi\empty_behavior\EmptyBehaviorNone.
 */

namespace Drupal\facetapi\Plugin\facetapi\empty_behavior;

use Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginBase;

/**
 * @FacetApiEmptyBehavior(
 *   id = "none",
 *   label = @Translation("Do not display facet"),
 *   description = @Translation("Do not display a facet when no results"),
 * )
 */
class EmptyBehaviorNone extends EmptyBehaviorPluginBase {}
