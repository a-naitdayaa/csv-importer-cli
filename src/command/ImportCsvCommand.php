<?php

namespace App\CsvImporter\Command;

use PDOException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use App\CsvImporter\Service\CsvImporter;
use App\CsvImporter\Database\Database;
use App\CsvImporter\Service\CustomerValidator;
use App\CsvImporter\Service\CsvReader;
use App\CsvImporter\Repository\CustomerRepository;

#[AsCommand(
    name: 'csv:import'
)]
class ImportCsvCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Import customers from a CSV file into the database')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $output->writeln("Importing data from CSV file: $file");
        try {
            $pdo = Database::getConnection();
            $repo = new CustomerRepository($pdo);
            $validator = new CustomerValidator();
            $reader = new CsvReader();

            $importer = new CsvImporter(csvReader: $reader, repo: $repo, validator: $validator);

            $startTime = microtime(true);
            $result = $importer->importFile($file);
            $elapsed = round(microtime(true) - $startTime, 2);

            $output->writeln("Import complete!");
            $output->writeln("Rows inserted: {$result['inserted']}");
            $output->writeln("Rows skipped: {$result['skipped']}");
            $output->writeln("Time taken: {$elapsed}s");

            return Command::SUCCESS;
        } catch (PDOException $e) {
            $output->writeln("<error>Database Connection Failed: {$e->getMessage()}</error>");
            return Command::FAILURE;
        } catch (Exception $e) {
            $output->writeln("<error>General error: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
