<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AutrePaiement;
use AppBundle\Entity\Logs;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Autrepaiement controller.
 *
 * @Route("autre-paiement")
 */
class AutrePaiementController extends Controller
{
    /**
     * Lists all autrePaiement entities.
     *
     * @Route("/", name="autrepaiement_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $autrePaiements = $em->getRepository('AppBundle:AutrePaiement')->findBy([], ['date' => 'desc']);

        return $this->render('autrepaiement/index.html.twig', array(
            'titre' => 'Liste des autres paiements',
            'autrePaiements' => $autrePaiements,
        ));
    }

    /**
     * Creates a new autrePaiement entity.
     *
     * @Route("/ajouter", name="autrepaiement_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $autrePaiement = new Autrepaiement();
        $form = $this->createForm('AppBundle\Form\AutrePaiementType', $autrePaiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $autrePaiement->setUserCreate($this->getUser()->getNameInitial());
            $em = $this->getDoctrine()->getManager();
            $em->persist($autrePaiement);
            $em->flush();
            $this->addFlash('success', 'Paiement enregistré avec succès.');
            $logs = new Logs($this->getUser(), 'Insert', "Autre Paiement Id:{$autrePaiement->getId()}");
            $em->persist($logs);
            $em->flush();
            return $this->redirectToRoute('autrepaiement_show', array('id' => $autrePaiement->getId()));
        }

        return $this->render('autrepaiement/new.html.twig', array(
            'titre' => "Enregistrement nouvel autre paiement",
            'autrePaiement' => $autrePaiement,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a autrePaiement entity.
     *
     * @Route("/fiche/{id}", name="autrepaiement_show")
     * @Method("GET")
     */
    public function showAction(AutrePaiement $autrePaiement)
    {
        $deleteForm = $this->createDeleteForm($autrePaiement);

        return $this->render('autrepaiement/show.html.twig', array(
            'titre' => 'Fiche autre paiement',
            'autrePaiement' => $autrePaiement,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to delete a autrePaiement entity.
     *
     * @param AutrePaiement $autrePaiement The autrePaiement entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(AutrePaiement $autrePaiement)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('autrepaiement_delete', array('id' => $autrePaiement->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Displays a form to edit an existing autrePaiement entity.
     *
     * @Route("/modifier/{id}", name="autrepaiement_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, AutrePaiement $autrePaiement)
    {
        $deleteForm = $this->createDeleteForm($autrePaiement);
        $editForm = $this->createForm('AppBundle\Form\AutrePaiementType', $autrePaiement);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $autrePaiement->setUserUpdate($this->getUser()->getNameInitial());
            $this->addFlash('success', 'Paiement modifié avec succès.');
            $logs = new Logs($this->getUser(), 'Update', "Autre Paiement Id:{$autrePaiement->getId()}");
            $em = $this->getDoctrine()->getManager();
            $em->persist($logs);
            $em->flush();
            return $this->redirectToRoute('autrepaiement_show', array('id' => $autrePaiement->getId()));
        }

        return $this->render('autrepaiement/edit.html.twig', array(
            'titre' => 'Edition autre paiement',
            'autrePaiement' => $autrePaiement,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a autrePaiement entity.
     *
     * @Route("/supprimer/{id}", name="autrepaiement_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, AutrePaiement $autrePaiement)
    {
        $form = $this->createDeleteForm($autrePaiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($autrePaiement);
            $this->addFlash('success', 'Autre paiement supprimé avec succès.');
            $logs = new Logs($this->getUser(), 'Delete', "Autre Paiement Id:{$autrePaiement->getId()}");
            $em->persist($logs);
            $em->flush();
        }

        return $this->redirectToRoute('autrepaiement_index');
    }
}
