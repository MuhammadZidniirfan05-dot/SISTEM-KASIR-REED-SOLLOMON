import sys
import json
from reedsolo import RSCodec, ReedSolomonError

def decode_only_rs():
    # Butuh 2 argumen: [Hasil Scan] dan [Encoded dari DB]
    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Argumen kurang"}))
        return

    scanned_text = sys.argv[1].strip()
    database_hex = sys.argv[2].strip()

    try:
        # Konfigurasi paritas 8 bytes (toleransi error 4 byte)
        rs = RSCodec(8)
        
        # Ekstraksi paritas dari basis data
        encoded_db_bytes = bytearray.fromhex(database_hex)
        parity_from_db = encoded_db_bytes[-8:]
        
        # Tempelkan paritas ke data yang cacat dari scanner
        virtual_codeword = bytearray(scanned_text.encode('utf-8')) + parity_from_db
        
        # Eksekusi algoritma perbaikan Reed-Solomon
        decoded_mes, _, err_indices = rs.decode(virtual_codeword)
        
        fixed_text = decoded_mes.decode('utf-8', errors='ignore')
        original_db_text = encoded_db_bytes[:-8].decode('utf-8', errors='ignore')

        # Kembalikan status jika berhasil
        if fixed_text == original_db_text:
            print(json.dumps({
                "status": "success",
                "barcode": original_db_text,
                "error_corrected": len(err_indices)
            }))
        else:
            print(json.dumps({"status": "failed"}))

    except ReedSolomonError:
        # Jika error melebihi batas, biarkan PHP yang mengambil alih logika selanjutnya
        print(json.dumps({"status": "failed", "message": "RS Error"}))
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))

if __name__ == "__main__":
    decode_only_rs()