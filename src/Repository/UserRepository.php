<?php

namespace App\Repository;

use App\Criteria\UserCriteria;
use App\Entity\User;
use App\Enum\Sync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findOneByEmail(string $email)
    {
        return $this->findOneBy([
            'email' => $email
        ]);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    /**
     * @param UserCriteria $criteria
     * @return mixed
     */
    public function findUsersByCriteria(UserCriteria $criteria)
    {
        $qb = $this->findUsersByCriteriaQueryBuilder($criteria);

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param UserCriteria $criteria
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findUsersByCriteriaQueryBuilder(UserCriteria $criteria)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('user')
            ->from('App\Entity\User', 'user');

        if ($criteria->isEnabled() != null) {
            $qb->andWhere('user.enabled = :enabled')
                ->setParameter('enabled', $criteria->isEnabled());
        }

        if ($criteria->getSort()) {
            foreach ($criteria->getSort() as $column => $direction) {
                $qb->addOrderBy($qb->getRootAliases()[0] . '.' . $column, $direction);
            }
        }

        if ($criteria->getLimit()) {
            $qb->setMaxResults($criteria->getLimit());
        }

        return $qb;
    }

    /**
     * @param User $user
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(User $user, string $sync = Sync::YES)
    {
        $this->getEntityManager()->persist($user);
        if ($sync === Sync::YES) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
