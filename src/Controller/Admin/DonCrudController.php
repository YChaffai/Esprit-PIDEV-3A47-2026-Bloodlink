<?php

namespace App\Controller\Admin;

use App\Entity\Don;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class DonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Don::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Don')
            ->setEntityLabelInPlural('Dons')
            ->setDefaultSort(['date' => 'DESC'])
            // Apply the custom glassmorphism detail view
            ->overrideTemplate('crud/detail', 'admin/don/show.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // ID: Read-only
            IdField::new('id')->hideOnForm(),

            // Client: Editable (Select dropdown)
            AssociationField::new('client', 'Donneur')
                ->setRequired(true),

            // Date: Editable
            DateTimeField::new('date', 'Date du don')
                ->setFormat('yyyy-MM-dd HH:mm'),

            // Type: Editable
            ChoiceField::new('typeDon', 'Type de don')
                ->setChoices([
                    'Sang total' => 'Sang total',
                    'Plasma' => 'Plasma',
                    'Plaquettes' => 'Plaquettes',
                    'Globules rouges' => 'Globules rouges',
                ])
                ->renderAsBadges(),

            // Quantity: Editable
            NumberField::new('quantite', 'Quantité (ml)')
                ->setNumDecimals(2)
                ->setFormTypeOption('attr', ['step' => '0.01']),

            // Entity ID: Editable
            IntegerField::new('idEntite', 'ID Entité'),

            // Timestamps: Read-only (Hidden on Create/Edit forms)
            DateTimeField::new('createdAt', 'Créé le')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm'),
                
            DateTimeField::new('updatedAt', 'Modifié le')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Don && $entityInstance->getIdEntite() === null) {
            $entityInstance->setIdEntite(1); // Default value if somehow missing
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
}