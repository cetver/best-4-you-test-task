<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation as ApiPlatform;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as ORMAssert;

/**
 * @ORM\Entity(repositoryClass=\App\Repository\MovieRepository::class)
 * @ORM\Table(name="movies")
 *
 * @ORMAssert\UniqueEntity(fields="title", repositoryMethod="findByTitle")
 *
 * @ApiPlatform\ApiResource(
 *     shortName="movies",
 *     description="The movie object",
 *     formats={"jsonapi"},
 *     itemOperations={"get","delete","patch"},
 *     collectionOperations={
 *          "get",
 *          "post",
 *          "digest"={
 *              "method"="GET",
 *              "route_name"="api_movies_digest_collection"
 *          }
 *     })
 * )
 * @ApiPlatform\ApiFilter(
 *     \ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter::class,
 *     properties={
 *          "genre": "iexact",
 *          "title": "istart"
 *     }
 * )
 */
class Movie
{
    /**
     * Identifier
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="string", length=36)
     */
    private string $id;
    /**
     * Genre, e.g.: crime, comedy, etc
     *
     * @ORM\Column(type="string", length=30)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Constant\GenreConstant", "asArray"})
     */
    private string $genre;
    /**
     * Title, e.g.: The Shawshank Redemption
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=3, max=255)
     */
    private string $title;
    /**
     * The movie availability date for viewing
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank
     *
     * @ApiPlatform\ApiProperty(
     *     attributes={
     *          "openapi_context"={"format"="date"}
     *      }
     * )
     */
    private \DateTimeInterface $releasedAt;

    public function __construct(string $genre, string $title, \DateTimeInterface $releasedAt)
    {
        $this->genre = $genre;
        $this->title = $title;
        $this->releasedAt = $releasedAt;
    }

    /**
     * For CRUD operations only!
     *
     * @param string $genre
     */
    public function setGenre(string $genre): void
    {
        $this->genre = $genre;
    }

    /**
     * For CRUD operations only!
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * For CRUD operations only!
     *
     * @param \DateTimeInterface $releasedAt
     */
    public function setReleasedAt(\DateTimeInterface $releasedAt): void
    {
        $this->releasedAt = $releasedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGenre(): string
    {
        return $this->genre;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getReleasedAt(): \DateTimeInterface
    {
        return $this->releasedAt;
    }
}
