<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Helper rahasia untuk memanggil Python dari dalam PHP
     */
    private function runPythonRS($barcode)
    {
        $path = base_path('rs_calc.py');
        $command = escapeshellcmd("python \"$path\" " . escapeshellarg($barcode));
        $output = shell_exec($command);
        
        // Mengembalikan string array dari Python (hapus spasi berlebih)
        return trim($output);
    }

    /**
     * Memanggil Python untuk menghitung Reed-Solomon via AJAX (Kamera Scanner)
     */
    public function calculateRS(Request $request)
    {
        $barcode = $request->barcode;
        $output = $this->runPythonRS($barcode);
        
        return response()->json([
            'barcode' => $barcode,
            'codeword' => json_decode($output)
        ]);
    }

    /**
     * Menampilkan daftar produk beserta fitur Search
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $query = Product::with('category')->latest();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('barcode_original', 'LIKE', "%{$search}%")
                  ->orWhere('barcode_encoded', 'LIKE', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
        }

        $products = $query->paginate(10)->withQueryString();
        
        return view('products.index', compact('products'));
    }

    /**
     * Menampilkan form tambah produk
     */
    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    /**
     * Menyimpan produk baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'category'    => 'required',
            'name'        => 'required|string|max:255',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable', // Opsional
            'price'       => 'required',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Opsional
            'barcode'     => 'required|string|unique:products,barcode_original',
        ]);

        try {
            // 1. Olah data Barcode dengan memanggil PYTHON!
            $px = $request->barcode;
            $cx = $this->runPythonRS($px);

            // 2. Handle Upload Gambar (Hanya jika ada file)
            $imageName = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->extension();
                $image->storeAs('public/products', $imageName);
            }

            // 3. Bersihkan format harga
            $cleanPrice = preg_replace('/[^0-9]/', '', $request->price);

            // 4. Simpan ke Database
            Product::create([
                'category_id'      => $request->category,
                'name'             => $request->name,
                'stock'            => $request->stock,
                'description'      => $request->description, 
                'price'            => $cleanPrice,
                'image'            => $imageName,
                'barcode_original' => $px,
                'barcode_encoded'  => $cx, // Menyimpan hasil perhitungan dari Python
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Produk "' . $request->name . '" berhasil ditambahkan!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Menyimpan perubahan data (Update)
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'category'    => 'required',
            'name'        => 'required|string|max:255',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable',
            'price'       => 'required',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'barcode'     => 'required|string|unique:products,barcode_original,' . $id,
        ]);

        try {
            // 1. Handle Update Gambar
            $imageName = $product->image; // Gunakan gambar lama sebagai default
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($product->image) {
                    Storage::delete('public/products/' . $product->image);
                }
                
                $image = $request->file('image');
                $imageName = time() . '.' . $image->extension();
                $image->storeAs('public/products', $imageName);
            }

            // 2. Olah Barcode via Python (Update jika barcode diubah)
            $px = $request->barcode;
            $cx = $product->barcode_encoded;

            if ($px !== $product->barcode_original) {
                $cx = $this->runPythonRS($px);
            }

            // 3. Update data
            $product->update([
                'category_id'      => $request->category,
                'name'             => $request->name,
                'stock'            => $request->stock,
                'description'      => $request->description,
                'price'            => preg_replace('/[^0-9]/', '', $request->price),
                'image'            => $imageName,
                'barcode_original' => $px,
                'barcode_encoded'  => $cx,
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Data produk berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus data produk
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Simpan nama gambar untuk dihapus nanti
            $imageToDelete = $product->image;

            $product->delete();

            if ($imageToDelete) {
                Storage::delete('public/products/' . $imageToDelete);
            }

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil dihapus!');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->route('products.index')
                    ->with('error', 'Gagal! Produk ini sudah masuk dalam riwayat transaksi Toko Dua Putra.');
            }
            return redirect()->route('products.index')
                ->with('error', 'Kesalahan sistem: ' . $e->getMessage());
        }
    }
}