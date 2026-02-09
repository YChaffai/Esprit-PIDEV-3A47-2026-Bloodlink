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
                    return "#{$d->getId()} - {$d->getTypeDon()} - {$date}";
                },
                'query_builder' => function (DonRepository $repo) use ($client) {
                    return $repo->createQueryBuilder('d')
                        ->andWhere('d.client = :c')
                        ->setParameter('c', $client)
                        ->orderBy('d.date', 'DESC');
                },
                'placeholder' => '-- Select a donation --',
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
                    'Autre' => 'Autre'
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

            // ✅ MISSING FIELD FIXED HERE
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
                    new NotBlank(['message' => 'Le contact urgence est obligatoire.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMed::class,
            'client' => null,
        ]);
    }
}
