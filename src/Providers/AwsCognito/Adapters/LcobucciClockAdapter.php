<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito\Adapters;

use DateTimeImmutable;
use Lcobucci\Clock\Clock;
use StellaMaris\Clock\ClockInterface;

class LcobucciClockAdapter implements Clock
{
    private ClockInterface $clock;

    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
    }

    public function now(): DateTimeImmutable
    {
        return $this->clock->now();
    }
}
