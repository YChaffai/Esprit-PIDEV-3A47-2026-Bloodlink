<?php

namespace App\Validator;

use App\Repository\CompagneRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueCampagneTypeSangValidator extends ConstraintValidator
{
    public function __construct(private CompagneRepository $compagneRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueCampagneTypeSang) {
            return;
        }

        $campagne = $value;
        
        if (!$campagne->getDateDebut() || !$campagne->getDateFin() || empty($campagne->getTypeSang()) || $campagne->getEntites()->isEmpty()) {
            return;
        }

        $existingCampagnes = $this->compagneRepository->findAll();

        foreach ($existingCampagnes as $existing) {
            // Skip self
            if ($existing->getId() && $existing->getId() === $campagne->getId()) {
                continue;
            }

            // Check if dates match
            if ($existing->getDateDebut() && $existing->getDateFin() &&
                $existing->getDateDebut()->format('Y-m-d') === $campagne->getDateDebut()->format('Y-m-d') &&
                $existing->getDateFin()->format('Y-m-d') === $campagne->getDateFin()->format('Y-m-d')
            ) {
                // Check for shared entites
                foreach ($campagne->getEntites() as $entite) {
                    if ($existing->getEntites()->contains($entite)) {
                        // Check for shared blood types
                        $sharedTypes = array_intersect($campagne->getTypeSang(), $existing->getTypeSang());
                        if (!empty($sharedTypes)) {
                            foreach ($sharedTypes as $type) {
                                $this->context->buildViolation($constraint->message)
                                    ->atPath('typeSang')
                                    ->setParameter('{{ type_sang }}', $type)
                                    ->setParameter('{{ entite }}', $entite->getNom())
                                    ->setParameter('{{ date_debut }}', $campagne->getDateDebut()->format('d/m/Y'))
                                    ->setParameter('{{ date_fin }}', $campagne->getDateFin()->format('d/m/Y'))
                                    ->addViolation();
                            }
                            return; // One violation is enough
                        }
                    }
                }
            }
        }
    }
}
