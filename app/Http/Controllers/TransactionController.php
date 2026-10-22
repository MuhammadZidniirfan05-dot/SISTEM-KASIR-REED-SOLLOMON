<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $pagination = 10;
        $search = $request->search;
        $query = Transaction::with(['customer', 'details.product']);

        if ($search) {
            $query->where('invoice_number', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'LIKE', '%' . $search . '%');
                  });
        }

        $transactions = $query->latest()->paginate($pagination)->withQueryString();
        return view('transactions.index', compact('transactions'))
            ->with('i', ($request->input('page', 1) - 1) * $pagination);
    }

    /**
     * METHOD CREATE - TAMPILKAN FORM TRANSAKSI BARU
     */
    public function create(): View
    {
        $customers = Customer::get(['id', 'name']);
        return view('transactions.create', compact('customers'));
    }

    public function searchProduct(Request $request)
    {
        $search = $request->search;
        $products = Product::where('barcode_original', 'LIKE', "%$search%")
            ->orWhere('name', 'LIKE', "%$search%")
            ->orWhere('barcode_encoded', 'LIKE', "%$search%")
            ->get(['id', 'name', 'price', 'barcode_original', 'barcode_encoded', 'stock']); 

        return response()->json($products);
    }

    /**
     * REED-SOLOMON DECODE DENGAN PYTHON & PHP FALLBACK
     * Support: Scanner barcode rusak (goresan fisik)
     *
     * VERSI RESTRUKTURISASI (untuk white-box testing):
     * - Menggunakan variabel $status/$message/$responseData
     * - Semua percabangan berbentuk IF-ELSE bersarang (bukan early return)
     * - Hanya ada SATU titik keluar (return) di akhir fungsi
     */
    public function decodeRS(Request $request)
    {
        // Node 1: Start & inisialisasi variabel
        $status       = null;
        $message      = null;
        $responseData = [];

        // Node 2: Ambil dan bersihkan input barcode
        $scanned_text = trim($request->barcode);
        $scanned_text = preg_replace('/[^0-9]/', '', $scanned_text);

        Log::info('=== RS DECODE REQUEST ===', ['barcode' => $scanned_text]);

        // Node 3: Validasi barcode kosong
        if (empty($scanned_text)) {
            $status  = "empty";
            $message = "Barcode kosong.";
        } else {
            $len = strlen($scanned_text);

            // Node 4: Validasi minimal 4 digit (untuk barcode rusak)
            if ($len < 4) {
                $status  = "too_short";
                $message = "Barcode terlalu pendek untuk dipulihkan.";
                $responseData['length']   = $len;
                $responseData['required'] = 4;
            } else {
                // Node 5: Cek barcode normal (exact match)
                $exactProduct = Product::where('barcode_original', $scanned_text)->first();

                if ($exactProduct) {
                    Log::info('✅ Exact match found', ['barcode' => $scanned_text]);

                    $status  = "success";
                    $message = "Produk ditemukan (normal).";
                    $responseData = array_merge($responseData, [
                        "barcode" => $exactProduct->barcode_original,
                        "product" => [
                            'id'    => $exactProduct->id,
                            'name'  => $exactProduct->name,
                            'price' => $exactProduct->price,
                            'stock' => $exactProduct->stock,
                        ],
                        "error_corrected" => 0,
                        "confidence"      => 100,
                        "format"          => $this->detectFormat($scanned_text),
                    ]);
                } else {
                    // Node 6: Cek barcode encoded
                    $encodedProduct = Product::where('barcode_encoded', $scanned_text)->first();

                    if ($encodedProduct) {
                        $status  = "success";
                        $message = "Produk ditemukan (encoded match).";
                        $responseData = array_merge($responseData, [
                            "barcode" => $encodedProduct->barcode_original,
                            "product" => [
                                'id'    => $encodedProduct->id,
                                'name'  => $encodedProduct->name,
                                'price' => $encodedProduct->price,
                                'stock' => $encodedProduct->stock,
                            ],
                            "error_corrected" => 0,
                            "confidence"      => 95,
                            "format"          => "Barcode Encoded",
                        ]);
                    } else {
                        // Node 7: Panggil proses Reed-Solomon (Python)
                        Log::info('🔍 Running Reed-Solomon Python...', ['scanned' => $scanned_text]);
                        $pythonResult = $this->callPythonRS($scanned_text);
                        $rsProduct    = null;

                        // Node 8: Validasi hasil RS & cari produk terkait
                        if ($pythonResult && isset($pythonResult['barcode'])) {
                            $rsProduct = Product::where('barcode_original', $pythonResult['barcode'])->first();
                        }

                        // Node 9: Cek apakah hasil RS ketemu produknya
                        if ($rsProduct) {
                            Log::info('✅ RS Python success', [
                                'barcode'          => $rsProduct->barcode_original,
                                'errors_corrected' => $pythonResult['error_corrected'],
                            ]);

                            $status  = "success";
                            $message = $pythonResult['message'];
                            $responseData = array_merge($responseData, [
                                "barcode" => $rsProduct->barcode_original,
                                "product" => [
                                    'id'    => $rsProduct->id,
                                    'name'  => $rsProduct->name,
                                    'price' => $rsProduct->price,
                                    'stock' => $rsProduct->stock,
                                ],
                                "error_corrected" => $pythonResult['error_corrected'],
                                "confidence"      => $pythonResult['confidence'],
                                "format"          => $pythonResult['format'],
                                "original"        => $scanned_text,
                            ]);
                        } else {
                            // Node 10: Fallback - cocokkan dari database (Levenshtein/similar_text)
                            Log::info('🔍 Trying database fallback...', ['scanned' => $scanned_text]);
                            $matchedProduct = $this->matchFromDatabase($scanned_text);

                            if ($matchedProduct) {
                                Log::info('✅ Database fallback success', [
                                    'product' => $matchedProduct->barcode_original,
                                ]);

                                $status  = "success";
                                $message = "Produk ditemukan melalui pencocokan algoritma sistem.";
                                $responseData = array_merge($responseData, [
                                    "barcode" => $matchedProduct->barcode_original,
                                    "product" => [
                                        'id'    => $matchedProduct->id,
                                        'name'  => $matchedProduct->name,
                                        'price' => $matchedProduct->price,
                                        'stock' => $matchedProduct->stock,
                                    ],
                                    "error_corrected" => 1,
                                    "confidence"      => 75,
                                    "format"          => "Database Match",
                                ]);
                            } else {
                                // Node 11: Barcode tidak dikenali sama sekali
                                Log::warning('❌ Barcode not found', ['barcode' => $scanned_text]);

                                $status  = "not_found";
                                $message = "Produk tidak dapat dipulihkan atau tidak terdaftar.";
                                $responseData['barcode'] = $scanned_text;
                                $responseData['length']  = $len;
                            }
                        }
                    }
                }
            }
        }

        // Node 12: Gabungkan status + message + data tambahan
        $finalResponse = array_merge(
            ["status" => $status, "message" => $message],
            $responseData
        );

        // Node 13: Terminasi fungsi - satu-satunya return di seluruh fungsi
        return response()->json($finalResponse);
    }

    /**
     * PANGGIL PYTHON SCRIPT UNTUK REED-SOLOMON
     * Terhubung ke rs_decode.py yang sudah disederhanakan
     */
    private function callPythonRS($scanned_text)
    {
        $path = base_path('rs_decode.py');
        
        if (!file_exists($path)) {
            Log::error('rs_decode.py tidak ditemukan');
            return null;
        }

        // Hanya cari barang yang sudah melewati proses Encode (punya Paritas)
        $products = Product::whereNotNull('barcode_encoded')->get();
        
        if ($products->isEmpty()) {
            return null;
        }

        $len = strlen($scanned_text);
        
        // Filter awal untuk efisiensi komputasi
        $filteredProducts = $products->filter(function ($p) use ($len) {
            $originalLen = strlen($p->barcode_original);
            return $originalLen >= 8 && abs($originalLen - $len) <= 4;
        });

        if ($filteredProducts->isEmpty()) {
            $filteredProducts = $products;
        }

        // Lakukan pengujian ke skrip Python
        foreach ($filteredProducts as $product) {
            try {
                $escapedScanned = escapeshellarg($scanned_text);
                $escapedEncoded = escapeshellarg($product->barcode_encoded);
                
                $command = 'python "' . $path . '" ' . $escapedScanned . ' ' . $escapedEncoded . ' 2>&1';
                $output = shell_exec($command);
                
                if (!$output) {
                    continue;
                }

                $result = json_decode(trim($output), true);

                // Jika Python berhasil memulihkan matriks tanpa Error
                if (isset($result['status']) && $result['status'] === 'success') {
                    return [
                        'barcode' => $result['barcode'],
                        'error_corrected' => $result['error_corrected'] ?? 1,
                        'confidence' => 90,
                        'format' => $this->detectFormat($result['barcode']),
                        'message' => 'Barcode dipulihkan dengan matematika Reed-Solomon'
                    ];
                }

            } catch (\Exception $e) {
                Log::error('Error calling Python RS:', ['error' => $e->getMessage()]);
                continue;
            }
        }

        return null;
    }

    private function detectFormat($barcode)
    {
        $len = strlen($barcode);
        if ($len == 13) return 'EAN-13';
        if ($len == 12) return 'UPC-A';
        if ($len == 8) return 'EAN-8';
        if ($len >= 14) return 'EAN-14 (GTIN)';
        if ($len == 6) return 'UPC-E';
        return 'Unknown';
    }

    /**
     * ALGORITMA FUZZY MATCHING (LEVENSHTEIN & SIMILAR TEXT)
     * Dieksekusi langsung oleh PHP Laravel
     */
    private function matchFromDatabase($input)
    {
        $products = Product::all();
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($products as $product) {
            $original = $product->barcode_original;
            
            // 1. Metode PHP Levenshtein
            $levDistance = levenshtein($input, $original);
            $maxLen = max(strlen($input), strlen($original));
            $levScore = $maxLen > 0 ? (($maxLen - $levDistance) / $maxLen) * 100 : 0;
            
            // 2. Metode PHP Similar Text
            similar_text($input, $original, $simScore);
            
            // Ambil probabilitas tertinggi
            $finalScore = max($levScore, $simScore);
            
            // Evaluasi ambang batas kelayakan (Threshold > 60%)
            if ($finalScore > $bestScore && $finalScore > 60) {
                $bestScore = $finalScore;
                $bestMatch = $product;
            }
        }
        
        Log::info('Database match fallback result', [
            'input' => $input,
            'match_score' => round($bestScore, 2),
            'matched' => $bestMatch ? $bestMatch->barcode_original : 'None'
        ]);
        
        return $bestMatch;
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'date'        => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'total'       => 'required|numeric',
            'items'       => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'date'           => $request->date,
                'customer_id'    => $request->customer_id,
                'total'          => $request->total,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                $transaction->details()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'price'      => $product->price,
                    'subtotal'   => $product->price * $item['qty'],
                ]);

                $product->decrement('stock', $item['qty']);
            }

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaksi Berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function print($id): View
    {
        $transaction = Transaction::with(['customer', 'details.product'])->findOrFail($id);
        return view('transactions.print', compact('transaction'));
    }

    public function destroy($id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $transaction = Transaction::with('details')->findOrFail($id);
            foreach ($transaction->details as $detail) {
                Product::where('id', $detail->product_id)->increment('stock', $detail->qty);
            }
            $transaction->details()->delete();
            $transaction->delete();
            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaksi dihapus & stok dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus.');
        }
    }
}