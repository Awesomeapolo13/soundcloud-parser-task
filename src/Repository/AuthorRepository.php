<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Author|null find($id, $lockMode = null, $lockVersion = null)
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[]    findAll()
 * @method Author[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    /**
     * Находит автора по id и все его треки
     *
     * @param int $id
     * @return int|mixed|string
     * @throws NonUniqueResultException
     */
    public function findAuthorWithTracks(int $id)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.resourceId = :id')
            ->setParameter('id', $id)
            ->leftJoin('a.tracks', 't')
            ->addSelect('t')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
