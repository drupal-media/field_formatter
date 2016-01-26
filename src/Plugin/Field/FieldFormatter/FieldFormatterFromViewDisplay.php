<?php

/**
 * @file
 * Contains \Drupal\field_formatter\Plugin\Field\FieldFormatter\FieldFormatter.
 */

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "field_formatter_from_view_display",
 *   label = @Translation("Field formatter from view display"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class FieldFormatterFromViewDisplay extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'view_display_id' => '',
      'field_name' => '',
    ];
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['view_display_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'entity_view_mode',
      '#default_value' => EntityViewMode::load($this->getSetting('view_display_id')),
    ];

    $form['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Field name'),
      '#default_value' => $this->getSetting('field_name'),
      '#options' => $this->getAvailableFieldNames(),
    ];

    return $form;
  }

  protected function getViewDisplay($bundle_id) {
    if (!isset($this->viewDisplay[$bundle_id])) {
      $field_name = $this->getSetting('field_name');
      // Odd that this is needed.
      list($entity_type_id, $view_mode) = explode('.', $this->getSetting('view_display_id'));
      if (($view_display_id = $this->getSetting('view_display_id')) && $view_display = EntityViewDisplay::load($entity_type_id . '.' . $bundle_id . '.' . $view_mode)) {
        /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $view_display */
        $components = $view_display->getComponents();
        foreach ($components as $component_name => $component) {
          if ($component_name != $field_name) {
            $view_display->removeComponent($component_name);
          }
        }
        $this->viewDisplay[$bundle_id] = $view_display;
      }
    }
    return $this->viewDisplay[$bundle_id];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($field_name = $this->getSetting('field_name')) {
      $summary[] = $this->t('Field %field_name displayed.', ['%field_name' => $field_name]);
    }
    else {
      $summary[] = $this->t('Field not configured.');
    }

    if ($display = $this->getSetting('view_display_id')) {
      $summary[] = $this->t('View display %display used.', ['%display' => $display]);
    }
    else {
      $summary[] = $this->t('View display not configured.');
    }

    return $summary;
  }



}
