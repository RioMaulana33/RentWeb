<template>
  <div class="blucarra-landing-page" style="font-family: 'Poppins', sans-serif ;">
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
    <a @click="scrollToSection('why-blucarra')"
      style="color: white; text-decoration: none; margin: 0 20px; transition: color 0.3s ease; cursor: pointer;">
      Mengapa Memilih BluCarra
    </a>
    <a @click="scrollToSection('tutorial')"
      style="color: white; text-decoration: none; margin: 0 20px; transition: color 0.3s ease; cursor: pointer;">
      Cara Menyewa
    </a>
    <a @click="scrollToSection('testimonials')"
      style="color: white; text-decoration: none; margin: 0 20px; transition: color 0.3s ease; cursor: pointer;">
      Apa Kata Pengguna
    </a>
  </div>

      <div class="language-selector" style="display: flex; align-items: center;">
        <select v-model="selectedLanguage" @change="changeLanguage"
          style="background-color: #3b82f6; color: white; border: none; padding: 10px 8px;border-radius: 20px;">
          <option value="id" style="background-color: white; color: black;">🇮🇩 Indonesia</option>
          <option value="en" style="background-color: white; color: black;">🇬🇧 English</option>
        </select>
      </div>
    </nav>

    <!-- Hero Section -->
    <header
      style="background: linear-gradient(to bottom right, #93C5FD, #3B82F6); min-height: 100vh; display: flex; align-items: center; padding-top: 80px; padding-bottom: 80px; position: relative; overflow: hidden;">
      <div class="wave-container"
        style="position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none;">
        <svg viewBox="0 0 1440 320" preserveAspectRatio="none"
          style="position: absolute; bottom: 0; width: 100%; height: 30%; transform: rotate(180deg);">
          <path d="M0,0 
               C480,100 960,220 1440,100 
               L1440,320 
               L0,320 Z" fill="#1D4ED8" fill-opacity="0.4">
          </path>
        </svg>
        <svg viewBox="0 0 1440 320" preserveAspectRatio="none"
          style="position: absolute; bottom: 0; width: 100%; height: 40%; transform: rotate(180deg);">
          <path d="M0,50 
               C480,150 960,270 1440,150 
               L0,320 Z" fill="#3B82F6" fill-opacity="0.3">
          </path>
        </svg>
      </div>

      <div class="container"
        style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; z-index: 2; position: relative;">
        <div style="width: 50%; text-align: left; padding: 0 15px; ">
          <h1 style="color: #f5f5f5; font-size: 2.5rem; margin-bottom: 10px; line-height: 1.2; ">
            Aplikasi Rental Mobil
            <span style="color: blue; font-weight: bold;">BluCarra</span>
          </h1>
          <p style="color: #f3f4f6; font-size: 1.1rem; margin-bottom: 25px; line-height: 1.6;">
            BluCarra hadir untuk memudahkan Anda menyewa mobil kapan pun dan di mana pun,
            dengan pilihan mobil yang lengkap dan harga yang terjangkau
          </p>

          <p style="font-weight: bold;">Download BluCarra di</p>
          <a href="https://play.google.com/store/apps/details?id=com.blucarra" target="_blank"
            style="display: inline-block; transition: transform 0.3s ease; margin-right: 20px;">
            <img src="/media/misc/download-google-play.png" alt="Download di Google Play"
              style="width: 200px; height: auto; display: block;">
          </a>

        </div>
        <div style="width: 50%; text-align: center; position: relative; z-index: 3;">
          <img src="/media/misc/vector-landing.png" alt="BluCarra Mobile App Preview"
            style="max-width: 100%; border-radius: 10px; position: relative;" />
        </div>
      </div>
    </header>

    <!-- Features Section -->
    <section id="why-blucarra" style="padding: 100px 0; background-color: white; position: relative; overflow: hidden;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
      <h2 style="color: #1e40af; font-size: 2rem; margin-bottom: 60px;">
        Mengapa Memilih BluCarra?
      </h2>
      <div style="display: flex; justify-content: space-between; gap: 30px; position: relative;">
        <div v-for="(feature, index) in features" :key="index"
          class="feature-card"
          style="
            width: 30%; 
            background-color: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            transition: all 0.3s ease;
            border: 1px solid #e6e6e6;
          ">
          <div style="margin-bottom: 20px; display: flex; justify-content: center; align-items: center;">
            <div style="
              background-color: #f0f4ff; 
              border-radius: 50%; 
              padding: 20px; 
              box-shadow: 0 5px 15px rgba(59,130,246,0.2); 
              display: inline-block;
            ">
              <img :src="feature.icon" alt="Feature Icon" style="width: 120px; height: 120px; object-fit: contain;" />
            </div>
          </div>
          
          <h3 style="color: #1e40af; margin-bottom: 15px;">
            {{ feature.title }}
          </h3>
          <p style="color: #4b5563;">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>

    <!-- Tutorial Section with Wave Decoration -->
    <section id="tutorial" style="padding: 100px 0; background-color: white; position: relative; overflow: hidden;">
    <div style="max-width: 1200px; margin: 0 auto; position: relative; z-index: 2;">
      <h2 style="color: #1e40af; font-size: 2rem; text-align: center; margin-bottom: 60px;">
        Cara Menyewa Mobil di BluCarra
      </h2>
      <div style="display: flex; align-items: center; gap: 50px;">
        <div style="width: 50%;">
          <div class="tutorial-steps">
            <!-- Card 1 -->
            <div class="tutorial-card">
             
              <div class="step-content">
                <div class="step-number">01</div>
                <h3>Download Aplikasi</h3>
                <p>Unduh BluCarra di Google Play Store</p>
              </div>
            </div>

            <!-- Card 2 -->
            <div class="tutorial-card">           
              <div class="step-content">
                <div class="step-number">02</div>
                <h3>Buat Akun</h3>
                <p>Daftar dengan email atau nomor telepon Anda</p>
              </div>
            </div>

            <!-- Card 3 -->
            <div class="tutorial-card">              
              <div class="step-content">
                <div class="step-number">03</div>
                <h3>Pilih Mobil</h3>
                <p>Telusuri berbagai pilihan mobil sesuai kebutuhan</p>
              </div>
            </div>

            <!-- Card 4 -->
            <div class="tutorial-card">              
              <div class="step-content">
                <div class="step-number">04</div>
                <h3>Booking & Konfirmasi</h3>
                <p>Pilih tanggal, konfirmasi pesanan, dan lakukan pembayaran</p>
              </div>
            </div>
          </div>
        </div>
        <div style="width: 36%; text-align: center;">
          <img src="media/misc/mobile-preview.png" alt="BluCarra App Tutorial Preview" class="tutorial-image"/>
          <div class="blur-circle-1"></div>
          <div class="blur-circle-2"></div>
        </div>
      </div>
    </div>
  </section>

    <!-- Testimonials Section with Wave Decoration -->
    <section id="testimonials"
      style="padding: 100px 0; background-color: #dbeafe; position: relative; overflow: hidden;">
      <div style="max-width: 1200px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
        <h2 style="color: #1e40af; font-size: 2rem; margin-bottom: 60px;">
          Apa Kata Pengguna
        </h2>
        <div style="display: flex; justify-content: space-between; gap: 30px;">
          <div v-for="(testimonial, index) in testimonials" :key="index"
            style="width: 48%; background-color: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <p style="font-style: italic; margin-bottom: 20px; color: #4b5563;">
              "{{ testimonial.review }}"
            </p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <h4 style="color: #1e40af;">
                {{ testimonial.name }}
              </h4>
              <div style="color: #eab308;">
                <span v-for="n in 5" :key="n">
                  {{ n <= testimonial.rating ? '★' : '☆' }} </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <!-- Footer -->
    <footer
      style="background: linear-gradient(to bottom right, #2563eb, #1e40af); color: white; padding: 60px 0; text-align: center;">
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
            <h4 style="margin-bottom: 25px; color: #93C5FD; font-size: 1.2rem;">Jelajahi</h4>
            <ul style="list-style: none; padding: 0;">
              <li style="margin-bottom: 15px;">
                <a @click="scrollToSection('why-blucarra')"
                  style="color: white; text-decoration: none; transition: color 0.3s; position: relative; padding-left: 20px; font-size: 0.95rem;">
                  <span
                    style="position: absolute; left: 0; top: 6px; width: 5px; height: 5px; background-color: #60A5FA; border-radius: 50%;"></span>
                  Mengapa BluCarra
                </a>
              </li>
              <li style="margin-bottom: 15px;">
                <a @click="scrollToSection('tutorial')"
                  style="color: white; text-decoration: none; transition: color 0.3s; position: relative; padding-left: 20px; font-size: 0.95rem;">
                  <span
                    style="position: absolute; left: 0; top: 6px; width: 5px; height: 5px; background-color: #60A5FA; border-radius: 50%;"></span>
                  Cara Menyewa
                </a>
              </li>
              <li style="margin-bottom: 15px;">
                <a @click="scrollToSection('testimonials')"
                  style="color: white; text-decoration: none; transition: color 0.3s; position: relative; padding-left: 20px; font-size: 0.95rem;">
                  <span
                    style="position: absolute; left: 0; top: 6px; width: 5px; height: 5px; background-color: #60A5FA; border-radius: 50%;"></span>
                  Apa Kata Pengguna
                </a>
              </li>
            </ul>
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
            <div>
              <p style="color: #93C5FD; margin-bottom: 6px; font-size: 0.95rem;">Download Aplikasi di:</p>
              <a href="https://play.google.com/store/apps/details?id=com.blucarra" target="_blank">
                <img src="/media/misc/download-google-play.png" alt="Google Play" style="width: 180px;" />
              </a>
            </div>
          </div>
        </div>

        <div
          style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 25px; margin-top: 25px; display: flex; justify-content: space-between; align-items: center;">
          <p style="font-size: 0.9rem; color: #e0e7ff;">
            &copy; 2024 BluCarra. Hak Cipta Dilindungi.
          </p>
          <div>
            <a href="#" style="color: #93C5FD; text-decoration: none; margin-right: 20px; font-size: 0.9rem;">Kebijakan
              Privasi</a>
            <a href="/landing/syarat-ketentuan" style="color: #93C5FD; text-decoration: none; font-size: 0.9rem;">Syarat & Ketentuan</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'BluCarraLandingPage',
  data() {
    return {
      selectedLanguage: 'id',
      lastScrollTop: 0,
      isNavbarVisible: true,
      features: [
        {
          icon: '/media/misc/vector-landing.png',
          title: 'Berbagai Pilihan Mobil',
          description: 'Tersedia beragam pilihan mobil dari berbagai merk dan tipe'
        },
        {
          icon: '/media/misc/vector-secure.png',
          title: 'Keamanan Terjamin',
          description: 'Proses sewa aman, transparan, dan terpercaya'
        },
        {
          icon: '/media/misc/vector-time.png',
          title: 'Proses Cepat',
          description: 'Booking dalam hitungan menit tanpa ribet'
        }
      ],
      testimonials: [
        {
          name: 'Budi Setiawan',
          review: 'BluCarra sangat membantu saya dalam menyewa mobil dengan cepat dan mudah!',
          rating: 5
        },
        {
          name: 'Ani Rahayu',
          review: 'Aplikasi yang user-friendly dan pilihan mobilnya lengkap banget!',
          rating: 5
        }
      ]
    }
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    scrollToSection(sectionId) {
      const targetSection = document.getElementById(sectionId);
      if (targetSection) {
        const navbarHeight = 80; 
        const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
        
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
      }
    },
    handleScroll() {
      const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

      if (currentScrollTop > this.lastScrollTop) {
        this.isNavbarVisible = false;
      } else {
        this.isNavbarVisible = true;
      }

      this.lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
    },
    changeLanguage() {
      console.log(`Language changed to: ${this.selectedLanguage}`);
    }
  }
}
</script>

