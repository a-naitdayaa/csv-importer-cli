<?php

namespace App\CsvImporter\Service;

use App\CsvImporter\Repository\CustomerRepository;

class CsvImporter
{
    private CsvReader $csvReader;
    private CustomerValidator $validator;
    private CustomerRepository $repo;

    public function __construct(CsvReader $csvReader, CustomerValidator $validator, CustomerRepository $repo)
    {
        $this->csvReader = $csvReader;
        $this->validator = $validator;
        $this->repo = $repo;
    }

    public function importFile(string $filePath): array
    {
        $inserted = 0;
        $skipped = 0;
        $batch = [];

        foreach ($this->csvReader->read($filePath) as $record) {
            if (!$this->validator->isValid($record)) {
                $skipped++;
                continue;
            }

            $batch[] = $record;
            if (count($batch) === $this->repo->getBatchSize()) {
                $this->repo->insertBatch($batch);
                $inserted += count($batch);

                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->repo->insertBatch($batch);
            $inserted += count($batch);
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }
}
