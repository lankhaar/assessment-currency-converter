<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidIp extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        dd($value);
        /* @var $constraint \App\Validator\Constraints\ValidIp */

        if (null === $value || '' === $value) {
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
