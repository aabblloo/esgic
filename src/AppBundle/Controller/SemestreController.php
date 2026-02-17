<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Semestre;
use AppBundle\Form\SemestreType;
use AppBundle\Repository\SemestreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/semestre")
 */
class SemestreController extends Controller
{
    /**
     * @Route("/", name="semestre_index", methods="GET")
     */
    public function index(SemestreRepository $semestreRepository): Response
    {
        return $this->render('semestre/index.html.twig', ['semestres' => $semestreRepository->findAll()]);
    }

    /**
     * @Route("/new", name="semestre_new", methods="GET|POST")
     */
    public function newAction(Request $request): Response
    {
        $semestre = new Semestre();
        $form = $this->createForm(SemestreType::class, $semestre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($semestre);
            $em->flush();

            return $this->redirectToRoute('semestre_index');
        }

        return $this->render('semestre/new.html.twig', [
            'semestre' => $semestre,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="semestre_show", methods="GET")
     */
    public function show(Semestre $semestre): Response
    {
        return $this->render('semestre/show.html.twig', ['semestre' => $semestre]);
    }

    /**
     * @Route("/{id}/edit", name="semestre_edit", methods="GET|POST")
     */
    public function edit(Request $request, Semestre $semestre): Response
    {
        $form = $this->createForm(SemestreType::class, $semestre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('semestre_edit', ['id' => $semestre->getId()]);
        }

        return $this->render('semestre/edit.html.twig', [
            'semestre' => $semestre,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="semestre_delete", methods="DELETE")
     */
    public function delete(Request $request, Semestre $semestre): Response
    {
        if ($this->isCsrfTokenValid('delete'.$semestre->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($semestre);
            $em->flush();
        }

        return $this->redirectToRoute('semestre_index');
    }
}
