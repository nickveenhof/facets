<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\facetapi\url_processor\UrlProcessorQueryString
 */

namespace Drupal\facetapi\Plugin\facetapi\url_processor;

use Drupal\Core\Url;
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


  public function addUriToResults(FacetInterface $facet) {
    // Create links for all the values.
    // First get the current list of get paramaters.
    $get_params = $this->request->query;

    $results = $facet->getResults();

    // No results are found for this facet, so dont try to create urls.
    if (is_null($results)) {
      return;
    }

    foreach ($results as $result) {
      $filter_string = $facet->getFieldAlias() . ':' . $result->getValue();
      $result_get_params = clone $get_params;

      $filter_params = $result_get_params->get($this->filter_key, [], TRUE);
      // If the value is active, remove the filter string from the
      // parameters.
      if ($result->isActive()) {
        foreach ($filter_params as $key => $filter_param) {
          if ($filter_param == $filter_string) {
            unset($filter_params[$key]);
          }
        }
      }
      // If the value is not active, add the filter string.
      else {
        $filter_params[] = $filter_string;
      }

      $result_get_params->set($this->filter_key, $filter_params);
      $url = Url::createFromRequest($this->request);
      $url->setOption('query', $result_get_params->all());
      $result->setUrl($url);
    }

  }

  public function processFacet(FacetInterface $facet) {
    // Get the filterkey of the facet.
    if (isset($this->active_filters[$facet->getFieldAlias()])) {
      foreach ($this->active_filters[$facet->getFieldAlias()] as $value) {
        $facet->setActiveItem($value);
      }
    }
  }

  /**
   * Initialize the active filters.
   *
   * Get all the filters that are active.
   * This method only get's all the filters,
   * but doesn't assign them to facets.
   * In the processFacet method the active values
   * for a specific facet are added to the facet.
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
