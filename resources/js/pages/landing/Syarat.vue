<template>
    <div class="terms-privacy-page" style="font-family: 'Poppins', sans-serif;">
      <!-- Navbar -->
      <nav :style="{
        position: 'fixed',
        top: '0',
        left: '0',
        right: '0',
        backgroundColor: '#2563eb',
        color: 'white',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: '15px 5%',
        zIndex: '1000',
        transform: isNavbarVisible ? 'translateY(0)' : 'translateY(-100%)',
        transition: 'transform 0.3s ease-in-out'
      }">
        <div class="logo-container" style="display: flex; align-items: center;">
          <div class="logo" style="font-size: 24px; font-weight: bold;">
            BluCarra
          </div>
        </div>
  
        <div class="nav-buttons" style="position: absolute; left: 50%; transform: translateX(-50%); display: flex; align-items: center;">
          <a href="/landing" style="color: white; text-decoration: none; margin: 0 20px; transition: color 0.3s ease;">
            Kembali ke Beranda
          </a>
        </div>
  
        <div class="language-selector" style="display: flex; align-items: center;">
          <select v-model="selectedLanguage" @change="changeLanguage"
            style="background-color: #3b82f6; color: white; border: none; padding: 10px 8px; border-radius: 20px;">
            <option value="id" style="background-color: white; color: black;">🇮🇩 Indonesia</option>
            <option value="en" style="background-color: white; color: black;">🇬🇧 English</option>
          </select>
        </div>
      </nav>
  
      <!-- Header Section -->
      <header style="background: linear-gradient(to bottom right, #93C5FD, #3B82F6); padding: 120px 0 60px; text-align: center;">
        <h1 style="color: white; font-size: 2.5rem; margin-bottom: 20px;">Syarat & Ketentuan dan Kebijakan Privasi</h1>
        <p style="color: white; font-size: 1.1rem;">Informasi Lengkap Mengenai Layanan BluCarra</p>
  
      <!-- Main Content -->
      <div style="max-width: 1200px; margin: 0 auto; padding: 60px 20px;">
        <!-- Navigation Tabs -->
        <div style="display: flex; justify-content: center; margin-bottom: 40px; gap: 20px;">
          <button 
            @click="activeTab = 'terms'"
            :style="{
              padding: '15px 30px',
              borderRadius: '30px',
              border: 'none',
              backgroundColor: activeTab === 'terms' ? '#2563eb' : '#e5e7eb',
              color: activeTab === 'terms' ? 'white' : '#4b5563',
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              fontWeight: 'bold'
            }"
          >
            Syarat & Ketentuan
          </button>
          <button 
            @click="activeTab = 'privacy'"
            :style="{
              padding: '15px 30px',
              borderRadius: '30px',
              border: 'none',
              backgroundColor: activeTab === 'privacy' ? '#2563eb' : '#e5e7eb',
              color: activeTab === 'privacy' ? 'white' : '#4b5563',
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              fontWeight: 'bold'
            }"
          >
            Kebijakan Privasi
          </button>
        </div>
  
        <!-- Terms Content -->
        <div v-if="activeTab === 'terms'" style="animation: fadeIn 0.5s ease-in-out;">
          <div v-for="(section, index) in termsSections" :key="index"
            style="background-color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <h2 style="color: #2563eb; margin-bottom: 20px; font-size: 1.5rem;">{{ section.title }}</h2>
            <div style="color: #4b5563; line-height: 1.8;">
              <p v-for="(paragraph, pIndex) in section.content" :key="pIndex" style="margin-bottom: 15px;">
                {{ paragraph }}
              </p>
            </div>
          </div>
        </div>
  
        <!-- Privacy Policy Content -->
        <div v-if="activeTab === 'privacy'" style="animation: fadeIn 0.5s ease-in-out;">
          <div v-for="(section, index) in privacyPolicySections" :key="index"
            style="background-color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <h2 style="color: #2563eb; margin-bottom: 20px; font-size: 1.5rem;">{{ section.title }}</h2>
            <div style="color: #4b5563; line-height: 1.8;">
              <p v-for="(paragraph, pIndex) in section.content" :key="pIndex" style="margin-bottom: 15px;">
                {{ paragraph }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </header>
    
  
      <!-- Footer -->
      <footer style="background: linear-gradient(to bottom right, #2563eb, #1e40af); color: white; padding: 60px 0; text-align: center;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 40px; gap: 30px;">
            <div style="text-align: left; width: 25%;">
              <div class="logo" style="display: flex; align-items: center; margin-bottom: 25px;">
                <img src="/media/misc/mobilelog.png" alt="BluCarra Logo" style="width: 60px; margin-right: 15px;" />
                <span style="font-size: 28px; font-weight: bold; color: white;">BluCarra</span>
              </div>
              <p style="color: #e0e7ff; font-size: 0.95rem; line-height: 1.7;">
                Solusi terbaik untuk kebutuhan rental mobil Anda. Mudah, aman, dan terpercaya dengan teknologi terkini.
              </p>
            </div>
  
            <div style="text-align: left;">
              <h4 style="margin-bottom: 25px; color: #93C5FD; font-size: 1.2rem;">Kontak</h4>
              <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 15px; display: flex; align-items: center; font-size: 0.95rem;">
                  <img src="/media/misc/phone.png" alt="Phone" style="width: 20px; margin-right: 12px;" />
                  +62 812-3456-7890
                </li>
                <li style="margin-bottom: 15px; display: flex; align-items: center; font-size: 0.95rem;">
                  <img src="/media/misc/email.png" alt="Email" style="width: 20px; margin-right: 12px;" />
                  blucarra@gmail.com
                </li>
              </ul>
            </div>
  
            <div style="text-align: left;">
              <h4 style="margin-bottom: 25px; color: #93C5FD; font-size: 1.2rem;">Temukan Kami</h4>
              <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                <a href="#" style="color: white; text-decoration: none; transition: transform 0.3s;">
                  <img src="/media/misc/facebook.png" alt="Facebook" style="width: 28px;" />
                </a>
                <a href="#" style="color: white; text-decoration: none; transition: transform 0.3s;">
                  <img src="/media/misc/instagram.png" alt="Instagram" style="width: 28px;" />
                </a>
              </div>
            </div>
          </div>
  
          <div style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 25px; text-align: center;">
            <p style="font-size: 0.9rem; color: #e0e7ff;">
              &copy; 2024 BluCarra. Hak Cipta Dilindungi.
            </p>
          </div>
        </div>
      </footer>
    </div>
  </template>
  
  <script>
  export default {
    name: 'TermsPrivacyPage',
    data() {
      return {
        selectedLanguage: 'id',
        activeTab: 'terms',
        isNavbarVisible: true,
        lastScrollTop: 0,
        termsSections: [
          {
            title: "1. Ketentuan Umum",
            content: [
              "BluCarra adalah platform rental mobil online yang mempertemukan penyedia jasa rental mobil dengan pengguna yang membutuhkan layanan rental mobil.",
              "Dengan menggunakan layanan BluCarra, Anda secara otomatis menyetujui seluruh syarat dan ketentuan yang berlaku.",
              "BluCarra berhak untuk mengubah syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan terlebih dahulu."
            ]
          },
          {
            title: "2. Persyaratan Pengguna",
            content: [
              "Pengguna wajib berusia minimal 18 tahun dan memiliki KTP yang masih berlaku.",
              "Pengguna wajib memiliki Surat Izin Mengemudi (SIM) yang masih berlaku sesuai dengan jenis kendaraan yang akan disewa.",
              "Pengguna bertanggung jawab atas kebenaran dan keakuratan data yang diberikan saat pendaftaran.",
              "BluCarra berhak menolak atau membatalkan pendaftaran pengguna yang tidak memenuhi persyaratan atau memberikan data palsu."
            ]
          },
          { 
            title: "3. Pemesanan dan Pembayaran",
            content: [
              "Pemesanan mobil hanya dapat dilakukan melalui aplikasi resmi BluCarra.",
              "Harga sewa yang tercantum sudah termasuk asuransi dasar kendaraan.",
              "Pembayaran dapat dilakukan melalui metode pembayaran yang tersedia di aplikasi.",
              "Pembatalan pemesanan akan dikenakan biaya sesuai dengan ketentuan yang berlaku.",
              "BluCarra berhak membatalkan pemesanan apabila ditemukan indikasi penipuan atau pelanggaran terhadap syarat dan ketentuan."
            ]
          },
          {
            title: "4. Penggunaan Kendaraan",
            content: [
              "Pengguna wajib memeriksa kondisi kendaraan sebelum dan sesudah penggunaan.",
              "Pengguna bertanggung jawab atas kerusakan yang terjadi selama masa sewa.",
              "Dilarang menggunakan kendaraan untuk kegiatan ilegal atau yang melanggar hukum.",
              "Pengguna wajib mematuhi seluruh peraturan lalu lintas yang berlaku.",
              "Perpanjangan masa sewa harus dikonfirmasi minimal 6 jam sebelum masa sewa berakhir."
            ]
          },
          {
            title: "5. Asuransi dan Pertanggungan",
            content: [
              "Setiap kendaraan dilindungi oleh asuransi comprehensive.",
              "Pengguna bertanggung jawab atas biaya pertanggungan sendiri (deductible) dalam hal terjadi kecelakaan.",
              "Kerusakan akibat kelalaian pengguna tidak ditanggung oleh asuransi.",
              "Prosedur klaim asuransi harus dilakukan sesuai dengan ketentuan yang berlaku."
            ]
          }
        ],
        privacyPolicySections: [
          {
            title: "1. Pengumpulan Data",
            content: [
              "Kami mengumpulkan data pribadi Anda termasuk nama, alamat, nomor telepon, email, dan dokumen identitas.",
              "Data lokasi dikumpulkan untuk memudahkan proses pengantaran dan pengembalian kendaraan.",
              "Informasi pembayaran seperti detail kartu kredit dienkripsi dan disimpan dengan standar keamanan tinggi.",
              "Kami juga mengumpulkan data penggunaan aplikasi untuk meningkatkan layanan kami."
            ]
          },
          {
            title: "2. Penggunaan Data",
            content: [
              "Data pribadi digunakan untuk memproses pemesanan dan verifikasi identitas.",
              "Informasi kontak digunakan untuk mengirim konfirmasi pemesanan dan notifikasi penting.",
              "Data lokasi digunakan untuk mengoptimalkan layanan pengantaran dan pengembalian.",
              "Kami menganalisis data penggunaan untuk meningkatkan kualitas layanan dan pengalaman pengguna."
            ]
          },
          {
            title: "3. Perlindungan Data",
            content: [
              "Kami menerapkan enkripsi end-to-end untuk melindungi data sensitif.",
              "Akses ke data pribadi dibatasi hanya untuk karyawan yang berwenang.",
              "Sistem keamanan kami secara rutin diperbarui untuk mencegah kebocoran data.",
              "Backup data dilakukan secara berkala dengan protokol keamanan tinggi."
            ]
          },
          {
            title: "4. Berbagi Data",
            content: [
              "Data dapat dibagikan dengan mitra rental mobil untuk keperluan pemesanan.",
              "Kami tidak menjual atau menyewakan data pribadi kepada pihak ketiga.",
              "Pengungkapan data kepada pihak berwenang hanya dilakukan sesuai ketentuan hukum.",
              "Mitra kami diwajibkan untuk mematuhi standar keamanan data yang sama."
            ]
          },
          {
            title: "5. Hak Pengguna",
            content: [
              "Anda berhak mengakses dan memperbarui data pribadi Anda.",
              "Permintaan penghapusan data dapat diajukan melalui layanan pelanggan.",
              "Anda dapat memilih untuk tidak menerima komunikasi pemasaran.",
              "Anda dapat mengajukan keluhan terkait penggunaan data pribadi.",
            "Perubahan kebijakan privasi akan diberitahukan melalui email atau notifikasi aplikasi."
          ]
        }
      ]
    }
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
    
    // Set active tab based on URL hash if present
    const hash = window.location.hash;
    if (hash === '#privacy') {
      this.activeTab = 'privacy';
    } else if (hash === '#terms') {
      this.activeTab = 'terms';
    }
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    handleScroll() {
      const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
      
      // Show/hide navbar based on scroll direction
      if (currentScrollTop > this.lastScrollTop) {
        this.isNavbarVisible = false;
      } else {
        this.isNavbarVisible = true;
      }
      
      this.lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
    },
    changeLanguage() {
      // Handle language change
      console.log(`Language changed to: ${this.selectedLanguage}`);
      // Add implementation for language switching
    }
  },
  watch: {
    activeTab(newTab) {
      // Update URL hash when tab changes
      window.location.hash = newTab;
    }
  }
}
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

button {
  outline: none;
}

button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
}

.nav-buttons a {
  position: relative;
}

.nav-buttons a::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: -5px;
  left: 0;
  background-color: white;
  transition: width 0.3s ease-in-out;
  border-radius: 8px;
}

.nav-buttons a:hover::after {
  width: 100%;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .nav-buttons {
    display: none;
  }
  
  .container {
    padding: 0 20px;
  }
  
  h1 {
    font-size: 2rem;
  }
  
  .terms-privacy-page {
    padding-top: 60px;
  }
}

@media (max-width: 480px) {
  button {
    padding: 10px 20px !important;
    font-size: 14px;
  }
  
  h1 {
    font-size: 1.8rem;
  }
  
  .logo {
    font-size: 20px;
  }
}
</style>