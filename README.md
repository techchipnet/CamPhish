# CamPhish
Ambil gambar dari kamera depan ponsel atau webcam PC target hanya dengan mengirimkan sebuah tautan.
![CamPhish](https://raw.githubusercontent.com/wanzxploit/CamPhish/refs/heads/master/logo.png)

# Apa itu CamPhish?
CamPhish adalah alat yang digunakan untuk mengambil gambar dari kamera depan ponsel atau webcam PC target. Alat ini meng-host situs web palsu menggunakan server PHP bawaan dan menggunakan Savero untuk menghasilkan tautan yang dapat diteruskan ke target melalui internet. Situs web tersebut meminta izin kamera, dan jika target mengizinkannya, alat ini akan mengambil gambar dari perangkat target.

## Fitur
- Dua template halaman otomatis untuk membuat target tetap terlibat di situs web:
  - Festival Wishing
  - Live YouTube TV
  - Online Meeting [Beta]
- Cukup masukkan nama festival atau ID video YouTube.

## Sistem yang Telah Diuji:
- Kali Linux
- Termux
- MacOS
- Ubuntu
- Parrot Sec OS

# Persyaratan dan Instalasi
Alat ini memerlukan PHP untuk server web dan Savero untuk menghasilkan tautan. Jalankan perintah berikut di terminal Anda:

```
apt-get -y install php openssh git wget
```

## Instalasi (Kali Linux/Termux):

```
git clone https://github.com/wanzxploit/CamPhish
cd CamPhish
bash camphish.sh
```

## Cara Penggunaan
1. Jalankan perintah instalasi di atas untuk mengunduh dan mengatur alat ini.
2. Saat menjalankan `bash camphish.sh`, Anda akan diminta memilih server:
   - **Opsi 1: Ngrok**
   - **Opsi 2: Savero**
3. Disarankan untuk memilih opsi 2 (Savero) karena lebih stabil dibandingkan Ngrok, terutama jika jaringan internet Anda tidak stabil.
4. Gunakan alat ini dengan memilih template halaman yang diinginkan (Festival Wishing, Live YouTube TV, atau Online Meeting).
5. Bagikan tautan yang dihasilkan kepada target Anda. Ingat untuk hanya menggunakan alat ini untuk keperluan etis.

## Catatan untuk Pengguna Android
Jika mengalami masalah menggunakan Termux, disarankan untuk menggunakan **Linux Userland** sebagai alternatif. Aplikasi ini menyediakan lingkungan Linux lengkap untuk menjalankan alat seperti CamPhish dengan lebih lancar.

## Perubahan Terbaru:
- **Versi 1.7:**
  - Perbaikan direktori home yang gagal di Termux.
  - Dukungan untuk Apple Silicon (M1/M2/M3 ARM64).
  - Dukungan untuk ARM64 seperti Raspberry Pi.
- **Versi 1.6:** Perbaikan pembuatan tautan langsung Ngrok.
- **Versi 1.5:** Penambahan template pertemuan daring baru.
- **Versi 1.4:** Pembaruan token autentikasi Ngrok.
- **Versi 1.3:** Perbaikan tautan langsung Ngrok.

## Kredit
CamPhish adalah proyek hasil *fork* dari [TechChipNet](https://github.com/techchipnet/CamPhish) dan terinspirasi oleh [TheLinuxChoice](https://github.com/thelinuxchoice/). Terima kasih kepada pengembang aslinya atas ide dan kontribusinya.

**Peringatan:** CamPhish dibuat untuk membantu dalam pengujian penetrasi. Pengembang tidak bertanggung jawab atas penyalahgunaan atau penggunaan ilegal alat ini. Gunakan dengan bijak.

