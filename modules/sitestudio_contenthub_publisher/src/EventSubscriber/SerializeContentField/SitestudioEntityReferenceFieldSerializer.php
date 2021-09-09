<?php

namespace Drupal\sitestudio_contenthub_publisher\EventSubscriber\SerializeContentField;

use Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent;
use Drupal\acquia_contenthub\EventSubscriber\SerializeContentField\EntityReferenceFieldSerializer;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to entity field serialization to handle entity references.
 */
class SitestudioEntityReferenceFieldSerializer extends EntityReferenceFieldSerializer implements EventSubscriberInterface {

  const SITESTUDIO_REFERENCE_FIELD_TYPE = 'cohesion_entity_reference_revisions';

  /**
   * EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CohesionEntityReferenceFieldSerializer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Extract entity uuids as field values.
   *
   * @param \Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent $event
   *   The content entity field serialization event.
   *
   * @throws \Exception
   */
  public function onSerializeContentField(SerializeCdfEntityFieldEvent $event) {
    if ($event->getField()->getFieldDefinition()->getType() == self::SITESTUDIO_REFERENCE_FIELD_TYPE) {
      $this->fieldTypes[] = self::SITESTUDIO_REFERENCE_FIELD_TYPE;
      parent::onSerializeContentField($event);
    }
  }

}
