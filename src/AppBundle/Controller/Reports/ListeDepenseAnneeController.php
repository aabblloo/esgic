<?php

namespace AppBundle\Controller\Reports;

use AppBundle\Entity\Classe;
use AppBundle\Entity\Depense;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Entity\Paiement;
use AppBundle\Entity\Site;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ListeDepenseAnneeController extends Controller
{

    /**
     * @Route("/etat/liste_depenses_par_annee_scolaire", name="lst_dep_annee")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('anScolaire', EntityType::class, [
                'label' => 'Année scolaire',
                'class' => AnScolaire::class,
                'choice_value' => 'id',
                'choice_label' => 'nom',
                'placeholder' => '',
                'required' => true,
                'attr' => [
                    'class' => 'chosen-select',
                    'data-placeholder' => MyConfig::CHOSEN_TEXT,
                ],
                'constraints' => [new NotBlank(), new Valid()],
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'required' => false,
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT,]
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anScolaire = $form->getData()['anScolaire'];
            $site = $form->getData()['site'];
            $critere = '';
            $param = ['anScolaire' => $anScolaire];

            if ($site){
                $critere .= ' and d.site = :site';
                $param['site'] = $site;
            }

            $sql = 'select d from AppBundle\Entity\Depense d where d.anScolaire = :anScolaire ' . $critere . ' order by d.date desc';

            $query = $em->createQuery($sql);
            $query->setParameters($param);
            $depenses = $query->getResult();
            $total = 0;

            foreach ($depenses as $dep) {
                $total += $dep->getMontant();
            }

            $vue = $this->renderView('etat/liste_depense_annee.html.twig', [
                'titre' => 'Liste des dépenses',
                'depenses' => $depenses,
                'anScolaire' => $anScolaire,
                'site' => $site,
                'total' => $total,
                'asset' => MyConfig::asset(),
            ]);

            return new Response($vue);

            /*
            $file    = 'liste_depenses_par_annee_scolaire_'.date('Y_m_d_His')
                .'.pdf';
            $options = MyConfig::printOption();

            return new Response(
                $this->get('knp_snappy.pdf')
                    ->getOutputFromHtml($vue, $options, true), 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => "inline; filename=\"{$file}\"",
                ]
            );
            //*/
        }

        return $this->render('etat/liste_depense_annee_form.html.twig', [
            'titre' => 'Etat - Liste des dépenses par année scolaire',
            'form' => $form->createView(),
        ]);
    }
}
