<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Logs;
use AppBundle\Entity\Classe;
use AppBundle\Form\ClasseType;
use AppBundle\Entity\ClasseMatiere;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Repository\ClasseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * @Route("/classe")
 */
class ClasseController extends Controller
{

    /**
     * @Route("/", name="classe_index", methods="GET")
     */
    public function indexAction()
    {
        return $this->render(
            'classe/classe_index.html.twig', [
                'titre'   => 'Liste des classes',
                'classes' => $this->getDoctrine()
                    ->getRepository(Classe::class)
                    ->findBy([], ['code' => 'asc']),
            ]
        );
    }

    /**
     * @Route("/ajouter", name="classe_new", methods="GET|POST")
     */
    public function newAction(Request $request)
    {
        $classe = new Classe();
        $form   = $this->createForm(ClasseType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($classe);
            $em->flush();
            $logs = new Logs(
                $this->getUser(), 'Insert', "Classe Id:{$classe->getId()}"
            );
            $em->persist($logs);
            $em->flush();
            $this->addFlash(
                'success',
                "La classe <b>{$classe->getCode()}</b> a été enregistrée avec succès."
            );

            return $this->redirectToRoute('classe_index');
        }

        return $this->render(
            'classe/classe_form.html.twig', [
                'titre'  => 'Enregistrer une classe',
                'classe' => $classe,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/modifier/{id}", name="classe_edit", methods="GET|POST")
     */
    public function editAction(Request $request, Classe $classe)
    {
        $form = $this->createForm(ClasseType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $em   = $this->getDoctrine()->getManager();
            $logs = new Logs(
                $this->getUser(), 'Update', "Classe Id:{$classe->getId()}"
            );
            $em->persist($logs);
            $em->flush();
            $this->addFlash(
                'success',
                "La classe <b>{$classe->getCode()}</b> a été modifiée avec succès."
            );

            return $this->redirectToRoute('classe_index');
        }

        return $this->render(
            'classe/classe_form.html.twig', [
                'titre'  => 'Modifier la classe',
                'classe' => $classe,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/supprimer/{id}", name="classe_delete", methods="GET|POST")
     */
    public function deleteAction(Request $request, Classe $classe)
    {
        $form = $this->createFormBuilder()
            ->add(
                'id', HiddenType::class, [
                    'attr'        => ['data' => $classe->getId()],
                    'constraints' => new EqualTo($classe->getId()),
                ]
            )
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $em              = $this->getDoctrine()->getManager();
            $classeMatieres  = $em->getRepository(ClasseMatiere::class)->findBy(
                ['classe' => $classe]
            );
            $classeEtudiants = $em->getRepository(EtudiantClasse::class)
                ->findBy(['classe' => $classe]);

            if ($classeMatieres) {
                $this->addFlash(
                    'danger',
                    "Impossible de supprimer <b>{$classe->getNom()}</b> car il y a des matières associées."
                );
            }

            if ($classeEtudiants) {
                $this->addFlash(
                    'danger',
                    "Impossible de supprimer <b>{$classe->getNom()}</b> car il y a des étudiants associés."
                );
            }

            if ($classeMatieres or $classeEtudiants) {
                return $this->redirectToRoute(
                    'classe_delete', ['id' => $classe->getId()]
                );
            }

            $em->remove($classe);
            $logs = new Logs(
                $this->getUser(), 'Delete', "Classe Id:{$classe->getId()}"
            );
            $em->persist($logs);
            $em->flush();
            $this->addFlash(
                'success',
                "La classe <b>{$classe->getCode()}</b> a été supprimée avec succès."
            );

            return $this->redirectToRoute('classe_index');
        }

        return $this->render(
            'classe/classe_delete.html.twig', [
                'titre'  => 'Supprimer la classe',
                'classe' => $classe,
                'form'   => $form->createView(),
            ]
        );
    }
}
