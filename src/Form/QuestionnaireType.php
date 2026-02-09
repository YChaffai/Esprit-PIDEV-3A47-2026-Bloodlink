<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Questionnaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class QuestionnaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('client', EntityType::class, [
            //     'class' => Client::class,
            //     'choice_label' => 'prenom', 
            //     'placeholder' => 'Choisissez un client',
            // ])
            // ->add('nom')
            // ->add('prenom')
        // ->add('nom', HiddenType::class, ['mapped' => false]) 
        // ->add('prenom', HiddenType::class, ['mapped' => false])
            ->add('age')
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
                'placeholder' => 'Choisissez un sexe', 
            ])            
            ->add('poids', NumberType::class, [
                'invalid_message' => "Le poids ne doit contenir que des chiffres (ex: 75.5)",
                'html5' => true,
                'attr' => [
                    'step' => '0.1',
                ],
            ])
            ->add('autres', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Veuillez spécifier d\'autres informations',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Continue'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Questionnaire::class,
        ]);
    }

    
}
