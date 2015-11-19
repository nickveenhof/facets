<?php

/**
 * @file
 * Contains \Drupal\facetapi\Config\FacetSourceConfigFactoryOverride.
 */

namespace Drupal\facetapi\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigCollectionInfo;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigFactoryOverrideBase;
use Drupal\Core\Config\ConfigRenameEvent;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Provides language overrides for the configuration factory.
 */
class FacetSourceConfigFactoryOverride extends ConfigFactoryOverrideBase implements FacetSourceConfigFactoryOverrideInterface, EventSubscriberInterface {

  use FacetSourceConfigCollectionNameTrait;

  /**
   * The configuration storage.
   *
   * Do not access this directly. Should be accessed through self::getStorage()
   * so that the cache of storages is used.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $baseStorage;

  /**
   * An array of configuration storages keyed by id.
   *
   * @var \Drupal\Core\Config\StorageInterface[]
   */
  protected $storages;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * An event dispatcher instance to use for configuration events.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The object used to override configuration data.
   *
   * @var \Drupal\facetapi\FacetSource\FacetSourceInterface
   */
  protected $facetSource;

  /**
   * The plugin manager for facet sources.
   *
   * @var \Drupal\facetapi\FacetSource\FacetSourcePluginManager
   */
  protected $facetSourcePluginManager;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   The configuration storage engine.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   An event dispatcher instance to use for configuration events.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The typed configuration manager.
   * @param \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facet_source_plugin_manager
   *   The plugin manager for facet sources.
   */
  public function __construct(StorageInterface $storage, EventDispatcherInterface $event_dispatcher, TypedConfigManagerInterface $typed_config, FacetSourcePluginManager $facet_source_plugin_manager) {
    $this->baseStorage = $storage;
    $this->eventDispatcher = $event_dispatcher;
    $this->typedConfigManager = $typed_config;
    $this->facetSourcePluginManager = $facet_source_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFacetSource() {
    return $this->facetSource;
  }

  /**
   * {@inheritdoc}
   */
  public function setFacetSource(FacetSourceInterface $facetSource = NULL) {
    $this->facetSource = $facetSource;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    if ($this->getFacetSource()) {
      $storage = $this->getStorage($this->getFacetSource());
      return $storage->readMultiple($names);
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getOverride($facetSource, $name) {
    $storage = $this->getStorage($facetSource);
    $data = $storage->read($name);

    $override = new FacetSourceConfigOverride(
      $name,
      $storage,
      $this->typedConfigManager,
      $this->eventDispatcher
    );

    if (!empty($data)) {
      $override->initWithData($data);
    }
    return $override;
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage($facetSource) {
    if (!isset($this->storages[$facetSource])) {
      $this->storages[$facetSource] = $this->baseStorage->createCollection($this->createConfigCollectionName($facetSource));
    }
    return $this->storages[$facetSource];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return $this->facetSource ? $this->getFacetSource() : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function installFacetSourceOverrides($facetSource) {
    /** @var \Drupal\Core\Config\ConfigInstallerInterface $config_installer */
    $config_installer = \Drupal::service('config.installer');
    $config_installer->installCollectionDefaultConfig($this->createConfigCollectionName($facetSource->getDefinition));
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    $langcode = $this->getFacetSourceFromCollectionName($collection);
    return $this->getOverride($langcode, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function addCollections(ConfigCollectionInfo $collection_info) {
    foreach ($this->facetSourcePluginManager->getDefinitions() as $definition) {
      $collection_info->addCollection($this->createConfigCollectionName($definition), $this);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    $name = $config->getName();
    foreach (\Drupal::languageManager()->getLanguages() as $language) {
      $config_translation = $this->getOverride($language->getId(), $name);
      if (!$config_translation->isNew()) {
        $this->filterOverride($config, $config_translation);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onConfigRename(ConfigRenameEvent $event) {
    $config = $event->getConfig();
    $name = $config->getName();
    $old_name = $event->getOldName();
    foreach (\Drupal::languageManager()->getLanguages() as $language) {
      $config_translation = $this->getOverride($language->getId(), $old_name);
      if (!$config_translation->isNew()) {
        $saved_config = $config_translation->get();
        $storage = $this->getStorage($language->getId());
        $storage->write($name, $saved_config);
        $config_translation->delete();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onConfigDelete(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    $name = $config->getName();
    foreach (\Drupal::languageManager()->getLanguages() as $language) {
      $config_translation = $this->getOverride($language->getId(), $name);
      if (!$config_translation->isNew()) {
        $config_translation->delete();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    $metadata = new CacheableMetadata();
    if ($this->getFacetSource()) {
      $metadata->setCacheContexts(['facetapi:facetsource']);
    }
    return $metadata;
  }
}
