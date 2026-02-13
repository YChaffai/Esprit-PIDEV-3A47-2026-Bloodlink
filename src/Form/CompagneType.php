<?php

namespace App\Form;

use App\Entity\Compagne;
use App\Entity\EntiteCollecte;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompagneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la campagne',
                'help' => 'Donnez un titre accrocheur à votre campagne.',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'help' => 'Décrivez les objectifs et les détails de la campagne.',
            ])
            ->add('date_debut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'help' => 'Quand commence la campagne ?',
            ])
            ->add('date_fin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'help' => 'Quand se termine la campagne ?',
            ])
            ->add('typeSang', ChoiceType::class, [
                'label' => 'Type(s) de sang',
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
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'help' => 'Sélectionnez un ou plusieurs types de sang recherchés.',
            ])
            ->add('entites', EntityType::class, [
                'class' => EntiteCollecte::class,
                'choice_label' => 'nom',
                'label' => 'Entités de collecte',
                'placeholder' => 'Sélectionner des entités...',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'attr' => ['class' => 'form-select select-entites'],
                'help' => 'Sélectionnez les entités organisatrices.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Compagne::class,
            'csrf_protection' => false,
        ]);
    }
}
