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
          'label' => 'New Password',
          'attr' => [
            'placeholder' => 'Enter new password',
            'class' => 'form-control form-control-lg bg-light border-0 rounded-3'
          ],
        ],
        'second_options' => [
          'label' => 'Confirm Password',
          'attr' => [
            'placeholder' => 'Confirm new password',
            'class' => 'form-control form-control-lg bg-light border-0 rounded-3'
          ],
        ],
        'invalid_message' => 'The password fields must match',
        'required' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([]);
  }
}
