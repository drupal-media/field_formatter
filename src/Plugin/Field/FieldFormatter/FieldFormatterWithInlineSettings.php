<?php

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "field_formatter_with_inline_settings",
 *   label = @Translation("Field formatter with inline settings"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class FieldFormatterWithInlineSettings extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'field_name' => '',
      'type' => '',
      'settings' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Field name'),
      '#default_value' => $this->getSetting('field_name'),
      '#options' => $this->getAvailableFieldNames(),
    ];

    // @todo

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getViewDisplay($bundle_id) {
    if (!isset($this->viewDisplay[$bundle_id])) {

      $display = EntityViewDisplay::create([
        'targetEntityType' => $this->fieldDefinition->getSetting('target_type'),
        'bundle' => $bundle_id,
        'status' => TRUE,
      ]);
      $display->setComponent($this->getSetting('field_name'), [
        'type' => $this->getSetting('type'),
        'settings' => $this->getSetting('settings'),
      ]);
      $this->viewDisplay[$bundle_id] = $display;
    }
    return $this->viewDisplay[$bundle_id];
  }

}
