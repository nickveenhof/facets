<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\facetapi\url_processor\UrlProcessorQueryString
 */

namespace Drupal\facetapi\Plugin\facetapi\url_processor;

use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\UrlProcessor\UrlProcessorPluginBase;
use Symfony\Component\HttpFoundation\Request;

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

  const SEPARATOR = ':';

  protected $active_filters = array();

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Request $request
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,
      $request);

    $this->initializeActiveFilters();
  }


  public function getUri(FacetInterface $facet, $value) {
    // TODO: Implement getUri() method.
  }

  public function processFacet(FacetInterface $facet) {
    // Get the filterkey of the facet.
    $filter_key = $facet->getFieldAlias();
    if (isset($this->active_filters[$filter_key])) {
      foreach ($this->active_filters[$filter_key] as $value) {
        $facet->setActiveItem($value);
      }
    }
  }

  /**
   * Initialize the active filters.
   */
  protected function initializeActiveFilters() {
    $url_parameters = $this->request->query;

    // Get the active facet parameters.
    $active_params = $url_parameters->get($this->filter_key, array(), TRUE);

    // Explode the active params on the separator.
    foreach ($active_params as $param) {
      list($key, $value) = explode(self::SEPARATOR, $param);
      if (!isset($this->active_filters[$key])) {
        $this->active_filters[$key] = array(
          $value
        );
      }
      else {
        $this->active_filters[$key][] = $value;
      }
    }
  }
}