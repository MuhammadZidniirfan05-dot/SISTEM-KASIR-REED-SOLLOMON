import sys
from reedsolo import RSCodec

def encode_with_library(barcode_text):
    # Untuk memperbaiki 4 kesalahan, kita butuh 8 parity bytes (nsym = 8)
    nsym = 8 
    rsc = RSCodec(nsym)
    
    # Ubah string teks menjadi format bytes (UTF-8)
    data_bytes = bytearray(barcode_text, 'utf-8')
    
    # Proses encode: menghasilkan gabungan data asli + parity bytes
    codeword = rsc.encode(data_bytes)
    
    # Ubah array angka menjadi string Hexadecimal agar lancar di-scan
    hex_codeword = "".join("{:02x}".format(x) for x in codeword)
    
    return hex_codeword

if __name__ == "__main__":
    if len(sys.argv) > 1:
        barcode_text = sys.argv[1]
        
        # Dapatkan hasil hex
        hex_output = encode_with_library(barcode_text)
        
        # Cetak output untuk ditangkap oleh Laravel
        print(hex_output)
    else:
        # Jika tidak ada argumen barcode yang dikirim dari PHP
        hex_output = ""
        print(hex_output)