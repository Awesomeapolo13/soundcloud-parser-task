<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Сущность трека
 *
 * @ORM\Entity(repositoryClass=TrackRepository::class)
 */
class Track
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", unique=true)
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
     * Идентификатор трека, присвоенный на сайте откуда получена информация о нем (например на SoundCloud)
     *
     * @ORM\Column(type="integer", unique="true")
     */
    private $resourceId;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="tracks")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getResourceId(): ?int
    {
        return $this->resourceId;
    }

    public function setResourceId(int $resourceId): self
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * Создает трек из переданных данных и объекта автора
     *
     * @param object $trackData
     * @param Author $author
     * @return Track
     */
    public static function create(object $trackData, Author $author): Track
    {
        return (new self)
            ->setTitle($trackData->title)
            ->setResourceId($trackData->id)
            ->setDuration($trackData->full_duration)
            ->setPlaybackCount($trackData->playback_count)
            ->setCommentsCount($trackData->comment_count)
            ->setAuthor($author)
        ;
    }

    /**
     * Производит создание коллекции треков
     *
     * @param iterable $tracks - коллекция треков
     * @param Author $author - автор треков
     * @return iterable
     */
    public static function createMany(iterable $tracks, Author $author): iterable
    {
        $newTracks = [];
        foreach ($tracks as $track) {
            $newTracks[] = self::create($track, $author);
        }

        return $newTracks;
    }
}
