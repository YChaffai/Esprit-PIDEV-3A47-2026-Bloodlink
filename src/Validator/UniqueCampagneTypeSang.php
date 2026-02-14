<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueCampagneTypeSang extends Constraint
{
    public string $message = 'Une campagne avec le type de sang "{{ type_sang }}" existe déjà pour l\'entité "{{ entite }}" sur la même période ({{ date_debut }} - {{ date_fin }}).';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
