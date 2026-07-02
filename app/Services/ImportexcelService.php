<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImportExcelService
{
    protected SupabaseService $db;

    public function __construct(SupabaseService $db)
    {
        $this->db = $db;
    }

    public function proses(UploadedFile $fail): array
    {
        $ext  = strtolower($fail->getClientOriginalExtension());
        $path = $fail->getRealPath();

        try {
            if ($ext === 'csv') {
                $rows = $this->bacaCsv($path);
            } elseif (in_array($ext, ['xlsx', 'xls'])) {
                // Cuba guna ZipArchive — kalau tak ada, convert ke CSV dulu
                if (!class_exists('ZipArchive')) {
                    return [
                        'status' => 'error',
                        'mesej'  => 'Sila simpan fail Excel anda sebagai .csv dahulu (dalam Excel: File → Save As → CSV UTF-8), kemudian muat naik semula.',
                    ];
                }
                $rows = $this->bacaXlsx($path);
            } else {
                return ['status' => 'error', 'mesej' => 'Format tidak disokong. Sila guna .csv'];
            }

            if (empty($rows)) {
                return ['status' => 'error', 'mesej' => 'Fail kosong atau format tidak betul.'];
            }

            return $this->insertPelajar($rows);

        } catch (\Throwable $e) {
            Log::error('Import error: ' . $e->getMessage());
            return ['status' => 'error', 'mesej' => 'Ralat: ' . $e->getMessage()];
        }
    }

    private function bacaCsv(string $path): array
    {
        $rows   = [];
        $header = null;

        if (($f = fopen($path, 'r')) === false) {
            throw new \Exception('Tidak dapat membuka fail CSV.');
        }

        // Strip BOM
        $bom = fread($f, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($f);

        while (($line = fgetcsv($f, 0, ',')) !== false) {
            if ($header === null) {
                $header = array_map('trim', $line);
                continue;
            }
            if (count($line) < 2) continue;
            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }

        fclose($f);
        return $rows;
    }

    private function bacaXlsx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \Exception('Tidak dapat membuka fail XLSX.');
        }

        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ss = new \SimpleXMLElement($ssXml);
            foreach ($ss->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string) $si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $r) $text .= (string) $r->t;
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) throw new \Exception('Tiada data dalam sheet pertama.');

        $sheet  = new \SimpleXMLElement($sheetXml);
        $rows   = [];
        $header = null;

        foreach ($sheet->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $type  = (string) ($cell['t'] ?? '');
                $value = (string) ($cell->v ?? '');
                if ($type === 's') $value = $sharedStrings[(int)$value] ?? '';
                $rowData[] = trim($value);
            }
            if (empty(array_filter($rowData))) continue;
            if ($header === null) { $header = $rowData; continue; }
            $rows[] = array_combine($header, array_pad($rowData, count($header), null));
        }

        return $rows;
    }

    private function insertPelajar(array $rows): array
    {
        $berjaya = 0;
        $gagal   = 0;

        $headerMap = [
            'Nama Penuh'          => 'nama_pelajar',
            'Nama'                => 'nama_pelajar',
            'nama_pelajar'        => 'nama_pelajar',
            'No. Matrik'          => 'no_matrik',
            'No Matrik'           => 'no_matrik',
            'no_matrik'           => 'no_matrik',
            'Program'             => 'program',
            'program'             => 'program',
            'Fakulti'             => 'fakulti',
            'fakulti'             => 'fakulti',
            'Semester Semasa'     => 'semester',
            'semester'            => 'semester',
            'Status Pengajian'    => 'status_pengajian',
            'status_pengajian'    => 'status_pengajian',
            'Tarikh Tamat Tajaan' => 'tarikh_tamat_tajaan',
            'tarikh_tamat_tajaan' => 'tarikh_tamat_tajaan',
            'No. Telefon'         => null,
            'No Telefon'          => null,
            'Email'               => null,
        ];

        foreach ($rows as $row) {
            $payload = [];
            foreach ($row as $key => $val) {
                $dbKey = $headerMap[trim($key)] ?? null;
                if ($dbKey && !empty(trim($val ?? ''))) {
                    $payload[$dbKey] = trim($val);
                }
            }

            if (empty($payload['nama_pelajar'])) { $gagal++; continue; }

            $payload['status_pengajian'] = $payload['status_pengajian'] ?? 'Aktif';

            if (!empty($payload['tarikh_tamat_tajaan'])) {
                $ts = strtotime($payload['tarikh_tamat_tajaan']);
                if ($ts) {
                    $payload['tarikh_tamat_tajaan'] = date('Y-m-d', $ts);
                } else {
                    unset($payload['tarikh_tamat_tajaan']);
                }
            }

            $result = $this->db->insert('pelajar', $payload);
            $result ? $berjaya++ : $gagal++;
        }

        return ['status' => 'ok', 'berjaya' => $berjaya, 'gagal' => $gagal];
    }

    public function getHistory(): array
    {
        return $this->db->select('pelajar', [
            'select' => 'id_pelajar,nama_pelajar,no_matrik,semester,status_pengajian',
            'order'  => 'id_pelajar.desc',
            'limit'  => 10,
        ]) ?? [];
    }
}
