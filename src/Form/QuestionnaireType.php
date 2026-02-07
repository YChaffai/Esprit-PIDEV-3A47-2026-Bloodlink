<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Questionnaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
            ->add('nom')
            ->add('prenom')
            ->add('age')
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
                'placeholder' => 'Choisissez un sexe', 
            ])            
            ->add('poids')
            ->add('autres', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Veuillez spécifier d\'autres informations',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer'
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
