<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Project;

class RecommendationController extends Controller
{
    private function normalizeSkills(array $skills): array
    {
        $aliases = [
            'js' => 'javascript',
            'react.js' => 'react',
            'node.js' => 'node',
            'tailwind' => 'tailwind css',
            'html5' => 'html',
            'css3' => 'css',
            'canvaa' => 'canva',
        ];

        return collect($skills)
            ->map(function ($skill) use ($aliases) {
                $skill = strtolower(trim((string) $skill));
                return $aliases[$skill] ?? $skill;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function parseSkills($skills): array
    {
        if (is_array($skills)) {
            return $this->normalizeSkills($skills);
        }

        if (is_string($skills)) {
            $decoded = json_decode($skills, true);

            if (is_array($decoded)) {
                return $this->normalizeSkills($decoded);
            }

            return $this->normalizeSkills(explode(',', $skills));
        }

        return [];
    }

    public function recommendedStudents(Request $request)
    {
        $company = $request->attributes->get('company');

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found in request.'
            ], 400);
        }

        $projects = Project::where('company_id', $company->id)
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->get();

        $students = Student::with(['user', 'resumeSkills'])->get();

        $jobWiseRecommendations = [];

        foreach ($projects as $project) {
            $jobSkills = $this->parseSkills($project->required_skills);

            if (empty($jobSkills)) {
                continue;
            }

            $matchedStudents = [];

            foreach ($students as $student) {
                $studentSkillsRaw = $student->resumeSkills->pluck('skill')->toArray();

                $studentSkills = [];

                foreach ($studentSkillsRaw as $skillRow) {
                    $studentSkills = array_merge(
                        $studentSkills,
                        $this->parseSkills($skillRow)
                    );
                }

                $studentSkills = $this->normalizeSkills($studentSkills);

                if (empty($studentSkills)) {
                    continue;
                }

                $matchedSkills = array_values(array_intersect($studentSkills, $jobSkills));
                $matchCount = count($matchedSkills);

                if ($matchCount < 1) {
                    continue;
                }

                $matchedStudents[] = [
                    'student_id' => $student->id,
                    'name' => optional($student->user)->name ?? 'Student',
                    'email' => optional($student->user)->email ?? '',
                    'matched_skills' => $matchedSkills,
                    'match_count' => $matchCount,
                    'match_percentage' => round(($matchCount / count($jobSkills)) * 100, 2),
                ];
            }

            if (count($matchedStudents) < 1) {
                continue; // skip jobs with no matching students
            }

            usort($matchedStudents, function ($a, $b) {
                return $b['match_count'] <=> $a['match_count'];
            });

            $jobWiseRecommendations[] = [
                'job_id' => $project->id,
                'job_title' => $project->title,
                'required_skills' => $jobSkills,
                'recommended_students' => $matchedStudents
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $jobWiseRecommendations
        ]);
    }
}