<?php

/**
 * @file
 * Contains \Drupal\facetapi\FacetListBuilder.
 */

namespace Drupal\facetapi;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds a listing of search index entities.
 */
class FacetListBuilder extends ConfigEntityListBuilder {

  /**
   * The entity storage class for the 'facetapi_facet' entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $serverStorage;

  /**
   * Constructs an IndexListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Entity\EntityStorageInterface $server_storage
   *   The entity storage class for the 'search_api_server' entity type.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EntityStorageInterface $server_storage) {
    parent::__construct($entity_type, $storage);

    $this->serverStorage = $server_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('entity.manager')->getStorage('facetapi_facet')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity instanceof FacetInterface) {
      $route_parameters['facetapi_facet'] = $entity->id();
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return array(
      'type' => $this->t('Type'),
      'title' => $this->t('Name'),
      'status' => array(
        'data' => $this->t('Status'),
        'class' => array('checkbox'),
      ),
    ) + parent::buildHeader();
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
      '#uri' => $entity->status() ? 'core/misc/icons/73b355/check.svg' : 'core/misc/icons/ea2800/error.svg',
      '#width' => 18,
      '#height' => 18,
      '#alt' => $status_label,
      '#title' => $status_label,
    );

    return array(
      'data' => array(
        'type' => array(
          'data' => $this->t('Facet'),
          'class' => array('search-api-type'),
        ),
        'title' => array(
          'data' => array(
              '#type' => 'link',
              '#title' => $entity->label(),
              '#suffix' => '<div>' . $entity->get('description') . '</div>',
            ) + $entity->urlInfo('canonical')->toRenderArray(),
          'class' => array('search-api-title'),
        ),
        'status' => array(
          'data' => $status_icon,
          'class' => array('checkbox'),
        ),
        'operations' => $row['operations'],
      ),
      'title' => $this->t('ID: @name', array('@name' => $entity->id())),
      'class' => array(
        Html::cleanCssIdentifier($entity->getEntityTypeId() . '-' . $entity->id()),
        $entity->status() ? 'search-api-list-enabled' : 'search-api-list-disabled'
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $entity_groups = $this->loadGroups();
    $list['#type'] = 'container';
    $list['#attached']['library'][] = 'search_api/drupal.search_api.admin_css';

    $list['facets'] = array(
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#rows' => array(),
      '#empty' => $this->t('There are no facets defined.'),
      '#attributes' => array(
        'id' => 'search-api-entity-list',
      ),
    );
    foreach ($entity_groups['facets'] as $facet_groups) {
      /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
      foreach ($facet_groups as $entity) {
        $list['facets']['#rows'][$entity->getEntityTypeId() . '.' . $entity->id()] = $this->buildRow($entity);
      }
    }

    return $list;
  }

  /**
   * Loads search servers and indexes, grouped by servers.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface[][]
   *   An associative array with two keys:
   *   - servers: All available search servers, each followed by all search
   *     indexes attached to it.
   *   - lone_indexes: All search indexes that aren't attached to any server.
   */
  public function loadGroups() {
    $indexes = $this->storage->loadMultiple();
    /** @var \Drupal\search_api\ServerInterface[] $servers */
    $servers = $this->serverStorage->loadMultiple();

    $this->sortByStatusThenAlphabetically($indexes);
    $this->sortByStatusThenAlphabetically($servers);

    $server_groups = array();
    foreach ($servers as $server) {
      $server_group = array(
        'server.' . $server->id() => $server,
      );

      foreach ($server->getIndexes() as $index) {
        $server_group['index.' . $index->id()] = $index;
        // Remove this index from $index so it will finally only contain those
        // indexes not belonging to any server.
        unset($indexes[$index->id()]);
      }

      $server_groups['server.' . $server->id()] = $server_group;
    }

    return array(
      'servers' => $server_groups,
      'lone_indexes' => $indexes,
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
