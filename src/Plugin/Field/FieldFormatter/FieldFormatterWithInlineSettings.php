<?php

/**
 * @file
 * Contains
 *   \Drupal\field_formatter\Plugin\Field\FieldFormatter\FieldFormatterWithInlineSettings.
 */

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;

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

    $entity_type_id = $this->fieldDefinition->getSetting('target_type');
    $bundle_id = $this->fieldDefinition->getTargetBundle();
    $field_names = array_map(function (FieldDefinitionInterface $field_definition) {
      return $field_definition->getLabel();
    }, \Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle_id));

    $form['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Field name'),
      '#default_value' => $this->getSetting('field_name'),
      '#options' => $field_names,
    ];

    // @todo

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  protected function getViewDisplay() {
    if (!isset($this->viewDisplay)) {

      $display = EntityViewDisplay::create([
        'targetEntityType' => $this->fieldDefinition->getSetting('target_type'),
        'bundle' => $this->fieldDefinition->getTargetBundle(),
        'status' => TRUE,
      ]);
      $display->setComponent($this->getSetting('field_name'), [
        'type' => $this->getSetting('type'),
        'settings' => $this->getSetting('settings'),
      ]);
      $this->viewDisplay = $display;
    }
    return $this->viewDisplay;
  }

}
