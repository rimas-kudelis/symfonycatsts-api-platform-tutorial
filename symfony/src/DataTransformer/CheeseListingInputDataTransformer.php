<?php

declare(strict_types=1);

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;

class CheeseListingInputDataTransformer implements DataTransformerInterface
{
    public function transform($input, string $to, array $context = [])
    {
        if (!$input instanceof CheeseListingInput) {
            throw new InvalidArgumentException(sprintf(
                'The object to transform must be an instance of %s, but got %s.',
                CheeseListingInput::class,
                is_object($input) ? get_class($input) : gettype($input),
            ));
        }

        if (isset($context[AbstractItemNormalizer::OBJECT_TO_POPULATE])) {
            $cheeseListing = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];
        } else {
            $cheeseListing = new CheeseListing($input->title);
        }

        $cheeseListing->setDescription($input->description);
        $cheeseListing->setPrice($input->price);
        $cheeseListing->setOwner($input->owner);
        $cheeseListing->setIsPublished($input->isPublished);

        return $cheeseListing;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof CheeseListing) {
            // Already transformed
            return false;
        }

        return $to === CheeseListing::class && CheeseListingInput::class === ($context['input']['class'] ?? null);
    }
}
