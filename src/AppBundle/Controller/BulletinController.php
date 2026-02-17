<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\Classe;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\Evaluation;
use AppBundle\Entity\Parents;
use AppBundle\Entity\Periode;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/bulletin")
 */
class BulletinController extends Controller
{

    /**
     * @Route("/{id}", name="bulletin_index", requirements={"id":"\d+"})
     */
    public function indexAction(Request $request, Etudiant $etudiant)
    {
        $em = $this->getDoctrine()->getManager();
        $parent = $em->getRepository(Parents::class)->findOneByUser($this->getUser());

        if ($parent && !$parent->getEtudiants()->contains($etudiant)) {
            throw $this->createAccessDeniedException('Accès non autorisé.');
        }

        return $this->render('bulletin/bulletin_index.html.twig', [
            'etudiant' => $etudiant,
            'bulletins' => $em->getRepository(Evaluation::class)->getEvaluationsByEtudiant($etudiant)
        ]);
    }

    /**
     * @Route("/fiche/{eid}/{aid}/{cid}/{pid}", name="bulletin_show",
     *          requirements={"eid":"\d+", "aid":"\d+", "cid":"\d+", "pid":"\d+"})
     * @ParamConverter("etudiant", options={"id" = "eid"})
     * @ParamConverter("annee", options={"id" = "aid"})
     * @ParamConverter("classe", options={"id" = "cid"})
     * @ParamConverter("periode", options={"id" = "pid"})
     */
    public function showAction(Request $request, Etudiant $etudiant, AnScolaire $annee, Classe $classe, Periode $periode)
    {

        $em = $this->getDoctrine()->getManager();
        $parent = $em->getRepository(Parents::class)->findOneByUser($this->getUser());

        if ($parent && !$parent->getEtudiants()->contains($etudiant)) {
            throw $this->createAccessDeniedException('Accès non autorisé.');
        }

        $eval = 'sf3_evaluation';
        $mat = 'sf3_matiere';
        $note = 'sf3_note';

        $query = "select {$mat}.code as matiere, "
            . "{$note}.note_classe as noteClasse, {$note}.note_compo as noteCompo, "
            . "{$note}.coeff as coeff, {$note}.moyenne as moyenne "
            . "from {$note} "
            . "join {$eval} on {$eval}.id = {$note}.evaluation_id "
            . "join {$mat} on {$mat}.id = {$eval}.matiere_id "
            . "where {$note}.etudiant_id = :eid "
            . "and {$eval}.an_scolaire_id = :aid "
            . "and {$eval}.classe_id = :cid "
            . "and {$eval}.periode_id = :pid "
            . "order by {$mat}.nom asc ";

        $db = $this->getDoctrine()->getConnection();
        $stmt = $db->executeQuery($query, [
            'eid' => $etudiant->getId(),
            'aid' => $annee->getId(),
            'cid' => $classe->getId(),
            'pid' => $periode->getId()
        ]);
        $notes = $stmt->fetchAll();

        $sommeCoeff = 0;
        $sommeMoyenne = 0;
        $moyenneGenerale = 0;

        foreach ($notes as $note) {
            if ($note['moyenne']) {
                $sommeCoeff += $note['coeff'];
                $sommeMoyenne += $note['moyenne'];
            }
        }

        if ($sommeCoeff) {
            $moyenneGenerale = $sommeMoyenne / $sommeCoeff;
        }

        return $this->render('bulletin/bulletin_show.html.twig', [
            'etudiant' => $etudiant,
            'annee' => $annee,
            'classe' => $classe,
            'periode' => $periode,
            'notes' => $notes,
            'sommeCoeff' => $sommeCoeff,
            'sommeMoyenne' => $sommeMoyenne,
            'moyenneGenerale' => $moyenneGenerale
        ]);
    }

}
