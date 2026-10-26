<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BAST - {{ $bast->nomor_bast }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #222;
            line-height: 1.4;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0B2545;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #0B2545;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 9pt;
            color: #555;
        }
        .doc-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            color: #0B2545;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .doc-number {
            text-align: center;
            font-size: 10pt;
            color: #666;
            margin-bottom: 20px;
        }
        .watermark {
            position: fixed;
            top: 40%;
            left: 20%;
            font-size: 55pt;
            color: rgba(220, 53, 69, 0.15);
            transform: rotate(-35deg);
            z-index: -1000;
            font-weight: bold;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        .info-table td {
            vertical-align: top;
            padding: 3px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 25px 0;
            font-size: 9.5pt;
        }
        .items-table th, .items-table td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #0B2545;
            color: #ffffff;
            font-weight: bold;
        }
        .sig-table {
            width: 100%;
            margin-top: 30px;
            text-align: center;
            font-size: 10pt;
        }
        .sig-box {
            height: 70px;
            vertical-align: bottom;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .sig-title {
            font-size: 9pt;
            color: #555;
        }
    </style>
</head>
<body>

@if($isDraft)
    <div class="watermark">DRAFT DOKUMEN</div>
@endif

<table class="header-table">
    <tr>
        <td style="width: 75%;">
            <div class="company-name">{{ $namaPerusahaan }}</div>
            <div class="company-sub">{{ $alamatPerusahaan }}</div>
            <div class="company-sub">Telepon: {{ $teleponPerusahaan }} | Sistem Manajemen Aset Terpadu (SI-ASET)</div>
        </td>
        <td style="width: 25%; text-align: right; vertical-align: middle;">
            <div style="font-size: 8pt; color: #888; border: 1px dashed #aaa; padding: 4px;">
                Dokumen Resmi Korporasi<br>
                Status: <strong>{{ strtoupper(str_replace('_', ' ', $bast->status_bast)) }}</strong>
            </div>
        </td>
    </tr>
</table>

<div class="doc-title">BERITA ACARA SERAH TERIMA ASET</div>
<div class="doc-number">Nomor: {{ $bast->nomor_bast }}</div>

<p style="text-align: justify;">
    Pada hari ini, <strong>{{ \Carbon\Carbon::parse($bast->tanggal_bast)->isoFormat('dddd, D MMMM Y') }}</strong>, bertempat di 
    <strong>{{ $bast->kantor->nama_kantor ?? 'Kantor Perusahaan' }}</strong>, kami yang bertanda tangan di bawah ini:
</p>

<table class="info-table">
    <tr>
        <td style="width: 30px; font-weight: bold;">I.</td>
        <td style="width: 150px;">Nama Pihak Penyerah</td>
        <td style="width: 10px;">:</td>
        <td><strong>{{ $bast->pihakPenyerah->nama ?? '-' }}</strong> (NIP: {{ $bast->pihakPenyerah->nip ?? '-' }})</td>
    </tr>
    <tr>
        <td></td>
        <td>Jabatan / Peran</td>
        <td>:</td>
        <td>{{ $bast->pihakPenyerah->peran->nama_peran ?? 'Staf Operasional' }}</td>
    </tr>
    <tr>
        <td></td>
        <td>Unit Kantor / Divisi</td>
        <td>:</td>
        <td>{{ $bast->kantor->nama_kantor ?? '-' }}</td>
    </tr>
    <tr>
        <td colspan="4" style="padding-top: 6px;">Selanjutnya disebut sebagai <strong>PIHAK PERTAMA (Yang Menyerahkan)</strong>.</td>
    </tr>
    <tr>
        <td colspan="4" style="height: 10px;"></td>
    </tr>
    <tr>
        <td style="font-weight: bold;">II.</td>
        <td>Nama Pihak Penerima</td>
        <td>:</td>
        <td><strong>{{ $bast->pihakPenerima->nama ?? '-' }}</strong> (NIP: {{ $bast->pihakPenerima->nip ?? '-' }})</td>
    </tr>
    <tr>
        <td></td>
        <td>Jabatan / Peran</td>
        <td>:</td>
        <td>{{ $bast->pihakPenerima->peran->nama_peran ?? 'Penerima Aset' }}</td>
    </tr>
    <tr>
        <td></td>
        <td>Unit Kantor / Divisi</td>
        <td>:</td>
        <td>{{ $bast->kantor->nama_kantor ?? '-' }}</td>
    </tr>
    <tr>
        <td colspan="4" style="padding-top: 6px;">Selanjutnya disebut sebagai <strong>PIHAK KEDUA (Yang Menerima)</strong>.</td>
    </tr>
</table>

<p style="text-align: justify;">
    PIHAK PERTAMA telah menyerahkan kepada PIHAK KEDUA, dan PIHAK KEDUA menyatakan telah menerima aset milik perusahaan dalam keadaan baik dan lengkap sesuai perincian berikut:
</p>

<table class="items-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 25%;">Nomor Index Aset</th>
            <th style="width: 30%;">Nama & Spesifikasi Aset</th>
            <th style="width: 20%;">Merk / Nomor Seri</th>
            <th style="width: 20%;">Kondisi Fisik</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bast->detail as $index => $item)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td><strong>{{ $item->aset->nomor_index ?? '-' }}</strong></td>
            <td>
                <strong>{{ $item->aset->nama_aset ?? '-' }}</strong><br>
                <span style="font-size: 8pt; color: #555;">{{ $item->aset->kategori->nama_kategori ?? '-' }}</span>
            </td>
            <td>
                {{ $item->aset->merk ?? '-' }} {{ $item->aset->tipe_model ?? '' }}<br>
                <span style="font-size: 8pt; color: #666;">SN: {{ $item->aset->nomor_seri ?? '-' }}</span>
            </td>
            <td>
                <span style="text-transform: capitalize;">{{ str_replace('_', ' ', $item->kondisi_saat_serah) }}</span>
                @if($item->keterangan)
                    <br><span style="font-size: 8pt; color: #666;">Catatan: {{ $item->keterangan }}</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<p style="text-align: justify; font-size: 9.5pt;">
    Klausul: Sejak tanggal penandatanganan Berita Acara ini, tanggung jawab pemeliharaan fisik, penggunaan operasional, dan kepatuhan pengamanan aset berada sepenuhnya pada PIHAK KEDUA sesuai kebijakan tata kelola aset korporasi. Apabila terjadi kehilangan, kerusakan di luar keausan wajar, atau mutasi lokasi, wajib segera dilaporkan kepada Bagian Pengelolaan Aset.
