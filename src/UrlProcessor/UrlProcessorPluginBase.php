<?php
/**
 * @file
 * Contains Drupal\facetapi\UrlProcessor\UrlProcessorPluginBase
 */

namespace Drupal\facetapi\UrlProcessor;

use Drupal\facetapi\Facet\FacetInterface;

abstract class UrlProcessorPluginBase implements UrlProcessorInterface {

  protected $filter_key = 'f';

  abstract public function getUri(FacetInterface $facet, $value);

  public function getFilterKey() {
    return $this->filter_key;
  }

  abstract public function processFacet(FacetInterface $facet);
}