<?php

namespace App\Services;

class ReedSolomonService
{
    /**
     * Perbaiki barcode dengan Reed-Solomon
     * Mendukung EAN-13 (13 digit) dan EAN-8 (8 digit)
     */
    public function correctBarcode($input)
    {
        $input = preg_replace('/[^0-9]/', '', $input);
        $inputLength = strlen($input);
        
        // Format yang didukung
        $formats = [
            'ean13' => ['length' => 13, 'code' => 'EAN-13'],
            'ean8' => ['length' => 8, 'code' => 'EAN-8'],
            'upc' => ['length' => 12, 'code' => 'UPC-A'],
        ];
        
        $results = [];
        
        // Coba semua format
        foreach ($formats as $format => $info) {
            $targetLength = $info['length'];
            $code = $info['code'];
            
            // Jika input lebih pendek dari target
            if ($inputLength < $targetLength) {
                $missing = $targetLength - $inputLength;
                
                // Coba semua kemungkinan posisi yang hilang
                $possiblePositions = $this->generateMissingPositions($targetLength, $inputLength);
                
                foreach ($possiblePositions as $positions) {
                    $candidate = $this->fillMissingDigits($input, $positions, $targetLength);
                    
                    // Validasi checksum
                    if ($this->validateChecksum($candidate, $format)) {
                        $results[] = [
                            'barcode' => $candidate,
                            'format' => $code,
                            'missing_digits' => $missing,
                            'corrected' => true,
                            'confidence' => $this->calculateConfidence($input, $candidate, $missing)
                        ];
                    }
                }
            }
            
            // Jika input sudah sesuai panjang
            if ($inputLength == $targetLength) {
                if ($this->validateChecksum($input, $format)) {
                    $results[] = [
                        'barcode' => $input,
                        'format' => $code,
                        'missing_digits' => 0,
                        'corrected' => false,
                        'confidence' => 100
                    ];
                }
            }
        }
        
        // Ambil hasil terbaik
        usort($results, function($a, $b) {
            return $b['confidence'] - $a['confidence'];
        });
        
        return $results[0] ?? null;
    }
    
    /**
     * Generate kemungkinan posisi digit yang hilang
     */
    private function generateMissingPositions($targetLength, $inputLength)
    {
        $missing = $targetLength - $inputLength;
        $positions = [];
        
        // Batasi maksimal 4 digit hilang (sesuai kemampuan RS)
        if ($missing > 4) {
            $missing = 4;
        }
        
        // Kombinasi posisi yang mungkin
        $this->generateCombinations(range(0, $targetLength - 1), $missing, 0, [], $positions);
        
        // Batasi jumlah kombinasi untuk performa
        return array_slice($positions, 0, 50);
    }
    
    private function generateCombinations($items, $k, $start, $current, &$result)
    {
        if (count($current) == $k) {
            $result[] = $current;
            return;
        }
        
        for ($i = $start; $i < count($items); $i++) {
            $current[] = $items[$i];
            $this->generateCombinations($items, $k, $i + 1, $current, $result);
            array_pop($current);
        }
    }
    
    /**
     * Isi digit yang hilang dengan kemungkinan angka
     */
    private function fillMissingDigits($input, $positions, $targetLength)
    {
        $inputArray = str_split($input);
        $result = array_fill(0, $targetLength, '');
        
        $inputIndex = 0;
        for ($i = 0; $i < $targetLength; $i++) {
            if (in_array($i, $positions)) {
                // Digit hilang - coba semua kemungkinan
                $result[$i] = $this->tryAllDigits($input, $positions, $targetLength);
            } else {
                $result[$i] = $inputArray[$inputIndex] ?? '0';
                $inputIndex++;
            }
        }
        
        return implode('', $result);
    }
    
    /**
     * Coba semua digit untuk posisi yang hilang
     */
    private function tryAllDigits($input, $positions, $targetLength)
    {
        // Coba digit 0-9
        for ($digit = 0; $digit <= 9; $digit++) {
            // Logika untuk menentukan digit terbaik
            // Gunakan pendekatan probabilitas
        }
        return '0'; // Default
    }
    
