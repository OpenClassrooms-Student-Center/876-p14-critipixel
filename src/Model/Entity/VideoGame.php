<?php

declare(strict_types=1);

namespace App\Model\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Gedmo\Mapping\Annotation\Slug;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

#[Entity]
#[UniqueEntity('slug')]
#[Uploadable]
class VideoGame
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    private ?int $id = null;

    #[NotBlank]
    #[Length(max: 100)]
    #[Column(length: 100)]
    private string $title;

    #[Column(nullable: true)]
    private ?string $imageName = null;

    #[Column(nullable: true)]
    private ?int $imageSize = null;

    #[UploadableField(mapping: 'video_games', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[Column(unique: true)]
    #[Slug(fields: ['title'])]
    private string $slug;

    #[NotBlank]
    #[Column(type: Types::TEXT)]
    private string $description;

    #[Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $releaseDate;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt;

    #[Column(type: Types::TEXT, nullable: true)]
    private ?string $test = null;

    #[Range(min: 1, max: 5)]
    #[Column(nullable: true)]
    private ?int $rating = null;

    #[Column(nullable: true)]
    private ?int $averageRating = null;

    #[Embedded(class: NumberOfRatingPerValue::class, columnPrefix: '')]
    private NumberOfRatingPerValue $numberOfRatingsPerValue;

    /**
     * @var Collection<int, Tag>
     */
    #[ManyToMany(targetEntity: Tag::class)]
    #[JoinTable(name: 'video_game_tags')]
    private Collection $tags;

    /**
     * @var Collection<int, Review>
     */
    #[OneToMany(targetEntity: Review::class, mappedBy: 'videoGame')]
    private Collection $reviews;

    public function __construct()
    {
        $this->numberOfRatingsPerValue = new NumberOfRatingPerValue();
        $this->tags = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): VideoGame
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): VideoGame
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): VideoGame
    {
        $this->imageSize = $imageSize;
        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): VideoGame
    {
        $this->description = $description;
        return $this;
    }

    public function getReleaseDate(): DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(DateTimeImmutable $releaseDate): VideoGame
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function setTest(?string $test): VideoGame
    {
        $this->test = $test;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): VideoGame
    {
        $this->rating = $rating;
        return $this;
    }

    public function getAverageRating(): ?int
    {
        return $this->averageRating;
    }

    public function setAverageRating(?int $averageRating): VideoGame
    {
        $this->averageRating = $averageRating;
        return $this;
    }

    public function getNumberOfRatingsPerValue(): NumberOfRatingPerValue
    {
        return $this->numberOfRatingsPerValue;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function hasAlreadyReview(User $user): bool
    {
        return $this->reviews->exists(static fn (int $key, Review $review): bool => $review->getUser() === $user);
    }
}
