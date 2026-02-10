<?php

namespace App\Form;

use App\Entity\Banque;
use App\Entity\Client;
use App\Entity\Commande;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference')
            ->add('quantite')
            ->add('priorite', ChoiceType::class, [
                'choices' => [
                    'Faible' => 'Faible',
                    'Élevée' => 'Élevée',
                    'Urgente' => 'Urgente',
                ],
                'placeholder' => 'Choisir une priorité',
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
                'placeholder' => 'Choisir un type de sang',
            ])
            ->add('banque', EntityType::class, [
                'class' => Banque::class,
                'choice_label' => 'nom',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'user',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
