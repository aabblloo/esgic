<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AutrePaiement;
use AppBundle\Entity\Logs;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\Paiement;
use AppBundle\Form\PaiementType;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Service\MyServices;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/etudiant/paiement")
 */
class EtudiantPaiementController extends AbstractController
{

    /**
     * @Route("/liste/{id}", name="etd_paie_index")
     */
    public function indexAction(Etudiant $etudiant)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder();
        $query->select('ec')
            ->from(EtudiantClasse::class, 'ec')
            ->addSelect('a, c')
            ->join('ec.anScolaire', 'a')
            ->join('ec.classe', 'c')
            ->where('ec.etudiant = :etudiant')
            ->orderBy('a.nom', 'desc')
            ->setParameter('etudiant', $etudiant);
        $etdClasses = $query->getQuery()->getResult();

        foreach ($etdClasses as $ec) {
            $query2 = $em->createQueryBuilder();
            $query2->select('p, b')
                ->from(Paiement::class, 'p')
                ->join('p.banque', 'b')
                ->where('p.anScolaire = :anScolaire')
                ->andWhere('p.etudiant = :etudiant')
                ->orderBy('p.date', 'desc')
                ->setParameters([
                    'anScolaire' => $ec->getAnScolaire(),
                    'etudiant' => $etudiant
                ]);
            $ec->paiementsTotal = $query2->getQuery()->getResult();
        }

        $autrePaiements = $em->getRepository(AutrePaiement::class)
            ->findBy(['etudiant' => $etudiant], ['date' => 'desc']);

        return $this->render('etudiant_paiement/etd_paie_index.html.twig', [
            'etudiant' => $etudiant,
            'etdClasses' => $etdClasses,
            'autre_paiements' => $autrePaiements,
        ]);
    }

    /**
     * @Route("/ajouter/{id}", name="etd_paie_new")
     */
    public function newAction(Request $request, ValidatorInterface $validator, Etudiant $etudiant)
    {
        $paiement = new Paiement();
        $paiement->setEtudiant($etudiant);
        $paiement->setSite($this->getUser()->getSite());

        $form = $this->createForm(PaiementType::class, $paiement);
        $form->remove('etudiant');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $ref = $paiement->getDate()->format('dmY') . '/' . $paiement->getBanque()->getNom() . '/' . $paiement->getRef();
            $test = $em->getRepository(Paiement::class)
                ->findBy(['ref' => $ref]);

            if ($test) {
                $this->addFlash('danger', 'Cette référence est déjà utilisé pour cette banque.');
                goto Afficher;
                // return $this->redirectToRoute('etd_paie_new', ['id' => $etudiant->getId()]);
            }

            $etdClasse = $em->getRepository(EtudiantClasse::class)->findOneBy([
                'etudiant' => $etudiant,
                'anScolaire' => $paiement->getAnScolaire(),
            ]);

            if (!$etdClasse) {
                $this->addFlash('danger', "Le paiement ne peut pas être enregistré car la classe n'est pas enregistrée.");
                goto Afficher;
                // return $this->redirectToRoute('paiement_new');
            }

            $paiement->setEtudiantClasse($etdClasse);

            // if ($this->testDepassement($paiement)) {
            if ($paiement->getNature() == Paiement::getNatures()[0] and MyServices::TestDepassementPaiement($em, $paiement, 0)) {
                $this->addFlash('danger', "Attention ce paiement provoquera un dépassement du montant dû<br>");
                goto Afficher;
            }

            $paiement->setUserCreate($this->getUser()->getNameInitial());
            $em->persist($paiement);
            $em->flush();
            $logs = new Logs($this->getUser(), 'Insert', "Paiement Id:{$paiement->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "Le paiement <b>{$paiement->getRef()}</b> a été enregistré avec succès.");
            return $this->redirectToRoute('etd_paie_index', ['id' => $etudiant->getId()]);
        }

        Afficher:
        return $this->render('etudiant_paiement/etd_paie_new.html.twig', [
            'etudiant' => $etudiant,
            'paiement' => $paiement,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="etd_paie_edit")
     */
    public function editAction(Request $request, Paiement $paiement)
    {
        $oldMontant = $paiement->getMontant();
        $etudiant = $paiement->getEtudiant();
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->remove('etudiant');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $etdClasse = $em->getRepository(EtudiantClasse::class)->findOneBy([
                'etudiant' => $etudiant,
                'anScolaire' => $paiement->getAnScolaire(),
            ]);

            if (!$etdClasse) {
                $this->addFlash('danger', "Le paiement ne peut pas être enregistré car la classe n'est pas enregistrée.");
                goto Afficher;
                // return $this->redirectToRoute('paiement_new');
            }

            $paiement->setEtudiantClasse($etdClasse);

            // if ($this->testDepassement($paiement, $oldMontant)) {
            if ($paiement->getNature() == Paiement::getNatures()[0] and MyServices::TestDepassementPaiement($em, $paiement, $oldMontant)) {
                $this->addFlash('danger', "Attention ce paiement provoquera un dépassement du montant dû");
                goto Afficher;
            }

            $paiement->setUserUpdate($this->getUser()->getNameInitial());
            $em->persist($paiement);
            $logs = new Logs($this->getUser(), 'Update', "Paiement Id:{$paiement->getId()}");
            $em->persist($logs);
            $this->addFlash('success', "Le paiement <b>{$paiement->getRef()}</b> a été modifié avec succès.");
            $em->flush();
            return $this->redirectToRoute('etd_paie_index', ['id' => $etudiant->getId()]);
        }

        Afficher:
        return $this->render('etudiant_paiement/etd_paie_edit.html.twig', [
            'etudiant' => $etudiant,
            'paiement' => $paiement,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/supprimer/{id}", name="etd_paie_delete", methods="GET|POST")
     * @IsGranted("ROLE_DIRECTEUR")
     */
    public function deleteAction(Request $request, Paiement $paiement)
    {
        $etudiant = $paiement->getEtudiant();
        $form = $this->createFormBuilder()
            ->add('id', HiddenType::class, [
                'attr' => ['data' => $paiement->getId()],
                'constraints' => new EqualTo($paiement->getId())
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($paiement);
            $em->flush();
            return $this->redirectToRoute('etd_paie_index', ['id' => $etudiant->getId()]);
        }

        return $this->render('etudiant_paiement/etd_paie_delete.html.twig', [
            'etudiant' => $etudiant,
            'paiement' => $paiement,
            'form' => $form->createView(),
        ]);
    }

    // public function testDepassement(Paiement $paiement, $oldMontant = 0)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $paiementsTotal = 0;
    //     $query = $em->createQuery('select sum(p.montant) as total from AppBundle\Entity\Paiement p '
    //         . 'where p.anScolaire = :annee and p.etudiant = :etudiant')
    //         ->setParameters([
    //             'annee' => $paiement->getAnScolaire(),
    //             'etudiant' => $paiement->getEtudiant()
    //         ]);
    //     $res = $query->getOneOrNullResult();
    //     if ($res) $paiementsTotal = $res['total'];
    //     $ec = $paiement->getEtudiantClasse();
    //     //$total = $ec->getPaiementsTotal() + $paiement->getMontant() - $oldMontant;
    //     $total = (int)$paiementsTotal + $paiement->getMontant() - $oldMontant;
    //     if ($ec->getMontant() < $total) {
    //         return true;
    //     }
    //     return false;
    // }
}
