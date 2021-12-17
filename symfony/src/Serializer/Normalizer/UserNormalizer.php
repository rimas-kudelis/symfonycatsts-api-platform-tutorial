<?php

namespace App\Serializer\Normalizer;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class UserNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private array $alreadyProcessedObjectIds = [];
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param User $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $isOwner = $this->userIsOwner($object);

        if ($isOwner) {
            $context['groups'][] = 'owner:read';
        }

        $this->alreadyProcessedObjectIds[] = spl_object_id($object);

        $data = $this->normalizer->normalize($object, $format, $context);
        $data['isMe'] = $isOwner;

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
        /** @var User|null $authenticatedUser */
        $authenticatedUser = $this->security->getUser();

        return null !== $authenticatedUser && $authenticatedUser->getEmail() === $user->getEmail();
    }
}
