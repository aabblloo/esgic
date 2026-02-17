<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Logs;
use AppBundle\Entity\Cycle;
use AppBundle\Form\CycleType;
use AppBundle\Entity\Etudiant;
use AppBundle\Repository\CycleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * @Route("/cycle")
 */
class CycleController extends Controller
{
    /**
     * @Route("/", name="cycle_index", methods="GET")
     */
    public function indexAction()
    {
        return $this->render('cycle/cycle_index.html.twig', [
            'titre' => 'Liste des cycles',
            'cycles' => $this->getDoctrine()
                ->getRepository(Cycle::class)
                ->findBy([], ['code' => 'asc'])
        ]);
    }

    /**
     * @Route("/ajouter", name="cycle_new", methods="GET|POST")
     */
    public function newAction(Request $request)
    {
        $cycle = new Cycle();
        $form = $this->createForm(CycleType::class, $cycle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($cycle);
            $em->flush();
            $logs = new Logs($this->getUser(), 'Insert', "Cycle Id:{$cycle->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "Le cycle <b>{$cycle->getCode()}</b> a été crée avec succès.");
            return $this->redirectToRoute('cycle_index');
        }

        return $this->render('cycle/cycle_form.html.twig', [
            'titre' => 'Ajouter un cycle',
            'cycle' => $cycle,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="cycle_edit", methods="GET|POST")
     */
    public function editAction(Request $request, Cycle $cycle)
    {
        $form = $this->createForm(CycleType::class, $cycle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $logs = new Logs($this->getUser(), 'Update', "Cycle Id:{$cycle->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "Le cycle <b>{$cycle->getCode()}</b> a été modifiée avec succès.");
            return $this->redirectToRoute('cycle_index');
        }

        return $this->render('cycle/cycle_form.html.twig', [
            'titre' => 'Modfier le cycle',
            'cycle' => $cycle,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/supprimer/{id}", name="cycle_delete", methods="GET|POST")
     */
    public function deleteAction(Request $request, Cycle $cycle)
    {
        $form = $this->createFormBuilder()
            ->add('id', HiddenType::class, [
                'attr' => ['data' => $cycle->getId()],
                'constraints' => new EqualTo($cycle->getId())
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $etudiants = $em->getRepository(Etudiant::class)->findBy(['cycle' => $cycle]);

            if ($etudiants) {
                $this->addFlash('danger', "Impossible de supprimer le cycle <b>{$cycle->getCode()}</b> car il y a des etudiants associés.");
                return $this->redirectToRoute('cycle_delete', ['id' => $cycle->getId()]);
            }

            $em->remove($cycle);
            $logs = new Logs($this->getUser(), 'Delete', "Cycle Id:{$cycle->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "Le cycle <b>{$cycle->getCode()}</b> a été supprimé avec succès.");
            return $this->redirectToRoute('cycle_index');
        }

        return $this->render('cycle/cycle_delete.html.twig', [
            'titre' => 'Supprimer le cycle',
            'cycle' => $cycle,
            'form' => $form->createView(),
        ]);
    }
}
