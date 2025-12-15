<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubjectModule;
use App\Models\ModuleTopic;
use App\Models\ModuleCompetency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubjectModuleController extends Controller
{
    /**
     * Get all modules for a subject
     */
    public function index(Request $request): JsonResponse
    {
        $subjectId = $request->query('subject_id');
        $isActive = $request->query('is_active', true);

        $query = SubjectModule::with('topics', 'competencies');

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($isActive !== 'all') {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        $modules = $query->orderBy('order')->get();

        return response()->json([
            'data' => $modules,
            'count' => $modules->count(),
        ]);
    }

    /**
     * Get single module with topics and competencies
     */
    public function show($id): JsonResponse
    {
        $module = SubjectModule::with('topics', 'competencies', 'subject')->findOrFail($id);

        return response()->json($module);
    }

    /**
     * Create a new module
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'required|unique:subject_modules',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced,expert',
            'estimated_hours' => 'nullable|integer|min:1',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $module = SubjectModule::create($validated);

        return response()->json([
            'message' => 'Module created successfully',
            'data' => $module,
        ], 201);
    }

    /**
     * Update a module
     */
    public function update(Request $request, $id): JsonResponse
    {
        $module = SubjectModule::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'code' => "unique:subject_modules,code,$id",
            'difficulty_level' => 'in:beginner,intermediate,advanced,expert',
            'estimated_hours' => 'nullable|integer|min:1',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $module->update($validated);

        return response()->json([
            'message' => 'Module updated successfully',
            'data' => $module,
        ]);
    }

    /**
     * Delete a module
     */
    public function destroy($id): JsonResponse
    {
        $module = SubjectModule::findOrFail($id);
        $module->delete();

        return response()->json([
            'message' => 'Module deleted successfully',
        ]);
    }

    /**
     * Add topic to module
     */
    public function addTopic(Request $request, $moduleId): JsonResponse
    {
        $module = SubjectModule::findOrFail($moduleId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $topic = $module->topics()->create($validated);

        return response()->json([
            'message' => 'Topic added successfully',
            'data' => $topic,
        ], 201);
    }

    /**
     * Update topic
     */
    public function updateTopic(Request $request, $moduleId, $topicId): JsonResponse
    {
        $module = SubjectModule::findOrFail($moduleId);
        $topic = $module->topics()->findOrFail($topicId);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $topic->update($validated);

        return response()->json([
            'message' => 'Topic updated successfully',
            'data' => $topic,
        ]);
    }

    /**
     * Delete topic
     */
    public function deleteTopic($moduleId, $topicId): JsonResponse
    {
        $module = SubjectModule::findOrFail($moduleId);
        $topic = $module->topics()->findOrFail($topicId);
        $topic->delete();

        return response()->json([
            'message' => 'Topic deleted successfully',
        ]);
    }

    /**
     * Add competency to module
     */
    public function addCompetency(Request $request, $moduleId): JsonResponse
    {
        $module = SubjectModule::findOrFail($moduleId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'competency_type' => 'required|in:knowledge,skill,attitude',
            'order' => 'nullable|integer',
        ]);

        $competency = $module->competencies()->create($validated);

        return response()->json([
            'message' => 'Competency added successfully',
            'data' => $competency,
        ], 201);
    }

    /**
     * Update competency
     */
    public function updateCompetency(Request $request, $moduleId, $competencyId): JsonResponse
    {
        $module = SubjectModule::findOrFail($moduleId);
        $competency = $module->competencies()->findOrFail($competencyId);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'competency_type' => 'in:knowledge,skill,attitude',
            'order' => 'nullable|integer',
        ]);

        $competency->update($validated);

        return response()->json([
            'message' => 'Competency updated successfully',
            'data' => $competency,
        ]);
    }

    /**
     * Delete competency
     */
    public function deleteCompetency($moduleId, $competencyId): JsonResponse
    {
        $module = SubjectModule::findOrFail($moduleId);
        $competency = $module->competencies()->findOrFail($competencyId);
        $competency->delete();

        return response()->json([
            'message' => 'Competency deleted successfully',
        ]);
    }

    /**
     * Reorder modules
     */
    public function reorder(Request $request, $subjectId): JsonResponse
    {
        $validated = $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:subject_modules,id',
            'modules.*.order' => 'required|integer',
        ]);

        foreach ($validated['modules'] as $item) {
            SubjectModule::where('id', $item['id'])
                ->where('subject_id', $subjectId)
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Modules reordered successfully',
        ]);
    }
}
