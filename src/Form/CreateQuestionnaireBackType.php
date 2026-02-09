<?php

namespace App\Form;

use App\Entity\Campagne;
use App\Entity\Client;
use App\Entity\Questionnaire;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use App\Repository\CampagneRepository;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
class CreateQuestionnaireBackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        //    ->add('nom')
        //     ->add('prenom')
        ->add('nom', HiddenType::class, ['data' => 'temp']) // Évite l'erreur NotBlank
    ->add('prenom', HiddenType::class, ['data' => 'temp'])
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
           
    //         ->add('client', EmailType::class, [
    //             'label' => 'Email du client',
    //             'attr' => [
    //             'placeholder' => 'exemple@domaine.com',
    //             'class' => 'form-control'
    //         ],
    // 'required' => true,
    //             'mapped' => false
    //         ])



->add('client', EmailType::class, [
    'label' => 'Email du client',
    'mapped' => false, 
    'required' => true,
    'constraints' => [
        new NotBlank([
            'message' => 'L’email est obligatoire',
        ]),
        new Email([
            'message' => 'Veuillez saisir une adresse email valide',
        ]),
    ],
    'attr' => [
        'placeholder' => 'exemple@domaine.com',
        'class' => 'form-control'
    ],
])

            ->add('campagne', EntityType::class, [
                'class' => Campagne::class,
                'choice_label' => 'titre',
                'query_builder' => function (CampagneRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.date_fin > :today')
                        ->setParameter('today', new \DateTime());
                },
                'placeholder' => 'Choisissez une campagne'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Suivant'
            ])
        //     ->add('campagne', EntityType::class, [
        //         'class' => Campagne::class,
        //         'choice_label' => 'id',
        //     ])
        //     ->add('rendezVous', EntityType::class, [
        //         'class' => RendezVous::class,
        //         'choice_label' => 'id',
        //     ])
        //     ->add('client', EntityType::class, [
        //         'class' => Client::class,
        //         'choice_label' => 'id',
        //     ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Questionnaire::class,
        ]);
    }
}
