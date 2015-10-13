<?php

/**
 * @file
 * Contains \Drupal\facetapi\FacetListBuilder.
 */

namespace Drupal\facetapi;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;

/**
 * Builds a listing of facet entities.
 */
class FacetListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entities = parent::load();
    $this->sortByStatusThenAlphabetically($entities);
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity instanceof FacetInterface) {

      if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
        $operations['edit'] = array(
          'title' => $this->t('Edit'),
          'weight' => 10,
          'url' => $entity->urlInfo('edit-form'),
        );
      }
      if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
        $operations['delete'] = array(
          'title' => $this->t('Delete'),
          'weight' => 100,
          'url' => $entity->urlInfo('delete-form'),
        );
      }

    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return [
      'type' => $this->t('Type'),
      'title' => [
        'data' => $this->t('Title'),
      ],
      'status' => [
        'data' => $this->t('Enabled'),
        'class' => ['checkbox'],
      ],
    ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $row = parent::buildRow($entity);

    $status_label = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    $status_icon = array(
      '#theme' => 'image',
      '#uri' => $entity->status() ? 'core/misc/icons/73b355/check.svg' : 'core/misc/icons/e32700/error.svg',
      '#width' => 18,
      '#height' => 18,
      '#alt' => $status_label,
      '#title' => $status_label,
    );

    return array(
      'data' => array(
        'type' => array(
          'data' => 'Facet',
          'class' => array('facetapi-type'),
        ),
        'title' => array(
          'data' => 'Field: ' . $entity->getFieldAlias() . ', Widget: ' . $entity->getWidget(),
        ),
        'status' => array(
          'data' => $status_icon,
          'class' => array('checkbox'),
        ),
        'operations' => $row['operations'],
      ),
      'title' => $this->t('ID: @name', array('@name' => $entity->id())),
      'class' => array('facet'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildFacetSourceRow(FacetSourceInterface $facet_source) {
    /** @var \Drupal\facetapi\FacetSource\FacetSourceInterface $facet_source */

    return array(
      'data' => array(
        'type' => array(
          'data' => 'Facet source',
          'class' => array('facetapi-type'),
        ),
        'title' => array(
          'data' => $facet_source['id'],
        ),
        'status' => array(
          'data' => '',
        ),
        'operations' => array(),
      ),
      'class' => array('facet-source'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $groups = $this->loadGroups();
    $list['#attached']['library'][] = 'facetapi/drupal.facetapi.admin_css';

    $list['#type'] = 'container';

    $list['facet_sources'] = array(
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#rows' => array(),
      '#empty' => $groups['lone_facets'] ? '' : $this->t('There are no facet sources or facets defined.'),
      '#attributes' => array(
        'id' => 'facetapi-groups-list',
      ),
    );

    foreach ($groups['facet_source_groups'] as $facet_source_group) {
      $list['facet_sources']['#rows'][$facet_source_group['facet_source']['id']] = $this->buildFacetSourceRow($facet_source_group['facet_source']);
      foreach ($facet_source_group['facets'] as $i => $facet) {
        $list['facet_sources']['#rows'][$facet->id()] = $this->buildRow($facet);
      }
    }

    // Output the list of facets without a facet source separately.
    if (!empty($groups['lone_facets'])) {
      $list['lone_facets']['heading']['#markup'] = '<h3>' . $this->t('Facets not currently associated with any facet source') . '</h3>';
      $list['lone_facets']['table'] = array(
        '#type' => 'table',
        '#header' => $this->buildHeader(),
        '#rows' => array(),
      );

      foreach ($groups['lone_facets'] as $entity) {
        $list['lone_facets']['table']['#rows'][$entity->id()] = $this->buildRow($entity);
      }
    }
    return $list;
  }

  /**
   * Loads facet sources and facets, grouped by facet sources.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface[][]
   *   An associative array with two keys:
   *   - facet sources: All available facet sources, each followed by all facets
   *     attached to it.
   *   - lone_facets: All facets that aren't attached to any facet source.
   */
  public function loadGroups() {
    $facet_source_plugin_manager = \Drupal::service('plugin.manager.facetapi.facet_source');
    $facets = $this->storage->loadMultiple();
    $facet_sources = $facet_source_plugin_manager->getDefinitions();

    $this->sortByStatusThenAlphabetically($facets);
//    $this->sortByStatusThenAlphabetically($facet_sources);

    $facet_source_groups = array();
    foreach ($facet_sources as $facet_source) {
      $facet_source_groups[$facet_source['id']] = [
        'facet_source' => $facet_source,
        'facets' => []
      ];

      foreach ($facets as $facet) {
        /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $facet */
        if ($facet->getFacetSource() == $facet_source['id']) {
          $facet_source_groups[$facet_source['id']]['facets'][$facet->id()] = $facet;
          // Remove this facet from $facet so it will finally only contain those
          // facets not belonging to any facet_source.
          unset($facets[$facet->id()]);
        }
      }
    }

    return array(
      'facet_source_groups' => $facet_source_groups,
      'lone_facets' => $facets,
    );
  }

  /**
   * Sorts an array of entities by status and then alphabetically.
   *
   * Will preserve the key/value association of the array.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface[] $entities
   *   An array of config entities.
   */
  protected function sortByStatusThenAlphabetically(array &$entities) {
    uasort($entities, function (ConfigEntityInterface $a, ConfigEntityInterface $b) {
      if ($a->status() == $b->status()) {
        return strnatcasecmp($a->label(), $b->label());
      }
      else {
        return $a->status() ? -1 : 1;
      }
    });
  }

}
