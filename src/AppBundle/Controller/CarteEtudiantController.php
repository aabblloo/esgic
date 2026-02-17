<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Etudiant;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Entity\EtudiantLastClasse;
use AppBundle\Entity\Parametres;
use AppBundle\Form\MyType\AnneeType;
use AppBundle\Form\MyType\ClasseType;
use AppBundle\Form\MyType\LettreType;
use http\QueryString;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @Route("carte-etudiant")
 * @IsGranted("ROLE_SAISIE_CARTE")
 */
class CarteEtudiantController extends Controller
{
    /**
     * @Route("/{id}", name="carte_etudiant_index")
     */
    public function indexAction(Request $request, Etudiant $etudiant)
    {
        $form = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('annee', AnneeType::class, [
                'label' => 'Année académique',
                'attr' => ['placeholder' => 'Ex: Octobre 2020'],
                'constraints' => [new NotBlank()],
            ])
            ->add('validite', null, [
                'label' => 'Validité',
                'attr' => ['placeholder' => 'Ex: Octobre 2020'],
                'constraints' => [new NotBlank()],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $etudiant = $em->getRepository(EtudiantLastClasse::class)->findOneBy(['etudiant' => $etudiant]);
            $liste[0] = $etudiant;

            return $this->render('carte_etudiant/index.html.twig', [
                'liste_etudiants' => $liste,
                'search' => $search,
            ]);
        }

        return $this->render('carte_etudiant/form.html.twig', [
            'etudiant' => $etudiant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/par/classe", name="carte_etudiant_liste")
     */
    public function listeAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('classe', ClasseType::class, [
                'constraints' => [new NotBlank(), new Valid()],
            ])
            ->add('annee', AnneeType::class, [
                'label' => 'Année',
                'constraints' => [new NotBlank(), new Valid()],
            ])
            ->add('lettre', LettreType::class, [
                'constraints' => [new Valid()],
            ])
            ->add('validite', null, [
                'label' => 'Validité',
                'attr' => ['placeholder' => 'Ex: Octobre 2020'],
                'constraints' => [new NotBlank()],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $param = [
                'classe' => $search['classe'],
                'anScolaire' => $search['annee'],
            ];

            if ($search['lettre']) $param['lettre'] = $search['lettre'];
            $etudiants = $em->getRepository(EtudiantLastClasse::class)->findBy($param);

            $critere = '';
            $param = [$search['annee']->getId(), $search['classe']->getId()];

            return $this->render('carte_etudiant/index.html.twig', [
                'liste_etudiants' => $etudiants,
                'search' => $search,
            ]);

        }

        return $this->render('carte_etudiant/form_liste.html.twig', [
            'titre' => 'Etat - Liste des étudiants par classe',
            'form' => $form->createView(),
        ]);
    }

}