</p>

<table class="sig-table">
    <tr>
        <td style="width: 33%;">
            PIHAK PERTAMA<br>Yang Menyerahkan,
            <div class="sig-box">
                @php
                    $ttdPenyerah = $bast->tandaTangan->where('peran_penanda_tangan', 'penyerah')->first();
                @endphp
                @if($ttdPenyerah && $ttdPenyerah->file_tanda_tangan)
                    <img src="{{ $ttdPenyerah->file_tanda_tangan }}" style="max-height: 55px; max-width: 140px;">
                @endif
            </div>
            <div class="sig-name">{{ $bast->pihakPenyerah->nama ?? '-' }}</div>
            <div class="sig-title">NIP: {{ $bast->pihakPenyerah->nip ?? '-' }}</div>
        </td>
        <td style="width: 33%;">
            PIHAK KEDUA<br>Yang Menerima,
            <div class="sig-box">
                @php
                    $ttdPenerima = $bast->tandaTangan->where('peran_penanda_tangan', 'penerima')->first();
                @endphp
                @if($ttdPenerima && $ttdPenerima->file_tanda_tangan)
                    <img src="{{ $ttdPenerima->file_tanda_tangan }}" style="max-height: 55px; max-width: 140px;">
                @endif
            </div>
            <div class="sig-name">{{ $bast->pihakPenerima->nama ?? '-' }}</div>
            <div class="sig-title">NIP: {{ $bast->pihakPenerima->nip ?? '-' }}</div>
        </td>
        <td style="width: 33%;">
            MENGETAHUI / MENYAHKAN<br>Manajer Pengelola Aset,
            <div class="sig-box">
                @if($bast->disetujuiManajer)
                    <div style="font-size: 8pt; color: #28a745; font-weight: bold; padding-top: 20px;">
                        [DISETUJUI SECARA ELEKTRONIK]<br>
                        {{ $bast->updated_at->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
            <div class="sig-name">{{ $bast->disetujuiManajer->nama ?? '( ...................................... )' }}</div>
            <div class="sig-title">NIP: {{ $bast->disetujuiManajer->nip ?? '-' }}</div>
        </td>
    </tr>
</table>

</body>
</html>
