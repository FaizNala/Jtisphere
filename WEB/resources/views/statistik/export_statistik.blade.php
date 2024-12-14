<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 6px 20px 5px 20px;
            line-height: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
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
            border-bottom: 1px solid;
        }

        .border-all,
        .border-all th,
        .border-all td {
            border: 1px solid;
        }
    </style>
</head>

<body>
    <table class="border-bottom-header">
        <tr>
            <img src="./polinema-bw.png" style="width: auto; height: 80px; max-width: 150px; max-height: 150px;">
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

    <h3 class="text-center">LAPORAN DATA STATISTIK DOSEN</h3>

    <table>
        <tr>
            <td width="20%">Username</td>
            <td>: {{ $user->username }}</td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>: {{ $user->nama }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>: {{ $user->nip }}</td>
        </tr>
        <tr>
            <td>Level</td>
            <td>: {{ $user->level_nama }}</td>
        </tr>
        <tr>
            <td>Total Kegiatan</td>
            <td>: {{ $user->total_kegiatan }}</td>
        </tr>
        <tr>
            <td>Total Bobot</td>
            <td>: {{ $user->total_bobot }}</td>
        </tr>
    </table>

    <h4>Daftar Kegiatan</h4>
    <table class="border-all">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nama Kegiatan</th>
                <th>Peran</th>
                <th>Bobot</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kegiatan as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->kegiatan_nama }}</td>
                    <td>{{ $item->peran_nama }}</td>
                    <td>{{ $item->bobot }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
