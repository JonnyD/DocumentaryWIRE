<?php

namespace App\EventListener;

use App\Event\CommentEvent;
use App\Event\CommentEvents;
use App\Service\DocumentaryService;
use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCommentCountListener implements EventSubscriberInterface
{
    /**
     * @var DocumentaryService
     */
    private $documentaryService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param DocumentaryService $documentaryService
     * @param UserService $userService
     */
    public function __construct(
        DocumentaryService $documentaryService,
        UserService $userService
    )
    {
        $this->documentaryService = $documentaryService;
        $this->userService = $userService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            CommentEvents::COMMENT_CREATED => "onCommentCreated",
            CommentEvents::COMMENT_DELETED => "onCommentDeleted"
        );
    }

    /**
     * @param CommentEvent $commentEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onCommentCreated(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();

        $documentary = $comment->getDocumentary();
        $user = $comment->getUser();

        $this->documentaryService->updateCommentCountForDocumentary($documentary);
        $this->userService->updateCommentCountForUser($user);
    }

    /**
     * @param CommentEvent $commentEvent
     * @throws \Doctrine\ORM\ORMException
     */
    public function onCommentDeleted(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();

        $documentary = $comment->getDocumentary();
        $documentary->removeComment($comment);
        $this->documentaryService->updateCommentCountForDocumentary($documentary);

        $user = $comment->getUser();
        $user->removeComment($comment);
        $this->userService->updateCommentCountForUser($user);
    }
}