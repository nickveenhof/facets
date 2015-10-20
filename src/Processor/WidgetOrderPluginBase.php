<?php

namespace Drupal\facetapi\Processor;

abstract class WidgetOrderPluginBase extends ProcessorPluginBase implements \Drupal\facetapi\Processor\WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(array $results) {

    // This should load the facet's config to find the ordering direction.
    return $this->sortResults($results, 'DESC');
  }

}
