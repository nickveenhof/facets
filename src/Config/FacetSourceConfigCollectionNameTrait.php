<?php

/**
 * @file
 * Contains \Drupal\language\Config\LanguageConfigCollectionNameTrait.
 */

namespace Drupal\facetapi\Config;


/**
 * Provides a common trait for working with facet source override collection names.
 */
trait FacetSourceConfigCollectionNameTrait {

  /**
   * Creates a configuration collection name based on a language code.
   *
   * @param array $definition
   *   The facet source definition.
   *
   * @return string
   *   The configuration collection name for a language code.
   */
  protected function createConfigCollectionName(array $definition = []) {
    return 'facet_source.' . $definition['label'];
  }

  /**
   * Converts a configuration collection name to a facet source label.
   *
   * @param string $collection
   *   The configuration collection name.
   *
   * @return string
   *   The facet source label of the collection.
   *
   * @throws \InvalidArgumentException
   *   Exception thrown if the provided collection name is not in the format
   *   "facet_source.LANGCODE".
   *
   * @see self::createConfigCollectionName()
   */
    protected function getFacetSourceFromCollectionName($collection) {
    preg_match('/^facet_source\.(.*)$/', $collection, $matches);
    if (!isset($matches[1])) {
      throw new \InvalidArgumentException("'$collection' is not a valid facet source override collection");
    }
    return $matches[1];
  }

}
