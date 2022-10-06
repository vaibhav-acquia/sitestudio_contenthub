<?php

namespace Drupal\sitestudio_contenthub_subscriber\EventSubscriber\Cdf;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\UnserializeAdditionalMetadataEvent;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles links during CohesionLayout entity unserialization.
 *
 * @see \Drupal\acquia_contenthub\Event\SerializeAdditionalMetadataEvent
 * @see \Drupal\acquia_contenthub\Event\UnserializeAdditionalMetadataEvent
 */
class CohesionLayoutUnserializationHandler implements EventSubscriberInterface {

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
    $events[AcquiaContentHubEvents::UNSERIALIZE_ADDITIONAL_METADATA][] = [
      'onUnserializeCohesionLayout',
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
   * Handle links during CohesionLayout unserialization.
   *
   * @param \Drupal\acquia_contenthub\Event\UnserializeAdditionalMetadataEvent $event
   *   Unserialize event for additional metadata.
   */
  public function onUnserializeCohesionLayout(UnserializeAdditionalMetadataEvent $event) {
    $entity = $event->getEntity();
    if (!$entity instanceof CohesionLayout) {
      return;
    }

    $cdf = $event->getCdf();
    $metadata = $cdf->getMetadata();
    if (isset($metadata['additional_data']['processed_links'])) {
      foreach ($metadata['additional_data']['processed_links'] as &$element) {
        foreach ($element as &$link_reference) {
          $linked_entity = $this->entityTypeManager
            ->getStorage($link_reference['entity_type'])
            ->loadByProperties(['uuid' => $link_reference['entity_id']]);
          $linked_entity = reset($linked_entity);

          if ($linked_entity instanceof ContentEntityInterface) {
            $link_reference['entity_id'] = $linked_entity->id();
          }
          else {
            unset($link_reference);
          }
        }
      }
      $link_references = $metadata['additional_data']['processed_links'];
      unset($metadata['additional_data']['processed_links']);

      if (!empty($link_references)) {
        $layout_canvas = $entity->getLayoutCanvasInstance();
        $this->updateLinks($layout_canvas, $link_references);
        $entity->setJsonValue(json_encode($layout_canvas));
        $event->setEntity($entity);
      }
    }
    $event->stopPropagation();
  }

  /**
   * Updates links in LayoutCanvas entity.
   *
   * @param \Drupal\cohesion\LayoutCanvas\LayoutCanvas $layoutCanvas
   *   LayoutCanvas instance.
   * @param array $updated_links
   *   Array of updated links, keyed by their property_key in LayoutCanvas.
   */
  protected function updateLinks(LayoutCanvas &$layoutCanvas, array $updated_links) {
    foreach ($layoutCanvas->iterateCanvas() as &$element) {
      if (array_key_exists($element->getUUID(), $updated_links)) {
        if ($element->getModel()) {
          foreach ($updated_links[$element->getUUID()] as $updated_link) {
            $link = $element->getModel()->getProperty($updated_link['path']);
            if ($link) {
              $link = $updated_link['entity_type'] . '::' . $updated_link['entity_id'];
              $element->getModel()->setProperty($updated_link['path'], $link);
            }
          }
        }
      }
    }
  }

}
