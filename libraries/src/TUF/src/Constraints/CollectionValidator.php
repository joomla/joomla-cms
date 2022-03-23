<?php

namespace Tuf\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator as SymfonyCollectionValidator;

class CollectionValidator extends SymfonyCollectionValidator
{

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        foreach ($constraint->unsupportedFields as $unsupportedField) {
            $existsInArray = \is_array($value) && \array_key_exists($unsupportedField, $value);
            $existsInArrayAccess = $value instanceof \ArrayAccess && $value->offsetExists($unsupportedField);
            if ($existsInArray || $existsInArrayAccess) {
                $this->context->buildViolation('This field is not supported.')
                  ->atPath("[$unsupportedField]")
                  ->setInvalidValue(null)
                  ->setCode(Collection::MISSING_FIELD_ERROR)
                  ->addViolation();
            }
        }
        parent::validate($value, $constraint);
    }
}
