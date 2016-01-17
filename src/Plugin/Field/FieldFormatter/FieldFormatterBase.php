<?php

/**
 * @file
 * Contains
 *   \Drupal\field_formatter\Plugin\Field\FieldFormatter\FieldFormatterBase.
 */

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;

abstract class FieldFormatterBase extends EntityReferenceFormatterBase {

  /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface */
  protected $viewDisplay;

  /**
   * @param string $bundle_id
   *   The bundle ID.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  abstract protected function getViewDisplay($bundle_id);

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
    $entities = $this->getEntitiesToView($items, $langcode);

    $build = [];
    foreach($entities as $delta => $entity) {
        $build[$delta] = $this->getViewDisplay($entity->bundle())->build($entity);
    }
    return $build;
  }

  protected function getAvailableFieldNames() {
    $array_off_field_names = [];
    $entity_type_id = $this->fieldDefinition->getSetting('target_type');
    $bundle_id = $this->fieldDefinition->getSetting('handler_settings');
    $bundle_id = $bundle_id['target_bundles'];
//  or  $bundle_id = reset($bundle_id);
    foreach($bundle_id as $id => $value) {
      $bundle_id = $value;
      $field_names = array_map(function (FieldDefinitionInterface $field_definition) {
        return $field_definition->getLabel();
      }, \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type_id, $bundle_id));
      $array_off_field_names = array_merge($array_off_field_names, $field_names);
    }
    return $array_off_field_names;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = \Drupal::entityTypeManager()->getDefinition($field_definition->getTargetEntityTypeId());
    return $entity_type->isSubclassOf(FieldableEntityInterface::class);
  }

}
