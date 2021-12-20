<?php

declare(strict_types=1);

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Dto\CheeseListingOutput;
use App\Entity\CheeseListing;

class CheeseListingOutputDataTransformer implements DataTransformerInterface
{
    public function transform($cheeseListing, string $to, array $context = []): CheeseListingOutput
    {
        if (!$cheeseListing instanceof CheeseListing) {
            throw new InvalidArgumentException(sprintf(
                'The object to transform must be an instance of %s, but got %s.',
                CheeseListing::class,
                is_object($cheeseListing) ? get_class($cheeseListing) : gettype($cheeseListing),
            ));
        }

        return CheeseListingOutput::createFromEntity($cheeseListing);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $data instanceof CheeseListing && $to === CheeseListingOutput::class;
    }
}
