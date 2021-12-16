<?php

namespace App\Serializer\Normalizer;

use App\Entity\User;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class UserNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private array $alreadyProcessedObjectIds = [];

    /**
     * @param User $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        if ($this->userIsOwner($object)) {
            $context['groups'][] = 'owner:read';
        }

        $this->alreadyProcessedObjectIds[] = spl_object_id($object);

        $data = $this->normalizer->normalize($object, $format, $context);

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof User && !in_array(spl_object_id($data), $this->alreadyProcessedObjectIds);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    private function UserIsOwner(User $user): bool
    {
        return 5 >= rand(0, 9);
    }
}
