<?php

namespace App\CsvImporter\Service;

use League\Csv\Reader;

class CsvReader
{
    public function read(string $filePath): iterable
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("File not found or not readable: $filePath");
        }

        $csv = Reader::from($filePath, 'r');
        $csv->setHeaderOffset(0);
        return $csv->getRecords();
    }
}
