<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Don;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                // ✅ choose how you want to display client in dropdown:
                // If Client has getUser()->getNomComplet():
                'choice_label' => function (Client $c) {
                    return $c->getUser() ? $c->getUser()->getNomComplet() : ('Client #' . $c->getId());
                },
                'placeholder' => '-- Sélectionner un client --',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un client.']),
                ],
            ])

            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
            ])

            ->add('typeDon', ChoiceType::class, [
                'choices' => [
                    'Sang total' => 'Sang total',
                    'Plasma' => 'Plasma',
                    'Plaquettes' => 'Plaquettes',
                    'Globules rouges' => 'Globules rouges',
                ],
            ])
            ->add('quantite', NumberType::class, [
                'scale' => 2,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Don::class]);
    }
}
