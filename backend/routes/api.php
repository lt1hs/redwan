<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AllUsersController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Admin\PassportController;
use App\Http\Controllers\Admin\ContractController; // Already imported
use App\Http\Controllers\Admin\CardController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\SmsController;
use App\Http\Controllers\SpeechController;

// PUBLIC ROUTES - NO MIDDLEWARE
Route::get('unfinished-passports-public', [\App\Http\Controllers\UnfinishedPassportController::class, 'index']);
Route::post('unfinished-passports-public', function(\Illuminate\Http\Request $request) {
    $data = $request->except(['personal_photo', 'passport_photo', 'residence_photo', 'passport_extension_photo']);
    
    // Handle file uploads
    $photoFields = ['personal_photo', 'passport_photo', 'residence_photo', 'passport_extension_photo'];
    foreach ($photoFields as $field) {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/uploads'), $filename);
            $data[$field] = '/storage/uploads/' . $filename;
        }
    }
    
    $passport = \App\Models\UnfinishedPassport::create($data);
    return $passport;
});
Route::get('unfinished-passports-public/{id}', function($id) {
    return \App\Models\UnfinishedPassport::findOrFail($id);
});
Route::post('unfinished-passports-public/{id}', function(\Illuminate\Http\Request $request, $id) {
    $passport = \App\Models\UnfinishedPassport::findOrFail($id);
    $data = $request->except(['personal_photo', 'passport_photo', 'residence_photo', 'passport_extension_photo']);
    
    // Handle file uploads
    $photoFields = ['personal_photo', 'passport_photo', 'residence_photo', 'passport_extension_photo'];
    foreach ($photoFields as $field) {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/uploads'), $filename);
            $data[$field] = '/storage/uploads/' . $filename;
        }
    }
    
    $passport->update($data);
    return $passport;
});
Route::delete('unfinished-passports-public/{id}', function($id) {
    $passport = \App\Models\UnfinishedPassport::findOrFail($id);
    $passport->delete();
    return response()->json(['message' => 'Deleted successfully']);
});

// OPTIONS route for CORS preflight
Route::options('unfinished-passports-import', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
});

Route::post('unfinished-passports-import', function(\Illuminate\Http\Request $request) {
    // Add CORS headers
    $response = null;
    
    try {
        if (!$request->hasFile('file')) {
            $response = response()->json(['error' => 'No file uploaded'], 400);
        } else {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            
            if ($extension === 'csv') {
                $data = array_map('str_getcsv', file($file->path()));
            } else {
                $response = response()->json(['error' => 'Only CSV files supported for now'], 422);
            }
            
            if (!$response) {
                // Skip header row
                if (!empty($data)) {
                    array_shift($data);
                }
                
                $imported = 0;
                foreach ($data as $row) {
                    if (empty($row) || empty($row[2])) continue; // Skip empty rows (check full name)
                    
                    // Debug: log the row to see the structure
                    \Log::info('Importing row: ' . json_encode($row));
                    
                    \App\Models\UnfinishedPassport::create([
                        'full_name' => $row[2] ?? null, // الاسم الكامل
                        'passport_number' => $row[3] ?? null, // رقم الجواز
                        'nationality' => $row[4] ?? null, // الجنسية
                        'date_of_birth' => isset($row[5]) && $row[5] ? date('Y-m-d', strtotime($row[5])) : null,
                        'mobile_number' => $row[6] ?? null, // رقم الهاتف
                        'residence_expiry_date' => isset($row[7]) && $row[7] ? date('Y-m-d', strtotime($row[7])) : null,
                        'zipcode' => $row[8] ?? null, // كد ناجا - this should be column 8
                        'address' => trim(($row[9] ?? '') . ' - ' . ($row[10] ?? ''), ' -'), // المحافظة + العنوان
                        'notes' => 'التسلسل: ' . ($row[0] ?? '') . ', سیده: ' . ($row[1] ?? ''),
                        'completion_status' => 'مسودة'
                    ]);
                    $imported++;
                }

                $response = response()->json(['message' => "تم استيراد {$imported} جواز بنجاح"]);
            }
        }
    } catch (\Exception $e) {
        $response = response()->json(['error' => 'خطأ في استيراد الملف: ' . $e->getMessage()], 422);
    }
    
    // Add CORS headers
    return $response->header('Access-Control-Allow-Origin', '*')
                   ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                   ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
});

// Add a root route for API health check
Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

// Explicitly define Sanctum CSRF cookie route under /api prefix
Route::get('/sanctum/csrf-cookie', function (Illuminate\Http\Request $request) {
    return response('')->withCookie(cookie('XSRF-TOKEN', $request->session()->token(), config('sanctum.expiration')));
});

// Debug route for testing
Route::post('/test', function (Request $request) {
    return response()->json([
        'message' => 'Test route working',
        'received_data' => $request->all(),
        'headers' => collect($request->headers->all())->map(function($item) {
            return is_array($item) ? $item[0] : $item;
        })->toArray(),
        'cors' => [
            'origin' => request()->header('Origin'),
            'allowed_origins' => config('cors.allowed_origins'),
            'app_url' => config('app.url'),
            'session_domain' => config('session.domain'),
            'sanctum_stateful' => config('sanctum.stateful'),
        ],
        'auth' => auth()->check() ? 'Authenticated' : 'Not authenticated'
    ]);
});

