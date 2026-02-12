<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Entity\Compagne;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
class QuestionnaireFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           ->add('nom', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => ['placeholder' => 'Rechercher par nom...']
            ])
            ->add('prenom', TextType::class, ['required' => false, 'label' => false, 'attr' => ['placeholder' => 'Prénom...']])
//             ->add('date_don', DateTimeType::class, [
//                 'widget' => 'single_text',
//                 'required' => false,
//                 'label' => 'Date et heure',
//                 'attr' => ['class' => 'datepicker','placeholder'=> 'choisir date et heure',
// ],
//             ])

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
            ->add('campagne', EntityType::class, [
                'class' => Compagne::class,
                'choice_label' => 'titre',
                'required' => false,
                'placeholder' => 'Toutes les campagnes',
                'label' => false,
            ])
            ->add('groupSanguin', ChoiceType::class, [
                'choices' => [
                    'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
                    'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-',
                ],
                'required' => false,
                'placeholder' => 'Tous les groupes',
                'label' => false,
            ])
            
             ->add('tri', ChoiceType::class, [
        'choices' => [
            'ID questionnaire : Croissant' => 'id_ASC',
            'ID questionnaires : Décroissant' => 'id_DESC',
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
    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           
            'method' => 'GET', // Crucial pour les filtres
            'csrf_protection' => false, // Optionnel pour la recherche
      
        ]);
    }
}
