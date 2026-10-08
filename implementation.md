# Implementation Plan: Handle SXG2 Missing Mower/Collector in Mower Area

## Deskripsi Masalah
Saat melakukan proses scan di area MOWER (Id_Area = 1) dengan tipe scan `mocol`, sistem saat ini memeriksa `Model_Mower_Plan` dan `Model_Collector_Plan`. Jika kedua field tersebut kosong, maka sistem akan mengembalikan error *"Tidak ada Model Mower atau Model Collector untuk sequence ini."*.
Namun, untuk model **SXG2**, seringkali terdapat kasus di mana Mower dan/atau Collector-nya kosong pada data plan, tetapi unit tersebut tetap harus discan di area Mower. Pada kasus ini, jam standar perakitan (Hour) pada master data tractor (di database `iseki_efficiency`) justru di-assign ke nama traktornya (`Model_Name_Plan`), bukan nama mower atau collector.

## Solusi yang Diusulkan
Kita akan menyesuaikan fungsi `scanStore` di dalam file `app/Http/Controllers/Area/AreaController.php`.

### Langkah-langkah Perubahan:
1.  **Identifikasi Area Mower:** Kita bisa menggunakan `$idArea == 1`.
2.  **Identifikasi Model SXG2:** Kita akan mengecek apakah `$plan->Model_Name_Plan` mengandung kata `SXG2`.
3.  **Logika Fallback:** Pada blok `elseif ($scanType === 'mocol')`, sistem akan tetap mencoba memasukkan `Model_Mower_Plan` dan `Model_Collector_Plan` terlebih dahulu.
    *   Jika salah satu ada (misal Mower saja atau Collector saja), maka akan menggunakan model tersebut saja tanpa menambah nama traktor.
    *   Jika **KEDUANYA kosong**, barulah kita mengecek apakah Area adalah MOWER (`$idArea == 1`) dan Model adalah SXG2 (`stripos($plan->Model_Name_Plan, 'SXG2') !== false`).
    *   Jika terpenuhi, maka kita gunakan `Model_Name_Plan`. Jika tidak terpenuhi, kita kembalikan pesan error seperti biasa.

    Contoh potongan kode yang akan diubah:
    ```php
    } elseif ($scanType === 'mocol') {
        $scanTypeLabel = 'Mocol';
        
        // Logika normal: kumpulkan mower dan collector jika ada
        if (! empty($plan->Model_Mower_Plan)) {
            $modelsToScan[] = $plan->Model_Mower_Plan;
        }
        if (! empty($plan->Model_Collector_Plan)) {
            $modelsToScan[] = $plan->Model_Collector_Plan;
        }

        // Jika keduanya kosong, kita periksa apakah SXG2 di area MOWER
        if (empty($modelsToScan)) {
            $isSXG2 = stripos($plan->Model_Name_Plan, 'SXG2') !== false;
            
            if ($idArea == 1 && $isSXG2) {
                // Gunakan nama traktor (Model_Name_Plan) sebagai fallback
                $modelsToScan[] = $plan->Model_Name_Plan;
            } else {
                return redirect()->back()->with('error', 'Tidak ada Model Mower atau Model Collector untuk sequence ini.');
            }
        }
    }
    ```

## File yang Diubah
- `app/Http/Controllers/Area/AreaController.php` pada fungsi `scanStore()`
