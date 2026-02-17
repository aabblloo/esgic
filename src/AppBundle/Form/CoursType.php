<?php

namespace AppBundle\Form;

use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\Classe;
use AppBundle\Entity\Matiere;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\Professeur;
use AppBundle\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class CoursType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date',
                DateType::class, [
                    'widget' => 'single_text',
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
                ],
            ])
            ->add('site', EntityType::class, [
                'label' => 'Site',
                'class' => Site::class,
                'placeholder' => '',
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT,]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Cours',
            'attr' => ['novalidate' => 'novalidate']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_cours';
    }


}
