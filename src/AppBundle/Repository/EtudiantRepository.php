<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class EtudiantRepository extends EntityRepository
{

    /**
     * @return Etudiant[]
     */
    public function search($value)
    {
        $query = "SELECT e.id AS id, CONCAT(e.prenom, ' ', e.nom, ' - ', e.matricule) AS prenomNomMle,
        cy.code AS cycle, e.date_naiss, e.telephone, last_classe,
        IF(e.photo IS NOT NULL, e.photo, 'default.jpg') AS photoDefault
        FROM sf3_etudiant e JOIN sf3_cycle cy ON e.cycle_id = cy.id 
        WHERE MATCH(e.prenom, e.nom, e.telephone, e.date_naiss_str, e.matricule, e.lieu_naiss, e.quartier, e.last_classe) 
        AGAINST(:value IN BOOLEAN MODE)";

        $db = $this->_em->getConnection();
        $stmt = $db->executeQuery($query, ['value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * @return Etudiant[]
     */
    public function listeParClasse($classe, $anScolaire, $lettre, $site = null)
    {
        $criteres = '';
        $param = ['classe' => $classe, 'anScolaire' => $anScolaire];

        if ($lettre) {
            $criteres .= ' AND ec.lettre = :lettre ';
            $param['lettre'] = $lettre;
        }

        if ($site) {
            $criteres .= ' AND e.site = :site ';
            $param['site'] = $site;
        }

        $sql = "SELECT e FROM AppBundle\Entity\Etudiant e JOIN e.etudiantClasses ec 
        WHERE ec.classe = :classe AND ec.anScolaire = :anScolaire {$criteres} ORDER BY e.nom ASC, e.prenom ASC";
        $query = $this->_em->createQuery($sql);
        $query->setParameters($param);
        // $query->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);
        return $query->getResult();
    }

     /**
     * @return Etudiant[]
     */
    public function listeParrainnage($anScolaire, $site = null)
    {
        $criteres = '';
        $param = ['anScolaire' => $anScolaire];


        if ($site) {
            $criteres .= ' AND e.site = :site ';
            $param['site'] = $site;
        }

        $sql = "SELECT e FROM AppBundle\Entity\Etudiant e 
                JOIN e.etudiantClasses ec
                JOIN ec.classe c 
                JOIN e.professeur p
        WHERE ec.anScolaire = :anScolaire AND e.professeur != 'NULL' {$criteres} ORDER BY p.nom ASC, p.prenom ASC,  e.prenom ASC, e.nom ASC";
        $query = $this->_em->createQuery($sql);
        $query->setParameters($param);
        // $query->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);
        return $query->getResult();
    }

    /**
     * @return Etudiant[]
     */
    public function listeParFiliere($filiere, $anScolaire, $site = null)
    {
        $criteres = '';
        $param = ['filiere' => $filiere, 'anScolaire' => $anScolaire];

        if ($site){
            $criteres .= ' AND e.site = :site';
            $param['site'] = $site;
        }

        $sql = "SELECT e FROM AppBundle\Entity\Etudiant e JOIN e.etudiantClasses ec 
            JOIN ec.classe cl
        WHERE cl.filiere = :filiere AND ec.anScolaire = :anScolaire {$criteres} ORDER BY e.prenom ASC, e.nom ASC";
        $query = $this->_em->createQuery($sql);
        $query->setParameters($param);
        // $query->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);
        return $query->getResult();
    }

}
