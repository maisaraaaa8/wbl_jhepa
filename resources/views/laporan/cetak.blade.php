<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan {{ ucfirst($jenis) }} — Protege Bitara UPSI</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Segoe UI', Arial, sans-serif; font-size:11pt; color:#1a1a1a; background:#fff; }

.header { padding:20px 30px 15px; border-bottom:3px solid #1e3a5f; display:flex; justify-content:space-between; align-items:flex-start; }
.header-left h1 { font-size:16pt; font-weight:700; color:#1e3a5f; }
.header-left p  { font-size:9pt; color:#666; margin-top:3px; }
.header-right   { text-align:right; font-size:9pt; color:#666; }
.header-right .tarikh { font-weight:600; color:#1e3a5f; font-size:10pt; }

.jenis-badge {
    display:inline-block; background:#1e3a5f; color:#fff;
    padding:3px 12px; border-radius:999px; font-size:9pt; font-weight:600;
    margin:15px 30px 10px;
}

.summary { display:flex; gap:15px; padding:10px 30px 15px; }
.sum-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 16px; flex:1; text-align:center; }
.sum-label { font-size:8pt; color:#64748b; text-transform:uppercase; letter-spacing:.04em; font-weight:600; }
.sum-value { font-size:14pt; font-weight:700; color:#1e3a5f; margin-top:2px; }

.content { padding:0 30px 20px; }

table { width:100%; border-collapse:collapse; font-size:9.5pt; }
thead th {
    background:#1e3a5f; color:#fff; padding:7px 10px;
    text-align:left; font-weight:600; font-size:8.5pt;
    text-transform:uppercase; letter-spacing:.05em;
}
tbody tr:nth-child(even) { background:#f8fafc; }
tbody tr { border-bottom:1px solid #e2e8f0; }
tbody td { padding:7px 10px; vertical-align:middle; }
tfoot td { padding:8px 10px; font-weight:700; border-top:2px solid #1e3a5f; }

.badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:8pt; font-weight:600; }
.badge.green  { background:#dcfce7; color:#15803d; }
.badge.red    { background:#fee2e2; color:#b91c1c; }
.badge.blue   { background:#eff6ff; color:#1d4ed8; }
.badge.gray   { background:#f3f4f6; color:#6b7280; }
.badge.amber  { background:#fef3c7; color:#b45309; }

.footer { margin:20px 30px 0; padding-top:10px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; font-size:8pt; color:#94a3b8; }

@media print {
    body { font-size:10pt; }
    .no-print { display:none !important; }
    @page { margin:1.5cm; size: {{ request('saiz','A4') }} {{ request('orientasi','portrait') }}; }
}

.print-btn {
    position:fixed; bottom:25px; right:25px; z-index:999;
    background:#1e3a5f; color:#fff; border:none; border-radius:12px;
    padding:12px 22px; font-size:13pt; font-weight:600; cursor:pointer;
    box-shadow:0 4px 20px rgba(0,0,0,.25); display:flex; align-items:center; gap:8px;
    transition:opacity .15s;
}
.print-btn:hover { opacity:.9; }
</style>
</head>
<body>

{{-- Print Button (hidden on print) --}}
<button class="print-btn no-print" onclick="window.print()">
    🖨️ Cetak / Simpan PDF
</button>

{{-- HEADER --}}
<div class="header">
    <div class="header-left">
        <h1>Program Protege Bitara UPSI</h1>
        <p>Laporan {{ match($jenis) {
            'pelajar'   => 'Senarai Pelajar Lengkap',
            'sumbangan' => 'Rekod Sumbangan',
            'keluarga'  => 'Senarai Keluarga Angkat',
            'meeting'   => 'Rekod Pertemuan',
            'prestasi'  => 'Prestasi Akademik Pelajar',
            default     => ucfirst($jenis),
        } }}</p>
    </div>
    <div class="header-right">
        <div class="tarikh">{{ now()->format('d M Y') }}</div>
        <div>{{ now()->format('h:i A') }}</div>
        <div style="margin-top:4px;">Dijana oleh sistem</div>
    </div>
</div>

{{-- JENIS BADGE --}}
<div class="jenis-badge">
    {{ match($jenis) {
        'pelajar'   => '👤 Senarai Pelajar',
        'sumbangan' => '💰 Rekod Sumbangan',
        'keluarga'  => '🏠 Keluarga Angkat',
        'meeting'   => '📅 Meeting Record',
        'prestasi'  => '📊 Prestasi Akademik',
        default     => ucfirst($jenis),
    } }}
</div>

{{-- SUMMARY --}}
<div class="summary">
    <div class="sum-item">
        <div class="sum-label">Jumlah Rekod</div>
        <div class="sum-value">{{ count($data) }}</div>
    </div>
    <div class="sum-item">
        <div class="sum-label">Tarikh Laporan</div>
        <div class="sum-value" style="font-size:11pt;">{{ now()->format('d/m/Y') }}</div>
    </div>
    @if($jenis === 'sumbangan')
    <div class="sum-item">
        <div class="sum-label">Jumlah Sumbangan</div>
        <div class="sum-value">RM{{ number_format(collect($data)->sum('jumlah'), 2) }}</div>
    </div>
    @elseif($jenis === 'prestasi')
    <div class="sum-item">
        <div class="sum-label">Purata CGPA</div>
        <div class="sum-value">{{ count($data) > 0 ? number_format(collect($data)->avg('cgpa'), 2) : '—' }}</div>
    </div>
    @elseif($jenis === 'meeting')
    <div class="sum-item">
        <div class="sum-label">Jumlah Sesi</div>
        <div class="sum-value">{{ collect($data)->sum('jumlah_sesi') }}</div>
    </div>
    @else
    <div class="sum-item">
        <div class="sum-label">Jumlah Aktif</div>
        <div class="sum-value">{{ collect($data)->filter(fn($r) => strtolower($r['status_pengajian'] ?? $r['status_tajaan'] ?? '') === 'aktif')->count() }}</div>
    </div>
    @endif
</div>

<div class="content">

{{-- ===== PELAJAR ===== --}}
@if($jenis === 'pelajar')
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nama Pelajar</th>
            <th>No. Matrik</th>
            <th>Semester</th>
            <th>Status</th>
            <th>Tamat Tajaan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $p)
        <tr>
            <td>{{ $i+1 }}</td>
            <td><strong>{{ $p['nama_pelajar'] ?? '—' }}</strong></td>
            <td>{{ $p['no_matrik'] ?? '—' }}</td>
            <td>{{ $p['semester'] ?? '—' }}</td>
            <td>
                @php $st = strtolower($p['status_pengajian'] ?? ''); @endphp
                <span class="badge {{ $st === 'aktif' ? 'green' : ($st === 'tamat' ? 'gray' : 'amber') }}">
                    {{ $p['status_pengajian'] ?? '—' }}
                </span>
            </td>
            <td>{{ !empty($p['tarikh_tamat_tajaan']) ? \Carbon\Carbon::parse($p['tarikh_tamat_tajaan'])->format('d/m/Y') : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">Tiada rekod ditemui.</td></tr>
        @endforelse
    </tbody>
</table>

{{-- ===== SUMBANGAN ===== --}}
@elseif($jenis === 'sumbangan')
<table>
    <thead>
        <tr><th>#</th><th>Pelajar</th><th>Jumlah (RM)</th><th>Tarikh Terima</th><th>Bulan</th><th>Status</th></tr>
    </thead>
    <tbody>
        @forelse($data as $i => $s)
        <tr>
            <td>{{ $i+1 }}</td>
            <td><strong>{{ $s['nama_pelajar'] ?? '—' }}</strong></td>
            <td><strong>RM{{ number_format($s['jumlah'] ?? 0, 2) }}</strong></td>
            <td>{{ !empty($s['tarikh_terima']) ? \Carbon\Carbon::parse($s['tarikh_terima'])->format('d/m/Y') : '—' }}</td>
            <td>{{ !empty($s['bulan']) ? \Carbon\Carbon::parse($s['bulan'].'-01')->format('M Y') : '—' }}</td>
            <td>
                @php $st = strtolower($s['status'] ?? ''); @endphp
                <span class="badge {{ $st === 'diterima' ? 'green' : 'red' }}">{{ $s['status'] ?? '—' }}</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">Tiada rekod.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">Jumlah Keseluruhan</td>
            <td>RM{{ number_format(collect($data)->sum('jumlah'), 2) }}</td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>

{{-- ===== KELUARGA ANGKAT ===== --}}
@elseif($jenis === 'keluarga')
<table>
    <thead>
        <tr><th>#</th><th>Nama Keluarga Angkat</th><th>Pelajar Ditaja</th><th>No. Telefon</th><th>Status Tajaan</th><th>Tamat Tajaan</th></tr>
    </thead>
    <tbody>
        @forelse($data as $i => $k)
        <tr>
            <td>{{ $i+1 }}</td>
            <td><strong>{{ $k['nama_keluarga_angkat'] ?? '—' }}</strong></td>
            <td>{{ $k['nama_pelajar'] ?? '—' }}</td>
            <td>{{ $k['no_telefon'] ?? '—' }}</td>
            <td>
                @php $st = strtolower($k['status_tajaan'] ?? ''); @endphp
                <span class="badge {{ $st === 'aktif' ? 'green' : 'gray' }}">{{ $k['status_tajaan'] ?? '—' }}</span>
            </td>
            <td>{{ !empty($k['tarikh_tamat_tajaan']) ? \Carbon\Carbon::parse($k['tarikh_tamat_tajaan'])->format('d/m/Y') : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">Tiada rekod.</td></tr>
        @endforelse
    </tbody>
</table>

{{-- ===== MEETING ===== --}}
@elseif($jenis === 'meeting')
<table>
    <thead>
        <tr><th>#</th><th>Tarikh</th><th>Pelajar</th><th>Keluarga Angkat</th><th>Jenis</th><th>Sesi</th><th>Catatan</th></tr>
    </thead>
    <tbody>
        @forelse($data as $i => $m)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ !empty($m['tarikh_pertemuan']) ? \Carbon\Carbon::parse($m['tarikh_pertemuan'])->format('d/m/Y') : '—' }}</td>
            <td><strong>{{ $m['nama_pelajar'] ?? '—' }}</strong></td>
            <td>{{ $m['nama_keluarga'] ?? '—' }}</td>
            <td>
                @php $j = strtolower($m['jenis_pertemuan'] ?? ''); @endphp
                <span class="badge {{ $j === 'bersemuka' ? 'blue' : ($j === 'dalam talian' ? 'green' : 'amber') }}">
                    {{ $m['jenis_pertemuan'] ?? '—' }}
                </span>
            </td>
            <td style="text-align:center;">{{ $m['jumlah_sesi'] ?? 1 }}</td>
            <td style="font-size:8.5pt;color:#64748b;">{{ \Illuminate\Support\Str::limit($m['catatan'] ?? '—', 40) }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">Tiada rekod.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">Jumlah Sesi Keseluruhan</td>
            <td><strong>{{ collect($data)->sum('jumlah_sesi') }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>

{{-- ===== PRESTASI ===== --}}
@elseif($jenis === 'prestasi')
<table>
    <thead>
        <tr><th>#</th><th>Nama Pelajar</th><th>No. Matrik</th><th>Semester</th><th>GPA</th><th>CGPA</th></tr>
    </thead>
    <tbody>
        @forelse($data as $i => $p)
        <tr>
            <td>{{ $i+1 }}</td>
            <td><strong>{{ $p['nama_pelajar'] ?? '—' }}</strong></td>
            <td>{{ $p['no_matrik'] ?? '—' }}</td>
            <td>{{ $p['semester'] ?? '—' }}</td>
            <td>
                @php $gpa = (float)($p['gpa'] ?? 0); @endphp
                <span class="badge {{ $gpa >= 3.5 ? 'green' : ($gpa >= 3.0 ? 'blue' : ($gpa >= 2.0 ? 'amber' : 'red')) }}">
                    {{ number_format($gpa, 2) }}
                </span>
            </td>
            <td><strong>{{ number_format((float)($p['cgpa'] ?? 0), 2) }}</strong></td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">Tiada rekod.</td></tr>
        @endforelse
    </tbody>
    @if(count($data) > 0)
    <tfoot>
        <tr>
            <td colspan="4">Purata</td>
            <td>{{ number_format(collect($data)->avg('gpa'), 2) }}</td>
            <td>{{ number_format(collect($data)->avg('cgpa'), 2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>
@endif

</div>

{{-- FOOTER --}}
<div class="footer">
    <span>Program Protege Bitara UPSI — Sistem Pengurusan</span>
    <span>Dijana: {{ now()->format('d/m/Y h:i A') }}</span>
</div>

<script>
// Auto-print jika datang dari butang Cetak
window.addEventListener('load', function() {
    // Uncomment baris bawah kalau nak auto-print
    // window.print();
});
</script>

</body>
</html>
