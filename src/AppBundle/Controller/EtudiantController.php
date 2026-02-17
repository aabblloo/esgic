<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AutrePaiement;
use AppBundle\Entity\Classe;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Entity\Logs;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\MyTest;
use AppBundle\Entity\Site;
use AppBundle\Entity\Suivi;
use AppBundle\Form\EtudiantPhotoType;
use AppBundle\Form\EtudiantType;
use AppBundle\Form\MyType\AnneeType;
use AppBundle\Form\MyType\ClasseType;
use AppBundle\Form\MyType\LettreType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @Route("/etudiant")
 */
class EtudiantController extends Controller
{

    /**
     * @Route("/liste", name="etudiant_index", methods="GET|POST")
     */
    public function indexAction(Request $request)
    {
        if ($this->isGranted('ROLE_PARENT')) {
            return $this->redirectToRoute('espace_parent_index');
        }

        if ($this->isGranted('ROLE_SAISIE_CARTE') or $this->isGranted('ROLE_COMMUNICATION')) {
            return $this->redirectToRoute('etudiant_search');
        }

        $etudiants = [];

        $form = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('classe', ClasseType::class, [
                'placeholder' => 'Sélectionnez une classe',
                'constraints' => [new NotBlank(), new Valid()]
            ])
            ->add('anScolaire', AnneeType::class, [
                'placeholder' => 'Sélectionnez une année',
                'constraints' => [new NotBlank(), new Valid()]
            ])
            ->add('lettre', LettreType::class, [
                'placeholder' => 'Sélectionnez une lettre',
                'constraints' => [new Valid()], 'required' => false
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'placeholder' => '',
                'constraints' => [new Valid()], 'required' => false,
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => 'Sélectionnez un site']
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data['anScolaire'] = $form->getData()['anScolaire'];
            $data['classe'] = $form->getData()['classe'];

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder();
            $query->select('e, ec')
                ->from(Etudiant::class, 'e')
                ->join('e.etudiantClasses', 'ec')
                ->where('ec.anScolaire = :anScolaire')
                ->andWhere('ec.classe = :classe');

            if ($form->getData()['lettre']) {
                $data['lettre'] = $form->getData()['lettre'];
                $query->andWhere('ec.lettre = :lettre');
            }

            if ($form->getData()['site']) {
                $data['site'] = $form->getData()['site'];
                $query->andWhere('e.site = :site');
            }

            $query->setParameters($data);
            $etudiants = $query->getQuery()->getResult();
        }

        return $this->render('etudiant/etudiant_index.html.twig', [
            'titre' => 'Liste des Etudiant(es) par Classe',
            'etudiants' => $etudiants,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/rechercher", name="etudiant_search", methods="GET|POST")
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $etudiants = [];

        $form = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('search', SearchType::class, [
                'attr' => [
                    'placeholder' => "Rechercher étudiant Ex: Code Prénom Nom Date naiss…",
                    'class' => 'form-control',
                ],
                'constraints' => [new NotBlank()],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $value = $form->getData()['search'];
            $etudiants = $em->getRepository(Etudiant::class)->search($value);
        }

        return $this->render('etudiant/etudiant_search.html.twig', [
            'titre' => 'Rechercher un(e) étudiant(e)',
            'etudiants' => $etudiants,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ajouter", name="etudiant_new", methods="GET|POST")
     */
    public function newAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $etudiant = new Etudiant();
        // $etudiant = MyTest::generateEtudiant($etudiant);
        $password = $encoder->encodePassword($etudiant, $etudiant->getPasswordText());
        $etudiant->setPassword($password);
        $etudiant->setSite($this->getUser()->getSite());

        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            do {
                $etudiant->generateCode();
                $res = $em->getRepository(Etudiant::class)->findOneByMatricule($etudiant->getMatricule());
            } while ($res);

            $etudiant->upload();
            $etudiant->setDateNaissStr($etudiant->getDateNaiss()->format('d/m/Y'));
            //$etudiant->concat();
            $em->persist($etudiant);
            $em->flush();
            $logs = new Logs($this->getUser(), 'Insert', "Etudiant Id:{$etudiant->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "L'étudiant <b>{$etudiant->getPrenomNomMle()}</b> "
                . "a été enregistré avec succès.");

            return $this->redirectToRoute('etudiant_show', ['id' => $etudiant->getId()]);
        }

        return $this->render('etudiant/etudiant_form.html.twig', [
            'titre' => 'Inscription nouvel étudiant',
            'etudiant' => $etudiant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fiche/{id}", name="etudiant_show", methods="GET|POST",
     *                       requirements={"id":"\d+"})
     */
    public function showAction(Request $request, Etudiant $etudiant, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(EtudiantPhotoType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $etudiant->upload();
            $logs = new Logs($this->getUser(), 'Update', "Changer Photo Etudiant Id:{$etudiant->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "La photo a été changée avec succès.");
            return $this->redirectToRoute('etudiant_show', ['id' => $etudiant->getId()]);
        }

        if (!$etudiant->getLastClasse()) {
            $lastClasse = $em->getRepository(EtudiantClasse::class)->getLastClasse($etudiant);

            if ($lastClasse) {
                $etudiant->setLastClasse($lastClasse->getClasse()->getCode());
                $em->flush();
            }
        }

        if (!$etudiant->getPassword()) {
            $etudiant->generatePassword();
            $password = $encoder->encodePassword($etudiant, $etudiant->getPasswordText());
            $etudiant->setPassword($password);
            $em->flush();
        }

        return $this->render('etudiant/etudiant_show.html.twig', [
            'titre' => 'Fiche étudiant',
            'etudiant' => $etudiant,
            'paiements' => $em->getRepository(EtudiantClasse::class)->paiementsAnClasse($etudiant->getId()),
            'classes' => $em->getRepository(EtudiantClasse::class)->findBy(['etudiant' => $etudiant], ['anScolaire' => 'DESC']),
            'dossiers' => $em->getRepository(Dossier::class)->findBy(['etudiant' => $etudiant], ['nom' => 'ASC']),
            'form' => $form->createView(),
            'autre_paiements' => $em->getRepository(AutrePaiement::class)
                ->findBy(['etudiant' => $etudiant], ['date' => 'desc']),
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="etudiant_edit", methods="GET|POST",
     *                          requirements={"id":"\d+"})
     */
    public function editAction(Request $request, Etudiant $etudiant)
    {
        //$this->denyAccessUnlessGranted('ROLE_DIRECTEUR', null, "Accès refusé");

        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->remove('etudiantClasse');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $etudiant->upload();
            $etudiant->setDateNaissStr($etudiant->getDateNaiss()->format('d/m/Y'));
            //$etudiant->concat();
            $em = $this->getDoctrine()->getManager();
            $logs = new Logs($this->getUser(), 'Update', "Etudiant Id:{$etudiant->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "L'étudiant <b>{$etudiant->getPrenomNomMle()}</b> "
                . "a été modifié avec succès.");

            return $this->redirectToRoute('etudiant_show', ['id' => $etudiant->getId()]);
        }

        return $this->render('etudiant/etudiant_form.html.twig', [
            'titre' => "Modifier l'étudiant",
            'etudiant' => $etudiant,
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
    }

    /**
     * @Route("/supprimer/{id}", name="etudiant_delete", methods="GET|POST",
     *                           requirements={"id":"\d+"})
     */
    public function deleteAction(Etudiant $etudiant, Request $request)
    {
        //$this->denyAccessUnlessGranted('ROLE_DIRECTEUR', null, "Accès refusé");

        $form = $this->createFormBuilder()
            ->add('id', HiddenType::class, [
                'attr' => ['data' => $etudiant->getId()],
                'constraints' => new EqualTo($etudiant->getId()),
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $res = $this->verify($etudiant);

            if ($this->verify($etudiant)) {
                return $this->redirectToRoute('etudiant_delete', ['id' => $etudiant->getId()]);
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($etudiant);
            $logs = new Logs($this->getUser(), 'Delete', "Etudiant Id:{$etudiant->getId()}");
            $em->persist($logs);
            $em->flush();
            $this->addFlash('success', "L'étudiant <b>{$etudiant->getPrenomNomMle()}</b> "
                . "a été supprimé avec succès.");

            return $this->redirectToRoute('etudiant_index');
        }

        return $this->render('etudiant/etudiant_delete.html.twig', [
            'titre' => 'Suppression étudiant(e)',
            'etudiant' => $etudiant,
            'form' => $form->createView(),
        ]);
    }

    private function verify(Etudiant $etudiant)
    {
        $res = false;
        $msg = [];
        $em = $this->getDoctrine()->getManager();

        $suivis = $em->getRepository(Suivi::class)->findBy(['etudiant' => $etudiant]);
        if ($suivis) {
            $res = true;
            $msg[] = 'suivis';
        }

        $etudiantClasses = $em->getRepository(EtudiantClasse::class)->findBy(['etudiant' => $etudiant]);
        if ($etudiantClasses) {
            $res = true;
            $msg[] = 'classes';
        }

        $paiements = $em->getRepository(Suivi::class)->findBy(['etudiant' => $etudiant]);
        if ($paiements) {
            $res = true;
            $msg[] = 'paiements';
        }

        $dossiers = $em->getRepository(Dossier::class)->findOneBy(['etudiant' => $etudiant]);
        if ($dossiers) {
            $res = true;
            $msg[] = 'dossiers';
        }

        if ($msg) {
            $this->addFlash('danger', 'Impossible de supprimer car il y a des éléments asscocés : '
                . implode(', ', $msg));
        }

        return $res;
    }
}
