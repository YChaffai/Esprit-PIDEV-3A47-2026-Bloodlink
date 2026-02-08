<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\Campagne;
use App\Entity\EntiteCollecte;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
class RendezVousFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Nom du donneur...']
            ])
            ->add('prenom', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'Prénom...']])
            ->add('campagne', EntityType::class, [
                'class' => Campagne::class,
                'choice_label' => 'titre',
                'required' => false,
                'placeholder' => 'Toutes les campagnes',
            ])
            ->add('entite', EntityType::class, [
            'class' => EntiteCollecte::class,
            'choice_label' => 'nom', // ou 'nomEntite' selon ta propriété
            'required' => false,
            'placeholder' => 'Toutes les entités',
        ])
        //    ->add('date', DateTimeType::class, [
        //         'widget' => 'single_text',
        //         'required' => false,
                
        //         'label' => 'Date et heure',
        //         'attr' => ['class' => 'datepicker','placeholder'=> 'choisir date et heure'],
        //     ])
        ->add('filter_date', DateType::class, [
        'widget' => 'single_text',
        'required' => false,
        'label' => 'Date du don',
        'attr' => ['class' => 'form-control']
    ])
    ->add('filter_time', TimeType::class, [
        'widget' => 'single_text',
        'required' => false,
        'label' => 'Heure du don',
        'attr' => ['class' => 'form-control']
    ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'en attente',
                    'Confirmé' => 'confirmé',
                    'Annulé' => 'annulé',
                ],
                'required' => false,
                'placeholder' => 'Tous les statuts',
            ])
            ->add('statusClient', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'en attente',
                    'Confirmé' => 'confirmé',
                ],
                'required' => false,
                'placeholder' => 'Tous les statuts',
            ])
            
          ->add('tri', ChoiceType::class, [
        'choices' => [
            'ID rendez vous : Croissant' => 'id_ASC',
            'ID rendez vous : Décroissant' => 'id_DESC',
            'Date & Heure : Plus proche' => 'date_ASC',
            'Date & Heure : Plus lointaine' => 'date_DESC',
        ],
        'required' => false,
        'placeholder' => 'Ordre par défaut',
        'label' => 'Trier par'
    ])
    
    ->add('tri_date', ChoiceType::class, [
        'choices' => [
            'Date & Heure : Plus proche' => 'date_ASC',
        'Date & Heure : Plus lointaine' => 'date_DESC',
        ],
        'required' => false,
        'placeholder' => 'Ordre par défaut',
        'label' => 'Trier par'
    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET' ,
            'csrf_protection' => false,      
        ]);
    }
}
