<?php

namespace App\Form;

use App\Entity\EntiteCollecte;
use App\Entity\RendezVous;
use App\Repository\EntiteCollecteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class RendezVousType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('date_don', DateTimeType::class, [
        'widget' => 'single_text',
        // 'format' => 'yyyy-MM-dd HH:mm',  
        // 'html5' => false,
        'attr' => ['class' => 'datepicker'],
        'label' => 'Date et heure du rendez-vous',
      ])
      ->add('entite', EntityType::class, [
        'class' => EntiteCollecte::class,
        'choice_label' => 'nom',
        'placeholder' => 'Choisissez l\'entité de collecte',
        'query_builder' => function (EntiteCollecteRepository $er) use ($options) {
          // On récupère la campagne directement depuis les options du formulaire
          $rendezVous = $options['data'] ?? null;
          $campagne = $rendezVous?->getQuestionnaire()?->getCampagne();
          $campagneId = $campagne ? $campagne->getId() : 0;

          return $er->createQueryBuilder('e')
            ->innerJoin('e.campagnes', 'c')
            ->where('c.id = :campagneId')
            ->setParameter('campagneId', $campagneId)
            ->orderBy('e.nom', 'ASC');
        },
      ])
      //    ->add('client', EntityType::class, [
      //         'class' => Client::class,
      //         'choice_label' => 'id',            
      //         ])

      // ->add('status')
      // ->add('client', EntityType::class, [
      //     'class' => Client::class,
      //     'choice_label' => 'id',
      // ])
      // ->add('questionnaire', EntityType::class, [
      //     'class' => Questionnaire::class,
      //     'choice_label' => 'id',
      // ])

      // ->add('status', ChoiceType::class, [
      //     'choices' => [
      //         'en attente' => 'en attente',
      //         'confirme' => 'confirme',
      //         'annule' => 'annule',

      //     ],
      //     'placeholder' => 'Choisissez un sexe', 
      // ]) 
      ->add('submit', SubmitType::class, [
        'label' => 'Prendre rendez-vous'
      ]);;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => RendezVous::class,
    ]);
  }
}
