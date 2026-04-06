<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentRequirement;
use App\Services\LabelService;
use App\Services\RequirementSearchService;
use App\Services\SubscriptionAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequirementController extends Controller
{
    protected $searchService;
    protected LabelService $labelService;
    protected SubscriptionAccessService $accessService;

    public function __construct(RequirementSearchService $searchService, LabelService $labelService, SubscriptionAccessService $accessService)
    {
        $this->searchService = $searchService;
        $this->labelService = $labelService;
        $this->accessService = $accessService;
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
        
        $coinsDeducted = 0;
        
        // First 3 requirements are free, then payment required
        if ($requirementCount >= 3) {
            // ✅ Check if user has active subscription
            $activeSubscription = $user->activeSubscription();
            $hasSubscription = $activeSubscription !== null;
            
            if ($hasSubscription && $activeSubscription->canView()) {
                // ✅ FREE with active subscription
                \App\Models\SubscriptionViewLog::create([
                    'user_id' => $user->id,
                    'user_subscription_id' => $activeSubscription->id,
                    'viewable_id' => null,
                    'viewable_type' => 'requirement_post',
                    'action_type' => 'student_post_requirement',
                    'viewed_at' => now(),
                ]);
                
                // Update views_used count
                $activeSubscription->incrementViewCount();
                
                $coinsDeducted = 0;
            } else {
                // ❌ Deduct coins (no subscription or views exhausted)
                $requiredCoins = config('coins.requirement_post_fee', 10); // Default 10 coins
                
                if ($user->coins < $requiredCoins) {
                    return response()->json([
                        'error' => 'Insufficient coins',
                        'message' => $hasSubscription 
                            ? "Your subscription views are exhausted. You need {$requiredCoins} coins to post a requirement."
                            : "You need {$requiredCoins} coins to post a new requirement. Your first 3 requirements were free.",
                        'required_coins' => $requiredCoins,
                        'current_coins' => $user->coins,
                        'has_subscription' => $hasSubscription,
                        'views_exhausted' => $hasSubscription,
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
                    'meta' => json_encode([
                        'reason' => $hasSubscription ? 'subscription_views_exhausted' : 'post_fee',
                    ]),
                ]);
                
                $coinsDeducted = $requiredCoins;
            }
        }

        $requirement = StudentRequirement::create(array_merge($data, [
            'student_id' => $studentId,
            'posted_at' => now(),
        ]));
        
        return response()->json([
            'requirement' => $requirement,
            'coins_deducted' => $coinsDeducted,
            'remaining_coins' => $user->fresh()->coins,
            'subscription_used' => $coinsDeducted === 0 && $requirementCount >= 3,
            'message' => $requirementCount < 3 
                ? 'Requirement posted successfully (Free).'
                : ($coinsDeducted === 0
                    ? 'Requirement posted successfully (Free with subscription).'
                    : 'Requirement posted successfully. Coins deducted.'),
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

            // ✅ Apply subscription-based access delay filtering for authenticated users
            $user = $request->user();
            if ($user) {
                $filteredItems = $this->applyAccessDelayFilter($transformedItems, $user);
                
                // Enrich with access information
                $filteredItems = collect($filteredItems)->map(function($item) use ($user) {
                    return $this->enrichWithAccessInfo($item, $user);
                })->toArray();
            } else {
                $filteredItems = $transformedItems;
            }

            return response()->json([
                'data' => $filteredItems,
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'note' => $user && $user->activeSubscription() && $user->activeSubscription()->isBASICPlan() 
                    ? 'BASIC plan: Showing requirements older than ' . $user->activeSubscription()->plan->getAccessDelayHours() . ' hours'
                    : null,
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

        $user = $request->user();
        
        // ✅ Apply subscription-based access delay filtering for authenticated users
        if ($user) {
            $requirements = $this->applyAccessDelayFilter($requirements, $user);
        }

        $data = $requirements->map(function ($req) use ($user) {
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

            $itemData = [
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
            
            // Enrich with access information if user is authenticated
            if ($user) {
                $itemData = $this->enrichWithAccessInfo($itemData, $user);
            }
            
            return $itemData;
        });

        return response()->json([
            'data' => $data,
            'count' => $data->count(),
            'note' => $user && $user->activeSubscription() && $user->activeSubscription()->isBASICPlan() 
                ? 'BASIC plan: Showing requirements older than ' . $user->activeSubscription()->plan->getAccessDelayHours() . ' hours'
                : null,
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
            
            $user = $request->user();
            $items = $results->items();
            
            // ✅ Apply subscription-based access delay filtering for authenticated users
            if ($user) {
                $filteredItems = $this->applyAccessDelayFilter($items, $user);
                
                // Enrich with access information
                $filteredItems = collect($filteredItems)->map(function($item) use ($user) {
                    $item = (array)$item;
                    return $this->enrichWithAccessInfo($item, $user);
                })->toArray();
            } else {
                $filteredItems = $items;
            }

            return response()->json([
                'data' => $filteredItems,
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'search' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'radius' => $radius . 'km',
                ],
                'note' => $user && $user->activeSubscription() && $user->activeSubscription()->isBASICPlan() 
                    ? 'BASIC plan: Showing requirements older than ' . $user->activeSubscription()->plan->getAccessDelayHours() . ' hours'
                    : null,
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
            
            $user = $request->user();
            $items = $results->items();
            
            // ✅ Apply subscription-based access delay filtering for authenticated users
            if ($user) {
                $filteredItems = $this->applyAccessDelayFilter($items, $user);
                
                // Enrich with access information
                $filteredItems = collect($filteredItems)->map(function($item) use ($user) {
                    $item = (array)$item;
                    return $this->enrichWithAccessInfo($item, $user);
                })->toArray();
            } else {
                $filteredItems = $items;
            }

            return response()->json([
                'data' => $filteredItems,
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'search' => [
                    'location' => $location,
                ],
                'note' => $user && $user->activeSubscription() && $user->activeSubscription()->isBASICPlan() 
                    ? 'BASIC plan: Showing requirements older than ' . $user->activeSubscription()->plan->getAccessDelayHours() . ' hours'
                    : null,
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

    /**
     * Get a single requirement with full details
     * POST /api/requirements/{id}
     * 
     * This counts as ONE VIEW in the subscription system:
     * - PRO: Rs. 39 coins deducted (unlimited views)
     * - BASIC: 1 view from 5-view quota, or Rs. 49 coins if views exhausted
     * - No subscription: Rs. 49-99 coins (based on nationality)
     */
    public function show($id, Request $request)
    {
        $requirement = StudentRequirement::with('subject', 'subjects', 'student.user')->findOrFail($id);
        if ($requirement->student && ($requirement->student->is_disabled || $requirement->student->user?->is_disabled)) {
            return response()->json(['message' => 'Requirement not available'], 404);
        }

        $user = $request->user();
        
        // ✅ SUBSCRIPTION-BASED VIEW TRACKING
        // Track this as a view and handle coin/view deduction
        if ($user && $user->hasRole('tutor')) {
            try {
                $coinSpendingService = app(\App\Services\CoinSpendingService::class);
                
                $result = $coinSpendingService->checkAndDeductCoins(
                    $user,
                    'requirement_view',
                    [
                        'requirement_id' => $requirement->id,
                        'viewable_id' => $requirement->id,
                        'viewable_type' => 'requirement_detail',
                    ]
                );

                if (!$result['success']) {
                    return response()->json($result, $result['status_code']);
                }
            } catch (\Exception $e) {
                \Log::error('Requirement view tracking error', [
                    'user_id' => $user->id,
                    'requirement_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                // Continue showing requirement even if tracking fails
            }
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

    /**
     * Apply subscription-based access delay filtering to requirements
     * BASIC tier users see 1-2 hour delayed requirements
     * PRO tier and non-subscribers see requirements immediately
     */
    protected function applyAccessDelayFilter($requirements, $user = null)
    {
        if (!$user || !$requirements) {
            return $requirements;
        }

        // Get user's subscription
        $subscription = $user->activeSubscription();
        
        // No subscription or PRO tier - no filtering
        if (!$subscription || $subscription->isPROPlan()) {
            return $requirements;
        }

        // BASIC tier - filter out requirements that are too new
        $delayHours = $subscription->plan?->getAccessDelayHours() ?? 0;
        
        if ($delayHours === 0) {
            return $requirements; // No delay configured
        }

        $cutoffTime = now()->subHours($delayHours);

        // Filter requirements - only keep those older than the cutoff time
        return collect($requirements)->filter(function ($req) use ($cutoffTime) {
            $createdAt = $req->created_at ?? $req['created_at'] ?? null;
            
            if (!$createdAt) {
                return true; // If no timestamp, allow access
            }

            // Convert to Carbon if it's a string
            if (is_string($createdAt)) {
                $createdAt = \Carbon\Carbon::parse($createdAt);
            }

            return $createdAt->lessThanOrEqualTo($cutoffTime);
        })->values();
    }

    /**
     * Enrich requirement data with access information for BASIC tier users
     */
    protected function enrichWithAccessInfo(array $requirementData, $user = null)
    {
        if (!$user) {
            return $requirementData;
        }

        $subscription = $user->activeSubscription();
        if (!$subscription || $subscription->isPROPlan()) {
            return $requirementData;
        }

        $createdAt = isset($requirementData['created_at']) 
            ? \Carbon\Carbon::parse($requirementData['created_at'])
            : null;

        if ($createdAt) {
            $accessInfo = $this->accessService->canAccessRequirement($user, $createdAt);
            $requirementData['access'] = [
                'can_access' => $accessInfo['can_access'],
                'delay_hours' => $accessInfo['delay_hours'],
                'available_at' => $accessInfo['available_at']?->toIso8601String(),
            ];
        }

        return $requirementData;
    }
}

