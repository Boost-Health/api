<?php

namespace App\Objects;

class FlowChartNextObject
{
    public function __construct(
        public readonly ?string $next,
        public readonly array $responses,
        public readonly array $newData = []
    ) {}
}
