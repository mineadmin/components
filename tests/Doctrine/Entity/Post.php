<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'posts')]
class Post
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $published = false;

    /**
     * Many Posts have One User. This is the owning side.
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false)]
    private ?User $author = null;

    /**
     * One Post has Many Comments. This is the inverse side.
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['persist', 'remove'])]
    private Collection $comments;

    /**
     * Many Posts have Many Tags. This is the owning side.
     * @var Collection<int, Tag>
     */
    #[ORM\JoinTable(name: 'posts_tags')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'posts', cascade: ['persist'])]
    private Collection $tags;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): void
    {
        if (! $this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }
    }

    public function removeComment(Comment $comment): void
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): void
    {
        if (! $this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addPost($this);
        }
    }

    public function removeTag(Tag $tag): void
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removePost($this);
        }
    }
}
