<?php

namespace AppBundle\Controller\Reports;

use AppBundle\Entity\Cours;
use AppBundle\Entity\MyConfig;
use AppBundle\Utils\ProfAnneePeriode;
use AppBundle\Utils\ProfAnneePeriodeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HonoraireDetailProfController extends Controller {

    /**
     * @Route("/etat/honoraire_prof_par_periode_detail", name="honoraire_prof_periode_detail")
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $search = new ProfAnneePeriode();
        $form   = $this->createForm(ProfAnneePeriodeType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($search->getProf()) {
                $cours = $em->getRepository(Cours::class)->getHonoraireProfPeriodeDetail($search);
                $total = 0;
                foreach ($cours as $cr) {
                    $total += $cr['montant'];

                }

                $details = array();
                $detailsDeux = array();
                function categoriserCours(array $cours, array &$details): void {
                    foreach ($cours as $c) {
                        $key = $c['sitenom'];
                
                        if (!isset($details[$key])) {
                            $details[$key] = [
                                'heure' => 0,
                                'montant' => 0,
                                'cours' => []
                            ];
                        }
                
                        $details[$key]['heure'] += (int)$c['heure'];
                        $details[$key]['montant'] += (int)$c['montant'];
                        $details[$key]['cours'][] = $c;
                    }
                }

                function categoriserCoursD(array $cours, array &$details): void {
                    foreach ($cours as $c) {
                        $key = $c['sitenom'];
                
                        if (!isset($details[$key])) {
                            $details[$key] = [
                                'heure' => 0,
                                'montant' => 0,
                                'licenceHeure' => 0,
                                'licenceMontant' => 0,
                                'masterHeure' => 0,
                                'masterMontant' => 0,
                                'cours' => []
                            ];
                        }
                
                        $details[$key]['heure'] += (int)$c['heure'];
                        $details[$key]['montant'] += (int)$c['montant'];
                
                        if (strpos($c['classe'], 'L') === 0) {
                            $details[$key]['licenceHeure'] += (int)$c['heure'];
                            $details[$key]['licenceMontant'] += (int)$c['montant'];
                        } elseif (strpos($c['classe'], 'M') === 0) {
                            $details[$key]['masterHeure'] += (int)$c['heure'];
                            $details[$key]['masterMontant'] += (int)$c['montant'];
                        }
                
                        $details[$key]['cours'][] = $c;
                    }
                }
                

                categoriserCours($cours,$details);
                categoriserCoursD($cours,$detailsDeux);


                // return var_dump($cours);
                $vue = $this->renderView('etat/honoraire_prof_periode_detail.html.twig', [
                    'search' => $search,
                    'cours'  => $cours,
                    'total'  => $total,
                    'details'  => $details,
                    'detailsDeux'  => $detailsDeux,
                    'asset'  => MyConfig::asset(),
                ]);

                return new Response($vue);
                /*
                  $file    = "honoraire_prof_periode_{$search->getProf()->getId()}_" . date('Y_m_d_His') . '.pdf';
                  $options = MyConfig::printOption();

                  return new Response($this->get('knp_snappy.pdf')->getOutputFromHtml($vue, $options, true), 200, [
                  'Content-Type'        => 'application/pdf',
                  'Content-Disposition' => "inline; filename=\"{$file}\"",
                  ]);
                  // */
            } else {
                $cours = $em->getRepository(Cours::class)->getHonoraireAllProfPeriode($search);
                $total = 0;
                foreach ($cours as $cr) {
                    $total += $cr['montant'];
                }

                $vue = $this->renderView('etat/honoraire_all_prof_periode.html.twig', [
                    'search' => $search,
                    'cours'  => $cours,
                    'total'  => $total,
                    'asset'  => MyConfig::asset(),
                ]);

                return new Response($vue);

                /*
                  $file = "honoraire_all_prof_periode_" . date('Y_m_d_His') . '.pdf';
                  $options = MyConfig::printOption();

                  return new Response(
                  $this->get('knp_snappy.pdf')
                  ->getOutputFromHtml($vue, $options, true), 200, [
                  'Content-Type' => 'application/pdf',
                  'Content-Disposition' => "inline; filename=\"{$file}\"",
                  ]
                  );
                  // */
            }
        }

        return $this->render('etat/honoraire_prof_periode_form_detail.html.twig', [
                    'form' => $form->createView(),
        ]);
    }

}
