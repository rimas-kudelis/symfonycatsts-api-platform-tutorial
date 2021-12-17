<?php

namespace App\Validator;

use App\Entity\CheeseListing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class ValidIsPublishedValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidIsPublished)
        {
            throw new InvalidArgumentException(sprintf(
                'The constraint must be an instance of %d, got %d.',
                ValidIsPublished::class,
                get_class($constraint),
            ));
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof CheeseListing) {
            throw new InvalidArgumentException('The ValidIsPublisher constraint must be put on a CheeseListing object.');
        }

        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($value);

        $published = $value->getIsPublished();
        $previousPublished = $originalData['isPublished'] ?? false;

        if ($previousPublished === $published) {
            return;
        }

        if ($published) {
            if (100 > strlen($value->getDescription()) && !$this->security->isGranted('ROLE_ADMIN')) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('description')
                    ->addViolation();

            }

            return;
        }

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            //throw new AccessDeniedException($constraint->nonAdminUnpublishMessage);

            $this->context->buildViolation($constraint->nonAdminUnpublishMessage)
                ->atPath('isPublished')
                ->addViolation();
        }
    }
}
