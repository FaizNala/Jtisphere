<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;

        }
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 30px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .navbar img {
            width: 120px; /* Adjust logo size */
        }
        .header {
            font-size: 42px;
            font-weight: 550;
            color: #264585;
            margin-left: -165px;
            margin-top: 5px;
        }
        .subheader {
            font-size: 25px;
            color: #F9B41B;
            margin-left: -76px;
            margin-top: -33px;
            /* margin-right: 12px; */
        }
        .navbar ul {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .navbar li {
            margin: 0 15px;
            font-size: 18px;
            color: #333;
            cursor: pointer;
        }
        .navbar li:hover {
            color: #0554F2;
        }
        .login {
            background-color: #264585;
            width: 100px;
            height: 50px;
            border-radius: 8px;
            color: aliceblue;
            text-align: center;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 10px;
            transition: background-color 0.3s;

        }
        .login:hover {
            background-color: #0337A1;
        }
        .admin {
            color: black;
            text-decoration: none;
            margin-left: -10px;
            transition: color 0.3s;
        }
        .admin:hover {
            color: #0337A1;
        }
        main {
            text-align: center;
            margin: 20px 0;
        }
        .background-image {
            width: 100%;
            max-height: 250px;
            object-fit: cover;
            border-radius: 20px;
        }
        .judul {
            font-size: 49px;
            margin-top: 20px;
            color: #264585;
        }
        .footer-box {
            background-color: #f5f6f9;
            padding: 40px;
            border-radius: 15px;
            max-width: 900px;
            margin: 20px auto;
            text-align: left;
        }
        footer {
            background-color: #264585;
            color: white;
            padding: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="home.html"><img src="jti.png" alt="JTISphere Logo"></a>
        <div>
            <p class="header">JTISphere</p>
            <p class="subheader">POLINEMA</p>
        </div>
        <ul>
            <li class="home">Home</li>
            <li>Pengumuman</li>
            <li>Tentang SDM</li>
            <li>Dokumen</li>
        </ul>
        <div>
            <a href="{{ route('login') }}" class="login">Login</a>
            <a href="{{ route('login') }}" class="admin">Login Administrator</a>
        </div>
    </nav>
    <main>
        <img src="ti.webp" class="background-image" alt="Background Image">
        <h1 class="judul">Sistem Manajemen SDM JTI POLINEMA</h1>
        <div class="footer-box">
            <h2>Manajemen SDM yang Terpadu dan Efisien</h2>
            <fitur>Sistem Informasi Manajemen SDM JTI Polinema merupakan platform berbasis web dan mobile yang mempermudah distribusi tugas serta pemantauan beban kerja dosen dan staf di Jurusan Teknologi Informasi Polinema.
                Fitur real-time monitoring pada sistem ini membantu memastikan pemerataan tugas yang optimal, mendukung efisiensi kerja, serta menjaga keseimbangan beban di lingkungan JTI.</p>
        </div>
        {{-- <div class="footer-box">
            <h2>Struktur Organisasi</h2>
            <img src="struktur.png" alt="Struktur Organisasi" style="width: 100%; max-width: 600px; height: auto;">
        </div> --}}
        <div class="footer-box">
            <h2>Struktur Organisasi Jurusan Teknologi Informasi POLINEMA</h2>
                <div class="footer-box">
                    <img src="SO.png" alt="Struktur Organisasi" style="width: 100%; max-width: 600px; height: auto;">
                </div>
        </div>
    </main>
    <footer>
        <p class="hki">Copyright @ 2024 JTISphere</p>
        <div class="kontak">
            <img src="logo.png" alt="Logo" style="max-width: 200px; height: auto;">
            <h2>Contact Us</h2>
            <p>Politeknik Negeri Malang</p>
            <p>Jl. Soekarno Hatta No. 9</p>
            <p>Malang, Jawa Timur</p>
            <p>Email: info@polinema.ac.id</p>
            <p>Phone: (0341) 404424</p>
        </div>
    </footer>
</body>
</html>
