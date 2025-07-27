<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Mail;
use App\Mail\TemporaryCredentialsMailable;

class AccountsActivitiesController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }


    

    //ORGANIZATION
    public function showAccountsPage()
    {
        $organizations = $this->database->getReference('organizations')->getValue();
        $orgList = [];
        if ($organizations) {
            foreach ($organizations as $key => $org) {
                $orgList[] = [
                    'id' => $key,
                    'name' => $org['name'] ?? '',
                    'school_name' => $org['school_name'] ?? 'N/A',
                    'program' => $org['program'] ?? '',
                    'type' => $org['type'] ?? '',
                ];
            }

        }

        $accounts = $this->database->getReference('organization_accounts')->getValue();
        $orgUsers = [];
        if ($accounts) {
            foreach ($accounts as $acc) {
                $orgUsers[] = [
                    'organization_name' => $acc['organization_name'] ?? 'N/A',
                    'role' => $acc['role'],
                    'first_name' => $acc['first_name'],
                    'middle_name' => $acc['middle_name'] ?? '',
                    'last_name' => $acc['last_name'],
                    'suffix' => $acc['suffix'] ?? '',
                    'email' => $acc['email'],
                    'created_at' => $acc['created_at'] ?? '',
                ];
            }
        }

        $schools = $this->database->getReference('schools')->getValue();
        $schoolList = [];
        if ($schools) {
            foreach ($schools as $key => $school) {
                $schoolList[] = [
                    'id' => $key,
                    'name' => $school['name'] ?? '',
                    'programs' => $school['programs'] ?? [], // 👈 Include programs
                ];
            }
        }

        $comexAccounts = $this->database->getReference('comex_accounts')->getValue();
        $comex = [];
        if ($comexAccounts) {
            foreach ($comexAccounts as $acc) {
                $comex[] = [
                    'first_name' => $acc['first_name'],
                    'middle_name' => $acc['middle_name'] ?? '',
                    'last_name' => $acc['last_name'],
                    'suffix' => $acc['suffix'] ?? '',
                    'email' => $acc['email'],
                    'created_at' => $acc['created_at'] ?? '',
                ];
            }
        }

        $marketingAccounts = $this->database->getReference('marketing_accounts')->getValue();
        $marketing = [];
        if ($marketingAccounts) {
            foreach ($marketingAccounts as $acc) {
                $marketing[] = [
                    'first_name' => $acc['first_name'],
                    'middle_name' => $acc['middle_name'] ?? '',
                    'last_name' => $acc['last_name'],
                    'suffix' => $acc['suffix'] ?? '',
                    'email' => $acc['email'],
                    'created_at' => $acc['created_at'] ?? '',
                ];
            }
        }




        $deanAccounts = $this->database->getReference('deans')->getValue();
        $deans = [];
        if ($deanAccounts) {
            foreach ($deanAccounts as $dean) {
                $deans[] = [
                    'school_name' => $dean['school_name'] ?? 'N/A',
                    'first_name' => $dean['first_name'],
                    'middle_name' => $dean['middle_name'] ?? '',
                    'last_name' => $dean['last_name'],
                    'suffix' => $dean['suffix'] ?? '',
                    'email' => $dean['email'],
                ];
            }
        }


        $programChairAccounts = $this->database->getReference('program_chairs')->getValue();
        $programChairs = [];
        if ($programChairAccounts) {
            foreach ($programChairAccounts as $chair) {
                $programChairs[] = [
                    'school_name' => $chair['school_name'] ?? 'N/A',
                    'program' => $chair['program'] ?? '',
                    'first_name' => $chair['first_name'],
                    'middle_name' => $chair['middle_name'] ?? '',
                    'last_name' => $chair['last_name'],
                    'suffix' => $chair['suffix'] ?? '',
                    'email' => $chair['email'],
                ];
            }
        }



$facilitiesAccounts = $this->database->getReference('facilities_accounts')->getValue();
$facilities = [];
if ($facilitiesAccounts) {
    foreach ($facilitiesAccounts as $acc) {
        $facilities[] = [
            'first_name' => $acc['first_name'],
            'middle_name' => $acc['middle_name'] ?? '',
            'last_name' => $acc['last_name'],
            'suffix' => $acc['suffix'] ?? '',
            'email' => $acc['email'],
            'created_at' => $acc['created_at'] ?? '',
        ];
    }
}

$itSystemsAccounts = $this->database->getReference('it_systems_accounts')->getValue();
$itSystems = [];
if ($itSystemsAccounts) {
    foreach ($itSystemsAccounts as $acc) {
        $itSystems[] = [
            'first_name' => $acc['first_name'],
            'middle_name' => $acc['middle_name'] ?? '',
            'last_name' => $acc['last_name'],
            'suffix' => $acc['suffix'] ?? '',
            'email' => $acc['email'],
            'created_at' => $acc['created_at'] ?? '',
        ];
    }
}

$rawDirectors = $this->database->getReference('directors')->getValue();
$directors = [];
if ($rawDirectors) {
    foreach ($rawDirectors as $key => $dir) {
        $directors[] = [
            'id' => $key, 
            'type' => $dir['type'],
            'created_at' => $dir['created_at'] ?? 'N/A',
        ];
    }
}



        $directorUserAccounts = $this->database->getReference('director_users')->getValue();
$directorUsers = [];
if ($directorUserAccounts) {
    foreach ($directorUserAccounts as $acc) {
        $directorUsers[] = [
            'director_type' => $acc['director_type'] ?? 'N/A',
            'first_name' => $acc['first_name'],
            'middle_name' => $acc['middle_name'] ?? '',
            'last_name' => $acc['last_name'],
            'suffix' => $acc['suffix'] ?? '',
            'email' => $acc['email'],
            'created_at' => $acc['created_at'] ?? '',
        ];
    }
}


$executiveAccounts = $this->database->getReference('executives')->getValue();
    $executives = [];
    if ($executiveAccounts) {
        foreach ($executiveAccounts as $exec) {
            $executives[] = [
                'id' => $exec['id'] ?? null,
                'position' => $exec['position'] ?? 'N/A',
                'email' => $exec['email'] ?? 'N/A',
            ];
        }
    }

$departmentAccounts = $this->database->getReference('department_accounts')->getValue();
    $departments = [];
    if ($departmentAccounts) {
        foreach ($departmentAccounts as $acc) {
            $departments[] = [
                'school_name' => $acc['school_name'] ?? 'N/A',
                'program' => $acc['program'] ?? '',
                'first_name' => $acc['first_name'],
                'middle_name' => $acc['middle_name'] ?? '',
                'last_name' => $acc['last_name'],
                'suffix' => $acc['suffix'] ?? '',
                'email' => $acc['email'],
                'created_at' => $acc['created_at'] ?? '',
            ];
        }
    }
    return view('activities-admin.accounts', [
        'organizations' => $orgList,
        'orgUsers' => $orgUsers,
        'schools' => $schoolList,
        'deans' => $deans,
        'programChairs' => $programChairs, 
        'comex' => $comex,
        'marketing' => $marketing,
        'directors' => $directors, 
        'directorUsers' => $directorUsers,
        'facilities' => $facilities,
        'itSystems' => $itSystems,
        'departments' => $departments,
        'executives' => $executives,
    ]);
}

