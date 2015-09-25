<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\facetapi\url_processor\UrlProcessorQueryString
 */

namespace Drupal\facetapi\Plugin\facetapi\url_processor;

use Drupal\facetapi\Context\ContextInterface;


/**
 * @Facet(
 *   id = "views",
 *   label = @Translation("Views context"),
 *   description = @Translation("The default context implementation, this uses views ."),
 * )
 */
class ViewsContext implements ContextInterface {

  public function isActiveContext() {
    return true;
  }

}
