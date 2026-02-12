<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $isNew = $options['is_new'];
    $isRegistration = $options['is_registration'];

    $builder
      ->add('nom', TextType::class, [
        'required' => true,
        'attr' => ['class' => 'form-control']
      ])
      ->add('prenom', TextType::class, [
        'required' => true,
        'attr' => ['class' => 'form-control']
      ])
      ->add('email', EmailType::class, [
        'required' => true,
        'attr' => ['class' => 'form-control']
      ])
      ->add('telephone', TextType::class, [
        'required' => false,
        'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: +216 12 345 678']
      ])
      ->add('plainPassword', PasswordType::class, [
        'required' => $isNew,
        'mapped' => true,
        'constraints' => $isNew ? [
          new NotBlank([
            'message' => 'Le mot de passe est obligatoire'
          ]),
          new Length([
            'min' => 6,
            'minMessage' => '6 caractères minimum'
          ]),
          new Regex([
            'pattern' => '/^(?=.*[A-Za-z])(?=.*\d).+$/',
            'message' => 'Au moins une lettre et un chiffre requis'
          ])
        ] : [
          new Length([
            'min' => 6,
            'minMessage' => '6 caractères minimum'
          ]),
          new Regex([
            'pattern' => '/^(?=.*[A-Za-z])(?=.*\d).+$/',
            'message' => 'Au moins une lettre et un chiffre requis'
          ])
        ],
        'attr' => [
          'placeholder' => $isNew
            ? '••••••••'
            : 'Laisser vide pour conserver le mot de passe actuel',
          'class' => 'form-control'
        ]
      ]);

    // Only show role selector in admin panel, not on public registration
    if (!$isRegistration) {
      $builder->add('role', ChoiceType::class, [
        'choices' => [
          'Admin' => 'admin',
          'Client' => 'client',
          'Docteur' => 'doctor',
          'Agent Banque' => 'banque',
          'Agent CNTS' => 'cnts',
        ],
        'placeholder' => 'Choisir un rôle',
        'required' => true,
        'attr' => ['class' => 'form-control']
      ]);
    }
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => User::class,
      'is_new' => false,
      'is_registration' => false,
    ]);
  }
}
