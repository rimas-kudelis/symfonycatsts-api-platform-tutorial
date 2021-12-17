<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class IsValidOwnerValidator extends ConstraintValidator
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsValidOwner) {
            throw new InvalidArgumentException(sprintf(
                'The constraint must be an instance of %d, got %d.',
                IsValidOwner::class,
                get_class($constraint),
            ));
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof User) {
            throw new InvalidArgumentException('The IsValidOwner constraint must be put on a property containing a User object.');
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            $this->context->buildViolation($constraint->anonymousMessage)
                ->addViolation();

            return;
        }

        if ($value->getId() !== $user->getId()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
