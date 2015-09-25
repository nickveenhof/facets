<?php
/**
 * @file
 * Contains Drupal\facetapi\Result\Result
 */

namespace Drupal\facetapi\Result;

use Drupal\Core\Url;

class Result implements ResultInterface{

  /**
   * The facet value
   *
   * @var
   */
  protected $value;

  /**
   * The facet count.
   *
   * @var int
   */
  protected $count;

  /**
   * The Url object.
   *
   * @var Url
   */
  protected $url;

  function __construct($value, $count) {
    $this->value = $value;
    $this->count = $count;
  }

  /**
   * Gets the value.
   *
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Gets the count.
   *
   * @return int
   */
  public function getCount() {
    return $this->count;
  }

  /**
   * Gets the url.
   *
   * @return \Drupal\Core\Url
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Sets the url.
   *
   * @param \Drupal\Core\Url $url
   */
  public function setUrl(Url $url) {
    $this->url = $url;
  }
}