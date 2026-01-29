<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use App\Models\ImportFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Aws\S3\S3Client;

class ImportController extends Controller
{
    protected $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Show import orders page
     */
    public function showImportOrders()
    {
        $routePrefix = 'customer';
        $users = null; // Customer doesn't need user selection
        return view('admin.orders.import', compact('routePrefix', 'users'))->with('activeMenu', 'orders-import');
    }

    /**
     * Import orders (new format)
     */
    public function importOrders(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('file');
            
            // Parse file to get order data for review (before upload)
            $orderData = $this->parseOrderFile($file);
            
            // Upload file to S3
            $fileUrl = $this->uploadFileToS3($file, 'imports/orders');
            if (!$fileUrl) {
                return redirect()->back()
                    ->with('error', 'Failed to upload file to S3.')
                    ->withInput();
            }
            
            // Create import file record
            $importFile = ImportFile::create([
                'file_name' => Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension(),
                'file_path' => 'imports/orders/' . basename(parse_url($fileUrl, PHP_URL_PATH)),
                'file_url' => $fileUrl,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'total_orders' => count($orderData),
                'status' => 'pending',
                'order_data' => $orderData,
            ]);

            // Customer always uses their own user_id
            $result = $this->importService->importOrdersNewFormat($file, auth()->user()->id);

            // Update import file status
            $importFile->update([
                'status' => $result['success'] ? 'completed' : 'failed',
                'processed_orders' => $result['success_count'] ?? 0,
                'failed_orders' => $result['error_count'] ?? 0,
                'errors' => $result['errors'] ?? [],
                'processed_at' => now(),
            ]);

            if ($result['success']) {
                // Completely successful
                return redirect()->route('customer.orders.import')
                    ->with('success', $result['message']);
            } else {
                // Has errors - show error message with details
                return redirect()->route('customer.orders.import')
                    ->with('error', $result['message'])
                    ->with('import_errors', $result['errors'])
                    ->with('success_count', $result['success_count'])
                    ->with('error_count', $result['error_count'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Customer import orders failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('customer.orders.import')
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Upload file to S3 storage.
     */
    private function uploadFileToS3($file, string $folder = 'imports'): ?string
    {
        if (!$file || !$file->isValid()) {
            Log::warning('Invalid file for S3 upload');
            return null;
        }

        try {
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = "{$folder}/{$fileName}";

            $s3Config = config('filesystems.disks.s3');
            
            if (empty($s3Config['bucket']) || empty($s3Config['key']) || empty($s3Config['secret'])) {
                Log::error('S3 configuration incomplete');
                return null;
            }

            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $s3Config['region'],
                'credentials' => [
                    'key' => $s3Config['key'],
                    'secret' => $s3Config['secret'],
                ],
                'use_path_style_endpoint' => $s3Config['use_path_style_endpoint'] ?? false,
            ]);

            $result = $s3Client->putObject([
                'Bucket' => $s3Config['bucket'],
                'Key' => $filePath,
                'Body' => file_get_contents($file->getRealPath()),
                'ContentType' => $file->getMimeType(),
            ]);

            $uploaded = $result['@metadata']['statusCode'] === 200;

            if ($uploaded) {
                $exists = Storage::disk('s3')->exists($filePath);
                
                if ($exists) {
                    $bucket = $s3Config['bucket'];
                    $region = $s3Config['region'];
                    $usePathStyle = $s3Config['use_path_style_endpoint'] ?? false;
                    
                    if ($usePathStyle) {
                        $url = "https://s3.{$region}.amazonaws.com/{$bucket}/{$filePath}";
                    } else {
                        $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$filePath}";
                    }
                    
                    return $url;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('S3 upload exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Parse order file to extract order data
     */
    private function parseOrderFile($file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            if (count($rows) < 2) {
                return [];
            }

            // Get headers from first row
            $headers = array_map('trim', $rows[0]);
            
            // Parse data rows
            $orderData = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowData = [];
                
                foreach ($headers as $index => $header) {
                    $rowData[strtolower(str_replace(' ', '_', $header))] = $row[$index] ?? '';
                }
                
                if (!empty($rowData['external_id'])) {
                    $orderData[] = $rowData;
                }
            }
            
            return $orderData;
        } catch (\Exception $e) {
            Log::error('Failed to parse order file', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Download sample Excel file for orders import (new format)
     */
    public function downloadOrderImportSample()
    {
        $content = $this->getOrderImportSampleContent();
        $filename = 'orders_import_sample.xlsx';

        return response($content, 200)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get sample Excel file for orders import (new format)
     */
    protected function getOrderImportSampleContent()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'External ID',
            'Brand',
            'Channel',
            'Comment',
            'Buyer Email',
            'First Name',
            'Last Name',
            'Company',
            'Phone 1',
            'Phone 2',
            'Address 1',
            'Address 2',
            'City',
            'County',
            'Postcode',
            'Country',
            'Shipping Method',
            'Label Name',
            'Label Type',
            'Label Url',
            'Part Number',
            'Title',
            'Quantity',
            'Description',
            'Position 1',
            'Position 2',
            'Position 3',
            'Position 4',
            'Position 5',
            'Mockup Url 1',
            'Mockup Url 2',
            'Mockup Url 3',
            'Mockup Url 4',
            'Mockup Url 5',
            'Design Url 1',
            'Design Url 2',
            'Design Url 3',
            'Design Url 4',
            'Design Url 5'
        ];

        // Set headers
        foreach ($headers as $colIndex => $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($columnLetter . '1', $header);
        }

        // Sample data rows
        $sampleData = [
            [
                'SHOP-001',
                '', // Brand - empty
                '', // Channel - empty
                'First order',
                '', // Buyer Email - empty
                'John',
                'Doe',
                'Acme Inc',
                '+1234567890',
                '', // Phone 2 - empty
                '123 Main Street',
                'Apt 4B',
                'New York',
                'NY',
                '10001',
                'US',
                'tiktok_label', // Shipping Method: tiktok_label or empty
                '', // Label Name - empty
                '', // Label Type - empty
                'https://drive.google.com/file/d/xxx/view',
                'PROD-SKU-001',
                'T-Shirt Red',
                '2',
                'High quality cotton t-shirt',
                'Front', // Position: Front, Back, Left Sleeve, Right Sleeve, Hem
                'Back',
                '',
                '',
                '',
                'https://drive.google.com/file/d/mockup1/view',
                'https://drive.google.com/file/d/mockup2/view',
                '',
                '',
                '',
                'https://drive.google.com/file/d/design1/view',
                'https://drive.google.com/file/d/design2/view',
                '',
                '',
                ''
            ],
            [
                'SHOP-001',
                '', // Brand - empty
                '', // Channel - empty
                'First order',
                '', // Buyer Email - empty
                'John',
                'Doe',
                'Acme Inc',
                '+1234567890',
                '', // Phone 2 - empty
                '123 Main Street',
                'Apt 4B',
                'New York',
                'NY',
                '10001',
                'US',
                'tiktok_label', // Shipping Method: tiktok_label or empty
                '', // Label Name - empty
                '', // Label Type - empty
                'https://drive.google.com/file/d/yyy/view',
                'PROD-SKU-002',
                'Hoodie Blue',
                '1',
                'Warm hoodie',
                'Front', // Position: Front, Back, Left Sleeve, Right Sleeve, Hem
                'Left Sleeve',
                '',
                '',
                '',
                'https://drive.google.com/file/d/mockup3/view',
                '',
                '',
                '',
                '',
                'https://drive.google.com/file/d/design3/view',
                '',
                '',
                '',
                ''
            ],
            [
                'ETSY-002',
                '', // Brand - empty
                '', // Channel - empty
                'Second order',
                '', // Buyer Email - empty
                'Jane',
                'Smith',
                '',
                '+1234567891',
                '', // Phone 2 - empty
                '456 Oak Avenue',
                'Suite 5',
                'Los Angeles',
                'CA',
                '90001',
                'US',
                '', // Shipping Method: empty (ship by seller)
                '', // Label Name - empty
                '', // Label Type - empty
                '', // Label Url - empty when shipping method is empty
                'PROD-SKU-003',
                'Mug White',
                '3',
                'Ceramic mug',
                'Front', // Position: Front, Back, Left Sleeve, Right Sleeve, Hem
                'Back',
                'Right Sleeve',
                '',
                '',
                'https://example.com/mockup1.jpg',
                'https://example.com/mockup2.jpg',
                'https://example.com/mockup3.jpg',
                '',
                '',
                'https://example.com/design1.png',
                'https://example.com/design2.png',
                'https://example.com/design3.png',
                '',
                ''
            ],
            [
                'ETSY-003',
                '', // Brand - empty
                '', // Channel - empty
                'Multiple designs',
                '', // Buyer Email - empty
                'Bob',
                'Johnson',
                'Design Co',
                '+1234567892',
                '', // Phone 2 - empty
                '789 Pine Street',
                '',
                'Chicago',
                'IL',
                '60601',
                'US',
                'tiktok_label', // Shipping Method: tiktok_label or empty
                '', // Label Name - empty
                '', // Label Type - empty
                'https://drive.google.com/file/d/zzz/view',
                'PROD-SKU-004',
                'T-Shirt Custom',
                '1',
                'Custom design with 5 positions',
                'Front', // Position: Front, Back, Left Sleeve, Right Sleeve, Hem
                'Back',
                'Left Sleeve',
                'Right Sleeve',
                'Hem',
                'https://example.com/mockup1.jpg',
                'https://example.com/mockup2.jpg',
                'https://example.com/mockup3.jpg',
                'https://example.com/mockup4.jpg',
                'https://example.com/mockup5.jpg',
                'https://example.com/design1.png',
                'https://example.com/design2.png',
                'https://example.com/design3.png',
                'https://example.com/design4.png',
                'https://example.com/design5.png'
            ]
        ];

        // Add sample data rows
        $row = 2;
        foreach ($sampleData as $data) {
            // Ensure data array has exactly the same number of columns as headers
            $normalizedData = array_pad($data, count($headers), '');
            
            foreach ($normalizedData as $colIndex => $value) {
                $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($columnLetter . $row, $value);
            }
            $row++;
        }

        // Auto-size columns
        $totalColumns = count($headers);
        for ($col = 1; $col <= $totalColumns; $col++) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // Style header row
        $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F7961D']
            ]
        ]);

        $writer = new Xlsx($spreadsheet);
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        fclose($tempFile);
        $writer->save($tempPath);

        return file_get_contents($tempPath);
    }
}
