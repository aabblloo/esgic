<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Logs;
use AppBundle\Entity\Matiere;
use AppBundle\Form\MatiereType;
use AppBundle\Entity\ProfMatiere;
use AppBundle\Entity\ClasseMatiere;
use AppBundle\Entity\SemestreEtudiant;
use AppBundle\Repository\MatiereRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use AppBundle\Entity\Cours;

/**
 * @Route("/matiere")
 */
class MatiereController extends Controller
{

    /**
     * @Route("/", name="matiere_index", methods="GET")
     */
    public function indexAction()
    {
        return $this->render(
            'matiere/matiere_index.html.twig', [
                'titre' => 'Liste des matières',
                'matieres' => $this->getDoctrine()
                    ->getRepository(Matiere::class)
                    ->findBy([], ['code' => 'asc']),
            ]
        );
    }

    /**
     * @Route("/ajouter", name="matiere_new", methods="GET|POST")
     */
    public function newAction(Request $request)
    {
        $matiere = new Matiere();
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($matiere);
            $em->flush();
            $logs = new Logs(
                $this->getUser(), 'Insert', "Matière Id:{$matiere->getId()}"
            );
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "La matière <b>{$matiere->getCode()}</b> a été créée avec succès.");

            return $this->redirectToRoute('matiere_index');
        }

        return $this->render('matiere/matiere_form.html.twig', [
            'titre' => 'Enregistrer une matière',
            'matiere' => $matiere,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="matiere_edit", methods="GET|POST")
     */
    public function editAction(Request $request, Matiere $matiere)
    {
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $logs = new Logs(
                $this->getUser(), 'Update', "Matière Id:{$matiere->getId()}"
            );
            $em->persist($logs);
            $em->flush();
            $this->addFlash(
                'success',
                "La matière <b>{$matiere->getCode()}</b> a été modifiée avec succès."
            );

            return $this->redirectToRoute('matiere_index');
        }

        return $this->render(
            'matiere/matiere_form.html.twig', [
                'titre' => 'Modifier la matière',
                'matiere' => $matiere,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/supprimer/{id}", name="matiere_delete", methods="GET|POST")
     */
    public function deleteAction(Request $request, Matiere $matiere)
    {
        $form = $this->createFormBuilder()
            ->add(
                'id', HiddenType::class, [
                    'attr' => ['data' => $matiere->getId()],
                    'constraints' => new EqualTo($matiere->getId()),
                ]
            )
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $classeMatieres = $em->getRepository(ClasseMatiere::class)
                ->findBy(
                    ['matiere' => $matiere]
                );
            $semestreEtudiants = $em->getRepository(SemestreEtudiant::class)
                ->findBy(['matiere' => $matiere]);
            $profMatieres = $em->getRepository(ProfMatiere::class)->findBy(
                ['matiere' => $matiere]
            );
            $cours = $em->getRepository(Cours::class)->findBy(
                ['matiere' => $matiere]
            );

            if ($classeMatieres) {
                $this->addFlash(
                    'danger',
                    "Impossible de supprimer la matière <b>{$matiere->getCode()}</b> car il y a des classes associées."
                );
            }

            if ($semestreEtudiants) {
                $this->addFlash(
                    'danger',
                    "Impossible de supprimer la matière <b>{$matiere->getCode()}</b> car il ya des étudiants associés."
                );
            }

            if ($profMatieres) {
                $this->addFlash(
                    'danger',
                    "Impossible de supprimer la matière <b>{$matiere->getCode()}</b> car il a des professeurs associés."
                );
            }

            if ($cours) {
                $this->addFlash(
                    'danger',
                    "Impossible de supprimer la matière <b>{$matiere->getCode()}</b> car il a des cours associés."
                );
            }

            if ($classeMatieres or $semestreEtudiants or $profMatieres
                or $cours
            ) {
                return $this->redirectToRoute(
                    'matiere_delete', ['id' => $matiere->getId()]
                );
            }

            $em->remove($matiere);
            $logs = new Logs(
                $this->getUser(), 'Delete', "Matière Id:{$matiere->getId()}"
            );
            $em->persist($logs);
            $em->flush();
            $this->addFlash(
                'success',
                "La Matière <b>{$matiere->getCode()}</b> a été supprimée avec succès."
            );

            return $this->redirectToRoute('matiere_index');
        }

        return $this->render(
            'matiere/matiere_delete.html.twig', [
                'titre' => 'Supprimer la matière',
                'matiere' => $matiere,
                'form' => $form->createView(),
            ]
        );
    }
}
