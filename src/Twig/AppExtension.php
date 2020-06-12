<?php

declare(strict_types=1);

namespace App\Twig;

use Carbon\Carbon;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('diffForHumans', [$this, 'diffForHumans']),
        ];
    }

    public function diffForHumans($date): string
    {
        return (new Carbon($date))->diffForHumans();
    }
}