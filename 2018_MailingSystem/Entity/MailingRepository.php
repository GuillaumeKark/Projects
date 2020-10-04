<?php

namespace Phinedo\OutilsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MailingRepository extends \Doctrine\ORM\EntityRepository
{
    public function mailToSend(){
        $builder = $this->createQueryBuilder('m');
        $builder
            ->where($builder->expr()->lt('m.dateEnvoi', ':dateNow'))
            ->setParameter(':dateNow', new \DateTime('now'))
            ->andWhere($builder->expr()->eq('m.state', ':state'))
            ->setParameter(':state', false)
        ;
        return $builder->getQuery()->getResult();
    }

    public function liste($page = 1){
        $builder = $this->createQueryBuilder('m');
        $builder
            ->orderBy('m.dateEnvoi', 'DESC')
            ->setFirstResult( Mailing::MAX_PAR_PAGE * ($page-1) )
            ->setMaxResults( Mailing::MAX_PAR_PAGE );

        return new Paginator($builder);
    }
}
