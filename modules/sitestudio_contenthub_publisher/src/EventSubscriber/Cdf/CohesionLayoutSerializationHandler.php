<?php

namespace Drupal\sitestudio_contenthub_publisher\EventSubscriber\Cdf;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\SerializeAdditionalMetadataEvent;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles links during CohesionLayout entity serialization.
 *
 * @see \Drupal\acquia_contenthub\Event\SerializeAdditionalMetadataEvent
 */
class CohesionLayoutSerializationHandler implements EventSubscriberInterface {

  /**
   * EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AcquiaContentHubEvents::SERIALIZE_ADDITIONAL_METADATA][] = [
      'onSerializeCohesionLayout',
      100,
    ];

    return $events;
  }

  /**
   * CohesionLayoutSerializationHandler constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Handle links during CohesionLayout serialization.
   *
   * @param \Drupal\acquia_contenthub\Event\SerializeAdditionalMetadataEvent $event
   *   Serialize event for additional metadata.
   */
  public function onSerializeCohesionLayout(SerializeAdditionalMetadataEvent $event) {
    $entity = $event->getEntity();

    if (!$entity instanceof CohesionLayout) {
      return;
    }
    $layoutCanvas = $entity->getLayoutCanvasInstance();
    $linkReferences = $layoutCanvas->getLinksReferences();

    if (empty($linkReferences)) {
      return;
    }
    foreach ($linkReferences as &$element) {
      foreach ($element as &$linkReference) {
        $linkedEntity = $this->entityTypeManager
          ->getStorage($linkReference['entity_type'])
          ->load($linkReference['entity_id']);

        if ($linkedEntity instanceof ContentEntityInterface) {
          $linkReference['entity_id'] = $linkedEntity->uuid();
        }
      }

    }

    $cdf = $event->getCdf();
    $metadata = $cdf->getMetadata();
    $metadata['additional_data']['processed_links'] = $linkReferences;
    $cdf->setMetadata($metadata);
    $event->setCdf($cdf);

    $event->stopPropagation();
  }

}
