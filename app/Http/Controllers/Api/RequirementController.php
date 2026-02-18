<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentRequirement;
use App\Services\LabelService;
use App\Services\RequirementSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequirementController extends Controller
{
    protected $searchService;
    protected LabelService $labelService;

    public function __construct(RequirementSearchService $searchService, LabelService $labelService)
    {
        $this->searchService = $searchService;
        $this->labelService = $labelService;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id'=>'required|exists:subjects,id',
            'budget_min'=>'nullable|integer',
            'budget_max'=>'nullable|integer',
            'mode'=>'required|in:online,offline,both',
            'details'=>'nullable|string',
            'city'=>'nullable|string',
            'desired_start'=>'nullable|date'
        ]);

        $user = $request->user();
        
        // Get the student_id from the user's student relationship
        $studentId = $user->student ? $user->student->id : null;
        
        if (!$studentId) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }
        
        // Count existing requirements for this student
        $requirementCount = StudentRequirement::where('student_id', $studentId)
            ->whereNot(function ($query) {
                $query->where('post_fee', '<=', 0)
                    ->where('lead_status', 'cancelled');
            })
            ->count();
        
        // First 3 requirements are free, then payment required
        if ($requirementCount >= 3) {
            // Check if user has sufficient coins
            $requiredCoins = config('coins.requirement_post_fee', 10); // Default 10 coins
            
            if ($user->coins < $requiredCoins) {
                return response()->json([
                    'error' => 'Insufficient coins',
                    'message' => "You need {$requiredCoins} coins to post a new requirement. Your first 3 requirements were free.",
                    'required_coins' => $requiredCoins,
                    'current_coins' => $user->coins,
                ], 402); // 402 Payment Required
            }
            
            // Deduct coins
            $user->decrement('coins', $requiredCoins);
            
            // Create transaction record
            \App\Models\CoinTransaction::create([
                'user_id' => $user->id,
                'amount' => -$requiredCoins,
                'type' => 'debit',
                'description' => 'Post requirement fee',
                'balance_after' => $user->fresh()->coins,
            ]);
        }

        $requirement = StudentRequirement::create(array_merge($data, ['student_id'=>$studentId]));
        
        return response()->json([
            'requirement' => $requirement,
            'coins_deducted' => $requirementCount >= 3 ? config('coins.requirement_post_fee', 10) : 0,
            'remaining_coins' => $user->fresh()->coins,
            'message' => $requirementCount >= 3 ? 'Requirement posted successfully. Coins deducted.' : 'Requirement posted successfully (Free).',
        ], 201);
    }

    /**
     * Get job requirements for tutors
     * Supports location-based filtering and nearby searches
     * Public endpoint - no authentication required for browsing
     */
    public function index(Request $request)
    {
        // Build filter array from request
        $filters = [
            'q' => $request->input('q'),
            'subject' => $request->input('subject'),
            'subject_id' => $request->input('subject_id'),
            'subject_ids' => $request->input('subject_ids'),
            'location' => $request->input('location'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'nearby' => $request->input('nearby'),
            'radius' => $request->input('radius'),
            'mode' => $request->input('mode'),
            'budget_min' => $request->input('budget_min'),
            'budget_max' => $request->input('budget_max'),
            'gender_preference' => $request->input('gender_preference'),
            'level' => $request->input('level'),
            'languages' => $request->input('languages'),
            'sort_by' => $request->input('sort_by'),
        ];

        // Clean up empty filters
        $filters = array_filter($filters, fn($v) => !is_null($v) && $v !== '');

        $perPage = (int)$request->query('per_page', 20);
        $page = (int)$request->query('page', 1);

        try {
            // Use Elasticsearch via RequirementSearchService
            $results = $this->searchService->search($filters, $perPage, $page);

            // Transform results to include dynamic unlock pricing based on authenticated user's nationality
            $transformedItems = $this->transformRequirementsWithDynamicPricing(
                $results->items(),
                $request->user()
            );

            return response()->json([
                'data' => $transformedItems,
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
            ]);
        } catch (\Exception $e) {
            // Fallback to database query
            return $this->fallbackSearch($request);
        }
    }

    /**
     * Public latest requirements for home page cards
     * GET /api/requirements/latest?limit=3
     */
    public function latestPublic(Request $request)
    {
        $limit = (int) $request->query('limit', 3);
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 20) {
            $limit = 20;
        }

        $requirements = StudentRequirement::query()
            ->with([
                'subjects:id,name',
                'subject:id,name',
                'student.user:id,name',
            ])
            ->whereHas('subjects')
            ->where('visible', true)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $data = $requirements->map(function ($req) {
            $location = $req->location;
            if (!$location) {
                $parts = array_filter([$req->area, $req->city]);
                $location = $parts ? implode(', ', $parts) : null;
            }

            $subjects = $req->subjects ? $req->subjects->pluck('name')->filter()->values()->all() : [];
            if (empty($subjects)) {
                $subjects = DB::table('student_post_subjects')
                    ->join('subjects', 'subjects.id', '=', 'student_post_subjects.subject_id')
                    ->where('student_post_subjects.student_requirement_id', $req->id)
                    ->orderBy('subjects.name')
                    ->pluck('subjects.name')
                    ->filter()
                    ->values()
                    ->all();
            }
            if (empty($subjects) && $req->subject?->name) {
                $subjects = [$req->subject->name];
            }

            return [
                'id' => $req->id,
                'student_name' => $req->student?->user?->name
                    ?? $req->student?->name
                    ?? $req->student_name
                    ?? 'Student',
                'subjects' => $subjects,
                'details' => $req->details,
                'location' => $location,
                'posted_at' => $req->posted_at?->toIso8601String() ?? $req->created_at?->toIso8601String(),
                'created_at' => $req->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'count' => $data->count(),
        ]);
    }

    /**
     * Get nearby job requirements within a radius
     * GET /api/requirements/nearby?lat=28.5355&lng=77.3910&radius=10&subject_id=1
     */
    public function nearby(Request $request)
    {
        if (!$request->user()->hasRole('tutor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|integer|min:1|max:100',
        ]);

        $latitude = $request->input('lat');
        $longitude = $request->input('lng');
        $radius = (int)$request->input('radius', 10);

        $filters = [
            'subject' => $request->input('subject'),
            'subject_id' => $request->input('subject_id'),
            'mode' => $request->input('mode'),
            'budget_min' => $request->input('budget_min'),
            'budget_max' => $request->input('budget_max'),
            'level' => $request->input('level'),
            'sort_by' => $request->input('sort_by', 'distance'),
        ];

        $filters = array_filter($filters, fn($v) => !is_null($v) && $v !== '');

        $perPage = (int)$request->query('per_page', 20);
        $page = (int)$request->query('page', 1);

        try {
            $results = $this->searchService->searchNearby($latitude, $longitude, $filters, $radius, $perPage, $page);

            return response()->json([
                'data' => $results->items(),
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'search' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'radius' => $radius . 'km',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get job requirements by location name
     * GET /api/requirements/by-location?location=delhi&subject_id=1
     */
    public function byLocation(Request $request)
    {
        if (!$request->user()->hasRole('tutor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'location' => 'required|string|min:2',
        ]);

        $location = $request->input('location');

        $filters = [
            'subject' => $request->input('subject'),
            'subject_id' => $request->input('subject_id'),
            'mode' => $request->input('mode'),
            'budget_min' => $request->input('budget_min'),
            'budget_max' => $request->input('budget_max'),
            'level' => $request->input('level'),
            'sort_by' => $request->input('sort_by'),
        ];

        $filters = array_filter($filters, fn($v) => !is_null($v) && $v !== '');

        $perPage = (int)$request->query('per_page', 20);
        $page = (int)$request->query('page', 1);

        try {
            $results = $this->searchService->searchByLocation($location, $filters, $perPage, $page);

            return response()->json([
                'data' => $results->items(),
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'search' => [
                    'location' => $location,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get requirements matching the current tutor's profile
     * GET /api/requirements/for-me
     */
    public function forMe(Request $request)
    {
        if (!$request->user()->hasRole('tutor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tutor = $request->user()->tutor;
        if (!$tutor) {
            return response()->json(['error' => 'Tutor profile not found'], 404);
        }

        $perPage = (int)$request->query('per_page', 20);
        $page = (int)$request->query('page', 1);

        try {
            $results = $this->searchService->getMatchingForTutor($tutor, [], $perPage, $page);

            return response()->json([
                'data' => $results->items(),
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $requirement = StudentRequirement::with('subject', 'subjects', 'student.user')->findOrFail($id);
        if ($requirement->student && ($requirement->student->is_disabled || $requirement->student->user?->is_disabled)) {
            return response()->json(['message' => 'Requirement not available'], 404);
        }
        $requirement = $this->labelService->addLabels($requirement);

        return response()->json($requirement);
    }

    /**
     * Check if student can post a requirement and get pricing info
     * GET /api/requirements/posting-eligibility
     */
    public function postingEligibility(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasRole('student')) {
            return response()->json(['error' => 'Only students can post requirements'], 403);
        }
        
        // Get the student_id from the user's student relationship
        $studentId = $user->student ? $user->student->id : null;
        
        if (!$studentId) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }
        
        $requirementCount = StudentRequirement::where('student_id', $studentId)->count();
        $freeCount = config('coins.free_requirements_count', 3);
        // Use nationality-based pricing via CoinPricingService
        $postFee = \App\Services\CoinPricingService::getCoinCost($user, 'post_requirement');
        
        $isFree = $requirementCount < $freeCount;
        $remainingFree = max(0, $freeCount - $requirementCount);
        $canPost = $isFree || $user->coins >= $postFee;
        
        return response()->json([
            'can_post' => $canPost,
            'is_free' => $isFree,
            'requirements_posted' => $requirementCount,
            'free_requirements_remaining' => $remainingFree,
            'post_fee' => $isFree ? 0 : $postFee,
            'current_coins' => $user->coins,
            'nationality' => \App\Services\CoinPricingService::getNationalityInfo($user)['nationality'],
            'pricing_details' => [
                'indian' => config('coins.pricing_by_nationality.post_requirement.indian', 49),
                'non_indian' => config('coins.pricing_by_nationality.post_requirement.non_indian', 99),
            ],
            'message' => $isFree 
                ? "You have {$remainingFree} free requirement(s) remaining." 
                : ($canPost 
                    ? "Posting will cost {$postFee} coins." 
                    : "Insufficient coins. You need {$postFee} coins to post a requirement."),
        ]);
    }

    /**
     * Fallback to database search if Elasticsearch is unavailable
     */
    protected function fallbackSearch(Request $request)
    {
        $query = StudentRequirement::query()
            ->with('subject','student')
            ->where('visible', true)
            ->whereIn('status', ['active', 'posted', 'open'])
            ->whereHas('student', function ($q) {
                $q->where('is_disabled', false)
                  ->whereHas('user', fn($u) => $u->where('is_disabled', false));
            });

        // Subject search
        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        } elseif ($subject = $request->input('subject')) {
            $query->whereHas('subject', fn($q) => $q->where('name', 'LIKE', '%' . $subject . '%'));
        }

        // Location search
        if ($location = $request->input('location')) {
            $query->where(function($q) use ($location) {
                $q->where('city', 'LIKE', '%' . $location . '%')
                  ->orWhere('area', 'LIKE', '%' . $location . '%')
                  ->orWhere('location', 'LIKE', '%' . $location . '%');
            });
        }

        // Budget filter
        if ($budgetMin = $request->input('budget_min')) {
            $query->where('budget_max', '>=', $budgetMin);
        }
        if ($budgetMax = $request->input('budget_max')) {
            $query->where('budget_min', '<=', $budgetMax);
        }

        // Mode filter
        if ($mode = $request->input('mode')) {
            $query->where('mode', $mode);
        }

        $perPage = (int)$request->query('per_page', 20);
        $results = $query->paginate($perPage);

        return response()->json($results);
    }

    /**
     * Transform requirement/job listings with dynamic unlock pricing
     * based on authenticated user's nationality
     */
    private function transformRequirementsWithDynamicPricing($items, $user)
    {
        if (!is_array($items)) {
            $items = $items->toArray() ?? [];
        }

        return array_map(function($item) use ($user) {
            $item = (array)$item;
            
            // Calculate dynamic unlock price based on tutor's (user's) nationality
            // Requirements/jobs unlock uses post pricing (49/99), not tutor profile pricing (199/399)
            if ($user) {
                $isIndia = $user->country_iso === 'IN';
                $unlockPrice = $isIndia
                    ? config('enquiry.pricing_by_nationality.post.indian', 49)
                    : config('enquiry.pricing_by_nationality.post.non_indian', 99);
                $item['unlock_price'] = $unlockPrice;
                $item['pricing_details'] = [
                    'indian' => config('enquiry.pricing_by_nationality.post.indian', 49),
                    'non_indian' => config('enquiry.pricing_by_nationality.post.non_indian', 99),
                ];
            }
            
            return $item;
        }, $items);
    }
}

