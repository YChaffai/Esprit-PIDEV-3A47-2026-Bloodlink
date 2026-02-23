<?php

namespace App\Form;

use App\Entity\EntiteCollecte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntiteCollecteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'entité',
                'help' => 'Le nom officiel de l\'entité de collecte (ex: Hôpital X).',
                'attr' => ['placeholder' => 'Ex: Hôpital Charles Nicolle'],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'help' => 'Adresse complète de l\'entité.',
                'attr' => ['placeholder' => 'Ex: 12 Rue de la République'],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'help' => 'Ville de l\'entité.',
                'attr' => ['placeholder' => 'Ex: Tunis'],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'help' => 'Exactement 8 chiffres.',
                'attr' => ['placeholder' => 'Ex: 71234567'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EntiteCollecte::class,
        ]);
    }
}
