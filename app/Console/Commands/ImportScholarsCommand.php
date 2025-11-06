<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Mail\TemporaryCredentialsMailable;
use Throwable;

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
            $rows = $sheet->toArray(null, true, true, true);

            // 🔹 Academic year is always in row 2, column A
            $academicYear = trim($rows[2]['A'] ?? '');

            if (empty($academicYear)) {
                $this->warn("⚠️ Academic year missing for sheet {$sheetName}, defaulting to 'Unknown'");
                $academicYear = 'Unknown';
            } else {
                $this->info("📌 Detected Academic Year for {$sheetName}: {$academicYear}");
            }

            foreach ($rows as $index => $row) {
                // Skip rows 1, 2, 3, and 4
                if (in_array($index, [1, 2, 3, 4])) continue;

                $studentNo = trim($row['A'] ?? '');
                $email     = trim($row['B'] ?? '');
                $name      = trim($row['C'] ?? '');
                $school    = trim($row['D'] ?? '');
                $program   = trim($row['E'] ?? '');
                $birthday  = trim($row['F'] ?? '');

                if (empty($studentNo) || empty($email)) {
                    $this->warn("⚠️ Skipped row {$index} - missing student number or email");
                    continue;
                }

                // --- Check for duplicate email (within this academic year + scholarship type) ---
                $existing = $this->database
                    ->getReference("scholars/{$academicYear}/{$sheetName}")
                    ->orderByChild('email')
                    ->equalTo($email)
                    ->getValue();

                if (!empty($existing)) {
                    $this->warn("⚠️ Skipped row {$index} - email already exists in {$academicYear}/{$sheetName}: {$email}");
                    continue;
                }

                // --- Parse name ---
                $lastName = '';
                $firstName = '';
                $middleName = '';

                if (!empty($name) && strpos($name, ',') !== false) {
                    [$lastNamePart, $firstMiddlePart] = explode(',', $name, 2);
                    $lastName = trim($lastNamePart);

                    $firstParts = preg_split('/\s+/', trim($firstMiddlePart), -1, PREG_SPLIT_NO_EMPTY);
                    $firstName = $firstParts[0] ?? '';
                    $middleName = count($firstParts) > 1 ? implode(' ', array_slice($firstParts, 1)) : '';
                } else {
                    $lastName = trim($name);
                }

                // --- Generate password ---
                $temporaryPassword = Str::random(8);
                $hashedPassword = Hash::make($temporaryPassword);

                // --- Save to Firebase grouped by academic year → scholarship type ---
                $uid = (string) Str::uuid();

                $this->database
                    ->getReference("scholars/{$academicYear}/{$sheetName}/" . $uid)
                    ->set([
                        'student_no'       => $studentNo,
                        'email'            => $email,
                        'first_name'       => $firstName,
                        'middle_name'      => $middleName,
                        'last_name'        => $lastName,
                        'school'           => $school,
                        'program'          => $program,
                        'birthday'         => $birthday,
                        'scholarship_type' => $sheetName,
                        'created_at'       => now()->setTimezone('Asia/Manila')->toDateTimeString(),
                        'password'         => $hashedPassword,
                    ]);

                $fullName = trim("{$firstName} {$middleName} {$lastName}");
            }
        }
    }
}
