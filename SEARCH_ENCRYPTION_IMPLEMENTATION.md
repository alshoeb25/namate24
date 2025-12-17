# Search Query Encryption Implementation

## Overview
Implemented secure query parameter encryption/decryption for search functionality. Search inputs are persisted after navigation and all query parameters are encrypted.

## Frontend Changes

### 1. Encryption Utility (`resources/js/utils/encryption.js`)
- **Created**: AES encryption/decryption utilities using CryptoJS
- **Functions**:
  - `encryptQueryParams()` - Encrypts query object to single encrypted string
  - `decryptQueryParams()` - Decrypts encrypted string back to query object
  - `encryptString()` / `decryptString()` - Helper functions for string encryption

### 2. HeroSearch Component (`resources/js/components/HeroSearch.vue`)
- **Updated**: Now encrypts all query parameters before navigation
- **Features**:
  - Populates search fields from route query on mount
  - Watches route changes to update fields dynamically
  - Encrypts all hidden input values (subject_id, subject_url, etc.)
  - Sends single encrypted `q` parameter instead of multiple query params
  
**Query Structure**:
```javascript
{
  subject: "Math",
  location: "Delhi",
  subject_id: "112",
  subject_url: "math",
  subject_search_id: "",
  subject_search_name: "",
  search_type: "tutors"
}
```

**Encrypted URL**:
```
/math-tutors-in-delhi?q=U2FsdGVkX1...encrypted_string...
```

### 3. SearchResults Page (`resources/js/pages/SearchResults.vue`)
- **Updated**: Decrypts query parameter on load
- **Features**:
  - Reads encrypted `q` parameter from route
  - Decrypts to get all search data
  - Sends encrypted query to API via `X-Search-Query` header
  - Backward compatible with non-encrypted queries

## Backend Changes

### 1. Decryption Middleware (`app/Http/Middleware/DecryptSearchQuery.php`)
- **Created**: Middleware to decrypt search queries from API requests
- **Features**:
  - Reads `X-Search-Query` header
  - Decrypts AES-256-CBC encrypted data (CryptoJS compatible)
  - Merges decrypted parameters into request
  - Graceful error handling with logging

**Decryption Process**:
1. Read encrypted string from header
2. Extract salt from CryptoJS format
3. Derive key and IV using EVP_BytesToKey algorithm
4. Decrypt using OpenSSL AES-256-CBC
5. Merge decrypted JSON into request parameters

### 2. Kernel Configuration (`app/Http/Kernel.php`)
- **Updated**: Added middleware to API middleware group
- **Result**: All API requests automatically decrypt search queries

## Security Features

1. **AES-256-CBC Encryption**: Industry-standard symmetric encryption
2. **Salted Encryption**: CryptoJS adds random salt to prevent pattern recognition
3. **URL Encoding**: Encrypted string is URL-safe
4. **Server-Side Validation**: Backend validates and sanitizes decrypted data
5. **Error Handling**: Failed decryption doesn't break functionality

## Secret Key Management

**Current**: Hardcoded secret key `namate24-secret-key-2024`

**Production Recommendation**:
```javascript
// Frontend: resources/js/utils/encryption.js
const SECRET_KEY = import.meta.env.VITE_ENCRYPTION_KEY;
```

```php
// Backend: app/Http/Middleware/DecryptSearchQuery.php
$secretKey = config('app.search_encryption_key');
```

Add to `.env`:
```
VITE_ENCRYPTION_KEY=your-random-32-character-key
SEARCH_ENCRYPTION_KEY=your-random-32-character-key
```

## Benefits

1. **Clean URLs**: Single encrypted parameter instead of multiple visible params
2. **Data Privacy**: Search terms not visible in URL
3. **Persistent Search**: Input fields remain filled after search
4. **SEO-Friendly Paths**: Still uses clean paths like `/math-tutors-in-delhi`
5. **Backward Compatible**: Works with both encrypted and plain queries

## Testing

### Test Encrypted Search:
1. Go to homepage
2. Type "Math" in subject field (autocomplete appears)
3. Select "Math" from dropdown
4. Type "Delhi" in location field
5. Click search button
6. **Expected**:
   - URL: `/math-tutors-in-delhi?q=U2FsdGVk...`
   - Search fields remain filled with "Math" and "Delhi"
   - API receives decrypted parameters
   - Results displayed

### Test Plain Search (Backward Compatibility):
1. Navigate to `/tutors?subject=Physics&location=Mumbai`
2. **Expected**: Works without encryption

## API Request Example

**Frontend Sends**:
```javascript
GET /api/tutors
Headers:
  X-Search-Query: U2FsdGVkX1...encrypted_data...
Params:
  subject: "Math"
  location: "Delhi"
  online: "true"
```

**Backend Receives** (after middleware):
```php
$request->input('subject'); // "Math"
$request->input('subject_id'); // "112"
$request->input('subject_url'); // "math"
$request->input('location'); // "Delhi"
$request->input('search_type'); // "tutors"
```

## Files Modified

1. ✅ `resources/js/utils/encryption.js` - Created
2. ✅ `resources/js/components/HeroSearch.vue` - Updated
3. ✅ `resources/js/pages/SearchResults.vue` - Updated
4. ✅ `app/Http/Middleware/DecryptSearchQuery.php` - Created
5. ✅ `app/Http/Kernel.php` - Updated
6. ✅ `package.json` - Added crypto-js dependency

## Dependencies

- **crypto-js**: ^4.2.0 (installed via npm)
