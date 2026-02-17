<?php

namespace AppBundle\Controller;

use AppBundle\Form\MyType\AnneeType;
use AppBundle\Form\MyType\ClasseType;
use AppBundle\Form\MyType\LettreType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class CarteScolaireController extends Controller
{
    /**
     * @Route("/carte_scolaire_par_classe", name="carte_scolaire")
     */
    public function indexAction(Request $request)
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
            $critere = '';
            $param = [$search['annee']->getId(), $search['classe']->getId()];

            if ($search['lettre']) {
                $critere = 'AND ec.lettre = ? ';
                $param[] = $search['lettre'];
            }

            $query = 'SELECT e.prenom, e.nom, e.matricule, e.lieu_naiss, e.last_classe, '
                . 'DATE_FORMAT(e.date_naiss, "%d-%m-%Y") AS date_naiss, '
                . 'e.telephone, f.code AS filiere, '
                . 'IF(e.photo IS NOT NULL, e.photo, "default.jpg") AS photo '
                . 'FROM sf3_etudiant e '
                . 'JOIN sf3_etudiant_classe ec ON e.id = ec.etudiant_id '
                . 'JOIN sf3_classe c on c.id = ec.classe_id '
                . 'JOIN sf3_filiere f ON f.id = c.filiere_id '
                . 'WHERE ec.an_scolaire_id = ? AND ec.classe_id = ? ' . $critere;

            $db = $this->getDoctrine()->getConnection();
            $stmt = $db->executeQuery($query, $param);
            $etudiants = $stmt->fetchAll();

            $vue = $this->renderView('carte_scolaire/liste.html.twig', [
                'etudiants' => $etudiants,
                'search' => $search,
            ]);

            return new Response($vue);

            /*
              $file = 'carte_scolaire_' . $search['classe']->getCode() . '_' . date('Y_m_d_His') . '.pdf';
              $options = [
              'margin-top' => 25,
              'margin-right' => 25,
              'margin-bottom' => 25,
              'margin-left' => 25,
              'header-font-size' => '7',
              'header-spacing' => '5',
              'header-left' => "ESGIC - CARTE D'INDENITE SCOLAIRE",
              'header-right' => "CLASSE : {$search['classe']->getCode()} - ANNEE : {$search['annee']->getNom()}",
              'footer-font-size' => '7',
              'footer-spacing' => '5',
              'footer-right' => '[page]/[toPage]',
              'footer-left' => '[date] [time]',
              ];

              return new Response($this->get('knp_snappy.pdf')->getOutputFromHtml($vue, $options, true), 200, [
              'Content-Type' => 'application/pdf',
              'Content-Disposition' => "inline; filename=\"{$file}\"",
              ]);
              // */
        }

        return $this->render('carte_scolaire/form.html.twig', [
            'titre' => 'Etat - Liste des étudiants par classe',
            'form' => $form->createView(),
        ]);
    }

}
