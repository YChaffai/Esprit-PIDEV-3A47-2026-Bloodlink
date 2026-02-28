<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Visible selects
            ->add('orgType', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Type Organisation',
                'choices' => [
                    'Banque' => 'banque',
                    'Entite Collecte' => 'entitecollecte',
                ],
                'placeholder' => 'Choisir un type',
            ])
            ->add('orgId', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Organisation',
                'choices' => [],
                'placeholder' => 'Sélectionnez un type d\'abord',
            ])

            // Real DB fields (hidden, will be set by JS)
            ->add('type_org', HiddenType::class, ['error_bubbling' => false])
            ->add('type_orgid', HiddenType::class, ['error_bubbling' => false])

            // Type de sang dropdown
            ->add('type_sang', ChoiceType::class, [
                'label' => 'Type de sang',
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
                'placeholder' => 'Choisir un type de sang',
            ])
            
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
            ])
        ;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // Sync unmapped fields to mapped fields
            if (isset($data['orgType'])) {
                $data['type_org'] = $data['orgType'];
            }
            if (isset($data['orgId'])) {
                $data['type_orgid'] = $data['orgId'];
            }
            $event->setData($data);

            if (isset($data['orgId']) && $data['orgId']) {
                $form->add('orgId', ChoiceType::class, [
                    'mapped' => false,
                    'label' => 'Organisation',
                    'choices' => [$data['orgId'] => $data['orgId']], // Allow the submitted value
                    'placeholder' => 'Sélectionnez un type d\'abord',
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}