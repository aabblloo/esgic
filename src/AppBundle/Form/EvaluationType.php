<?php

namespace AppBundle\Form;

use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\Classe;
use AppBundle\Entity\Matiere;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\Periode;
use AppBundle\Entity\Site;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('anScolaire', EntityType::class, [
                    'label' => 'Année scolaire',
                    'class' => AnScolaire::class,
                    'choice_value' => 'id',
                    'choice_label' => 'nom',
                    'placeholder' => '',
                    'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
                ])
                ->add('periode', EntityType::class, [
                    'label' => 'Evaluation',
                    'class' => Periode::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                                ->orderBy('p.id', 'ASC');
                    },
                    'choice_value' => 'id',
                    'choice_label' => 'nom',
                    'placeholder' => '',
                    'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
                ])
                ->add('classe', EntityType::class, [
                    'label' => 'Classe',
                    'class' => Classe::class,
                    'choice_value' => 'id',
                    'choice_label' => 'code',
                    'placeholder' => '',
                    'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
                ])
                ->add('matiere', EntityType::class, [
                    'label' => 'Matière',
                    'class' => Matiere::class,
                    'choice_value' => 'id',
                    'choice_label' => 'code',
                    'placeholder' => '',
                    'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
                ])
            ->add('site', EntityType::class, [
                'label' => 'Site',
                'class' => Site::class,
                'placeholder' => '',
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT,]
            ]);

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Evaluation'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'appbundle_evaluation';
    }

}
