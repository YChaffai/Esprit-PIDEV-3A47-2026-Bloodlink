<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\EntiteCollecte;
use App\Entity\Questionnaire;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UpdateRendezVousType extends AbstractType
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
                'placeholder' => 'choisissez l\'entité de collecte'
            ])
            //  ->add('submit', SubmitType::class, [
            //     'label' => 'Prendre rendez-vous'
            // ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
