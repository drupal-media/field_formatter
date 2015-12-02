<?php

/**
 * @file
 * Contains
 *   \Drupal\field_formatter\Plugin\Field\FieldFormatter\FieldFormatterBase.
 */

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

abstract class FieldFormatterBase extends FormatterBase {

  /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface */
  protected $viewDisplay;

  /**
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  abstract protected function getViewDisplay();

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
    $entity = $items->getEntity();

    $build = $this->getViewDisplay()->build($entity);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = \Drupal::entityTypeManager()->getDefinition($field_definition->getTargetEntityTypeId());
    return $entity_type->isSubclassOf('\Drupal\Core\Entity\FieldableEntityInterface');
  }

}
