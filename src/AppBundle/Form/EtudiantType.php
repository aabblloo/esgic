<?php

namespace AppBundle\Form;

use AppBundle\Entity\Cycle;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\Professeur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use AppBundle\Entity\EtudiantClasse;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use AppBundle\Entity\MyConfig;

class EtudiantType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('matricule')
            ->add('prenom', null, [
                'label' => 'Prénom',
            ])
            ->add('nom')
            ->add('sexe', ChoiceType::class, [
                'choices' => Etudiant::getSexes(),
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'placeholder' => '',
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
            ])
            ->add('dateNaiss', DateType::class, [
                'label' => 'Date naiss.',
                'widget' => 'single_text'
            ])
            ->add('lieuNaiss')
            ->add('quartier')
            ->add('telephone', TelType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('email')
            ->add('contactParent', null, ['required' => false])
            ->add('emailParent')
            ->add('anneeDef', null, ['label' => 'Année DEF'])
            ->add('anneeBac', null, ['label' => 'Année BAC'])
            ->add('file', FileType::class, ['label' => 'Photo 413 x 531 px', 'required' => false])
            ->add('cycle', EntityType::class, [
                'label' => 'Cycle',
                'class' => Cycle::class,
                'choice_value' => 'id',
                'choice_label' => 'code',
                'placeholder' => '',
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
            ])
            ->add('professeur', EntityType::class, [
                'label' => 'Professeur parrain',
                'class' => Professeur::class,
                'choice_value' => 'id',
                'choice_label' => 'prenomNom',
                'placeholder' => '',
                'required' => false,
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => Etudiant::getEtats(),
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'placeholder' => '',
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
            ])
            ->add('isAccesCours', null, ['label' => 'Accès au cours'])
            ->add('site', null, [
                'placeholder' => '',
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT]
            ])

            // ->add('etudiantClasses', CollectionType::class, [
            //     'entry_type' => EtudiantClasse2Type::class,
            //     'entry_options' => ['label' => false],
            // ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Etudiant::class,
            //'attr' => ['novalidate' => 'novalidate']
        ]);
    }

}
