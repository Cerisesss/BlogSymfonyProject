<?php

namespace App\Repository;

use App\Entity\Likes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Likes>
 */
class LikesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Likes::class);
    }

    /**
     * @return int
     */
    public function totalLikesPerPost(int $postId)
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.post_like = :postId')
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @return int
     */
    public function totalLikesPerComment(int $commentId)
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.comment_like = :commentId')
            ->setParameter('commentId', $commentId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Likes[] Returns an array of Likes objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Likes
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
