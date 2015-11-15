<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\facetapi\url_processor\UrlProcessorQueryString.
 */

namespace Drupal\facetapi\Plugin\facetapi\processor;

use Drupal\Core\Url;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Processor\UrlProcessorPluginBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FacetApiProcessor(
 *   id = "query_string",
 *   label = @Translation("Query string url processor"),
 *   description = @Translation("Most simple url processor which uses the query sting."),
 *   stages = {
 *     "pre_query" = 50,
 *     "build" = 15,
 *   },
 *   locked = true
 * )
 */
class QueryStringUrlProcessor extends UrlProcessorPluginBase {

  /**
   * A string that separates the filters in the query string.
   */
  const SEPARATOR = ':';

  /**
   * @var array
   *   An array containing the active filters
   */
  protected $active_filters = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request);
    $this->initializeActiveFilters();
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    // Create links for all the values.
    // First get the current list of get parameters.
    $get_params = $this->request->query;

    // No results are found for this facet, so don't try to create urls.
    if (empty($results)) {
      return [];
    }

    /** @var \Drupal\facetapi\Result\ResultInterface $result */
    foreach ($results as &$result) {
      $filter_string = $facet->getFieldAlias() . ':' . $result->getRawValue();
      $result_get_params = clone $get_params;

      $filter_params = $result_get_params->get($this->filter_key, [], TRUE);
      // If the value is active, remove the filter string from the parameters.
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
      $request = $this->request;
      if ($facet->getPath()) {
        $request = Request::create('/' . $facet->getPath());
      }
      $url = Url::createFromRequest($request);
      $url->setOption('query', $result_get_params->all());

      $result->setUrl($url);
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function preQuery(FacetInterface $facet) {
    // Get the filter key of the facet.
    if (isset($this->active_filters[$facet->getFieldAlias()])) {
      foreach ($this->active_filters[$facet->getFieldAlias()] as $value) {
        $facet->setActiveItem(trim($value, '"'));
      }
    }
  }

  /**
   * Initialize the active filters.
   *
   * Get all the filters that are active. This method only get's all the
   * filters but doesn't assign them to facets. In the processFacet method the
   * active values for a specific facet are added to the facet.
   */
  protected function initializeActiveFilters() {
    $url_parameters = $this->request->query;

    // Get the active facet parameters.
    $active_params = $url_parameters->get($this->filter_key, array(), TRUE);

    // Explode the active params on the separator.
    foreach ($active_params as $param) {
      list($key, $value) = explode(self::SEPARATOR, $param);
      if (!isset($this->active_filters[$key])) {
        $this->active_filters[$key] = [$value];
      }
      else {
        $this->active_filters[$key][] = $value;
      }
    }
  }

}
