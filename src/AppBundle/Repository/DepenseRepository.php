<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DepenseRepository extends EntityRepository
{

    /**
     * @return Depense[] Returns an array of Depense objects
     */
    public function findByPeriode($debut, $fin, $type, $site = null)
    {
        $debut = $debut->format('Y-m-d');
        $fin = $fin->format('Y-m-d');
        $criteres = '';
        $param = ['debut' => $debut, 'fin' => $fin];

        if ($type) {
            $criteres .= ' AND d.type = :type ';
            $param['type'] = $type;
        }

        if ($site) {
            $criteres .= ' AND d.site = :site ';
            $param['site'] = $site;
        }

        $sql = "SELECT d FROM AppBundle\Entity\Depense d "
            . "WHERE d.date BETWEEN :debut AND :fin "
            . " {$criteres} ORDER BY d.date DESC";
        $query = $this->_em->createQuery($sql);
        $query->setParameters($param);

        return $query->getResult();
    }

}
