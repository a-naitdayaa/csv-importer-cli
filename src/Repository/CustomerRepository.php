<?php

namespace App\CsvImporter\Repository;

class CustomerRepository
{
    private \PDO $pdo;
    private int $batchSize;

    public function __construct(\PDO $pdo, int $batchSize = 500)
    {
        $this->pdo = $pdo;
        $this->batchSize = $batchSize;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function insertBatch(array $batch): array
    {
        $placeholders = [];
        $values = [];

        foreach ($batch as $record) {
            $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $values = array_merge($values, [
                $record['Customer Id'],
                $record['First Name'],
                $record['Last Name'],
                $record['Company'],
                $record['Country'],
                $record['City'],
                $record['Phone 1'],
                $record['Phone 2'],
                $record['Email'],
                $record['Subscription Date'],
                $record['Website'],
            ]);
        }

        $sql = 'INSERT INTO customers (customer_id, first_name, last_name, company, country, city, Phone1, Phone2, email, subscription_date, website) VALUES '
            . implode(', ', $placeholders);


        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);
            $this->pdo->commit();
            return ['inserted' => count($batch), 'skipped' => 0];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['inserted' => 0, 'skipped' => count($batch)];
        }
    }
}
