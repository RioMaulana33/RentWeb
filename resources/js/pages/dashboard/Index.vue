<template>
  <main class="dashboard-container">
 
    <!-- Modern Statistics Cards -->
    <div class="row g-5">
      <!-- Revenue Today Card -->
      <div class="col-xl-3 col-md-6">
        <div class="card h-100 shadow-sm hover-elevate-up">
          <div class="card-body">
            <div class="d-flex flex-column">
              <!-- Icon with Gradient Background -->
              <div class="symbol symbol-60px mb-6">
                <span class="symbol-label bg-primary bg-opacity-10 rounded-3">
                  <i class="ki-duotone ki-car text-primary fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                  </i>
                </span>
              </div>
              
              <!-- Statistics -->
              <div class="d-flex flex-column mb-2">
                <span class="text-gray-600 fw-semibold fs-7 mb-1">Data Rental Aktif</span>
                <div class="d-flex align-items-center">
                  <!-- <span class="text-dark fw-bolder fs-2x me-2">{{ data.permohonanBaru }}</span> -->
                  <span class="badge badge-light-primary fs-base">
                    <i class="ki-duotone ki-arrow-up fs-7 text-success ms-n1"></i>
                    120
                  </span>
                </div>
              </div>
              
              <!-- Progress Bar -->
              <!-- <div class="progress h-3px bg-light-primary">
                <div class="progress-bar bg-primary" role="progressbar" :style="{ width: '76%' }"></div>
              </div> -->
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Stats Card -->
      <div class="col-xl-3 col-md-6">
        <div class="card h-100 shadow-sm hover-elevate-up">
          <div class="card-body">
            <div class="d-flex flex-column">
              <div class="symbol symbol-60px mb-6">
                <span class="symbol-label bg-success bg-opacity-10 rounded-3">
                  <i class="ki-duotone ki-profile-circle text-success fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                  </i>
                </span>
              </div>
              
              <div class="d-flex flex-column mb-2">
                <span class="text-gray-600 fw-semibold fs-7 mb-1">Total Customer</span>
                <div class="d-flex align-items-center">
                  <!-- <span class="text-dark fw-bolder fs-2x me-2">{{ data.permohonanDiproses }}</span> -->
                  <span class="badge badge-light-success fs-base">
                    <i class="ki-duotone ki-arrow-down fs-5 text-danger ms-n1"></i>
                    170
                  </span>
                </div>
              </div>
              
              <!-- <div class="progress h-3px bg-light-success">
                <div class="progress-bar bg-success" role="progressbar" :style="{ width: '84%' }"></div>
              </div> -->
            </div>
          </div>
        </div>
      </div>

      <!-- Completed Rentals Card -->
      <div class="col-xl-3 col-md-6">
        <div class="card h-100 shadow-sm hover-elevate-up">
          <div class="card-body">
            <div class="d-flex flex-column">
              <div class="symbol symbol-60px mb-6">
                <span class="symbol-label bg-info bg-opacity-10 rounded-3">
                  <i class="ki-duotone ki-check text-info fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                  </i>
                </span>
              </div>
              
              <div class="d-flex flex-column mb-2">
                <span class="text-gray-600 fw-semibold fs-7 mb-1">Rental Selesai</span>
                <div class="d-flex align-items-center">
                  <!-- <span class="text-dark fw-bolder fs-2x me-2">{{ data.permohonanSelesai }}</span> -->
                  <span class="badge badge-light-info fs-base">
                    <i class="ki-duotone ki- fs-5 text-info ms-n1"></i>
                    200
                  </span>
                </div>
              </div>
              
              <!-- <div class="progress h-3px bg-light-info">
                <div class="progress-bar bg-info" role="progressbar" :style="{ width: '92%' }"></div>
              </div> -->
            </div>
          </div>
        </div>
      </div>

      <!-- Total Revenue Card -->
      <div class="col-xl-3 col-md-6">
        <div class="card h-100 shadow-sm hover-elevate-up">
          <div class="card-body">
            <div class="d-flex flex-column">
              <div class="symbol symbol-60px mb-6">
                <span class="symbol-label bg-warning bg-opacity-10 rounded-3">
                  <i class="ki-duotone ki-dollar text-warning fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                  </i>
                </span>
              </div>
              
              <div class="d-flex flex-column mb-2">
                <span class="text-gray-600 fw-semibold fs-7 mb-1">Total Pendapatan</span>
                <div class="d-flex align-items-center">
                  <!-- <span class="text-dark fw-bolder fs-2x me-2">{{ formatCurrency(data.permohonanTotal) }}</span> -->
                  <span class="badge badge-light-warning fs-base">
                    <i class="ki-duotone ki-arrow-up fs-5 text-success ms-n1"></i>
                    {{ formatCurrency(data.permohonanTotal) }}
                  </span>
                </div>
              </div>
              
              <!-- <div class="progress h-3px bg-light-warning">
                <div class="progress-bar bg-warning" role="progressbar" :style="{ width: '88%' }"></div>
              </div> -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Additional Statistics Section -->
    <div class="row g-5 mt-2">
      <!-- Chart Card -->
      <div class="col-xl-8">
        <div class="card shadow-sm">
          <div class="card-header border-0">
            <h3 class="card-title fw-bold text-dark">Statistik Rental</h3>
            <div class="card-toolbar">
              <button type="button" class="btn btn-sm btn-light">
                Export Data
              </button>
            </div>
          </div>
          <div class="card-body pt-4">
            <!-- Add your chart component here -->
            <div class="mixed-widget-10-chart" style="height: 350px">
              <!-- Chart will be rendered here -->
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities Card -->
      <div class="col-xl-4">
        <div class="card shadow-sm">
          <div class="card-header border-0">
            <h3 class="card-title fw-bold text-dark">Aktivitas Terkini</h3>
          </div>
          <div class="card-body pt-4">
            <div class="timeline-label">
              <!-- Timeline Item 1 -->
              <div class="timeline-item d-flex">
                <div class="timeline-label fw-bold text-gray-800 fs-7">08:42</div>
                <div class="timeline-badge">
                  <i class="ki-duotone ki-abstract-26 fs-1 text-success">
                    <span class="path1"></span>
                    <span class="path2"></span>
                  </i>
                </div>
                <div class="timeline-content ps-3">
                  <span class="fw-bold text-gray-800">Rental Baru</span>
                  <p class="text-gray-600 fs-7 mt-1 mb-0">Customer: John Doe<br>Mobil: Toyota Avanza</p>
                </div>
              </div>

              <!-- Timeline Item 2 -->
              <div class="timeline-item d-flex mt-4">
                <div class="timeline-label fw-bold text-gray-800 fs-7">10:00</div>
                <div class="timeline-badge">
                  <i class="ki-duotone ki-abstract-26 fs-1 text-primary">
                    <span class="path1"></span>
                    <span class="path2"></span>
                  </i>
                </div>
                <div class="timeline-content ps-3">
                  <span class="fw-bold text-gray-800">Pengembalian</span>
                  <p class="text-gray-600 fs-7 mt-1 mb-0">Customer: Sarah Smith<br>Mobil: Honda Jazz</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
export default {
  data() {
    return {
      tahun: null,
      tahuns: [2022, 2023, 2024],
      data: {
        permohonanBaru: 0,
        permohonanDiproses: 0,
        permohonanSelesai: 0,
        permohonanTotal: 0
      },
      user: {
        has_tagihan: false
      }
    }
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      }).format(value)
    },
    async fetchData() {
      try {
        // Implement your API call here
        // const response = await axios.get(`/api/dashboard?tahun=${this.tahun}`);
        // this.data = response.data;
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    }
  },
  mounted() {
    this.fetchData();
  },
  watch: {
    tahun() {
      this.fetchData();
    }
  }
}
</script>

<style>
.hover-elevate-up {
  transition: transform 0.3s ease;
}

.hover-elevate-up:hover {
  transform: translateY(-5px);
}

.timeline-label {
  position: relative;
  padding-left: 25px;
}

.timeline-label::before {
  content: '';
  position: absolute;
  left: 13px;
  width: 2px;
  top: 5px;
  bottom: 5px;
  background-color: #E4E6EF;
}

.timeline-badge {
  position: absolute;
  left: 0;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border: 2px solid #E4E6EF;
  z-index: 1;
}
</style>