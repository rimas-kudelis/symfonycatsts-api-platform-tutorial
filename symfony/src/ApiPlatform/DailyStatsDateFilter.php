<?php

declare(strict_types=1);

namespace App\ApiPlatform;

use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DailyStatsDateFilter implements FilterInterface
{
    public const FROM_FILTER_CONTEXT = 'daily_stats_from';

    private bool $throwOnInvalid;

    public function __construct(bool $throwOnInvalid = false)
    {
        $this->throwOnInvalid = $throwOnInvalid;
    }

    public function apply(Request $request, bool $normalization, array $attributes, array &$context)
    {
        $from = $request->query->get('from');

        if (null === $from || '' === $from) {
            return;
        }

        $fromDate = DateTimeImmutable::createFromFormat('Y-m-d', $from, new DateTimeZone('UTC'));
        if (!$fromDate) {
            if ($this->throwOnInvalid) {
                throw new BadRequestHttpException('Invalid "from" date format.');
            }

            return;
        }

        $fromDate = $fromDate->setTime(0, 0, 0);

        $context[self::FROM_FILTER_CONTEXT] = $fromDate;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'from' => [
                'property' => null,
                'type' => 'string',
                'required' => false,
                'openapi' => [
                    'description' => 'From date, e.g. 2020-09-01.',
                ],
            ],
        ];
    }
}
