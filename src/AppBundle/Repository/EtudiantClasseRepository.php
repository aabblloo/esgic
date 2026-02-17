<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Etudiant;
use AppBundle\Entity\EtudiantClasse;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class EtudiantClasseRepository extends EntityRepository
{

    /**
     * @return EtudiantClasse[] Returns an array of EtudiantClasse objects
     */
    public function paiementsAnClasse($id)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        //$rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('annee', 'annee');
        $rsm->addScalarResult('classe', 'classe');
        $rsm->addScalarResult('montant', 'montant');
        $rsm->addScalarResult('payer', 'payer');

        $sql = 'SELECT an.nom AS annee, cl.code AS classe, ec.montant AS montant,
            SUM(p.montant) AS payer
            FROM sf3_etudiant_classe ec
                LEFT JOIN sf3_an_scolaire an ON ec.an_scolaire_id = an.id
                LEFT JOIN sf3_classe cl ON ec.classe_id = cl.id
                LEFT JOIN sf3_paiement p ON ec.etudiant_id = p.etudiant_id AND ec.an_scolaire_id = p.an_scolaire_id
            WHERE ec.etudiant_id = :id
            AND p.nature = :nature
            GROUP BY an.id, cl.code, ec.montant
            ORDER BY an.nom DESC';

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters(['id' => $id, 'nature' => 'Scolarité']);
        return $query->getResult();
    }

    public function getEtudiantsByClasse($classe, $annee)
    {
        $sql = 'select ec, e from AppBundle:EtudiantClasse ec '
            . 'join ec.etudiant e '
            . 'where ec.classe = :classe '
            . 'and ec.anScolaire = :annee '
            . 'order by e.prenom asc, e.nom asc';
        return $query = $this->_em->createQuery($sql)
            ->setParameters(['classe' => $classe, 'annee' => $annee])
            ->getResult();
    }


    public function getLastClasse(Etudiant $etudiant)
    {
        return $query = $this->createQueryBuilder('ec')
            ->addSelect('a, c')
            ->join('ec.classe', 'c')
            ->join('ec.anScolaire', 'a')
            ->orderBy('a.nom', 'desc')
            ->where('ec.etudiant = :etudiant')
            ->setMaxResults(1)
            ->setParameter('etudiant', $etudiant)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
