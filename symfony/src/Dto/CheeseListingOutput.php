<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class CheeseListingOutput
{
    #[Groups(['cheese:read'])]
    public string $title;
}
