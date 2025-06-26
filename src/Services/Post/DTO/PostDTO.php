<?php

namespace App\Services\Post\DTO;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class PostDTO
{
    #[Assert\NotNull]
    public User $user;

    #[Assert\NotBlank]
    public string $title;

    #[Assert\NotBlank]
    public string $content;
    public \DateTimeImmutable $created_at;
}