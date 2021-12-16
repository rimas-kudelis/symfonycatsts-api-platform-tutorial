<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class DefaultGroupsContextBuilder implements SerializerContextBuilderInterface
{
    private SerializerContextBuilderInterface $decoratedBuilder;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->decoratedBuilder = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(
        Request $request,
        bool $normalization,
        ?array $extractedAttributes = null
    ): array {
        $context = $this->decoratedBuilder->createFromRequest($request, $normalization, $extractedAttributes);

        if (!isset($context['groups'])) {
            $context['groups'] = [];
        }

        $context['groups'] = array_unique(array_merge(
            $context['groups'],
            $this->getDefaultGroups($context, $normalization),
        ));

        return $context;
    }

    private function getDefaultGroups(array $context, bool $normalization): array
    {
        $resourceClass = $context['resource_class'] ?? null;
        if (!$resourceClass) {
            return [];
        }

        $shortName = (new \ReflectionClass($resourceClass))->getShortName();
        $classAlias = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($shortName)));
        $readOrWrite = $normalization ? 'read' : 'write';
        $itemOrCollection = $context['operation_type'];
        $operationName = $itemOrCollection === 'item' ? $context['item_operation_name'] : $context['collection_operation_name'];

        return [
            // {class}:{read/write}
            // e.g. user:read
            sprintf('%s:%s', $classAlias, $readOrWrite),
            // {class}:{item/collection}:{read/write}
            // e.g. user:collection:read
            sprintf('%s:%s:%s', $classAlias, $itemOrCollection, $readOrWrite),
            // {class}:{item/collection}:{operationName}
            // e.g. user:collection:get
            sprintf('%s:%s:%s', $classAlias, $itemOrCollection, $operationName),
        ];
    }
}
