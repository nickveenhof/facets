<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\facetapi\url_processor\UrlProcessorQueryString
 */

namespace Drupal\facetapi\Plugin\facetapi\url_processor;

use Drupal\facetapi\Facet\FacetInterface;
use Drupal\facetapi\UrlProcessor\UrlProcessorPluginBase;

/**
 * @FacetApiUrlProcessor(
 *   id = "query_string",
 *   label = @Translation("Query string url processor"),
 *   description = @Translation("Most simple url processor which uses the query sting."),
 * )
 *
 * Class UrlProcessorQueryString
 * @package Drupal\facetapi\Plugin\facetapi\url_processor
 */
class UrlProcessorQueryString extends UrlProcessorPluginBase{

  public function getUri(FacetInterface $facet, $value) {
    // TODO: Implement getUri() method.
  }

  public function processFacet(FacetInterface $facet) {
    // TODO: Implement processFacet() method.
  }
}