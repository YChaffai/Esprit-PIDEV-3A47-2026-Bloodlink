<?php

namespace App\Controller\Admin;

use App\Entity\DossierMed;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DossierMedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DossierMed::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Dossier médical')
            ->setEntityLabelInPlural('Dossiers médicaux')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'nom', 'prenom', 'typeSang', 'sexe', 'age']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            AssociationField::new('client', 'Client'),
            AssociationField::new('don', 'Don lié'),

            TextField::new('nom', 'Nom'),
            TextField::new('prenom', 'Prénom'),

            IntegerField::new('age', 'Âge'),

            ChoiceField::new('sexe', 'Sexe')->setChoices([
                'Homme' => 'Homme',
                'Femme' => 'Femme',
                'Autre' => 'Autre',
            ]),

            ChoiceField::new('typeSang', 'Groupe sanguin')->setChoices([
                'A+' => 'A+', 'A-' => 'A-',
                'B+' => 'B+', 'B-' => 'B-',
                'AB+' => 'AB+', 'AB-' => 'AB-',
                'O+' => 'O+', 'O-' => 'O-',
            ])->renderAsBadges(),

            NumberField::new('taille', 'Taille')->setNumDecimals(2),
            NumberField::new('poid', 'Poids')->setNumDecimals(2),
            NumberField::new('temperature', 'Température')->setNumDecimals(1),

            IntegerField::new('contactUrgence', 'Contact urgence'),
        ];
    }
}
