<?php

namespace App\Form;

use App\Entity\EntiteCollecte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntitecollecteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'entité',
                'help' => 'Le nom officiel de l\'entité de collecte (ex: Hôpital X).',
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'help' => 'Adresse complète de l\'entité.',
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'help' => 'Ville de l\'entité.',
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'help' => 'Format numérique uniquement (ex: 0612345678).',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EntiteCollecte::class,
            'csrf_protection' => false,
        ]);
    }
}
