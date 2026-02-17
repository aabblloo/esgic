<?php

namespace AppBundle\Utils;

use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\Classe;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\Site;
use AppBundle\Utils\ClasseAnnee;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClasseAnneeType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->setMethod('GET')
                ->add('classe', EntityType::class, [
                    'class' => Classe::class,
                    'choice_value' => 'id',
                    'choice_label' => 'codeNom',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => 'Classe',
                    ],
                ])
                ->add('annee', EntityType::class, [
                    'label' => 'Année scolaire',
                    'class' => AnScolaire::class,
                    'choice_value' => 'id',
                    'choice_label' => 'nom',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => 'Année',
                    ],
                ])
                ->add('site', EntityType::class, [
                    'label' => 'Année scolaire',
                    'class' => Site::class,
                    'placeholder' => '',
                    'required' => false,
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => 'Site',
                    ],
                ])
                ->add('lettre', ChoiceType::class, [
                    'choices' => EtudiantClasse::getLettres(),
                    'choice_label' => function ($choice) {
                        return $choice;
                    },
                    'placeholder' => '',
                    'required' => false,
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ClasseAnnee::class,
                //'attr' => ['novalidate' => 'novalidate']
        ]);
    }

}
