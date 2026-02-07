<?php

namespace App\Form;

use App\Entity\Banque;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BanqueType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('nom', TextType::class, [
        'label' => 'Blood Bank Name',
        'attr' => ['placeholder' => 'Enter blood bank name'],
        'required' => false,
      ])
      ->add('adresse', TextType::class, [
        'label' => 'Address',
        'attr' => ['placeholder' => 'Enter full address'],
        'required' => false,
      ])
      ->add('telephone', TextType::class, [
        'label' => 'Phone Number',
        'attr' => [
          'placeholder' => '12345678',
          'maxlength' => '8',
          'pattern' => '[0-9]{8}'
        ],
        'required' => false,
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Banque::class,
    ]);
  }
}
