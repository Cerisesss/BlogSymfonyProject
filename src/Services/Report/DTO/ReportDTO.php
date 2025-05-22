<?php

namespace App\Services\Report\DTO;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class ReportDTO
{
    #[Assert\NotBlank]
    public User $reporter;
    public Post $post_reported;
    public string $reason;
    public \DateTimeImmutable $created_at;
}