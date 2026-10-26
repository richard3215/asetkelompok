<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $nomorLaporan }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #222;
            line-height: 1.35;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0B2545;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #0B2545;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 8pt;
            color: #555;
        }
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            color: #0B2545;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .doc-number {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-bottom: 15px;
        }
        .kpi-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .kpi-card {
            background-color: #f4f6f9;
            border: 1px solid #dcdfe6;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-title {
            font-size: 7.5pt;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        .kpi-value {
            font-size: 11pt;
            font-weight: bold;
            color: #0B2545;
            margin-top: 3px;
        }
        .narasi-box {
            background-color: #f8fafc;
            border-left: 3px solid #0B2545;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-size: 8.5pt;
            color: #333;
            text-align: justify;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #bbb;
            padding: 4px 6px;
            text-align: left;
        }
        .data-table th {
            background-color: #0B2545;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .sig-table {
            width: 100%;
            margin-top: 20px;
            font-size: 9pt;
        }
        .sig-box {
            height: 50px;
        }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td style="width: 70%;">
            <div class="company-name">{{ $namaPerusahaan }}</div>
            <div class="company-sub">{{ $alamatPerusahaan }} | Telp: {{ $teleponPerusahaan }}</div>
            <div class="company-sub">Sistem Informasi Manajemen Aset Perusahaan (SI-ASET)</div>
        </td>
        <td style="width: 30%; text-align: right; font-size: 8pt; color: #555;">
            Tanggal Cetak: <strong>{{ now()->format('d/m/Y H:i') }}</strong><br>
            Dicetak Oleh: <strong>{{ $pengguna->nama ?? 'Admin' }}</strong><br>
            Unit: <strong>{{ $kantorFilter }}</strong>
        </td>
    </tr>
</table>

<div class="doc-title">LAPORAN INVENTARISASI & NILAI BUKU ASET PERUSAHAAN</div>
<div class="doc-number">Nomor Dokumen: {{ $nomorLaporan }} | Filter: {{ $kategoriFilter }} • {{ $kondisiFilter }}</div>

<table class="kpi-table">
    <tr>
        <td style="width: 20%;">
            <div class="kpi-card">
                <div class="kpi-title">Total Unit Aset</div>
                <div class="kpi-value">{{ $totalAset }} Unit</div>
            </div>
        </td>
        <td style="width: 20%;">
            <div class="kpi-card">
                <div class="kpi-title">Aset Milik Perusahaan</div>
                <div class="kpi-value">{{ $milikCount }} Unit</div>
            </div>
        </td>
        <td style="width: 20%;">
            <div class="kpi-card">
                <div class="kpi-title">Aset Sewa Vendor</div>
                <div class="kpi-value">{{ $sewaCount }} Unit</div>
            </div>
        </td>
        <td style="width: 20%;">
            <div class="kpi-card">
                <div class="kpi-title">Total Nilai Perolehan</div>
                <div class="kpi-value">Rp {{ number_format($totalNilaiPerolehan, 0, ',', '.') }}</div>
            </div>
        </td>
        <td style="width: 20%;">
            <div class="kpi-card">
                <div class="kpi-title">Nilai Buku Saat Ini</div>
                <div class="kpi-value" style="color: #00875A;">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</div>
            </div>
        </td>
    </tr>
</table>

<div class="narasi-box">
    <strong>Ringkasan Eksekutif & Narasi Administrasi:</strong><br>
    {{ $narasiEksekutif }}
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 3%;">No</th>
            <th style="width: 14%;">Nomor Index</th>
            <th style="width: 18%;">Nama Aset & Merk</th>
            <th style="width: 12%;">Kategori</th>
            <th style="width: 8%;">Kepemilikan</th>
            <th style="width: 11%;">Unit Kerja</th>
            <th style="width: 8%;">Kondisi</th>
            <th style="width: 13%;">Nilai Perolehan</th>
            <th style="width: 13%;">Nilai Buku</th>
        </tr>
    </thead>
    <tbody>
        @foreach($daftarAset as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td><strong>{{ $item->nomor_index }}</strong></td>
            <td>
                {{ $item->nama_aset }}<br>
                <span style="font-size: 7pt; color: #666;">SN: {{ $item->nomor_seri ?? '-' }} | {{ $item->merk ?? '' }}</span>
            </td>
            <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
            <td class="text-center">
                {{ $item->jenis_kepemilikan === 'milik_sendiri' ? 'Perusahaan' : 'Sewa' }}
            </td>
            <td>{{ $item->kantor->nama_kantor ?? '-' }}</td>
            <td class="text-center" style="text-transform: capitalize;">{{ str_replace('_', ' ', $item->kondisi) }}</td>
            <td class="text-right">
                @if($item->jenis_kepemilikan === 'milik_sendiri')
                    Rp {{ number_format($item->nilai_perolehan, 0, ',', '.') }}
                @else
                    <span style="color: #888;">(Sewa)</span>
                @endif
            </td>
            <td class="text-right">
                @if($item->jenis_kepemilikan === 'milik_sendiri')
                    <strong>Rp {{ number_format($item->nilai_buku_saat_ini, 0, ',', '.') }}</strong>
                @else
                    <span style="color: #888;">-</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f0f4f8; font-weight: bold;">
            <td colspan="7" class="text-right">TOTAL KESELURUHAN:</td>
            <td class="text-right">Rp {{ number_format($totalNilaiPerolehan, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

<table class="sig-table">
    <tr>
        <td style="width: 50%; text-align: left;">
            Catatan Dokumen:<br>
            • Laporan ini diterbitkan otomatis oleh SI-ASET.<br>
            • Nilai depresiasi dihitung menggunakan metode Garis Lurus (Straight-Line Method).<br>
            • Aset sewa vendor dikecualikan dari perhitungan nilai aset milik perusahaan.
        </td>
        <td style="width: 25%; text-align: center;">
            Disiapkan Oleh,<br>
            Staf Pengelola Aset
            <div class="sig-box"></div>
            <strong>( {{ $pengguna->nama ?? 'Staf Operasional' }} )</strong><br>
            <span style="font-size: 8pt; color: #666;">NIP: {{ $pengguna->nip ?? '-' }}</span>
        </td>
        <td style="width: 25%; text-align: center;">
            Mengetahui / Mengesahkan,<br>
            Kepala Kantor / Pimpinan
            <div class="sig-box"></div>
            <strong>( ............................................ )</strong><br>
            <span style="font-size: 8pt; color: #666;">Pejabat Berwenang</span>
        </td>
    </tr>
</table>

</body>
</html>
