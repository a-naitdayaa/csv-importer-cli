# CSV Importer CLI

This project imports customer data from a CSV file into a MySQL database using PHP, PDO, and the Symfony Console component.

## Project Structure
```
├── README.md
├── bin
│   └── console                        # Symfony console entry point
├── composer.json
├── composer.lock
├── data
│   └── customers.csv
└── src
    ├── Database
    │   └── Database.php                # Connection Logic
    ├── Repository
    │   └── CustomerRepository.php      # Batch insertion
    ├── command
    │   └── ImportCsvCommand.php        # Console Command
    ├── service
    │   ├── CsvImporter.php
    │   ├── CsvReader.php               # CSV reading using league csv
    │   └── CustomerValidator.php       # Minimal row validation
    └── setup.php                       # Database setup
└── vendor
```

## Setup

1. Installing dependencies:

```bash
composer install
```
2. Setting up environment variables

```bash
cp .env.example .env
```

3. Preparing the database

Run the script ```src/setup.php``` using the command ```php setup.php```

4. Making bin/console executable:

```bash
chmod +x bin/console
```

## Usage 
To import the data, run the command 

```bash
php bin/console csv:import data/customers.csv
```

## Project Components 

### ImportCsvCommand.php
* Handles CLI arguments and triggers the import workflow.
* Uses the CsvImporter service to process the CSV file.

### Database.php
* Provides a getConnection() method.

### Repository 
* Handles all database operations, including batch insertion.
* Controls batch size and transaction handling.

### CsvImporter.php
* Orchestrates the CSV import process:
    * Reads rows using CsvReader
    * Validates rows using CustomerValidator
    * Sends valid rows to the repository for batch insertion

### CsvReader.php
* Reads the cvs file using league CSV

### CustomerValidator.php
* Validates each row for required fields (Customer Id & Email).