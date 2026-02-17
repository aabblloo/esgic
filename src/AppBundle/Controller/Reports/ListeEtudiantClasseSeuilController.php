<?php

namespace AppBundle\Controller\Reports;

use AppBundle\Entity\Classe;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Entity\Filiere;
use AppBundle\Entity\MyConfig;
use AppBundle\Utils\ClasseAnneeSeuil;
use AppBundle\Utils\ClasseAnneeSeuilType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListeEtudiantClasseSeuilController extends Controller
{

    /**
     * @Route("/etat/liste_etudiants_par_classe_et_seuil", name="lst_etd_classe_seuil")
     */
    public function indexAction(Request $request)
    {
        $critere = new ClasseAnneeSeuil();
        

        $form = $this->createForm(ClasseAnneeSeuilType::class, $critere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $critere->typeSeuil;
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder();

            $query->select(
                   "ec.id,
                    ec.montant,
                    e.prenom,
                    e.nom,
                    e.matricule,
                    c.code as classe,
                    SUM(COALESCE(p.montant,0)) as payer,
                    (ec.montant - SUM(COALESCE(p.montant,0))) as reste,
                    CASE 
                        WHEN :type = 'pourcentage' 
                            THEN (ec.montant * :seuil / 100)
                        ELSE :seuil
                    END as seuil"
                    );

            $query->from(EtudiantClasse::class, 'ec')
                ->leftJoin('ec.etudiant', 'e')
                ->leftJoin('ec.paiements', 'p', 'WITH', 'p.nature = :nature')
                ->leftJoin('ec.classe', 'c')
                ->leftJoin('c.filiere', 'f')
                ->where('ec.anScolaire = :annee')
                ->orderBy('c.code', 'asc')
                ->addOrderBy('e.nom', 'asc')
                ->addOrderBy('e.prenom', 'asc');

            // ---- PARAMETRES ----
            $valeurSeuil = ($type === 'pourcentage')
                ? $critere->seuil
                : $critere->montantSeuil;

            $query->setParameters([
                'annee'  => $critere->annee,
                'seuil'  => $valeurSeuil,
                'nature' => 'Scolarité',
                'type'   => $type,  
            ]);

            if ($critere->lettre) {
                $query->andWhere('ec.lettre = :lettre');
                $query->setParameter('lettre', $critere->lettre);
            }

            if ($critere->classe) {
                $query->andWhere('ec.classe = :classe');
                $query->setParameter('classe', $critere->classe);
            }

            if ($critere->filiere) {
                $query->andWhere('c.filiere = :filiere');
                $query->setParameter('filiere', $critere->filiere);
            }

            if ($critere->site) {
                $query->andWhere('e.site = :site');
                $query->setParameter('site', $critere->site);
            }

            $query->groupBy('ec.id')
                ->having('SUM(COALESCE(p.montant,0)) <= seuil');

            $etdClasses = $query->getQuery()->getResult();

            $vue = $this->renderView('etat/liste_etudiant_classe_seuil.html.twig', [
                'critere' => $critere,
                'etdClasses' => $etdClasses,
                'asset' => MyConfig::asset(),
            ]);

            return new Response($vue);
        }

        return $this->render('etat/liste_etudiant_classe_seuil_form.html.twig', [
            'titre' => 'Etat - Liste des étudiants ayant payés un montant en dessous d’un seuil par classe',
            'form' => $form->createView(),
        ]);
    }



    



    /* public function indexAction(Request $request)
    {
        $critere = new ClasseAnneeSeuil();

        $form = $this->createForm(ClasseAnneeSeuilType::class, $critere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder();
            $query->select('ec.id, ec.montant, e.prenom, e.nom, e.matricule,c.code as classe, sum(coalesce(p.montant,0)) as payer, (ec.montant - sum(coalesce(p.montant,0))) as reste, ((ec.montant * :seuil)/100) as seuil')
                ->from(EtudiantClasse::class, 'ec')
                ->leftJoin('ec.etudiant', 'e')
                ->leftJoin('ec.paiements', 'p', 'WITH', 'p.nature = :nature')
                ->leftJoin('ec.classe', 'c')
                ->leftJoin('c.filiere', 'f')
                ->where('ec.anScolaire = :annee')
                //->andWhere('p.nature = :nature')
                ->orderBy('c.code', 'asc')
                ->addOrderBy('e.nom', 'asc') 
                ->addOrderBy('e.prenom', 'asc')
                ->setParameters([
                    'annee' => $critere->annee,
                    'seuil' => $critere->seuil,
                    'nature' => 'Scolarité',
                ]);

            if ($critere->lettre) {
                $query->andWhere('ec.lettre = :lettre');
                $query->setParameter('lettre', $critere->lettre);
            }

            if ($critere->classe) {
                $query->andWhere('ec.classe = :classe');
                $query->setParameter('classe', $critere->classe);
            }

            if ($critere->filiere) {
                $query->andWhere('c.filiere = :filiere');
                $query->setParameter('filiere', $critere->filiere);
            }

            if ($critere->site) {
                $query->andWhere('e.site = :site');
                $query->setParameter('site', $critere->site);
            }

            $query->groupBy('ec.id, e.id')
                ->having('payer <= seuil');

            $etdClasses = $query->getQuery()->getResult();

            $vue = $this->renderView('etat/liste_etudiant_classe_seuil.html.twig', [
                'critere' => $critere,
                'etdClasses' => $etdClasses,
                'asset' => MyConfig::asset(),
            ]);

            return new Response($vue);

            /*
        $file = 'liste_etudiants_par_classe_et_seuil_' . date('Y_m_d_His') . '.pdf';
        $options = MyConfig::printOption();

        return new PdfResponse(
        $this->get('knp_snappy.pdf')->getOutputFromHtml($vue),
        $file
        );

        
        }

        return $this->render('etat/liste_etudiant_classe_seuil_form.html.twig', [
            'titre' => 'Etat - Liste des étudiants en dessous d’un seuil par classe',
            'form' => $form->createView(),
        ]);
    }
 */

    /**
     * @Route("/get_classes/{id}", name="get_classes", requirements={"id":"\d+"})
     */
    public function getClasses(EntityManagerInterface $em, Filiere $filiere)
    {
        $classes = $em->getRepository(Classe::class)->findBy(['filiere' => $filiere]);

        $result = [];
        foreach ($classes as $classe) {
            $result[] = [
                'id' => $classe->getId(),
                'text' => $classe->getCodeNom(),
            ];
        }

        return new JsonResponse($result);
    }

}
