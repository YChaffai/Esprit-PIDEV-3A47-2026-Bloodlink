<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('password', RepeatedType::class, [
        'type' => PasswordType::class,
        'first_options' => [
          'label' => 'Nouveau mot de passe',
          'attr' => [
            'placeholder' => 'Entrez votre nouveau mot de passe',
            'class' => 'form-control form-control-lg bg-light border-0 rounded-3'
          ],
        ],
        'second_options' => [
          'label' => 'Confirmez le mot de passe',
          'attr' => [
            'placeholder' => 'Confirmez votre nouveau mot de passe',
            'class' => 'form-control form-control-lg bg-light border-0 rounded-3'
          ],
        ],
        'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
        'required' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([]);
  }
}