// Debug route for testing token auth
Route::get('/auth-test', function (Request $request) {
    if ($request->user()) {
        return response()->json([
            'message' => 'You are authenticated!',
            'user' => $request->user()
        ]);
    } else {
        return response()->json([
            'message' => 'Not authenticated'
        ], 401);
    }
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    if (!$user->hasRole('Super-Admin')) {
        $permissions = $user->getAllPermissions();
    } else {
        $permissions = collect();
    }

    return [
        ...$user->toArray(),
        "permissions" => $permissions->pluck('name'),
        "roles" => $user->roles->pluck('name'),
    ];
});

/* Public API */
Route::get('landing', [PublicApiController::class, 'landing']);
Route::get('general', [PublicApiController::class, 'general']);

// Passport API routes for frontend with custom names to avoid conflicts
Route::apiResource('passports', 'App\Http\Controllers\Api\PassportController')
    ->names([
        'index' => 'api.passports.index',
        'store' => 'api.passports.store',
        'show' => 'api.passports.show',
        'update' => 'api.passports.update',
        'destroy' => 'api.passports.destroy',
    ]);

Route::get('search', function (Request $request) {
    $request->validate([
        's' => ['required', 'string', 'max:255'],
    ]);

    return PostResource::collection(Post::search(Post::normalizeString($request->s))->query(fn(Builder $query) => $query->with(['categories:id,name,slug', 'tags'])->select(['id', 'title', 'slug', 'description', 'content', 'date']))->get());
});

/* Public API */

/* Pages */

Route::get('pages/{slug}', [PublicApiController::class, 'page']);
// Note: You have 'Add this with your other admin routes' here, but it's a public route.

/* Posts */

Route::get('posts', [PublicApiController::class, 'posts']);
Route::get('posts/categories', [PublicApiController::class, 'postsCategories']);
Route::get('posts/categories-with-content', [PublicApiController::class, 'postsCategoriesWithContent']);
Route::get('posts/get-by-categories/{ids}', [PublicApiController::class, 'postsGetByCategories']);
Route::get('posts/get-by-ids/{ids}', [PublicApiController::class, 'postsGetByIds']);
Route::get('posts/categories/{slug}', [PublicApiController::class, 'categoryPosts']);
Route::get('posts/{slug}', [PublicApiController::class, 'post']);

/* Videos */

Route::get('videos', [PublicApiController::class, 'videos']);
Route::get('videos/categories', [PublicApiController::class, 'videosCategories']);
Route::get('videos/categories-with-content', [PublicApiController::class, 'videosCategoriesWithContent']);
Route::get('videos/get-by-ids/{ids}', [PublicApiController::class, 'videosGetByIds']);
Route::get('videos/categories/{slug}', [PublicApiController::class, 'categoryVideos']);
Route::get('videos/{slug}', [PublicApiController::class, 'video']);


/* Tags */

Route::get('tags/{slug}', [PublicApiController::class, 'tag']);


// Temporary public routes for unfinished passports
Route::get('admin/unfinished-passports', [\App\Http\Controllers\UnfinishedPassportController::class, 'index']);
Route::post('admin/unfinished-passports/import/excel', [\App\Http\Controllers\UnfinishedPassportController::class, 'importExcel']);

// Simple test route
Route::get('test', function() {
    return response()->json(['message' => 'Test route works']);
});

// Simple POST test route
Route::post('test-post', function() {
    return response()->json(['message' => 'POST test works']);
});

// Simple file upload test
Route::post('test-upload', function(\Illuminate\Http\Request $request) {
    if ($request->hasFile('file')) {
        return response()->json(['message' => 'File received: ' . $request->file('file')->getClientOriginalName()]);
    }
    return response()->json(['message' => 'No file received']);
});

// Simple test import route
Route::post('import-test', [\App\Http\Controllers\UnfinishedPassportController::class, 'importExcel']);

/* Admin Routes */

// Public import route for testing
Route::post('admin/unfinished-passports/import/excel', [\App\Http\Controllers\UnfinishedPassportController::class, 'importExcel']);

