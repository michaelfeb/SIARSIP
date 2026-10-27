<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    @include('components.pdf.styles')
</head>

<body>
    @php
    $issuedDate = $letter->issued_at
    ? \Carbon\Carbon::parse($letter->issued_at)->locale('id')->translatedFormat('d F Y')
    : now()->locale('id')->translatedFormat('d F Y');
    @endphp

    @include('components.pdf.header')
    @if ($isPreview ?? false)
        <div class="preview-watermark">PRATINJAU - BELUM BERLAKU</div>
    @endif

    <div class="reference">
        <div class="reference-left">
            <table class="reference-table">
                <tr>
                    <td class="reference-label">Nomor</td>
                    <td class="reference-colon">:</td>
                    <td>{{ ($isPreview ?? false) ? 'Akan ditetapkan setelah persetujuan' : ($letter->letter_number ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="reference-label">Lampiran</td>
                    <td class="reference-colon">:</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td class="reference-label">Perihal</td>
                    <td class="reference-colon">:</td>
                    <td><strong>Permohonan Cuti Akademik</strong></td>
                </tr>
            </table>
        </div>
        <div class="reference-date">Banjarbaru, {{ $issuedDate }}</div>
    </div>

    <div class="recipient">
        Yth. Wakil Rektor Bidang Akademik<br>
        Universitas Lambung Mangkurat<br>
        di Banjarmasin
    </div>

    <p class="paragraph">Sehubungan dengan permohonan cuti akademik mahasiswa atas nama:</p>

    <table class="student-table">
        <tr>
            <td class="student-label">Nama</td>
            <td class="student-colon">:</td>
            <td>{{ $letter->applicant_name }}</td>
        </tr>
        <tr>
            <td class="student-label">NIM</td>
            <td class="student-colon">:</td>
            <td>{{ $letter->applicant_id_number }}</td>
        </tr>
        <tr>
            <td class="student-label">Program Studi</td>
            <td class="student-colon">:</td>
            <td>{{ $letter->program_studi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="student-label">Semester</td>
            <td class="student-colon">:</td>
            <td>{{ $meta['semester'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="student-label">Tahun Akademik</td>
            <td class="student-colon">:</td>
            <td>{{ $meta['tahun_akademik'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="student-label">Nomor Kontak</td>
            <td class="student-colon">:</td>
            <td>{{ $meta['nomor_hp'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="student-label">Nominal UKT</td>
            <td class="student-colon">:</td>
            <td>Rp {{ number_format((float) ($meta['nominal_ukt'] ?? 0), 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="paragraph">
        Maka bersama ini kami mohon agar mahasiswa tersebut dapat diberikan cuti akademik pada semester
        {{ $meta['semester'] ?? '-' }} Tahun Akademik {{ $meta['tahun_akademik'] ?? '-' }}.
    </p>

    <p class="paragraph">
        Demikian disampaikan, atas perkenan dan kesediaannya diucapkan terima kasih.
    </p>

    <div class="signature-row">
        <div class="signature-spacer"></div>
        <div class="signature">
            <p>Dekan,</p>
            @if ($isPreview ?? false)
                <div class="signature-space"></div>
            @else
                <img class="signature-qr" src="{{ $signatoryQrCode }}" alt="QR surat {{ $letter->letter_number }}">
            @endif
            <p class="signature-name">{{ $signatory['name'] }}</p>
            <p>NIP. {{ $signatory['nim'] ?? '-' }}</p>
        </div>
    </div>
</body>

</html>
