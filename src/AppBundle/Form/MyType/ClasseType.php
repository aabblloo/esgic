<?php

namespace AppBundle\Form\MyType;

use AppBundle\Entity\Classe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClasseType extends AbstractType {

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'class' => Classe::class,
            'choice_value' => 'id',
            'choice_label' => 'code',
            'placeholder' => '',
            'attr' => ['class' => 'chosen-select']
        ]);
    }

    public function getParent() {
        return EntityType::class;
    }

}
