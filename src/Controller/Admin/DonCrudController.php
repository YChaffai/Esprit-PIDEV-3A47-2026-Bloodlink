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
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'typeDon', 'quantite']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            // ✅ Correct field type for datetime
            // Using a simple format; if intl is disabled, this usually still works in most setups
            DateTimeField::new('date', 'Date')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->hideOnForm(), // keep as display only in admin (optional)

            NumberField::new('quantite', 'Quantité')
                ->setNumDecimals(2)
                ->setFormTypeOption('attr', ['step' => '0.01']),

            ChoiceField::new('typeDon', 'Type de don')
                ->setChoices([
                    'Sang total' => 'Sang total',
                    'Plasma' => 'Plasma',
                    'Plaquettes' => 'Plaquettes',
                    'Globules rouges' => 'Globules rouges',
                ])
                ->renderAsBadges(),

            IntegerField::new('idEntite', 'ID Entité')
                ->hideOnIndex(),

            AssociationField::new('client', 'Donneur'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Don) {
            if ($entityInstance->getDate() === null) {
                $entityInstance->setDate(new \DateTime());
            }
            if ($entityInstance->getIdEntite() === null) {
                $entityInstance->setIdEntite(1);
            }
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
}
