# 🛡️ Lab SQL Injection (SQLi)

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Status: Active](https://img.shields.io/badge/Status-Active-success.svg)]()

Selamat datang di **Lab SQL Injection**! Repositori ini berisi aplikasi web yang sengaja dibuat rentan untuk membantu penggiat keamanan siber (*cybersecurity*), *penetration tester*, dan *developer* berlatih serta memahami celah keamanan SQL Injection dalam lingkungan lokal yang aman.

## ⚠️ Peringatan (Disclaimer)
**HANYA UNTUK TUJUAN EDUKASI.** 
Lingkungan lab ini sengaja dibuat rentan. **Jangan** mengunggah (*hosting*) aplikasi ini di server web publik atau di lingkungan produksi (*production*). Penulis tidak bertanggung jawab atas penyalahgunaan atau kerusakan yang diakibatkan oleh informasi maupun kode yang ada di dalam repositori ini. Selalu junjung tinggi etika peretasan (*ethical hacking*).

## 🎯 Fitur & Kerentanan yang Dicakup
Lab ini mensimulasikan berbagai skenario SQL Injection yang sering ditemukan di dunia nyata, antara lain:
- **In-Band SQLi (Klasik)**: Error-based dan Union-based SQLi.
- **Inferential SQLi (Blind)**: Boolean-based dan Time-based SQLi.
- **Bypass Autentikasi**: Melewati mekanisme *login* menggunakan kelemahan pada logika SQL.

## 🛠️ Prasyarat
Untuk menjalankan lab ini di komputer Anda, pastikan Anda telah menginstal:
- Web server lokal (contoh: [XAMPP](https://www.apachefriends.org/index.html), WAMP, atau LAMP)
- PHP (Versi 7.x atau 8.x)
- MySQL / MariaDB

## 🚀 Instalasi & Persiapan

### Menggunakan XAMPP (Windows/Linux/Mac)
1. **Clone repositori ini:**
   ```bash
   git clone [https://github.com/arulf4dli/SQL-Injection-Lab.git](https://github.com/arulf4dli/SQL-Injection-Lab.git)