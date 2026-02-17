<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Logs;
use AppBundle\Entity\Remboursement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Remboursement controller.
 *
 * @Route("remboursement")
 * @IsGranted("ROLE_SUP_DIRECTEUR")
 */
class RemboursementController extends Controller
{
    /**
     * Lists all remboursement entities.
     *
     * @Route("/", name="remboursement_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $remboursements = $em->getRepository('AppBundle:Remboursement')->findAll();

        return $this->render('remboursement/index.html.twig', array(
            'remboursements' => $remboursements,
        ));
    }

    /**
     * Creates a new remboursement entity.
     *
     * @Route("/new", name="remboursement_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $remboursement = new Remboursement();
        $form = $this->createForm('AppBundle\Form\RemboursementType', $remboursement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($remboursement);
            $em->flush();

            return $this->redirectToRoute('remboursement_show', array('id' => $remboursement->getId()));
        }

        return $this->render('remboursement/new.html.twig', array(
            'remboursement' => $remboursement,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a remboursement entity.
     *
     * @Route("/{id}", name="remboursement_show")
     * @Method("GET")
     */
    public function showAction(Remboursement $remboursement)
    {
        $deleteForm = $this->createDeleteForm($remboursement);

        return $this->render('remboursement/show.html.twig', array(
            'remboursement' => $remboursement,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing remboursement entity.
     *
     * @Route("/{id}/edit", name="remboursement_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Remboursement $remboursement)
    {
        $deleteForm = $this->createDeleteForm($remboursement);
        $editForm = $this->createForm('AppBundle\Form\RemboursementType', $remboursement);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('remboursement_edit', array('id' => $remboursement->getId()));
        }

        return $this->render('remboursement/edit.html.twig', array(
            'remboursement' => $remboursement,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a remboursement entity.
     *
     * @Route("/delete/{id}", name="remboursement_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Remboursement $remboursement)
    {
        if ($this->isCsrfTokenValid('delete' . $remboursement->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($remboursement);
            $em->flush();
            $logs = new Logs($this->getUser(), 'Delete', "Remboursement Id:{$remboursement->getId()}");
            $em->persist($logs);
            $em->flush();

            $this->addFlash('success', "Remboursement de {$remboursement->getMontant()} supprimé avec succès.");
        }

        return $this->redirectToRoute('pret_show', ['id' => $remboursement->getPret()->getId()]);
    }

    /**
     * Creates a form to delete a remboursement entity.
     *
     * @param Remboursement $remboursement The remboursement entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Remboursement $remboursement)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('remboursement_delete', array('id' => $remboursement->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
