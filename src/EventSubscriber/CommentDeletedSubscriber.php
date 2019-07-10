<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Comment;
use App\Service\ActivityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CommentDeletedSubscriber implements EventSubscriberInterface
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @param ActivityService $activityService
     */
    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['commentDeletedActivity', EventPriorities::POST_WRITE],
        ];
    }

    public function commentDeletedActivity(ViewEvent $event)
    {
        $comment = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$comment instanceof Comment || Request::METHOD_DELETE !== $method) {
            return;
        }

        $this->activityService->removeCommentActivity($comment);
    }
}