<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Services;

interface BaselinkerApiRequestServiceInterface
{
    public function do(string $method, array $parameters = []): array;
}
