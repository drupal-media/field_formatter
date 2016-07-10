<?php

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_formatter_limiter' formatter.
 *
 * @FieldFormatter(
 *   id = "field_formatter_limiter",
 *   label = @Translation("Limit the amount of deltas being output"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class FieldFormatterLimiter extends FieldWrapperBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['limit'] = 0;
    $settings['offset'] = 0;
    return $settings;
  }

  /**
   * Returns the cardinality setting of the field instance.
   */
  protected function getCardinality() {
    if ($this->fieldDefinition instanceof FieldDefinitionInterface) {
      return $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    if ($this->getCardinality() == 1) {
      return [];
    }

    $element['offset'] = [
      '#type' => 'number',
      '#title' => $this->t('Skip items'),
      '#default_value' => $this->getSetting('offset'),
      '#required' => TRUE,
      '#min' => 0,
      '#description' => $this->t('Number of items to skip from the beginning.')
    ];

    $element['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Display items'),
      '#default_value' => $this->getSetting('limit'),
      '#required' => TRUE,
      '#min' => 0,
      '#description' => $this->t('Number of items to display. Set to 0 to display all items.')
    ];

    return $element;
   }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $field_output = $this->getFieldOutput($items, $langcode);

    $offset = $this->getSetting('offset');
    // Array_slice needs NULL to show all elements.
    $limit = $this->getSetting('limit') == 0 ? NULL : $this->getSetting('limit');

    return array_slice($field_output, $offset, $limit);
  }

}
