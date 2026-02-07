<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('typeSang', ChoiceType::class, [
        'label' => 'Blood Type',
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
        'placeholder' => 'Select blood type',
        'required' => false,
      ])
      ->add('dernierDon', DateType::class, [
        'label' => 'Last Donation Date',
        'widget' => 'single_text',
        'required' => false,
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Client::class,
    ]);
  }
}
