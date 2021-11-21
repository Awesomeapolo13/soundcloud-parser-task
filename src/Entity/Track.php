<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TrackRepository::class)
 */
class Track
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $playbackCount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $commentsCount;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="tracks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPlaybackCount(): ?int
    {
        return $this->playbackCount;
    }

    public function setPlaybackCount(?int $playbackCount): self
    {
        $this->playbackCount = $playbackCount;

        return $this;
    }

    public function getCommentsCount(): ?int
    {
        return $this->commentsCount;
    }

    public function setCommentsCount(?int $commentsCount): self
    {
        $this->commentsCount = $commentsCount;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->authorId;
    }

    public function setAuthor(?Author $authorId): self
    {
        $this->authorId = $authorId;

        return $this;
    }
}
