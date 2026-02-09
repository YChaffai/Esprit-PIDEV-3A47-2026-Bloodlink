<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface; // 👈 THIS WAS MISSING
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;

class ClientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // 1. ID Column (Visible on Index)
            IdField::new('id')->onlyOnIndex(),

            // 2. "Client" Column (Name + ID) - Visible ONLY on Index
            TextField::new('fullNameWithId', 'Client')
                ->setVirtual(true)
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    // Safe check to ensure we don't crash if data is missing
                    $prenom = $entity->getPrenom() ?? '';
                    $nom = $entity->getNom() ?? '';
                    return sprintf('%s %s (#%d)', $prenom, $nom, $entity->getId());
                }),

            // 3. Form Fields (Visible ONLY on Create/Edit forms)
            TextField::new('nom', 'Nom')->hideOnIndex(),
            TextField::new('prenom', 'Prénom')->hideOnIndex(),
            EmailField::new('email', 'Email'),

            // 4. Other Columns
            ChoiceField::new('typeSang', 'Groupe Sanguin')
                ->setChoices([
                    'A+' => 'A+', 'A-' => 'A-',
                    'B+' => 'B+', 'B-' => 'B-',
                    'AB+' => 'AB+', 'AB-' => 'AB-',
                    'O+' => 'O+', 'O-' => 'O-',
                ])
                ->renderAsBadges(),

            
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Client && $entityInstance->getDernierDon() === null) {
            $entityInstance->setDernierDon(new \DateTime('today'));
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
}