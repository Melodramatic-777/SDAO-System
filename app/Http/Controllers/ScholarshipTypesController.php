<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class ScholarshipTypesController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    public function index()
    {
        $types = $this->database->getReference('scholarship_types')->getValue() ?? [];
        return view('scholars-admin.scholarships', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scholarship_name'   => 'required|string',
            'grade_requirement'  => 'required|numeric',
            'eligibility_cutoff' => 'required|numeric',
            'requirements_pdf'   => 'required|mimes:pdf|max:2048',
        ]);

        $path = $request->file('requirements_pdf')->store('scholarship_pdfs', 'public');
        $pdfUrl = asset('storage/' . $path);

        $this->database->getReference('scholarship_types')->push([
            'scholarship_name'   => $request->scholarship_name,
            'grade_requirement'  => $request->grade_requirement,
            'eligibility_cutoff' => $request->eligibility_cutoff,
            'requirements_pdf'   => $pdfUrl,
        ]);

        return redirect()->route('scholars-admin.scholarships')
            ->with('success', 'Scholarship added with PDF.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'scholarship_name'   => 'required|string',
            'grade_requirement'  => 'required|numeric',
            'eligibility_cutoff' => 'required|numeric',
            'requirements_pdf'   => 'nullable|mimes:pdf|max:2048',
        ]);

        $updateData = [
            'scholarship_name'   => $request->scholarship_name,
            'grade_requirement'  => $request->grade_requirement,
            'eligibility_cutoff' => $request->eligibility_cutoff,
        ];

        if ($request->hasFile('requirements_pdf')) {
            $path = $request->file('requirements_pdf')->store('scholarship_pdfs', 'public');
            $updateData['requirements_pdf'] = asset('storage/' . $path);
        }

        $this->database->getReference('scholarship_types/' . $id)->update($updateData);

        return redirect()->route('scholars-admin.scholarships')
            ->with('success', 'Scholarship updated.');
    }

    public function destroy($id)
    {
        $this->database->getReference('scholarship_types/' . $id)->remove();

        return redirect()->route('scholars-admin.scholarships')
            ->with('success', 'Scholarship deleted.');
    }
}
