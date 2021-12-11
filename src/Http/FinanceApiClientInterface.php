<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

interface FinanceApiClientInterface
{
    public function fetchStockProfile(string $symbol, string $region): JsonResponse;
}