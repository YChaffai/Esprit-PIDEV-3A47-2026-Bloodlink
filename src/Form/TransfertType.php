<?php

namespace App\Form;

use App\Entity\Demande;
use App\Entity\Transfert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransfertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fromOrgId')
            ->add('fromOrg')
            ->add('toOrgId')
            ->add('toOrg')
            ->add('dateEnvoie')
            ->add('dateReception')
            ->add('quantite')
            ->add('status', ChoiceType::class, [
        'choices'  => [
            'En cours' => 'EN_COURS',
            'Reçu'     => 'RECU',
            'Annulé'   => 'ANNULE',
        ],
        'placeholder' => 'Sélectionner le status',
        'required' => true,
    ])
            ->add('idStock')
            ->add('demande', EntityType::class, [
                'class' => Demande::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transfert::class,
        ]);
    }
}
