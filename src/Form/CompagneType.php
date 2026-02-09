<?php

namespace App\Form;

use App\Entity\Compagne;
use App\Entity\Entitecollecte;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
            ->add('entite', EntityType::class, [
                'class' => Entitecollecte::class,
                'choice_label' => 'nom',
                'label' => 'Entité de collecte',
                'placeholder' => 'Sélectionner une entité...',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'attr' => ['class' => 'form-select'], // Standard Bootstrap select, or 'select-entite' if I want TomSelect on single too? TomSelect works on single too.
                'help' => 'Sélectionnez l\'entité organisatrice.',
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
