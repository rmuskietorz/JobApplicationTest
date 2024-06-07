<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

        /**
         * @return Employee[] Returns an array of Employee objects
         */
        public function findAll(): array
        {
            return $this->createQueryBuilder('e')
                ->orderBy('e.employeeID', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }

        public function findOneById($value): ?Employee
        {
            return $this->createQueryBuilder('e')
                ->andWhere('e.employeeID = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }
}
