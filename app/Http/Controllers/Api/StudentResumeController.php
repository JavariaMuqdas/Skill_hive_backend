<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentResume;
use App\Models\ResumeEducation;
use App\Models\ResumeExperience;
use App\Models\ResumeSkill;
use App\Models\ResumeProject;
use App\Models\Certificate;
use App\Models\ResumeLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentResumeController extends Controller
{
    // Ensure the student is authenticated and return the correct student object
    private function student(Request $request)
    {
        $student = $request->attributes->get('student');

        // Check if student exists and is authenticated
        if (!$student) {
            return response()->json(['error' => 'Student not authenticated'], 403);
        }

        return $student;
    }

    private function ensureBase($studentId): StudentResume
    {
        // Ensure a base record exists for the student, or create one
        return StudentResume::firstOrCreate(
            ['student_id' => $studentId],
            ['privacy' => 'private', 'default_template' => 'classic']
        );
    }

    public function show(Request $request)
    {
        $student = $this->student($request);

        // If student not found, return an error
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Ensure base resume exists for the student
        $base = $this->ensureBase($student->id);

        return response()->json([
            'resume' => $base,
            'educations' => ResumeEducation::where('student_id', $student->id)->orderByDesc('id')->get(),
            'experiences' => ResumeExperience::where('student_id', $student->id)->orderByDesc('id')->get(),
            'skills' => ResumeSkill::where('student_id', $student->id)->orderByDesc('id')->get(),
            'projects' => ResumeProject::where('student_id', $student->id)->orderByDesc('id')->get(),
            'languages' => ResumeLanguage::where('student_id', $student->id)->orderByDesc('id')->get(),
        ]);
    }

    public function saveBase(Request $request)
    {
        $student = $this->student($request);

        if (!$student) {
            return response()->json(['error' => 'Student not authenticated'], 403);
        }

        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'dob' => 'nullable|date_format:Y-m-d',
            'gender' => 'nullable|string|max:50',
            'linkedin' => 'nullable|string|max:255',
            'github' => 'nullable|string|max:255',
            'portfolio' => 'nullable|string|max:255',
            'default_template' => 'nullable|in:classic,modern,minimal',
            'hobbies' => 'nullable|array',
            'hobbies.*' => 'string|max:100',
        ]);

        // 🔥 CORE FIX
        $resume = StudentResume::firstOrCreate(
            ['student_id' => $student->id],
            [
                'privacy' => 'private',
                'default_template' => 'classic'
            ]
        );

        $resume->update($data);

        return response()->json(['resume' => $resume], 200);
    }
    public function updateBase(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'linkedin' => 'nullable|string',
            'github' => 'nullable|string',
            'portfolio' => 'nullable|string',
        ]);

        // Assuming you're updating an existing resume
        $resume = StudentResume::where('student_id', auth()->user()->id)->first();
        if ($resume) {
            $resume->update($validated);
            return response()->json(['resume' => $resume], 200);
        }

        return response()->json(['message' => 'Resume not found'], 404);
    }

    // ===== Education =====
    public function addEducation(Request $request)
    {
        $student = $this->student($request);

        if (!$student) {
            return response()->json(['error' => 'Student not authenticated'], 403);
        }

        $data = $request->validate([
            'degree' => 'nullable|string|max:150',
            'institute' => 'nullable|string|max:150',
            'field' => 'nullable|string|max:150',
            'grade' => 'nullable|string|max:50',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'description' => 'nullable|string|max:2000',
        ]);

        $data['student_id'] = $student->id;

        $row = ResumeEducation::create($data);
        return response()->json(['education' => $row], 201);
    }

    // Update Education
    public function updateEducation(Request $request, $id)
    {
        $student = $this->student($request);
        $row = ResumeEducation::where('student_id', $student->id)->where('id', $id)->firstOrFail();

        $data = $request->validate([
            'degree' => 'nullable|string|max:150',
            'institute' => 'nullable|string|max:150',
            'field' => 'nullable|string|max:150',
            'grade' => 'nullable|string|max:50',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'description' => 'nullable|string|max:2000',
        ]);

        $row->update($data);
        return response()->json(['education' => $row]);
    }

    public function deleteEducation(Request $request, $id)
    {
        $student = $this->student($request);
        $row = ResumeEducation::where('student_id', $student->id)->where('id', $id)->firstOrFail();
        $row->delete();
        return response()->json(['ok' => true]);
    }

    // ===== Other Methods: Experience, Skills, Projects, Languages =====
    // Same structure as education methods: Add, Update, Delete for each model
    //Languages
    // Add Language
    public function addLanguage(Request $request)
    {
        $student = $this->student($request);  // Ensure the student is authenticated

        if (!$student) {
            return response()->json(['error' => 'Student not authenticated'], 403);
        }

        // Validate incoming data for language
        $data = $request->validate([
            'language' => 'required|string|max:150',  // Language name should be required
            'proficiency' => 'required|string|max:50',  // Proficiency should be required
        ]);

        $data['student_id'] = $student->id;  // Attach the authenticated student's ID to the language record

        // Create new language record in the ResumeLanguage model
        $row = ResumeLanguage::create($data);

        // Return the newly created language record
        return response()->json(['language' => $row], 201);
    }

    // Update Language
    public function updateLanguage(Request $request, $id)
    {
        $student = $this->student($request);  // Ensure the student is authenticated
        $row = ResumeLanguage::where('student_id', $student->id)->where('id', $id)->firstOrFail();  // Find the language by student_id and id

        // Validate the incoming language data
        $data = $request->validate([
            'language' => 'nullable|string|max:150',  // Language name can be updated
            'proficiency' => 'nullable|string|max:50',  // Proficiency can be updated
        ]);

        // Update the language record with validated data
        $row->update($data);

        // Return the updated language record
        return response()->json(['language' => $row]);
    }

    //Project
    //Add project
    public function addProject(Request $request)
    {
        $student = $this->student($request);  // Ensure the student is authenticated

        if (!$student) {
            return response()->json(['error' => 'Student not authenticated'], 403);
        }

        // Validate incoming project data
        $data = $request->validate([
            'name' => 'required|string|max:255',  // Project name is required
            'tech_stack' => 'nullable|string|max:500',  // Tech stack (optional)
            'link' => 'nullable|url',  // Link to the project (optional)
            'description' => 'nullable|string|max:2000',  // Description (optional)
        ]);

        // Attach student ID to the project data
        $data['student_id'] = $student->id;

        // Create a new project record in the ResumeProject table
        $project = ResumeProject::create($data);

        // Return the newly created project record
        return response()->json(['project' => $project], 201);
    }
    // Update Project
    // public function updateProject(Request $request, $id)
    // {
    //     $student = $this->student($request);  // Ensure the student is authenticated
    //     $row = ResumeProject::where('student_id', $student->id)->where('id', $id)->firstOrFail();  // Find the project by student_id and id

    //     // Validate the incoming project data
    //     $data = $request->validate([
    //         'name' => 'nullable|string|max:255',  // Project name can be updated
    //         'tech_stack' => 'nullable|string|max:500',  // Tech stack (optional)
    //         'link' => 'nullable|url',  // Link to the project (optional)
    //         'description' => 'nullable|string|max:2000',  // Description (optional)
    //     ]);

    //     // Update the project record with validated data
    //     $row->update($data);

    //     // Return the updated project record
    //     return response()->json(['project' => $row]);
    // }
    //Experience
    // Add Experience
    public function addExperience(Request $request)
    {
        $student = $this->student($request);  // Ensure the student is authenticated

        if (!$student) {
            return response()->json(['error' => 'Student not authenticated'], 403);
        }

        // Validate the incoming experience data
        $data = $request->validate([
            'title' => 'required|string|max:150',  // Job title should be required
            'company' => 'nullable|string|max:150',  // Company name (optional)
            'start_date' => 'nullable|date_format:Y-m-d',  // Start date (optional)
            'end_date' => 'nullable|date_format:Y-m-d',  // End date (optional)
            'is_current' => 'nullable|boolean',  // Whether it's the current job (optional)
            'responsibilities' => 'nullable|array',  // Responsibilities (optional, array of strings)
        ]);

        // If responsibilities are provided as a string, split them into an array
        if (isset($data['responsibilities']) && is_string($data['responsibilities'])) {
            $data['responsibilities'] = explode("\n", $data['responsibilities']);
        }

        $data['student_id'] = $student->id;  // Attach the authenticated student's ID to the experience record

        // Create new experience record in the ResumeExperience model
        $row = ResumeExperience::create($data);

        // Return the newly created experience record
        return response()->json(['experience' => $row], 201);
    }
    // Update Experience
    public function updateExperience(Request $request, $id)
    {
        $student = $this->student($request);  // Ensure the student is authenticated
        $row = ResumeExperience::where('student_id', $student->id)->where('id', $id)->firstOrFail();  // Find the experience by student_id and id

        // Validate the incoming experience data
        $data = $request->validate([
            'title' => 'nullable|string|max:150',  // Job title can be updated
            'company' => 'nullable|string|max:150',  // Company name (optional)
            'start_date' => 'nullable|date_format:Y-m-d',  // Start date (optional)
            'end_date' => 'nullable|date_format:Y-m-d',  // End date (optional)
            'is_current' => 'nullable|boolean',  // Whether it's the current job (optional)
            'responsibilities' => 'nullable|array',  // Responsibilities (optional, array of strings)
        ]);

        // If responsibilities are provided as a string, split them into an array
        if (isset($data['responsibilities']) && is_string($data['responsibilities'])) {
            $data['responsibilities'] = explode("\n", $data['responsibilities']);
        }

        // Update the experience record with validated data
        $row->update($data);

        // Return the updated experience record
        return response()->json(['experience' => $row]);
    }
    //Skill
    // Add Skill
    public function addSkill(Request $request)
    {
        $student = $this->student($request);  // Ensure the student is authenticated

        if (!$student) {
            return response()->json(['error' => 'Student not authenticated'], 403);
        }

        // Validate the incoming skill data
        $data = $request->validate([
            'skill' => 'required|string|max:255',  // Skill name should be required
        ]);

        $data['student_id'] = $student->id;  // Attach the authenticated student's ID to the skill record

        // Create new skill record in the ResumeSkill model
        $row = ResumeSkill::create($data);

        // Return the newly created skill record
        return response()->json(['skill' => $row], 201);
    }
    //certificates
    // Add Certificate
    public function addCertificate(Request $request)
    {
        $student = $this->student($request);  // Ensure the student is authenticated

        if (!$student) {
            return response()->json(['error' => 'Student not authenticated'], 403);
        }

        // Validate the incoming certificate data
        $data = $request->validate([
            'title' => 'required|string|max:255',  // Certificate title should be required
            'issuer' => 'nullable|string|max:255',  // Issuer name (optional)
            'date' => 'nullable|date_format:Y-m-d',  // Certificate issue date (optional)
            'image_url' => 'nullable|url',  // Image URL (optional)
            'image' => 'nullable|image|max:2048',  // Certificate image file (optional)
        ]);

        $data['student_id'] = $student->id;  // Attach the authenticated student's ID to the certificate record

        // Handle the uploaded certificate image if any
        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('certificates', 'public');
        }

        // Create new certificate record in the ResumeCertificate model
        $row = Certificate::create($data);

        // Return the newly created certificate record
        return response()->json(['certificate' => $row], 201);
    }
    // Update Certificate
    public function updateCertificate(Request $request, $id)
    {
        $student = $this->student($request);  // Ensure the student is authenticated
        $row = Certificate::where('student_id', $student->id)->where('id', $id)->firstOrFail();  // Find the certificate by student_id and id

        // Validate the incoming certificate data
        $data = $request->validate([
            'title' => 'nullable|string|max:255',  // Certificate title can be updated
            'issuer' => 'nullable|string|max:255',  // Issuer name (optional)
            'date' => 'nullable|date_format:Y-m-d',  // Certificate issue date (optional)
            'image_url' => 'nullable|url',  // Image URL (optional)
            'image' => 'nullable|image|max:2048',  // Certificate image file (optional)
        ]);

        // Handle the uploaded certificate image if any
        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('certificates', 'public');
        }

        // Update the certificate record with validated data
        $row->update($data);

        // Return the updated certificate record
        return response()->json(['certificate' => $row]);
    }
}
