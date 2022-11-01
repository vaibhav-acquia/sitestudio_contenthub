<?php

namespace Drupal\sitestudio_contenthub_publisher\EventSubscriber\EnqueueEligibility;

use Drupal\acquia_contenthub_publisher\ContentHubPublisherEvents;
use Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent;
use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens to entity eligibility to prevent enqueueing CohesionConfig Entities.
 */
class IsNotCohesionConfig implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ContentHubPublisherEvents::ENQUEUE_CANDIDATE_ENTITY][] =
      ['onEnqueueCandidateEntity', 60];
    return $events;
  }

  /**
   * Prevent cohesion config from enqueueing.
   *
   * @param \Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent $event
   *   The event to determine entity eligibility.
   *
   * @throws \Exception
   */
  public function onEnqueueCandidateEntity(ContentHubEntityEligibilityEvent $event) {
    // Never export CohesionConfig entities as main entities.
    // They should only be exported as dependencies.
    $entity = $event->getEntity();
    if ($entity instanceof CohesionConfigEntityBase) {
      $event->setEligibility(FALSE);
      $event->stopPropagation();
    }
  }

}
