<?php

namespace App\CsvImporter\Service;

class CustomerValidator
{
    public function isValid(array $row): bool
    {
        return !empty($row['Customer Id']) && !empty($row['Email']);
    }
}
