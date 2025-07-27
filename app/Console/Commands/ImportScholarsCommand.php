<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class ImportScholarsCommand extends Command
{
    protected $signature = 'import:scholars {file}';
    protected $description = 'Import scholars from Excel or CSV file';

    protected $database;

    public function __construct()
    {
        parent::__construct();

        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("❌ File does not exist: {$filePath}");
            return 1;
        }

        $spreadsheet = IOFactory::load($filePath);

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray(null, true, true, true); // A, B, C, etc.

            foreach ($rows as $index => $row) {
                // Skip header rows 1–4, start importing at row 5
                if ($index < 5) continue;

                $studentNo = trim($row['A'] ?? '');
                $email = trim($row['B'] ?? '');
                $name = trim($row['C'] ?? '');
                $school = trim($row['D'] ?? '');
                $program = trim($row['E'] ?? '');
                $birthday = trim($row['F'] ?? '');

                if (empty($studentNo)) {
                    $this->warn("⚠️ Skipped row {$index} - student number empty");
                    continue;
                }

                $lastName = '';
                $firstName = '';
                $middleName = '';

                // Handle "LastName, FirstName MiddleName" format
                if (strpos($name, ',') !== false) {
                    [$lastNamePart, $firstMiddlePart] = explode(',', $name, 2);
                    $lastName = trim($lastNamePart);
                    $firstMiddle = trim(preg_replace('/\s+/', ' ', $firstMiddlePart)); // normalize spaces

                    $firstNameParts = explode(' ', $firstMiddle);
                    $firstName = array_shift($firstNameParts);
                    $middleName = implode(' ', $firstNameParts);
                } else {
                    $lastName = trim($name);
                }

                // Save to Firebase
                $this->database->getReference('scholar_accounts/' . Str::uuid())
                    ->set([
                        'student_no' => $studentNo,
                        'email' => $email,
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'school' => $school,
                        'program' => $program,
                        'birthday' => $birthday,
                        'scholarship_type' => $sheetName,
                        'created_at' => now()->toDateTimeString(),
                    ]);

                $this->info("✅ Imported row {$index}: {$studentNo} - {$lastName}, {$firstName} {$middleName}");
            }
        }

        $this->info('✅ All rows processed.');
        return 0;
    }
}
