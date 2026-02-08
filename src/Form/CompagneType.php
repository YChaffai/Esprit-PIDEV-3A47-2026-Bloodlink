<?php

namespace App\Form;

use App\Entity\Compagne;
use App\Entity\Entitecollecte;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompagneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('date_debut')
            ->add('date_fin')
            ->add('entite', EntityType::class, [
                'class' => Entitecollecte::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez une entité (facultatif)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Compagne::class,
        ]);
    }
}

