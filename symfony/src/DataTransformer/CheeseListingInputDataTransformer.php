<?php

declare(strict_types=1);

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;

class CheeseListingInputDataTransformer implements DataTransformerInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function transform($input, string $to, array $context = [])
    {
        dump('Transforming cheeseListing input.', ['input' => $input, 'to' => $to, 'context' => $context]);
        if (!$input instanceof CheeseListingInput) {
            throw new InvalidArgumentException(sprintf(
                'The object to transform must be an instance of %s, but got %s.',
                CheeseListingInput::class,
                is_object($input) ? get_class($input) : gettype($input),
            ));
        }

        $this->validator->validate($input);

        $cheeseListing = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null;

        return $input->createOrUpdateEntity($cheeseListing);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        dump(sprintf(
            'checking if cheese listing transformation is supported., will return %s.',
            ($to === CheeseListing::class && CheeseListingInput::class === ($context['input']['class'] ?? null)) ? 'true' : 'false',
        ), ['data' => $data, 'to' => $to, 'context' => $context]);

        if ($data instanceof CheeseListing) {
            // Already transformed
            return false;
        }

        return $to === CheeseListing::class && CheeseListingInput::class === ($context['input']['class'] ?? null);
    }
}
