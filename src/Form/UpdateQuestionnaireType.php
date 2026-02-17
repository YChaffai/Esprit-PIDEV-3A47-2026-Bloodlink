<?php

namespace App\Form;

use App\Entity\Questionnaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UpdateQuestionnaireType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      // ->add('nom')
      // ->add('prenom')
      ->add('age')
      ->add('sexe', ChoiceType::class, [
        'choices' => [
          'Homme' => 'Homme',
          'Femme' => 'Femme',
        ],
        'placeholder' => 'Choisissez un sexe',
      ])
      ->add('poids')
      ->add('autres')
      ->add('submit', SubmitType::class, [
        'label' => 'Update'
      ]);;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Questionnaire::class,
    ]);
  }
}
