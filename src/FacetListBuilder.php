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
use Drupal\Core\Url;

/**
 * Builds a listing of search index entities.
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
      'title' => $this->t('Facet'),
      'description' => [
        'data' => $this->t('Description'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
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
        'title' => array(
          'data' => $entity->label(),
          'class' => array('search-api-title'),
        ),
        'description' => array(
          'data' => 'Field: ' . $entity->getFieldAlias() . ', Widget: ' . $entity->getWidget(),
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
    $build = parent::render();
    $parameters = ['search_api_index' => 'default_index']; // TODO: Do not hardcode this value.
    $build['table']['#empty'] = $this->t('There are no facets defined. <a href=":link">Add new facet</a>.', [
      ':link' => Url::fromRoute('entity.facetapi_facet.add_form', $parameters)->toString()
    ]);
    return $build;
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
