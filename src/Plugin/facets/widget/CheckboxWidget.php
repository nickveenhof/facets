<?php

/**
 * @file
 * Contains \Drupal\facets\Plugin\facets\widget\CheckboxWidget.
 */

namespace Drupal\facets\Plugin\facets\widget;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\facets\FacetInterface;
use Drupal\facets\Widget\WidgetInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The checkbox / radios widget.
 *
 * @FacetsWidget(
 *   id = "checkbox",
 *   label = @Translation("List of checkboxes"),
 *   description = @Translation("A configurable widget that shows a list of checkboxes"),
 * )
 */
class CheckboxWidget implements WidgetInterface, FormInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // Execute all the things.
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $form_builder = \Drupal::getContainer()->get('form_builder');

    // The form builder's getForm method accepts 1 argument in the interface,
    // the form ID. Extra arguments get passed into the form states addBuildInfo
    // method. This way we can pass the facet to the ::buildForm method, it uses
    // FormState::getBuildInfo to get the facet out.
    $build = $form_builder->getForm(static::class, $facet);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {

    $form['show_numbers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the amount of results'),
    ];

    if (!is_null($config)) {
      $widget_configs = $config->get('widget_configs');
      if (isset($widget_configs['show_numbers'])) {
        $form['show_numbers']['#default_value'] = $widget_configs['show_numbers'];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType($query_types) {
    return $query_types['string'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facets_checkbox_widget';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\facets\FacetInterface $facet */
    // Get the facet form the build info, see the remark in ::build to know
    // where this comes from.
    $build_info = $form_state->getBuildInfo();
    $facet = $build_info['args'][0];

    /** @var \Drupal\facets\Result\Result[] $results */
    $results = $facet->getResults();

    $configuration = $facet->getWidgetConfigs();
    $show_numbers = (bool) (isset($configuration['show_numbers']) ? $configuration['show_numbers'] : FALSE);
    $form[$facet->getFieldAlias()] = [
      '#type' => 'checkboxes',
      '#title' => $facet->getName(),
    ];

    $options = array();
    foreach ($results as $result) {
      $text = $result->getDisplayValue();
      if ($show_numbers) {
        $text .= ' (' . $result->getCount() . ')';
      }

      $options[$result->getRawValue()] = $text;

      if ($result->isActive()) {
        $form[$facet->getFieldAlias()]['#default_value'][] = $result->getRawValue();
      }
    }

    $form[$facet->getFieldAlias()]['#options'] = $options;

    $form[$facet->id() . '_submit'] = [
      '#type' => 'submit',
      '#value' => 'submit',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    /** @var \Drupal\facets\FacetInterface $facet */
    $build_info = $form_state->getBuildInfo();
    $facet = $build_info['args'][0];

    $result_link = FALSE;
    $active_items = [];

    foreach ($values[$facet->getFieldAlias()] as $key => $value) {
      if ($value !== 0) {
        $active_items[] = $value;
      }
    }

    foreach ($facet->getResults() as $result) {
      if (in_array($result->getRawValue(), $active_items)) {
        $result_link = $result->getUrl();
      }
    }

    // We have an active item, so we redirect to the page that has that facet
    // selected. This should be an absolute link because RedirectResponse is a
    // symfony class that requires a full URL.
    if ($result_link instanceof Url) {
      $result_link->setAbsolute();
      $form_state->setResponse(new RedirectResponse($result_link->toString()));
      return;
    }

    // The form was submitted but nothing was active in the form, we should
    // still redirect, but the url for the new page can't come from a result.
    // So we're redirecting to the facet source's page.
    $link = Url::fromUri($facet->getFacetSource()->getPath());
    $link->setAbsolute();
    $form_state->setResponse(new RedirectResponse($link->toString()));
  }

}
