<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Logs;
use AppBundle\Entity\Pret;
use AppBundle\Entity\Remboursement;
use AppBundle\Form\RemboursementType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Pret controller.
 *
 * @Route("pret")
 * @IsGranted("ROLE_SUP_DIRECTEUR")
 */
class PretController extends Controller
{
    /**
     * Lists all pret entities.
     *
     * @Route("/", name="pret_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $prets = $em->getRepository('AppBundle:Pret')->findBy([], ['date' => 'desc']);

        return $this->render('pret/index.html.twig', array(
            'titre' => 'Liste des prêts',
            'prets' => $prets,
        ));
    }

    /**
     * Creates a new pret entity.
     *
     * @Route("/new", name="pret_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $pret = new Pret();
        $form = $this->createForm('AppBundle\Form\PretType', $pret);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($pret);
            $em->flush();
            $logs = new Logs($this->getUser(), 'Insert', "Pret Id:{$pret->getId()}");
            $em->persist($logs);
            $em->flush();

            $this->addFlash('success', "Prêt de <b>{$pret->getMontant()}</b> enregistré avec succès au compte de <b>{$pret->getProfesseur()->getPrenomNom()}</b>.");
            return $this->redirectToRoute('pret_show', array('id' => $pret->getId()));
        }

        return $this->render('pret/new.html.twig', array(
            'titre' => 'Enregistrement nouveau prêt',
            'pret' => $pret,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a pret entity.
     *
     * @Route("/show/{id}", name="pret_show")
     * @Method({"GET", "POST"})
     */
    public function showAction(Request $request, Pret $pret)
    {
        $em = $this->getDoctrine()->getManager();
        $remboursement = new Remboursement();
        $remboursement->setPret($pret);
        $rembForm = $this->createForm(RemboursementType::class, $remboursement);
        $rembForm->remove('pret');
        $rembForm->handleRequest($request);

        if ($rembForm->isSubmitted() and $rembForm->isValid()) {
            if ($pret->getMontant() < $pret->getTotalRemboursement() + $remboursement->getMontant()) {    
                $this->addFlash('warning', 'Attention le montant saisie provoquera un dépassement du montant prêté !');
                return $this->redirectToRoute('pret_show', ['id' => $pret->getId()]);
            }

            $em->persist($remboursement);
            $em->flush();
            $logs = new Logs($this->getUser(), 'Insert', "Remboursement Id:{$remboursement->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "Remboursement <b>{$remboursement->getMontant()}</b> enregistré avec succès.");
            return $this->redirectToRoute('pret_show', ['id' => $pret->getId()]);
        }

        $deleteForm = $this->createDeleteForm($pret);
        $remboursements = $em->getRepository(Remboursement::class)->findBy(['pret' => $pret], ['date' => 'desc']);

        return $this->render('pret/show.html.twig', array(
            'titre' => 'Fiche de prêt',
            'pret' => $pret,
            'delete_form' => $deleteForm->createView(),
            'remb_form' => $rembForm->createView(),
            'remboursements' => $remboursements,
            // 'total' => $this->getTotalRemboursement($pret),
        ));
    }

    /**
     * Displays a form to edit an existing pret entity.
     *
     * @Route("/edit/{id}", name="pret_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Pret $pret)
    {
        $deleteForm = $this->createDeleteForm($pret);
        $form = $this->createForm('AppBundle\Form\PretType', $pret);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($pret->getMontant() < $pret->getTotalRemboursement()) {
                $this->addFlash('warning', 'Attention le montant saisie est inférieur au total des paiements ce qui provoquerai une incohérence !');
                return $this->redirectToRoute('pret_edit', ['id' => $pret->getId()]);
            }

            $em = $this->getDoctrine()->getManager();
            $logs = new Logs($this->getUser(), 'Update', "Pret Id:{$pret->getId()}");
            $em->persist($logs);
            $em->flush($logs);

            $this->addFlash('success', "Prêt de <b>{$pret->getMontant()}</b> modifié avec succès au compte de <b>{$pret->getProfesseur()->getPrenomNom()}</b>.");
            return $this->redirectToRoute('pret_show', array('id' => $pret->getId()));
        }

        return $this->render('pret/edit.html.twig', array(
            'titre' => 'Edition du prêt',
            'pret' => $pret,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a pret entity.
     *
     * @Route("/delete/{id}", name="pret_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Pret $pret)
    {
        $form = $this->createDeleteForm($pret);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($pret);
            $logs = new Logs($this->getUser(), 'Delete', "Pret Id:{$pret->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "Le prêt de {$pret->getMontant()} supprimé avec succès.");
        }

        return $this->redirectToRoute('pret_index');
    }

    /**
     * Creates a form to delete a pret entity.
     *
     * @param Pret $pret The pret entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Pret $pret)
    {
        $confirm = "return confirm('Voulez-vous supprimer cet prêt ? Cette action supprimera aussi les paiements assocciés.')";
        return $this->createFormBuilder(null, ['attr' => ['class' => 'd-inline', 'onsubmit' => $confirm]])
            ->setAction($this->generateUrl('pret_delete', array('id' => $pret->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
