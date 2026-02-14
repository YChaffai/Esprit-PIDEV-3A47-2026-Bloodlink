<?php

namespace App\Form;

use App\Entity\Demande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('banque', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, [
                'class' => \App\Entity\Banque::class,
                'choice_label' => 'nom',
                'label' => 'Banque',
                'placeholder' => 'Sélectionnez une banque',
            ])
            ->add('typeSang', ChoiceType::class, [
                'choices' => [
                    'A+' => 'A+',
                    'A-' => 'A-',
                    'B+' => 'B+',
                    'B-' => 'B-',
                    'AB+' => 'AB+',
                    'AB-' => 'AB-',
                    'O+' => 'O+',
                    'O-' => 'O-',
                ],
                'label' => 'Type de sang',
                'placeholder' => 'Choisissez un type',
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité (unités)',
            ])
            ->add('urgence', ChoiceType::class, [
                'choices' => [
                    'Normale' => 'Normale',
                    'Urgente' => 'Urgente',
                ],
                'label' => 'Urgence',
                'placeholder' => 'Sélectionnez',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}
