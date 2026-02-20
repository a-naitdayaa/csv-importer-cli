<?php

use League\Csv\Reader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$application = new Application();
$application->register('csv:import')
    ->setDescription('Import customers from a CSV file into the database')
    ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file to import')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $file = $input->getArgument('file');
        $output->writeln("Importing data from CSV file: $file");;

        try {
            $pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $csv = Reader::from($file, 'r');
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();

            $inserted = 0;
            $skipped = 0;
            $startTime = microtime(true);

            //--- batch configuration ---
            $batchSize = 500;
            $batch = [];

            foreach ($records as $record) {
                if (empty($record['Customer Id']) || empty($record['Email'])) {
                    $skipped++;
                    continue;
                }

                $batch[] = $record;
                if (count($batch) === $batchSize) {
                    $result = insertBatch($pdo, $batch, $output);
                    $inserted += $result['inserted'];
                    $skipped += $result['skipped'];
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $result = insertBatch($pdo, $batch, $output);
                $inserted += $result['inserted'];
                $skipped += $result['skipped'];
                $batch = [];
            }

            $elapsed = round(microtime(true) - $startTime, 4);

            $output->writeln("   Import complete!");
            $output->writeln("   Rows inserted : $inserted");
            $output->writeln("   Rows skipped  : $skipped");
            $output->writeln("   Time taken    : {$elapsed}s");
        } catch (PDOException $e) {
            $output->writeln("Failed to import data from CSV file  " . $e->getMessage());
        } catch (Exception $e) {
            $output->writeln("an error occured " . $e->getMessage());
        }

        return Command::SUCCESS;
    });

function insertBatch($pdo, $batch, OutputInterface $output)
{
    $placeholders = [];
    $values = [];

    foreach ($batch as $record) {
        $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        array_push(
            $values,
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
            $record['Website']
        );
    }

    $sql = 'INSERT INTO CUSTOMERS (customer_id, first_name, last_name, company, country, city, Phone1, Phone2, email, subscription_date, website) VALUES ' . implode(', ', $placeholders);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        $pdo->commit();
        return [
            'inserted' => count($batch),
            'skipped' => 0
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        $output->writeln("Failed to insert batch  " . $e->getMessage());
        return [
            'inserted' => 0,
            'skipped' => count($batch)
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        $output->writeln("an error occured " . $e->getMessage());
        return [
            'inserted' => 0,
            'skipped' => count($batch)
        ];
    }
}

$application->run();
