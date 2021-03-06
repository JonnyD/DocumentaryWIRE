<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Enum\ActivityType;
use App\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Activity
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $component;

    /**
     * @ORM\Column(type="integer")
     */
    private $objectId;

    /**
     * @ORM\Column(type="integer")
     */
    private $groupNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="activities")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function setComponent(string $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setObjectId(int $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getGroupNumber(): ?int
    {
        return $this->groupNumber;
    }

    public function setGroupNumber(int $groupNumber): self
    {
        $this->groupNumber = $groupNumber;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return bool
     */
    public function isWatchlist(): bool
    {
        return ($this->type === ActivityType::WATCHLIST);
    }

    /**
     * @return bool
     */
    public function isJoined(): bool
    {
        return ($this->type === ActivityType::JOINED);
    }

    /**
     * @return bool
     */
    public function isComment(): bool
    {
        return ($this->type === ActivityType::COMMENT);
    }

    /**
     * @return bool
     */
    public function isAdded(): bool
    {
        return ($this->type === ActivityType::ADDED);
    }

    /**
     * @return bool
     */
    public function isFollow(): bool
    {
        return ($this->type === ActivityType::FOLLOW);
    }


}
