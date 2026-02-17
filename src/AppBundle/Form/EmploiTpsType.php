<?php

namespace AppBundle\Form;

use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\Classe;
use AppBundle\Entity\EmploiTps;
use AppBundle\Entity\Matiere;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\Professeur;
use AppBundle\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class EmploiTpsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('jour',
                ChoiceType::class, [
                    'choices' => EmploiTps::getJourSemaine(),
                    'placeholder' => 'Selectionnez un jour', // Optional placeholder
                    'attr' => [
                    'class' => 'chosen-select',  // If you're using the Chosen.js library for styling
                    'data-placeholder' => MyConfig::CHOSEN_TEXT,  // Custom placeholder from your config
            ],
                ])
            ->add('semaine',
                ChoiceType::class, [
                    'placeholder' => 'Selectionnez une semaine', // Optional placeholder
                    'choices' => array_flip(EmploiTps::getSemainesDeLAnnee()),
                    'attr' => [
                    'class' => 'chosen-select',  // If you're using the Chosen.js library for styling
                    'onchange' => 'chargerList()',
                    'data-placeholder' => MyConfig::CHOSEN_TEXT,  // Custom placeholder from your config
            ],
                ])
            ->add('debut',
                TimeType::class, [
                    'label' => 'Heure début',
                    'widget' => 'single_text',
                ])
            ->add('fin',
                TimeType::class, [
                    'label' => 'Heure fin',
                    'widget' => 'single_text',
                ])
            ->add('prof',
                EntityType::class, [
                    'label' => 'Professeur',
                    'class' => Professeur::class,
                    'choice_value' => 'id',
                    'choice_label' => 'prenomNom',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                ])
            ->add('classe',
                EntityType::class, [
                    'label' => 'Classe',
                    'class' => Classe::class,
                    'choice_value' => 'id',
                    'choice_label' => 'code',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                        'onchange' => 'chargerList()'
                    ],
                ])
            ->add('matiere', EntityType::class, [
                'label' => 'Matière',
                'class' => Matiere::class,
                'choice_value' => 'id',
                'choice_label' => 'code',
                'placeholder' => '',
                'attr' => [
                    'class' => 'chosen-select',
                    'data-placeholder' => MyConfig::CHOSEN_TEXT,
                ],
            ])
            ->add('anScolaire', EntityType::class, [
                'label' => 'Année scolaire',
                'class' => AnScolaire::class,
                'choice_value' => 'id',
                'choice_label' => 'nom',
                'placeholder' => '',
                'attr' => [
                    'class' => 'chosen-select',
                    'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    'onchange' => 'chargerList()'
                ],
            ])
            ->add('site', EntityType::class, [
                'label' => 'Site',
                'class' => Site::class,
                'placeholder' => '',
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT,'onchange' => 'chargerList()']
            ])
            ->add('exam', CheckboxType::class, [
            'label'    => 'Examen',
            'required' => false, // Allow unchecked state
            'attr' => [
                'class' => 'form-check-input',
            ],
        ]);
    
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\EmploiTps',
            'attr' => ['novalidate' => 'novalidate']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_emploi_du_temps';
    }


}
