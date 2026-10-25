<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.5; color: #111827; }
        .header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 26px; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin: 20px 0 4px; }
        .number { text-align: center; margin: 0 0 24px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; vertical-align: top; }
        .label { width: 160px; }
        .signature { width: 42%; margin-left: auto; margin-top: 40px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <strong>UNIVERSITAS LAMBUNG MANGKURAT</strong><br>
        FAKULTAS MATEMATIKA DAN ILMU PENGETAHUAN ALAM
    </div>

    <p class="title">SURAT IZIN CUTI AKADEMIK</p>
    <p class="number">Nomor: {{ $letter->letter_number }}</p>

    <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
    <table>
        <tr><td class="label">Nama</td><td>: {{ $letter->applicant_name }}</td></tr>
        <tr><td class="label">NIM</td><td>: {{ $letter->applicant_id_number }}</td></tr>
        <tr><td class="label">Program Studi</td><td>: {{ $letter->program_studi ?? '-' }}</td></tr>
        <tr><td class="label">Semester</td><td>: {{ $meta['semester'] ?? '-' }}</td></tr>
        <tr><td class="label">Tahun Akademik</td><td>: {{ $meta['tahun_akademik'] ?? '-' }}</td></tr>
        <tr><td class="label">Nomor Kontak</td><td>: {{ $meta['nomor_hp'] ?? '-' }}</td></tr>
        <tr><td class="label">Nominal UKT</td><td>: Rp {{ number_format((float) ($meta['nominal_ukt'] ?? 0), 0, ',', '.') }}</td></tr>
    </table>

    <p>Mahasiswa tersebut diberikan izin untuk mengambil cuti akademik selama satu semester dengan alasan:</p>
    <p>{{ $meta['alasan_cuti'] ?? $letter->purpose }}</p>

    <p>Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

    <div class="signature">
        Banjarmasin, {{ optional($letter->issued_at)->translatedFormat('d F Y') }}<br>
        Dekan FMIPA ULM<br><br><br><br>
        <strong>{{ $signatory['name'] }}</strong><br>
        DEKAN
    </div>
</body>
</html>
