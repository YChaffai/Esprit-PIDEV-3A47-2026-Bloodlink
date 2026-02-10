<?php

namespace App\Form;

use App\Entity\EntiteCollecte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EntiteCollecteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'entité',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Croissant Rouge Tunis'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                    new Length(['min' => 3, 'max' => 255])
                ]
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Adresse complète'],
                'constraints' => [
                    new NotBlank(['message' => 'La localisation est obligatoire'])
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 27173167'],
                'constraints' => [
                    new NotBlank(['message' => 'Le téléphone est obligatoire']),
                    new Regex([
                        'pattern' => '/^\d{8}$/',
                        'message' => 'Le numéro doit contenir 8 chiffres.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EntiteCollecte::class,
        ]);
    }
}