<style scoped>
.nav-buttons a {
  position: relative;
  text-decoration: none;
  color: white;
  transition: color 0.3s ease;
}

.nav-buttons a::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: -5px;
  border-radius: 8px;
  left: 0;
  background-color: white; 
  transition: width 0.3s ease-in-out;
}

.nav-buttons a:hover::after {
  width: 100%;
}

.feature-card {
  transition: all 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-15px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}
.tutorial-steps {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.tutorial-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 1px solid #e5e7eb;
  position: relative;
  overflow: hidden;
}

.tutorial-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 15px rgba(37, 99, 235, 0.1);
  border-color: #93c5fd;
}

.step-icon {
  width: 60px;
  height: 60px;
  background: #eff6ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.step-icon img {
  width: 30px;
  height: 30px;
  object-fit: contain;
}

.step-content {
  flex-grow: 1;
  position: relative;
}

.step-number {
  position: absolute;
  right: 0;
  top: 0;
  font-size: 1.5rem;
  font-weight: bold;
  color: #bfdbfe;
  opacity: 0.5;
}

.step-content h3 {
  color: #1e40af;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 8px;
}

.step-content p {
  color: #6b7280;
  font-size: 0.95rem;
  line-height: 1.5;
  margin: 0;
}

.tutorial-image {
  max-width: 100%;
  border-radius: 20px;
  box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 2;
}

.blur-circle-1 {
  position: absolute;
  width: 300px;
  height: 300px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 50%;
  filter: blur(40px);
  top: -50px;
  right: -50px;
  z-index: 1;
}

.blur-circle-2 {
  position: absolute;
  width: 250px;
  height: 250px;
  background: rgba(147, 197, 253, 0.15);
  border-radius: 50%;
  filter: blur(40px);
  bottom: -30px;
  left: -30px;
  z-index: 1;
}

@media (max-width: 768px) {
  div[style*="display: flex; align-items: center;"] {
    flex-direction: column;
  }

  div[style*="width: 50%"] {
    width: 100%;
  }

  .tutorial-card {
    width: 90%;
    margin: 0 auto;
  }
}

</style>