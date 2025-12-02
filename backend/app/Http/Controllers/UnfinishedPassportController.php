<?php

namespace App\Http\Controllers;

use App\Models\UnfinishedPassport;
use App\Models\Passport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnfinishedPassportController extends Controller
{
    public function index(Request $request)
    {
        $query = UnfinishedPassport::orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 10);
        if ($perPage === 'all') {
            $passports = $query->get();
            // Add index to each passport
            $passports->each(function ($passport, $index) {
                $passport->index = $index + 1;
            });
            return response()->json(['data' => $passports]);
        }

        $passports = $query->paginate((int)$perPage);
        // Add index to each passport based on pagination
        $passports->getCollection()->each(function ($passport, $index) use ($passports) {
            $passport->index = (($passports->currentPage() - 1) * $passports->perPage()) + $index + 1;
        });
        return response()->json($passports);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'nullable|string',
            'nationality' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'residence_expiry_date' => 'nullable|date',
            'phone_number' => 'nullable|string',
            'mobile_number' => 'nullable|string',
            'transaction_type' => 'nullable|string',
            'address' => 'nullable|string',
            'zipcode' => 'nullable|string',
            'notes' => 'nullable|string',
            'completion_status' => 'nullable|in:مسودة,قيد المراجعة,جاهز للنقل'
        ]);

        return UnfinishedPassport::create($data);
    }

    public function show(UnfinishedPassport $unfinishedPassport)
    {
        return $unfinishedPassport;
    }

    public function update(Request $request, UnfinishedPassport $unfinishedPassport)
    {
        $data = $request->validate([
            'full_name' => 'nullable|string',
            'nationality' => 'nullable|string',
            'passport_number' => 'nullable|string',
            'passport_id' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'residence_expiry_date' => 'nullable|date',
            'phone_number' => 'nullable|string',
            'mobile_number' => 'nullable|string',
            'transaction_type' => 'nullable|string',
            'residence_authority' => 'nullable|string',
            'address' => 'nullable|string',
            'governorate' => 'nullable|string',
            'zipcode' => 'nullable|string',
            'najacode' => 'nullable|string',
            'personal_photo' => 'nullable|string',
            'passport_photo' => 'nullable|string',
            'residence_photo' => 'nullable|string',
            'passport_extension_photo' => 'nullable|string',
            'notes' => 'nullable|string',
            'completion_status' => 'nullable|in:مسودة,قيد المراجعة,جاهز للنقل'
        ]);

        $unfinishedPassport->update($data);
        return $unfinishedPassport;
    }

    public function destroy(UnfinishedPassport $unfinishedPassport)
    {
        $unfinishedPassport->delete();
        return response()->json(['message' => 'تم حذف الجواز غير المكتمل بنجاح']);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            
            if ($extension === 'csv') {
                $data = array_map('str_getcsv', file($file->path()));
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                // Simple XLSX reader using ZipArchive
                $zip = new \ZipArchive();
                if ($zip->open($file->path()) === TRUE) {
                    $sharedStrings = [];
                    $worksheetData = '';
                    
                    // Read shared strings
                    if (($sharedStringsXML = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
                        $sharedStringsDoc = new \DOMDocument();
                        $sharedStringsDoc->loadXML($sharedStringsXML);
                        $sharedStringNodes = $sharedStringsDoc->getElementsByTagName('t');
                        foreach ($sharedStringNodes as $node) {
                            $sharedStrings[] = $node->nodeValue;
                        }
                    }
                    
                    // Read worksheet data
                    $worksheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
                    $zip->close();
                    
                    if ($worksheetData) {
                        $doc = new \DOMDocument();
                        $doc->loadXML($worksheetData);
                        $rows = $doc->getElementsByTagName('row');
                        
                        $data = [];
                        foreach ($rows as $row) {
                            $cells = $row->getElementsByTagName('c');
                            $rowData = [];
                            foreach ($cells as $cell) {
                                $value = '';
                                $vNode = $cell->getElementsByTagName('v')->item(0);
                                if ($vNode) {
                                    $cellValue = $vNode->nodeValue;
                                    // Check if it's a shared string
                                    if ($cell->getAttribute('t') === 's' && isset($sharedStrings[$cellValue])) {
                                        $value = $sharedStrings[$cellValue];
                                    } else {
                                        $value = $cellValue;
                                    }
                                }
                                $rowData[] = $value;
                            }
                            $data[] = $rowData;
                        }
                    } else {
                        throw new \Exception('Could not read worksheet data');
                    }
                } else {
                    throw new \Exception('Could not open Excel file');
                }
            } else {
                throw new \Exception('Unsupported file format');
            }
            
            // Skip header row if it exists
            if (!empty($data) && count($data) > 1) {
                array_shift($data);
            }
            
            $imported = 0;
            foreach ($data as $row) {
                if (empty($row) || empty($row[2])) continue; // Skip empty rows (check full name)
                
                UnfinishedPassport::create([
                    'full_name' => $row[2] ?? null, // الاسم الكامل
                    'passport_number' => $row[3] ?? null, // رقم الجواز
                    'nationality' => $row[4] ?? null, // الجنسية
                    'date_of_birth' => isset($row[5]) && $row[5] ? $this->parseDate($row[5]) : null, // تاريخ الميلاد
                    'mobile_number' => $row[6] ?? null, // رقم الهاتف
                    'residence_expiry_date' => isset($row[7]) && $row[7] ? $this->parseDate($row[7]) : null, // تاريخ الانتهاء
                    'zipcode' => $row[8] ?? null, // كد ناجا
                    'address' => ($row[9] ?? '') . ' - ' . ($row[10] ?? ''), // المحافظة + العنوان
                    'phone_number' => null,
                    'transaction_type' => null,
                    'notes' => 'التسلسل: ' . ($row[0] ?? '') . ', سیده: ' . ($row[1] ?? ''),
                    'completion_status' => 'مسودة'
                ]);
                $imported++;
            }

            return response()->json(['message' => "تم استيراد {$imported} جواز بنجاح"]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في استيراد الملف: ' . $e->getMessage()], 422);
        }
    }

    private function parseDate($value)
    {
        if (is_numeric($value)) {
            // Excel date serial number
            $unixDate = ($value - 25569) * 86400;
            return date('Y-m-d', $unixDate);
        } else {
            // Try to parse as regular date
            return date('Y-m-d', strtotime($value));
        }
    }

    public function convertToPassport(UnfinishedPassport $unfinishedPassport)
    {
        // Validate required fields
        $requiredFields = ['full_name', 'nationality', 'passport_number', 'date_of_birth', 'residence_expiry_date', 'mobile_number', 'transaction_type'];
        
        foreach ($requiredFields as $field) {
            if (empty($unfinishedPassport->$field)) {
                return response()->json(['error' => "الحقل {$field} مطلوب لإكمال النقل"], 422);
            }
        }

        // Create passport
        $passport = Passport::create([
            'full_name' => $unfinishedPassport->full_name,
            'nationality' => $unfinishedPassport->nationality,
            'passport_number' => $unfinishedPassport->passport_number,
            'unique_code' => 'P' . str_pad(Passport::count() + 1, 6, '0', STR_PAD_LEFT),
            'date_of_birth' => $unfinishedPassport->date_of_birth,
            'residence_expiry_date' => $unfinishedPassport->residence_expiry_date,
            'phone_number' => $unfinishedPassport->phone_number,
            'mobile_number' => $unfinishedPassport->mobile_number,
            'passport_status' => 'قيد الانجاز',
            'passport_delivery_date' => now(),
            'transaction_type' => $unfinishedPassport->transaction_type,
            'payment_status' => 'لم يتم',
            'delivered_by' => auth()->user()->name ?? 'النظام',
            'address' => $unfinishedPassport->address ?? '',
            'zipcode' => $unfinishedPassport->zipcode,
            'personal_photo' => $unfinishedPassport->personal_photo,
            'passport_photo' => $unfinishedPassport->passport_photo,
        ]);

        // Delete unfinished passport
        $unfinishedPassport->delete();

        return response()->json(['message' => 'تم نقل الجواز بنجاح', 'passport' => $passport]);
    }

    public function createUserFolders()
    {
        try {
            $unfinishedPassports = UnfinishedPassport::all();
            $created = 0;
            
            foreach ($unfinishedPassports as $passport) {
                $userFolder = public_path('storage/users/' . $passport->id);
                
                if (!file_exists($userFolder)) {
                    mkdir($userFolder, 0755, true);
                    $created++;
                    
                    // Create subfolders for different image types
                    mkdir($userFolder . '/personal', 0755, true);
                    mkdir($userFolder . '/passport', 0755, true);
                    mkdir($userFolder . '/residence', 0755, true);
                    mkdir($userFolder . '/extension', 0755, true);
                }
            }
            
            return response()->json(['message' => "تم إنشاء {$created} مجلد مستخدم"]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في إنشاء المجلدات: ' . $e->getMessage()], 500);
        }
    }

    public function getUserFiles($userId)
    {
        try {
            $userFolder = public_path('storage/users/' . $userId);
            $files = [];
            
            if (file_exists($userFolder)) {
                $subfolders = ['personal', 'passport', 'residence', 'extension'];
                
                foreach ($subfolders as $subfolder) {
                    $subfolderPath = $userFolder . '/' . $subfolder;
                    if (file_exists($subfolderPath)) {
                        $folderFiles = array_diff(scandir($subfolderPath), ['.', '..']);
                        foreach ($folderFiles as $file) {
                            $files[] = [
                                'name' => $file,
                                'type' => $subfolder,
                                'url' => '/storage/users/' . $userId . '/' . $subfolder . '/' . $file,
                                'full_url' => url('/storage/users/' . $userId . '/' . $subfolder . '/' . $file)
                            ];
                        }
                    }
                }
            }
            
            return response()->json(['files' => $files]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في قراءة ملفات المستخدم: ' . $e->getMessage()], 500);
        }
    }

    public function uploadImages(Request $request, $userId)
    {
        try {
            $request->validate([
                'images.*' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120'
            ]);

            $userFolder = public_path('storage/users/' . $userId);
            
            if (!file_exists($userFolder)) {
                mkdir($userFolder, 0755, true);
                foreach (['personal', 'passport', 'residence', 'extension'] as $subfolder) {
                    mkdir($userFolder . '/' . $subfolder, 0755, true);
                }
            }

            $uploadedFiles = [];
            
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    
                    // Upload all files to personal folder by default
                    $destinationPath = $userFolder . '/personal';
                    $file->move($destinationPath, $filename);
                    
                    $uploadedFiles[] = [
                        'name' => $filename,
                        'type' => 'personal',
                        'url' => '/storage/users/' . $userId . '/personal/' . $filename
                    ];
                }
            }

            return response()->json([
                'message' => 'تم رفع ' . count($uploadedFiles) . ' ملف بنجاح',
                'files' => $uploadedFiles
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في رفع الملفات: ' . $e->getMessage()], 500);
        }
    }

    public function changeImageType(Request $request, $userId)
    {
        try {
            $request->validate([
                'filename' => 'required|string',
                'old_type' => 'required|in:personal,passport,residence,extension',
                'new_type' => 'required|in:personal,passport,residence,extension'
            ]);

            $userFolder = public_path('storage/users/' . $userId);
            $oldPath = $userFolder . '/' . $request->old_type . '/' . $request->filename;
            $newPath = $userFolder . '/' . $request->new_type . '/' . $request->filename;
            
            if (!file_exists($userFolder . '/' . $request->new_type)) {
                mkdir($userFolder . '/' . $request->new_type, 0755, true);
            }

            if (file_exists($oldPath)) {
                rename($oldPath, $newPath);
                return response()->json(['message' => 'تم تغيير نوع الصورة بنجاح']);
            } else {
                return response()->json(['error' => 'الملف غير موجود'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في تغيير نوع الصورة: ' . $e->getMessage()], 500);
        }
    }

    public function deleteImage(Request $request, $userId)
    {
        try {
            $request->validate([
                'filename' => 'required|string',
                'type' => 'required|in:personal,passport,residence,extension'
            ]);

            $userFolder = public_path('storage/users/' . $userId);
            $filePath = $userFolder . '/' . $request->type . '/' . $request->filename;

            if (file_exists($filePath)) {
                unlink($filePath);
                return response()->json(['message' => 'تم حذف الملف بنجاح']);
            } else {
                return response()->json(['error' => 'الملف غير موجود'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في حذف الملف: ' . $e->getMessage()], 500);
        }
    }
}
