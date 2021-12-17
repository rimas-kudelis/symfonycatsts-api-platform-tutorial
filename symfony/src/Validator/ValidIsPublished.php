<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(flags: \Attribute::TARGET_CLASS)]
class ValidIsPublished extends Constraint
{
    public string $message = 'The description must be at least 100 characters long.';

    public string $nonAdminUnpublishMessage = 'Only admins can unpublish a listing.';

    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
