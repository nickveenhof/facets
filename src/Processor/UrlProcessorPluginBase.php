<?php
/**
 * @file
 * Contains Drupal\facetapi\Processor\UrlProcessorPluginBase.
 */

namespace Drupal\facetapi\Processor;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A base class for plugins that implements most of the boilerplate.
 */
abstract class UrlProcessorPluginBase extends ProcessorPluginBase implements UrlProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * @var string
   *   The query string variable that holds all the facet information.
   */
  protected $filter_key = 'f';

  /**
   * @var Request
   *  The current request.
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function getFilterKey() {
    return $this->filter_key;
  }

  /**
   * Constructs a new instance of the class.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var Request $request */
    $request = $container->get('request_stack')->getCurrentRequest();
    return new static($configuration, $plugin_id, $plugin_definition, $request);
  }

}
