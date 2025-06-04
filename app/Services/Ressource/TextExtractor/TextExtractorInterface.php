<?php

namespace App\Services\Resource\TextExtractor;

interface TextExtractorInterface
{
    public function supports(string $extension): bool;
    public function extract(string $filePath): string;
}