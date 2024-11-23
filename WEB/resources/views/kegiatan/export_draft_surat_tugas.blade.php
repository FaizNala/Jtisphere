<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Surat Tugas</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 6px 20px 5px 20px;
            line-height: 1.5;
            font-size: 12pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 4px 3px;
        }

        th {
            text-align: left;
        }

        .d-block {
            display: block;
        }

        img.image {
            width: auto;
            height: 80px;
            max-width: 150px;
            max-height: 150px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .p-1 {
            padding: 5px 1px 5px 1px;
        }

        .font-10 {
            font-size: 10pt;
        }

        .font-11 {
            font-size: 11pt;
        }

        .font-12 {
            font-size: 12pt;
        }

        .font-13 {
            font-size: 13pt;
        }

        .border-bottom-header {
            border-bottom: 1px solid black;
        }

        .mb-4 {
            margin-bottom: 2rem;
        }

        .mb-2 {
            margin-bottom: 1rem;
        }

        .mt-4 {
            margin-top: 2rem;
        }

        .content-spacing {
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .content-spacing p {
            margin: 0;
            padding: 3px 0;
        }

        .content-spacing table tr td {
            padding: 3px 0;
            line-height: 1.5;
        }

        .dosen-spacing tr td {
            padding: 3px 0;
            line-height: 1.5;
        }

        .header-spacing {
            margin-bottom: 30px;
        }

        .title-spacing {
            margin: 30px 0;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }

        .signature-content {
            margin-right: 40px;
        }

        .signature-space {
            height: 80px;
        }

        .text-bold {
            font-weight: bold;
        }

        .signature-spacing {
            line-height: 1.5;
            margin-top: 40px;
        }

        .signature-content p {
            margin: 3px 0;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="header-spacing">
        <table class="border-bottom-header">
            <tr>
                <td width="15%" class="text-center"><img src="{{ asset('polinema-bw.png') }}" width="130%"></td>
                <td width="85%">
                    <span class="text-center d-block font-12 font-bold mb-1">KEMENTERIAN
                        PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</span>
                    <span class="text-center d-block font-11 font-bold mb-1">POLITEKNIK NEGERI
                        MALANG</span>
                    <span class="text-center d-block font-10">Jl. Soekarno-Hatta No. 9 Malang
                        65141</span>
                    <span class="text-center d-block font-10">Telepon (0341) 404424 Pes. 101
                        105, 0341-404420, Fax. (0341) 404420</span>
                    <span class="text-center d-block font-10">Laman: www.polinema.ac.id</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="text-center title-spacing">
        <h3>SURAT TUGAS</h3>
        <p>Nomor: ....../....../....../.......</p>
    </div>

    <div class="content-spacing">
        <p>Yang bertanda tangan di bawah ini:</p>
        <p>Dengan ini menugaskan kepada:</p>
    </div>

    <table class="dosen-spacing">
        <tbody>
            @foreach ($dosenKegiatan as $dk)
            <tr>
                <td width="5%">{{ $loop->iteration }}.</td>
                <td width="25%">Nama</td>
                <td width="2%">:</td>
                <td>{{ $dk->dosen->nama }}</td>
            </tr>
            <tr>
                <td></td>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $dk->dosen->nip }}</td>
            </tr>
            <tr>
                <td></td>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $dk->peran->peran_nama }}</td>
            </tr>
            @if (!$loop->last)
            <tr><td colspan="4">&nbsp;</td></tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="content-spacing">
        <br>
        <p>Untuk melaksanakan kegiatan:</p>
        <table>
            <tr>
                <td width="25%">Nama Kegiatan</td>
                <td width="2%">:</td>
                <td>{{ $kegiatan->kegiatan_nama }}</td>
            </tr>
            <tr>
                <td>Kategori</td>
                <td>:</td>
                <td>{{ $kegiatan->kategori_nama }}</td>
            </tr>
            <tr>
                <td>Waktu Pelaksanaan</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($kegiatan->tanggal_selesai)->isoFormat('D MMMM Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="content-spacing">
        <p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>
    </div>

    <div class="signature signature-spacing">
        <div class="signature-content">
            <p>Malang, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
            <p>Direktur,</p>
            <div class="signature-space"></div>
            <p class="text-bold">SUPRIATNA ADHISUWIGNJO, ST., MT</p>
            <p>NIP. 19710108199031001</p>
        </div>
    </div>

</body>
</html>
