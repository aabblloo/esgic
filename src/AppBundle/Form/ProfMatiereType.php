<?php

namespace AppBundle\Form;

use AppBundle\Entity\Matiere;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\ProfMatiere;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfMatiereType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debut')
            ->add('fin', null, ['required' => false])
            ->add(
                'matiere',
                EntityType::class,
                [
                    'label'        => 'Matière',
                    'class'        => Matiere::class,
                    'choice_value' => 'id',
                    'choice_label' => 'code',
                    'placeholder'  => '',
                    'attr'         => [
                        'class'            => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => ProfMatiere::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_profmatiere';
    }

}
