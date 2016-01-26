<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\query_string\SearchApiStringTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\query_type;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\query_type\SearchApiString;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\Tests\UnitTestCase;

/**
 * Unit test for query type.
 *
 * @group facets
 */
class SearchApiStringTest extends UnitTestCase {

  /**
   * Test string query type without executing the query.
   */
  public function testQueryType() {
    $query = new SearchApiQuery([], 'search_api_query', []);
    $facet = new Facet(
      ['query_operator' => 'AND'],
      'facets_facet'
    );

    $original_results = [
      ['count' => 3, 'filter' => 'badger'],
      ['count' => 5, 'filter' => 'mushroom'],
      ['count' => 7, 'filter' => 'narwhal'],
      ['count' => 9, 'filter' => 'unicorn'],
    ];

    $query_type = new SearchApiString(
      [
        'facet' => $facet,
        'query' => $query,
        'results' => $original_results,
      ],
      'search_api_string',
      []
    );

    $built_facet = $query_type->build();
    $this->assertInstanceOf('\Drupal\facets\FacetInterface', $built_facet);

    $results = $built_facet->getResults();
    $this->assertInternalType('array', $results);

    foreach ($original_results as $k => $result) {
      $this->assertInstanceOf('\Drupal\facets\Result\ResultInterface', $results[$k]);
      $this->assertEquals($result['count'], $results[$k]->getCount());
      $this->assertEquals($result['filter'], $results[$k]->getDisplayValue());
    }
  }

  /**
   * Test string query type without results.
   */
  public function testEmptyResults() {
    $query = new SearchApiQuery([], 'search_api_query', []);
    $facet = new Facet([], 'facets_facet');

    $query_type = new SearchApiString(
      [
        'facet' => $facet,
        'query' => $query,
      ],
      'search_api_string',
      []
    );

    $built_facet = $query_type->build();
    $this->assertInstanceOf('\Drupal\facets\FacetInterface', $built_facet);

    $results = $built_facet->getResults();
    $this->assertInternalType('array', $results);
    $this->assertEmpty($results);
  }

}
