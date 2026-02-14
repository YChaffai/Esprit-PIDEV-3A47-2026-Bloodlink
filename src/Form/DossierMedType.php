<?php

namespace App\Form;

use App\Entity\DossierMed;
use App\Entity\Don;
use App\Repository\DonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
<<<<<<< HEAD
=======
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
>>>>>>> e5190a8 (JEW)

class DossierMedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $client = $options['client'];

        $builder
            ->add('don', EntityType::class, [
                'class' => Don::class,
                'choice_label' => function (Don $d) {
                    $date = $d->getDate() ? $d->getDate()->format('Y-m-d H:i') : 'no date';
<<<<<<< HEAD
                    $clientName = $d->getClient() && $d->getClient()->getUser() ? $d->getClient()->getUser()->getNomComplet() : 'Inconnu';
=======
                    $clientName = $d->getClient() && $d->getClient()->getUser()
                        ? $d->getClient()->getUser()->getNomComplet()
                        : 'Inconnu';

>>>>>>> e5190a8 (JEW)
                    return "#{$d->getId()} - {$clientName} ({$d->getTypeDon()}) - {$date}";
                },
                'query_builder' => function (DonRepository $repo) use ($client) {
                    $qb = $repo->createQueryBuilder('d')
                        ->orderBy('d.date', 'DESC');
<<<<<<< HEAD
                    
=======

>>>>>>> e5190a8 (JEW)
                    if ($client) {
                        $qb->andWhere('d.client = :c')
                           ->setParameter('c', $client);
                    }
<<<<<<< HEAD
                    
=======

>>>>>>> e5190a8 (JEW)
                    return $qb;
                },
                'placeholder' => '-- Sélectionner un don --',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un don.']),
                ],
            ])

            ->add('nom', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank(['message' => 'Le nom est obligatoire.'])],
            ])

            ->add('prenom', TextType::class, [
                'required' => true,
                'constraints' => [new NotBlank(['message' => 'Le prénom est obligatoire.'])],
            ])

            ->add('age', IntegerType::class, [
                'required' => true,
                'attr' => ['min' => 18],
                'constraints' => [new NotBlank(['message' => "L'âge est obligatoire."])],
            ])

            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
<<<<<<< HEAD
                    'Autre' => 'Autre'
=======
                    'Autre' => 'Autre',
>>>>>>> e5190a8 (JEW)
                ],
                'placeholder' => '-- Choose --',
                'required' => true,
                'constraints' => [new NotBlank(['message' => 'Le sexe est obligatoire.'])],
            ])

            ->add('typeSang', ChoiceType::class, [
                'choices' => [
                    'A+' => 'A+', 'A-' => 'A-',
                    'B+' => 'B+', 'B-' => 'B-',
                    'AB+' => 'AB+', 'AB-' => 'AB-',
                    'O+' => 'O+', 'O-' => 'O-',
                ],
                'placeholder' => '-- Choose --',
                'required' => true,
                'constraints' => [new NotBlank(['message' => 'Le groupe sanguin est obligatoire.'])],
            ])

            ->add('taille', NumberType::class, [
                'required' => true,
                'label' => 'Taille (cm)',
                'attr' => ['step' => '0.1', 'min' => 1, 'placeholder' => 'Ex: 175'],
                'constraints' => [
                    new NotBlank(['message' => 'La taille est obligatoire.']),
                    new Positive(['message' => 'La taille doit être positive.']),
                ],
            ])

<<<<<<< HEAD
            // ✅ MISSING FIELD FIXED HERE
=======
>>>>>>> e5190a8 (JEW)
            ->add('poid', NumberType::class, [
                'required' => true,
                'label' => 'Poids (kg)',
                'attr' => ['step' => '0.1', 'min' => 1, 'placeholder' => 'Ex: 70'],
                'constraints' => [
                    new NotBlank(['message' => 'Le poids est obligatoire.']),
                    new Positive(['message' => 'Le poids doit être positif.']),
                ],
            ])

            ->add('temperature', NumberType::class, [
                'required' => true,
                'label' => 'Température (°C)',
                'attr' => ['step' => '0.1', 'min' => 30, 'max' => 45, 'placeholder' => 'Ex: 36.6'],
                'constraints' => [
                    new NotBlank(['message' => 'La température est obligatoire.']),
                    new Positive(['message' => 'La température doit être positive.']),
                ],
            ])

            ->add('contactUrgence', IntegerType::class, [
                'required' => true,
                'label' => 'Contact urgence',
                'attr' => ['min' => 0, 'placeholder' => 'Ex: 99587306'],
                'constraints' => [
<<<<<<< HEAD
                    new NotBlank(['message' => 'Le contact urgence est obligatoire.']),
                ],
            ]);
=======
                    new NotBlank(['message' => "Le contact d'urgence est obligatoire."]),
                ],
            ]);

        // ✅ IMPORTANT: set client automatically from the selected Don (before final validation)
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var DossierMed $data */
            $data = $event->getData();
            if (!$data) return;

            if ($data->getDon() && $data->getDon()->getClient()) {
                $data->setClient($data->getDon()->getClient());
            }
        });
>>>>>>> e5190a8 (JEW)
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMed::class,
            'client' => null,
        ]);
    }
}
