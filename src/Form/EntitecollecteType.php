<?php

namespace App\Form;

use App\Entity\Entitecollecte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
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
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'help' => 'Adresse ou ville principale.',
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'help' => 'Format numérique uniquement (ex: 0612345678).',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entitecollecte::class,
            'csrf_protection' => false,
        ]);
    }
}
