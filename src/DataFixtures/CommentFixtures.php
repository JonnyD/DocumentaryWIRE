<?php

namespace App\DataFixtures;

use App\Enum\CommentStatus;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Comment;
use App\DataFixtures\DocumentaryFixtures;
use App\Entity\Documentary;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $documentary1 = $this->getDocumentary('documentary-1');
        $documentary2 = $this->getDocumentary('documentary-2');
        $documentary3 = $this->getDocumentary('documentary-3');

        $user1 = $this->getUser('user1');
        $user2 = $this->getUser('user2');
        $user3 = $this->getUser('user3');

        $comment1 = $this->createComment($documentary1, $user1, 'This is a comment 1', CommentStatus::PENDING);
        $comment2 = $this->createComment($documentary1, $user2, 'This is a comment 2', CommentStatus::PUBLISHED);
        $comment3 = $this->createComment($documentary2, $user3, 'This is a comment 3', CommentStatus::PENDING);
        $comment4 = $this->createComment($documentary2, $user1, 'This is a comment 4', CommentStatus::PUBLISHED);
        $comment5 = $this->createComment($documentary3, $user2, 'This is a comment 5', CommentStatus::PENDING);
        $comment6 = $this->createComment($documentary3, $user3, 'This is a comment 6', CommentStatus::PUBLISHED);

        $manager->persist($comment1);
        $manager->persist($comment2);
        $manager->persist($comment3);
        $manager->persist($comment4);
        $manager->persist($comment5);
        $manager->persist($comment6);
        $manager->flush();

        $this->createReference($comment1);
        $this->createReference($comment2);
        $this->createReference($comment3);
        $this->createReference($comment4);
        $this->createReference($comment5);
        $this->createReference($comment6);
    }

    /**
     * @param Documentary $documentary
     * @param User $user
     * @param string $commentText
     * @param int $commentStatus
     * @return Comment
     */
    private function createComment(Documentary $documentary, User $user, string $commentText, string $commentStatus)
    {
        $comment = new Comment();
        $comment->setDocumentary($documentary);
        $comment->setUser($user);
        $comment->setCommentText($commentText);
        $comment->setStatus($commentStatus);
        return $comment;
    }

    /**
     * @param Comment $comment
     */
    private function createReference(Comment $comment)
    {
        $this->addReference('comment.'.$comment->getCommentText(), $comment);
    }

    /**
     * @param string $slug
     * @return Documentary
     */
    private function getDocumentary(string $slug)
    {
        return $this->getReference('documentary.'.$slug);
    }

    /**
     * @param string $username
     * @return User
     */
    private function getUser(string $username)
    {
        return $this->getReference('user.'.$username);
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            DocumentaryFixtures::class,
            UserFixtures::class
        ];
    }
}