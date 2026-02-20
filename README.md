# CSV Importer CLI

This project imports customer data from a CSV file into a MySQL database using PHP, PDO, and the Symfony Console component.

## Project Structure
├── composer.json<br>
├── composer.lock<br>
├── data<br>
&emsp;&emsp;└── customers.csv<br>
└── src<br>
&emsp;&emsp;├── import.php<br>
&emsp;&emsp;└── setup.php<br>
└── vendor<br>

- `data/` — CSV files to import  
- `src/import.php` — CSV import CLI script  
- `src/setup.php` — database setup
- `vendor/` — Composer dependencies  

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

> Run the script ```src/setup.php``` using the command ```php setup.php```

4. Running the CLI import script 

```bash
php import.php csv:import ../data/customers.csv
```

* The script handles batches of 500 rows per insert