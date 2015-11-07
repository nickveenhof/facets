<?php
/**
 * @file
 * Contains Drupal\facetapi\Result\Result.
 */

namespace Drupal\facetapi\Result;

use Drupal\Core\Url;
/**
 *
 */
class Result implements ResultInterface {

  /**
   * The facet value.
   */
  protected $display_value;

  /**
   * The raw facet value.
   */
  protected $raw_value;

  /**
   * The facet count.
   *
   * @var int
   */
  protected $count;

  /**
   * The Url object.
   *
   * @var \Drupal\Core\Url
   */
  protected $url;

  /**
   * Is this a selected value or not.
   *
   * @var bool
   */
  protected $active = FALSE;

  /**
   * Construct a new instance of the value object.
   *
   * @param $raw_value
   * @param $display_value
   * @param $count
   */
  function __construct($raw_value, $display_value, $count) {
    $this->raw_value = $raw_value;
    $this->display_value = $display_value;
    $this->count = $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayValue() {
    return $this->display_value;
  }

  /**
   * {@inheritdoc}
   */
  public function getRawValue() {
    return $this->raw_value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCount() {
    return $this->count;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function setUrl(Url $url) {
    $this->url = $url;
  }

  /**
   * @inheritdoc
   */
  public function setActiveState($active) {
    $this->active = $active;
  }

  /**
   * @inheritdoc
   */
  public function isActive() {
    return $this->active;
  }

}
