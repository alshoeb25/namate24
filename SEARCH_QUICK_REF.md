# 🚀 Quick Reference - SEO Search System

## 📋 Quick Commands

```bash
# Setup (Run once)
php artisan migrate
php artisan meilisearch:configure
php artisan scout:import "App\Models\Tutor"

# Or use setup script
bash setup-search-system.sh      # Linux/Mac
setup-search-system.bat           # Windows

# Re-index after data changes
php artisan scout:flush "App\Models\Tutor"
php artisan scout:import "App\Models\Tutor"

# Clear cache
php artisan cache:clear
```

## 🔗 URL Examples

```
/tutors                              → All tutors
/mathematics-tutors                  → Math tutors
/tutors-in-delhi                     → Delhi tutors
/mathematics-tutors-in-delhi         → Math tutors in Delhi
/physics-tutors-in-mumbai            → Physics tutors in Mumbai
```

## 📡 API Endpoints

```bash
# Search tutors
GET /api/tutors?subject=math&location=delhi

# With filters
GET /api/tutors?subject=physics&verified=1&online=1

# Get tutor by ID
GET /api/tutors/123

# Popular locations
GET /api/tutors/popular-locations

# Statistics
GET /api/tutors/statistics

# Search subjects (autocomplete)
GET /api/search-subjects?search=mat&limit=10
```

## 🎯 Query Parameters

| Parameter | Type | Example | Description |
|-----------|------|---------|-------------|
| `subject` | string | `mathematics` | Subject name |
| `location` | string | `delhi` | City/state/PIN |
| `subject_id` | int | `5` | Exact subject ID |
| `verified` | boolean | `1` | Verified only |
| `online` | boolean | `1` | Online available |
| `home` | boolean | `1` | Home tuition |
| `experience` | string | `3-5` or `5+` | Years range |
| `price_range` | string | `500-1000` | Price range |
| `sort` | string | `rating`, `price_low`, `price_high` | Sort order |
| `per_page` | int | `20` | Results per page |

## 🎨 Vue Components Usage

### HeroSearch
```vue
<HeroSearch />
```
- Handles subject autocomplete
- Generates SEO URLs
- Manages search state

### SearchResults
```vue
<SearchResults />
```
- Parses URL path
- Loads and displays tutors
- Manages filters

### TutorCard
```vue
<TutorCard :tutor="tutorData" @contact="handleContact" @favorite="handleFavorite" />
```

## 🔍 Search Logic Flow

1. **User types** → Autocomplete shows suggestions (300ms debounce)
2. **User clicks search** → URL: `/mathematics-tutors-in-delhi`
3. **Vue Router** → Loads SearchResults.vue
4. **Parse URL** → Extract: subject="mathematics", location="delhi"
5. **API Call** → GET `/api/tutors?subject=mathematics&location=delhi`
6. **Backend** → Check Redis cache (15min)
7. **If cache miss** → Query Meilisearch/MySQL
8. **Response** → Display in TutorCard components

## 💾 Cache Strategy

```php
// Search results: 15 minutes
Cache::remember("tutors:search:{hash}", 900, ...);

// Popular locations: 1 hour
Cache::remember("tutors:popular_locations", 3600, ...);

// Statistics: 1 hour
Cache::remember("tutors:statistics", 3600, ...);
```

## 🔧 Troubleshooting

### Meilisearch not working
```bash
# Check if running
curl http://localhost:7700/health

# Start Meilisearch
meilisearch --master-key=your_key

# Re-index
php artisan scout:import "App\Models\Tutor"
```

### Cache issues
```bash
php artisan cache:clear
php artisan config:clear
```

### Routes not working
```bash
php artisan route:clear
php artisan route:list | grep tutors
```

### No results showing
```bash
# Check API directly
curl http://localhost/api/tutors

# Check database
php artisan tinker
>>> App\Models\Tutor::count()
```

## 📊 Performance Tips

1. **Enable opcache** in production
2. **Use Redis** for cache (not file)
3. **Index database** (already done via migration)
4. **CDN** for static assets
5. **Queue** heavy operations
6. **Horizon** for queue monitoring

## 🔐 Security Checklist

- [x] Input sanitization (Eloquent)
- [x] XSS prevention (Vue escaping)
- [ ] Rate limiting (add to routes)
- [ ] API authentication (for protected routes)
- [ ] CORS configuration
- [ ] SQL injection prevention (Eloquent)

## 📈 Monitoring

### Key Metrics
```php
// Add to controller
Log::info('Search performed', [
    'subject' => $request->subject,
    'location' => $request->location,
    'results' => $count,
    'time' => $executionTime
]);
```

### Recommended Tools
- Laravel Telescope
- Laravel Horizon
- New Relic/Datadog
- Meilisearch Dashboard

## 🧪 Testing

```bash
# Unit tests
php artisan test

# API testing
curl -X GET "http://localhost/api/tutors?subject=math" \
  -H "Accept: application/json"

# Frontend testing (in browser)
1. Navigate to /tutors
2. Search: "Mathematics" + "Delhi"
3. Check URL: /mathematics-tutors-in-delhi
4. Apply filters
5. Check results update
```

## 📝 Code Snippets

### Add new filter to backend
```php
// In TutorSearchController.php
if ($request->filled('new_filter')) {
    $query->where('new_field', $request->input('new_filter'));
}
```

### Add new filter to frontend
```vue
<!-- In SearchResults.vue -->
<button @click="toggleFilter('newFilter')" 
        :class="filters.newFilter ? 'active' : ''">
  New Filter
</button>
```

## 🎓 Best Practices

1. **Always cache** expensive queries
2. **Use eager loading** for relationships
3. **Index database** columns used in WHERE
4. **Paginate results** (don't load all)
5. **Validate input** on both frontend and backend
6. **Log searches** for analytics
7. **Monitor performance** regularly

## 📞 Support

- **Docs**: SEARCH_SYSTEM_README.md
- **Issues**: GitHub Issues
- **Email**: dev@namate24.com

---

**Quick Links:**
- [Full Documentation](./SEARCH_SYSTEM_README.md)
- [API Routes](./routes/api.php)
- [Web Routes](./routes/web.php)
- [Controller](./app/Http/Controllers/Api/TutorSearchController.php)
