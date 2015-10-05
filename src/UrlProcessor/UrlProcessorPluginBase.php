<?php
/**
 * @file
 * Contains Drupal\facetapi\UrlProcessor\UrlProcessorPluginBase
 */

namespace Drupal\facetapi\UrlProcessor;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class UrlProcessorPluginBase extends PluginBase implements UrlProcessorInterface, ContainerFactoryPluginInterface {

  protected $filter_key = 'f';

  /** @var Request  */
  protected $request;

  abstract public function addUriToResults(FacetInterface $facet, $value);

  public function getFilterKey() {
    return $this->filter_key;
  }

  abstract public function processFacet(FacetInterface $facet);

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Request $request
  ) {
    parent::__construct($configuration, $plugin_id,
      $plugin_definition);
    $this->request = $request;
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    // Add the request.
    /** @var Request $request */
    $request = $container->get('request_stack')->getCurrentRequest();
    $plugin = new static($configuration, $plugin_id, $plugin_definition, $request);
    return $plugin;
  }
}