public function storeOrganization(Request $request)
{
    $data = $request->validate([
        'org_name' => 'required|string',
        'type' => 'required|string|in:Academic,Non-Academic',
        'school_id' => 'nullable|string',
        'program' => 'nullable|string',
    ]);

    $schoolName = null;

    if ($data['type'] === 'Academic') {
        if (empty($data['school_id']) || empty($data['program'])) {
            return back()->withErrors(['error' => 'School and Program are required for Academic organizations.']);
        }

        // 🔑 Resolve school name like you do for Program Chair:
        $school = $this->database->getReference('schools/' . $data['school_id'])->getValue();
        $schoolName = $school['name'] ?? 'N/A';
    } else {
        // Non-Academic: make sure these are blank
        $data['school_id'] = null;
        $data['program'] = null;
    }

    $this->database->getReference('organizations/' . Str::uuid())
        ->set([
            'name' => $data['org_name'],
            'type' => $data['type'],
            'school_id' => $data['school_id'],
            'school_name' => $schoolName,
            'program' => $data['program'],
            'created_at' => now()->toDateString(),
        ]);

    return back()->with('success', 'Organization added!');
}



public function storeOrganizationAccount(Request $request)
{
    $data = $request->validate([
        'organization_id' => 'required|string',
        'account_type' => 'required|in:President,Adviser',

        // ✅ Make both optional, pick later
        'pres_first' => 'nullable|string',
        'pres_middle' => 'nullable|string',
        'pres_last' => 'nullable|string',
        'pres_suffix' => 'nullable|string',
        'pres_email' => 'nullable|email',

        'adv_first' => 'nullable|string',
        'adv_middle' => 'nullable|string',
        'adv_last' => 'nullable|string',
        'adv_suffix' => 'nullable|string',
        'adv_email' => 'nullable|email',
    ]);

    $org = $this->database->getReference('organizations/' . $data['organization_id'])->getValue();
    $orgName = $org['name'] ?? 'N/A';

    if ($data['account_type'] === 'President') {
        // Validate actual values for President
        $request->validate([
            'pres_first' => 'required|string',
            'pres_last' => 'required|string',
            'pres_email' => 'required|email',
        ]);

        $tempPass = Str::random(8);
        $hashed = Hash::make($tempPass);

        $this->database->getReference('organization_accounts/' . Str::uuid())
            ->set([
                'organization_id' => $data['organization_id'],
                'organization_name' => $orgName,
                'role' => 'President',
                'first_name' => $data['pres_first'],
                'middle_name' => $data['pres_middle'],
                'last_name' => $data['pres_last'],
                'suffix' => $data['pres_suffix'],
                'email' => $data['pres_email'],
                'password' => $hashed,
                'created_at' => now()->toDateTimeString(),
            ]);

        Mail::to($data['pres_email'])->send(
            new TemporaryCredentialsMailable(
                "{$data['pres_first']} {$data['pres_middle']} {$data['pres_last']}",
                $data['pres_email'],
                $tempPass
            )
        );

    } elseif ($data['account_type'] === 'Adviser') {
        // Validate actual values for Adviser
        $request->validate([
            'adv_first' => 'required|string',
            'adv_last' => 'required|string',
            'adv_email' => 'required|email',
        ]);

        $tempPass = Str::random(8);
        $hashed = Hash::make($tempPass);

        $this->database->getReference('organization_accounts/' . Str::uuid())
            ->set([
                'organization_id' => $data['organization_id'],
                'organization_name' => $orgName,
                'role' => 'Adviser',
                'first_name' => $data['adv_first'],
                'middle_name' => $data['adv_middle'],
                'last_name' => $data['adv_last'],
                'suffix' => $data['adv_suffix'],
                'email' => $data['adv_email'],
                'password' => $hashed,
                'created_at' => now()->toDateTimeString(),
            ]);

        Mail::to($data['adv_email'])->send(
            new TemporaryCredentialsMailable(
                "{$data['adv_first']} {$data['adv_middle']} {$data['adv_last']}",
                $data['adv_email'],
                $tempPass
            )
        );
    }

    return back()->with('success', "{$data['account_type']} account created!");
}



    //DEANS
   public function storeSchool(Request $request)
{
    $data = $request->validate([
        'school_name' => 'required|string',
        'programs' => 'required|array',
        'programs.*' => 'required|string',
    ]);

    $this->database->getReference('schools/' . Str::uuid())
        ->set([
            'name' => $data['school_name'],
            'programs' => $data['programs'],
            'registered_date' => now()->toDateString(),
        ]);

    return back()
        ->with('tab', $request->tab)
        ->with('subtab', $request->subtab)
        ->with('success', 'School and programs added!');
}


    public function storeDeanAccount(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|string',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'suffix' => 'nullable|string',
            'email' => 'required|email',
            'created_at' => now()->toDateTimeString(),
        ]);

        $school = $this->database->getReference('schools/' . $data['school_id'])->getValue();
        $schoolName = $school['name'] ?? 'N/A';

        $tempPass = Str::random(8);
        $hashed = Hash::make($tempPass);

        $this->database->getReference('deans/' . Str::uuid())
            ->set([
                'school_id' => $data['school_id'],
                'school_name' => $schoolName,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'suffix' => $data['suffix'],
                'email' => $data['email'],
                'password' => $hashed,
                'created_at' => now()->toDateTimeString(),
            ]);

        Mail::to($data['email'])->send(
            new TemporaryCredentialsMailable(
                "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
                $data['email'],
                $tempPass
            )
        );

        return back()->with('success', 'Dean account created!');
    }

    //PROGRAM CHAIRS
    public function storeProgramChairAccount(Request $request)

    
{
    $data = $request->validate([
        'school_id' => 'required|string',
        'program' => 'required|string',
        'first_name' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'suffix' => 'nullable|string',
        'email' => 'required|email',
    ]);

    $school = $this->database->getReference('schools/' . $data['school_id'])->getValue();
    $schoolName = $school['name'] ?? 'N/A';

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('program_chairs/' . Str::uuid())
        ->set([
            'school_id' => $data['school_id'],
            'school_name' => $schoolName,
            'program' => $data['program'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'password' => $hashed,
            'created_at' => now()->toDateTimeString(),
        ]);

    Mail::to($data['email'])->send(
        new TemporaryCredentialsMailable(
            "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
            $data['email'],
            $tempPass
        )
    );

    return back()->with('success', 'Program Chair account created!');
}

    //COMEX

    public function storeComexAccount(Request $request)
{
    $data = $request->validate([
        'first_name' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'suffix' => 'nullable|string',
        'email' => 'required|email',
    ]);

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('comex_accounts/' . Str::uuid())
        ->set([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'password' => $hashed,
            'created_at' => now()->toDateTimeString(),
        ]);

    Mail::to($data['email'])->send(
        new TemporaryCredentialsMailable(
            "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
            $data['email'],
            $tempPass
        )
    );

    return back()->with('success', 'COMEX account created!');
}

    //MARKETING

    public function storeMarketingAccount(Request $request)
{
    $data = $request->validate([
        'first_name' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'suffix' => 'nullable|string',
        'email' => 'required|email',
    ]);

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('marketing_accounts/' . Str::uuid())
        ->set([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'password' => $hashed,
            'created_at' => now()->toDateTimeString(),
        ]);

    Mail::to($data['email'])->send(
        new TemporaryCredentialsMailable(
            "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
            $data['email'],
            $tempPass
        )
    );

    return back()->with('success', 'Marketing account created!');
}


//DEPARTMENT
public function storeDepartmentAccount(Request $request)
{
    $data = $request->validate([
        'school_id' => 'required|string',
        'program' => 'required|string',
        'first_name' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'suffix' => 'nullable|string',
        'email' => 'required|email',
    ]);

    $school = $this->database->getReference('schools/' . $data['school_id'])->getValue();
    $schoolName = $school['name'] ?? 'N/A';

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('department_accounts/' . Str::uuid())
        ->set([
            'school_id' => $data['school_id'],
            'school_name' => $schoolName,
            'program' => $data['program'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'password' => $hashed,
            'created_at' => now()->toDateTimeString(),
        ]);

    Mail::to($data['email'])->send(
        new TemporaryCredentialsMailable(
            "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
            $data['email'],
            $tempPass
        )
    );

    return back()->with('success', 'Department account created!');
}

// FACILITIES
public function storeFacilitiesAccount(Request $request)
{
    $data = $request->validate([
        'first_name' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'suffix' => 'nullable|string',
        'email' => 'required|email',
    ]);

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('facilities_accounts/' . Str::uuid())
        ->set([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'password' => $hashed,
            'created_at' => now()->toDateTimeString(),
        ]);

    Mail::to($data['email'])->send(
        new TemporaryCredentialsMailable(
            "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
            $data['email'],
            $tempPass
        )
    );

    return back()->with('success', 'Facilities account created!');
}

// IT SYSTEMS
public function storeItSystemsAccount(Request $request)
{
    $data = $request->validate([
        'first_name' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'suffix' => 'nullable|string',
        'email' => 'required|email',
    ]);

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('it_systems_accounts/' . Str::uuid())
        ->set([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'password' => $hashed,
            'created_at' => now()->toDateTimeString(),
        ]);

    Mail::to($data['email'])->send(
        new TemporaryCredentialsMailable(
            "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
            $data['email'],
            $tempPass
        )
    );

    return back()->with('success', 'IT Systems account created!');
}

// ========================
// EXECUTIVES → DIRECTORS
public function storeDirector(Request $request)
{
    $data = $request->validate([
        'type' => 'required|string',
    ]);

    $this->database->getReference('directors/' . Str::uuid())
        ->set([
            'type' => $data['type'],
            'created_at' => now()->toDateTimeString(),
        ]);

    return back()->with('success', 'Director added!');
}

// EXECUTIVES → DIRECTOR USERS
public function storeDirectorUser(Request $request)
{
    $data = $request->validate([
        'director_id' => 'required|string',
        'first_name' => 'required|string',
        'middle_name' => 'nullable|string',
        'last_name' => 'required|string',
        'suffix' => 'nullable|string',
        'email' => 'required|email',
    ]);

    $director = $this->database->getReference('directors/' . $data['director_id'])->getValue();
    $directorType = $director['type'] ?? 'N/A';

    $tempPass = Str::random(8);
    $hashed = Hash::make($tempPass);

    $this->database->getReference('director_users/' . Str::uuid())
        ->set([
            'director_id' => $data['director_id'],
            'director_type' => $directorType,
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'password' => $hashed,
            'created_at' => now()->toDateTimeString(),
        ]);

    Mail::to($data['email'])->send(
        new TemporaryCredentialsMailable(
            "{$data['first_name']} {$data['middle_name']} {$data['last_name']}",
            $data['email'],
            $tempPass
        )
    );

    return back()->with('success', 'Director User created!');
}

}
