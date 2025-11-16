<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->when($request->user, function ($query) use ($request) {
                $query->where('user_id', $request->user);
            })
            ->when($request->action, function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->module, function ($query) use ($request) {
                $query->where('module', $request->module);
            })
            ->when($request->dateRange, function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    $request->dateRange['from'] . ' 00:00:00',
                    $request->dateRange['to'] . ' 23:59:59'
                ]);
            })
            ->latest();

        return response()->json([
            'data' => $query->paginate($request->per_page ?? 10),
        ]);
    }

    public function users()
    {
        return response()->json([
            'data' => User::select('id', 'name')->get()
        ]);
    }

    public function export(Request $request)
    {
        $activities = ActivityLog::with('user')
            ->when($request->user, function ($query) use ($request) {
                $query->where('user_id', $request->user);
            })
            ->when($request->action, function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->module, function ($query) use ($request) {
                $query->where('module', $request->module);
            })
            ->when($request->dateRange, function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    $request->dateRange['from'] . ' 00:00:00',
                    $request->dateRange['to'] . ' 23:59:59'
                ]);
            })
            ->latest()
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'التاريخ والوقت');
        $sheet->setCellValue('B1', 'المستخدم');
        $sheet->setCellValue('C1', 'الإجراء');
        $sheet->setCellValue('D1', 'الوحدة');
        $sheet->setCellValue('E1', 'الوصف');

        // Set data
        $row = 2;
        foreach ($activities as $activity) {
            $sheet->setCellValue('A' . $row, $activity->created_at);
            $sheet->setCellValue('B' . $row, $activity->user?->name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $activity->action);
            $sheet->setCellValue('D' . $row, $activity->module);
            $sheet->setCellValue('E' . $row, $activity->description);
            $row++;
        }

        // Create the Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'activity-log-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
        $path = storage_path('app/public/' . $filename);
        $writer->save($path);

        return Response::download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}