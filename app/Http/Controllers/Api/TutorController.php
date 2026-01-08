<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Services\TutorSearchService;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    protected $searchService;

    public function __construct(TutorSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index(Request $request)
    {
        // Build filter array from request
        $filters = [
            'q' => $request->input('q'),
            'subject' => $request->input('subject'),
            'subject_id' => $request->input('subject_id'),
            'subject_search_name' => $request->input('subject_search_name'),
            'location' => $request->input('location'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'nearby' => $request->input('nearby'),
            'radius' => $request->input('radius'),
            'teaching_mode' => $request->input('teaching_mode'),
            'online' => $request->input('online'),
            'verified' => $request->input('verified'),
            'gender' => $request->input('gender'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'price_range' => $request->input('price_range'),
            'experience' => $request->input('experience'),
            'rating_min' => $request->input('rating_min'),
            'sort_by' => $request->input('sort_by'),
            'featured' => $request->input('featured'),
        ];

        // Handle price_range filter (convert to min/max)
        if (!empty($filters['price_range'])) {
            if (strpos($filters['price_range'], '+') !== false) {
                $filters['min_price'] = (int)str_replace('+', '', $filters['price_range']);
                $filters['max_price'] = 999999;
            } elseif (strpos($filters['price_range'], '-') !== false) {
                [$min, $max] = explode('-', $filters['price_range']);
                $filters['min_price'] = (int)$min;
                $filters['max_price'] = (int)$max;
            }
        }

        // Handle experience filter (would need to be indexed in ES)
        if (!empty($filters['experience'])) {
            // This would require additional processing
            // For now, pass it through
        }

        // Clean up empty filters
        $filters = array_filter($filters, fn($v) => !is_null($v) && $v !== '');

        $perPage = (int)$request->query('per_page', 20);
        $page = (int)$request->query('page', 1);

        try {
            // Use Elasticsearch via TutorSearchService
            $results = $this->searchService->search($filters, $perPage, $page);
            
            return response()->json([
                'data' => $results->items(),
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
            ]);
        } catch (\Exception $e) {
            // Fallback to database query if Elasticsearch fails
            return $this->fallbackSearch($request);
        }
    }

    /**
     * Get featured tutors
     * GET /api/tutors/featured
     */
    public function featured(Request $request)
    {
        $perPage = (int)$request->query('per_page', 6);
        
        // Get featured tutors - highest rated, verified tutors
        $tutors = Tutor::with('user', 'subjects')
            ->where('moderation_status', 'approved')
            ->where('verified', true)
            ->whereNotNull('rating_avg')
            ->where('rating_avg', '>=', 4.5)
            ->orderBy('rating_avg', 'desc')
            ->orderBy('rating_count', 'desc')
            ->limit($perPage)
            ->get();

        return response()->json([
            'data' => $tutors,
            'total' => $tutors->count(),
        ]);
    }

    /**
     * Search for nearby tutors within a specified radius
     * GET /api/tutors/nearby?lat=28.5355&lng=77.3910&radius=5&subject=mathematics
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|integer|min=1|max=100',
            'subject' => 'nullable|string',
            'subject_id' => 'nullable|integer',
        ]);

        $latitude = $request->input('lat');
        $longitude = $request->input('lng');
        $radius = (int)$request->input('radius', 5);

        $filters = [
            'subject' => $request->input('subject'),
            'subject_id' => $request->input('subject_id'),
            'verified' => $request->input('verified'),
            'online' => $request->input('online'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
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
     * Search tutors by location name
     * GET /api/tutors/by-location?location=delhi&subject=mathematics
     */
    public function byLocation(Request $request)
    {
        $request->validate([
            'location' => 'required|string|min:2',
        ]);

        $location = $request->input('location');

        $filters = [
            'subject' => $request->input('subject'),
            'subject_id' => $request->input('subject_id'),
            'verified' => $request->input('verified'),
            'online' => $request->input('online'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
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

    public function show($id)
    {
        $tutor = Tutor::with('user','subjects')->findOrFail($id);
        if ($tutor->moderation_status !== 'approved') {
            return response()->json(['message'=>'Tutor not available'], 404);
        }
        return response()->json($tutor);
    }

    /**
     * Public tutor profile payload with extended relations
     */
    public function publicShow($id)
    {
        $tutor = Tutor::with(['user','subjects','documents'])
            ->findOrFail($id);

        if ($tutor->moderation_status !== 'approved') {
            return response()->json(['message' => 'Tutor not available'], 404);
        }

        // Eager-loaded JSON columns are already cast on the model
        return response()->json($tutor);
    }

    public function store(Request $request)
    {
        $request->validate([
            'headline'=>'required|string',
            'about'=>'nullable|string',
            'experience_years'=>'nullable|integer',
            'price_per_hour'=>'nullable|numeric',
            'teaching_mode'=>'nullable|in:online,offline,both',
            'city'=>'nullable|string',
        ]);

        $user = $request->user();
        if (! $user->hasRole('tutor')) {
            $user->assignRole('tutor');
        }

        $tutor = $user->tutor()->updateOrCreate([], $request->only(['headline','about','experience_years','price_per_hour','teaching_mode','city']));
        $tutor->update(['moderation_status'=>'pending']);

        return response()->json($tutor, 201);
    }

    /**
     * Fallback to database search if Elasticsearch is unavailable
     */
    protected function fallbackSearch(Request $request)
    {
        $query = Tutor::with('user','subjects')->where('moderation_status','approved');

        // Featured filter
        if ($request->input('featured') === 'true') {
            $query->where('verified', true)
                  ->whereNotNull('rating_avg')
                  ->where('rating_avg', '>=', 4.5)
                  ->orderBy('rating_avg', 'desc')
                  ->orderBy('rating_count', 'desc');
        }

        // Subject search
        if ($subjectId = $request->input('subject_id')) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.id', $subjectId));
        } elseif ($subjectSearchName = $request->input('subject_search_name')) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.name', 'LIKE', '%' . $subjectSearchName . '%'));
        } elseif ($subject = $request->input('subject')) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.name', 'LIKE', '%' . $subject . '%'));
        }

        // Location search
        if ($location = $request->input('location')) {
            $query->where(function($q) use ($location) {
                $q->where('city', 'LIKE', '%' . $location . '%')
                  ->orWhere('state', 'LIKE', '%' . $location . '%')
                  ->orWhere('area', 'LIKE', '%' . $location . '%');
            });
        }

        // Basic filters
        if ($request->input('online') === 'true') {
            $query->where('online_available', true);
        }

        if ($request->input('verified') === 'true') {
            $query->where('verified', true);
        }

        if ($min_price = $request->query('min_price')) {
            $query->where('price_per_hour', '>=', $min_price);
        }
        if ($max_price = $request->query('max_price')) {
            $query->where('price_per_hour', '<=', $max_price);
        }

        $perPage = (int)$request->query('per_page', 20);
        $results = $query->paginate($perPage);

        return response()->json($results);
    }
}
