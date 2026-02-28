<?php

namespace App\Form;

use App\Entity\DossierMed;
use App\Entity\Don;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DossierMedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('don', EntityType::class, [
                'class' => Don::class,
                // 🔥 LA CORRECTION EST ICI : Un affichage clair pour le médecin !
                'choice_label' => function(Don $don) {
                    $client = $don->getClient();
                    $nom = ($client && $client->getUser()) ? $client->getUser()->getPrenom() . ' ' . $client->getUser()->getNom() : 'Client Inconnu';
                    return 'Don #' . $don->getId() . ' - ' . $nom . ' (' . $don->getDate()->format('d/m/Y') . ')';
                },
                'placeholder' => '--- Choisissez le don correspondant ---',
                'required' => true,
            ])
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('age', IntegerType::class)
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                    'Autre' => 'Autre',
                ]
            ])
            ->add('typeSang', ChoiceType::class, [
                'choices' => [
                    'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
                    'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-',
                ]
            ])
            ->add('taille', NumberType::class)
            ->add('poid', NumberType::class)
            ->add('temperature', NumberType::class)
            ->add('contactUrgence', IntegerType::class);

        // L'écouteur qui attache automatiquement le bon client au dossier
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var DossierMed $dossier */
            $dossier = $event->getData();

            if ($dossier && $dossier->getDon() && $dossier->getDon()->getClient()) {
                $dossier->setClient($dossier->getDon()->getClient());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMed::class,
            'csrf_protection' => false,
        ]);
    }
}