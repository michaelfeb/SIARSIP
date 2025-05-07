<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4;
            margin: 2cm;
        }

        body {
            width: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
            box-sizing: border-box;
        }
    </style>

    <style>
        .column-left {
            float: left;
            width: 15%;
            text-align: center;
        }

        .column-right {
            float: left;
            width: 85%;
            text-align: center;
        }

        .column-ttd-left {
            float: left;
            width: 70%;
        }

        .column-ttd-right {
            float: left;
            width: 40%;
            text-align: left;
        }

        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        .kop {
            font-size: 20px;
            margin: 0;
        }

        .alamat {
            font-size: 15px;
            margin-top: 6px;
        }

        hr.line-break {
            border: none;
            border-top: 2px solid #000;
            margin: 5px 0 20px 0;
        }

        .isi {
            font-family: 'Times New Roman', Times, serif;
            margin: 0px 70px;
        }

        .title {
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            font-size: 20px;
            margin-bottom: 0;
        }

        .nomor {
            text-align: center;
            font-size: 15px;
            margin-top: 2px;
        }

        .content {
            line-height: 1.8;
            font-size: 16px;
            margin-top: 30px;
        }

        .info-table {
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }

        .footer {
            margin-top: 60px;
            text-align: right;
            font-size: 16px;
        }

        .footer p {
            display: inline-block;
            text-align: left;
        }

        .ttd {
            margin-top: 80px;
            font-size: 16px;
        }

        .ttd p {
            padding: 0;
            display: inline-block;
            text-align: left;
        }

        .nama-dibawah-ttd {
            font-size: 16px;
        }
    </style>
</head>

<body>

    <div class="row">
        <div class="column-left">
            <img src="{{ public_path('images/logo-ulm.png') }}" width="90">
        </div>
        <div class="column-right">
            <p class="kop">KEMENTERIAN PENDIDIKAN TINGGI,<br>SAINS, DAN TEKNOLOGI</p>
            <p class="kop">UNIVERSITAS LAMBUNG MANGKURAT</p>
            <p class="kop"><strong>FAKULTAS MATEMATIKA DAN ILMU PENGETAHUAN ALAM</strong></p>
            <p class="alamat">
                Jalan A. Yani Km 35,8 Banjarbaru 70714<br>
                Telepon : (0511) 4773112 Laman : https://fmipa.ulm.ac.id/
            </p>
        </div>
    </div>

    <hr class="line-break">

    <div class="isi">
        <p class="title">SURAT KETERANGAN</p>
        <p class="nomor">Nomor : {{ $nomor_surat }}</p>

        <div class="content">
            <p>Yang bertanda tangan di bawah, dengan ini menyatakan mahasiswa berikut:</p>

            <table class="info-table">
                <tr>
                    <td>Nama</td>
                    <td>: {{ $nama }}</td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td>: {{ $nim }}</td>
                </tr>
                <tr>
                    <td>Program Studi</td>
                    <td>: {{ $prodi }}</td>
                </tr>
            </table>

            <p>Telah melengkapi berkas Sidang Nol dan dinyatakan <strong>LULUS</strong> Sidang Nol. Demikian surat keterangan ini dibuat untuk digunakan sebagaimana mestinya.</p>
        </div>

        <div class="row">
            <div class="column-ttd-left">

            </div>
            <div class="column-ttd-right">
                <div class="ttd">
                    <p>Banjarbaru, {{ $tanggal }}<br>
                        Yang Memeriksa</p>
                </div>

                <img src="{{ $ttd_path }}" width="140">

                <div class="nama-dibawah-ttd">
                    <p style="text-decoration: underline; margin-bottom: 0;">{{ $penandatangan}}</p>
                    <p style="margin-top: 5px;">NIP {{ $nip }}</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>