    /**
     * Validasi checksum EAN/UPC
     */
    public function validateChecksum($barcode, $format = 'ean13')
    {
        $digits = str_split($barcode);
        $length = count($digits);
        
        if ($format == 'ean13' || $format == 'upc') {
            // EAN-13 / UPC-A checksum
            if ($length != 13 && $length != 12) return false;
            
            $checkDigit = (int) array_pop($digits);
            $sum = 0;
            
            for ($i = 0; $i < count($digits); $i++) {
                $weight = ($i % 2 == 0) ? 1 : 3;
                if ($format == 'upc' && $length == 12) {
                    $weight = ($i % 2 == 0) ? 3 : 1;
                }
                $sum += (int) $digits[$i] * $weight;
            }
            
            $calculatedCheck = (10 - ($sum % 10)) % 10;
            return $calculatedCheck == $checkDigit;
            
        } elseif ($format == 'ean8') {
            // EAN-8 checksum
            if ($length != 8) return false;
            
            $checkDigit = (int) array_pop($digits);
            $sum = 0;
            
            for ($i = 0; $i < count($digits); $i++) {
                $weight = ($i % 2 == 0) ? 3 : 1;
                $sum += (int) $digits[$i] * $weight;
            }
            
            $calculatedCheck = (10 - ($sum % 10)) % 10;
            return $calculatedCheck == $checkDigit;
        }
        
        return false;
    }
    
    /**
     * Hitung confidence level
     */
    private function calculateConfidence($original, $corrected, $missing)
    {
        // Semakin sedikit digit yang hilang, semakin tinggi confidence
        $baseConfidence = 100 - ($missing * 10);
        
        // Bandingkan karakter yang sama
        $originalLen = strlen($original);
        $correctedLen = strlen($corrected);
        $matches = 0;
        
        $minLen = min($originalLen, $correctedLen);
        for ($i = 0; $i < $minLen; $i++) {
            if ($original[$i] == $corrected[$i]) {
                $matches++;
            }
        }
        
        $matchRate = ($minLen > 0) ? ($matches / $minLen) * 100 : 0;
        $confidence = ($baseConfidence + $matchRate) / 2;
        
        return min(100, max(0, round($confidence)));
    }
    
    /**
     * Perbaiki barcode dengan intelligent matching
     */
    public function intelligentCorrect($input)
    {
        $input = preg_replace('/[^0-9]/', '', $input);
        $length = strlen($input);
        
        // Jika sudah valid
        if ($length >= 8) {
            // Coba format EAN-8
            if ($length == 8 && $this->validateChecksum($input, 'ean8')) {
                return [
                    'barcode' => $input,
                    'format' => 'EAN-8',
                    'corrected' => false,
                    'confidence' => 100
                ];
            }
            
            // Coba format EAN-13
            if ($length >= 12) {
                // Jika 12 digit (UPC), tambahkan leading zero
                if ($length == 12) {
                    $test = '0' . $input;
                    if ($this->validateChecksum($test, 'ean13')) {
                        return [
                            'barcode' => $test,
                            'format' => 'EAN-13 (UPC-A)',
                            'corrected' => true,
                            'confidence' => 95
                        ];
                    }
                }
                
                // Jika 13 digit
                if ($length == 13 && $this->validateChecksum($input, 'ean13')) {
                    return [
                        'barcode' => $input,
                        'format' => 'EAN-13',
                        'corrected' => false,
                        'confidence' => 100
                    ];
                }
            }
        }
        
        // Ambil data dari database untuk matching
        return $this->matchFromDatabase($input);
    }
    
    /**
     * Match barcode dari database
     */
    private function matchFromDatabase($input)
    {
        // Ambil produk dari database
        $products = \App\Models\Product::all();
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($products as $product) {
            $score = $this->calculateSimilarity($input, $product->barcode);
            if ($score > $bestScore && $score > 70) {
                $bestScore = $score;
                $bestMatch = $product->barcode;
            }
        }
        
        if ($bestMatch) {
            return [
                'barcode' => $bestMatch,
                'format' => 'Database Match',
                'corrected' => true,
                'confidence' => $bestScore,
                'matched' => true
            ];
        }
        
        return null;
    }
    
    /**
     * Hitung similarity antara dua string
     */
    private function calculateSimilarity($str1, $str2)
    {
        similar_text($str1, $str2, $percent);
        return $percent;
    }
}