Route::prefix('admin')
    ->middleware(['auth:sanctum'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        // User Management Routes
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
        Route::get('all-users', [AllUsersController::class, 'index']);
        
        // Role Management Routes  
        Route::apiResource('roles', RoleController::class);
        Route::get('roles/permissions/all', [RoleController::class, 'permissions']);
        
        Route::apiResource('permissions', PermissionController::class);

        // --- CONTRACTS ROUTE FIX ---
        // REMOVED: Your explicit Route::options and Route::post for contracts
        // REPLACED WITH: apiResource, which covers all CRUD methods (GET, POST, PUT/PATCH, DELETE, OPTIONS)
        Route::apiResource('contracts', ContractController::class);
        // --- END CONTRACTS ROUTE FIX ---

        Route::apiResource('pages', PageController::class);
        Route::apiResource('sliders', SliderController::class);
        Route::apiResource('navigations', NavigationController::class);
        Route::post('{type}/categories/set-order', [CategoryController::class, 'setOrder'])->whereIn('type', ['posts', 'videos']);
        Route::apiResource('{type}/categories', CategoryController::class)->whereIn('type', ['posts', 'videos']);
        Route::apiResource('posts', PostController::class);
        Route::apiResource('videos', VideoController::class);
        Route::apiResource('notifications', NotificationController::class);
        Route::apiResource('passports', PassportController::class);
        Route::post('passports/find-by-code', [PassportController::class, 'findByCode']);
        Route::get('passports/photos/{filename}', [PassportController::class, 'showPhoto'])->name('passports.showPhoto');
        
        // Unfinished Passports Routes
        Route::post('unfinished-passports/import/excel', [\App\Http\Controllers\UnfinishedPassportController::class, 'importExcel']);
        Route::post('unfinished-passports/create-folders', [\App\Http\Controllers\UnfinishedPassportController::class, 'createUserFolders']);
        Route::get('unfinished-passports/{userId}/files', [\App\Http\Controllers\UnfinishedPassportController::class, 'getUserFiles']);
        Route::post('unfinished-passports/{userId}/upload-images', [\App\Http\Controllers\UnfinishedPassportController::class, 'uploadImages']);
        Route::post('unfinished-passports/{userId}/change-image-type', [\App\Http\Controllers\UnfinishedPassportController::class, 'changeImageType']);
        Route::delete('unfinished-passports/{userId}/delete-image', [\App\Http\Controllers\UnfinishedPassportController::class, 'deleteImage']);
        Route::apiResource('unfinished-passports', \App\Http\Controllers\UnfinishedPassportController::class);
        Route::post('unfinished-passports/{unfinishedPassport}/convert', [\App\Http\Controllers\UnfinishedPassportController::class, 'convertToPassport']);

        // Activity Logs
        Route::get('activity-logs', [ActivityLogController::class, 'index']);
        Route::get('activity-logs/users', [ActivityLogController::class, 'users']);
        Route::get('activity-logs/export', [ActivityLogController::class, 'export']);
        Route::post('activity-logs', [ActivityLogController::class, 'store']);

        Route::get('/options', [OptionController::class, 'index']);
        Route::post('/options/update', [OptionController::class, 'update']);

        // Card Routes
        Route::get('cards/search', [CardController::class, 'search']);
        Route::get('cards/all', [CardController::class, 'getAllCards']);
        Route::apiResource('cards', CardController::class);
        Route::get('cards/photos/{filename}', [CardController::class, 'showPhoto'])->name('cards.showPhoto');
        Route::get('cards/{card}/family', [CardController::class, 'getFamilyCards']);
        Route::post('cards/{card}/family', [CardController::class, 'addFamilyMember']);
    });

// Temporary public route for testing import
Route::post('admin/unfinished-passports/import/excel', [\App\Http\Controllers\UnfinishedPassportController::class, 'importExcel']);

// SMS Management Routes
Route::prefix('sms')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Core SMS operations
        Route::post('/send', [SmsController::class, 'send']);
        Route::get('/logs', [SmsController::class, 'logs']);
        Route::get('/logs/recent', [SmsController::class, 'recentLogs']);
        Route::post('/retry/{id}', [SmsController::class, 'retry']);
        Route::delete('/logs/{id}', [SmsController::class, 'delete']);
        Route::get('/statistics', [SmsController::class, 'statistics']);
        Route::get('/credit', [SmsController::class, 'credit']);

        // MeliPayamak specific endpoints
        Route::get('/status/{messageId}', [SmsController::class, 'checkStatus']);
        Route::get('/messages', [SmsController::class, 'getMessages']);
    });

// Auth Routes with CSRF protection disabled
Route::group([], function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum')->name('api.me');
});

// Profile Routes - without CSRF middleware
Route::options('/profile/update', function() {
    return response('', 200);
});
Route::options('/profile/update-password', function() {
    return response('', 200);
});

Route::middleware(['auth:sanctum'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::post('/profile/update', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
        Route::post('/profile/update-password', [\App\Http\Controllers\Api\ProfileController::class, 'updatePassword']);
    });

// Add a debug route for testing file uploads
Route::post('/test-form-upload', function (Request $request) {
    logger()->info('Test form upload request', [
        'all' => $request->all(),
        'has_files' => $request->hasFile('personal_photo'),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Test form upload endpoint',
        'request_data' => $request->all(),
        'has_files' => [
            'personal_photo' => $request->hasFile('personal_photo'),
            'passport_photo' => $request->hasFile('passport_photo'),
        ],
        'headers' => collect($request->headers->all())->map(function($item) {
            return is_array($item) ? $item[0] : $item;
        })->toArray(),
        'method' => $request->method(),
        'content_type' => $request->header('Content-Type'),
    ]);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// SPEECH ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('speech-templates', [SpeechController::class, 'getTemplates']);
    Route::apiResource('speeches', SpeechController::class);
    Route::post('speeches/{speech}/duplicate', [SpeechController::class, 'duplicate']);
});
