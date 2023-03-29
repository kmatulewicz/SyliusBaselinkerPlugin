<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Service;

interface ApiRequestServiceInterface
{
    public function do(string $method, array $parameters = []): array;
}
