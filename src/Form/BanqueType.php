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
        'label' => "Nom de l'établissement",
        'attr' => ['placeholder' => "Entrez le nom de l'établissement"],
        'required' => false,
      ])
      ->add('adresse', TextType::class, [
        'label' => 'Adresse',
        'attr' => ['placeholder' => "Entrez l'adresse complète"],
        'required' => false,
      ])
      ->add('telephone', TextType::class, [
        'label' => 'Téléphone',
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
