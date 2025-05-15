<?php

namespace App\Entity;

use App\Repository\LikesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikesRepository::class)]
class Likes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_like = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post_like = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUserLike(): ?User
    {
        return $this->user_like;
    }

    public function setUserLike(?User $user_like): static
    {
        $this->user_like = $user_like;

        return $this;
    }

    public function getPostLike(): ?Post
    {
        return $this->post_like;
    }

    public function setPostLike(?Post $post_like): static
    {
        $this->post_like = $post_like;

        return $this;
    }
}
