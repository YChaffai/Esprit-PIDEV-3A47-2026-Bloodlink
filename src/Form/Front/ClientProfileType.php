<?php

namespace App\Form\Front;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ClientProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'mapped' => false,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'mapped' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'required' => true,
                'mapped' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau Mot de passe',
                'required' => false,
                'mapped' => false, // We'll handle hashing in the controller
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit faire au moins 6 caractères',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Za-z])(?=.*\d).+$/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre et un chiffre',
                    ]),
                ],
                'attr' => ['placeholder' => 'Laisser vide pour ne pas changer'],
            ])
            ->add('typeSang', ChoiceType::class, [
                'label' => 'Groupe Sanguin',
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
                'required' => true,
            ])
            ->add('dernierDon', DateType::class, [
                'label' => 'Date du dernier don',
                'widget' => 'single_text',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // This form is slightly complex as it covers both User and Client.
        // We'll pass the Client object, and we'll have 'nom', 'prenom', 'email'
        // mapped to the User property of the Client.
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
