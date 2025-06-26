<?php

namespace App\Services\Comment\DTO;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class CommentDTO
{
    #[Assert\NotBlank]
    public string $content;

    #[Assert\NotNull]
    public User $user;

    #[Assert\NotBlank]
    public Post $post;
    
    public \DateTimeImmutable $created_at;
}