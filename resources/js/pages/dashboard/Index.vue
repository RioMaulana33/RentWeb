<template>
  <main class="dashboard-container">

    <!-- Modern Statistics Cards -->
    <div class="row g-5">
      <!-- Active Rentals Card -->
      <div class="col-xl-3 col-md-6">
        <div class="card h-100 shadow-sm hover-elevate-up">
          <div class="card-body">
            <div class="d-flex flex-column">
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

              <div class="d-flex flex-column mb-2">
                <span class="text-gray-600 fw-semibold fs-7 mb-1">Data Rental Aktif</span>
                <div class="d-flex align-items-center">
                  <span class="badge badge-light-primary fs-base">
                    <i class="ki-duotone ki-arrow-up fs-7 text-success"></i>
                    {{ stats.activeRentals }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>


      <!-- Total Customers Card -->
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
                  <span class="badge badge-light-success fs-base">
                    <i class="ki-duotone ki-arrow-down fs-5 text-danger "></i>
                    {{ stats.totalCustomers }}
                  </span>
                </div>
              </div>
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
                  <span class="badge badge-light-info fs-base">
                    <i class="ki-duotone ki- fs-5 text-info "></i>
                    {{ stats.completedRentals }}
                  </span>
                </div>
              </div>
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
                  <span class="badge badge-light-warning fs-base">
                    <i class="ki-duotone ki-arrow-up fs-5 text-success "></i>
                    {{ formatCurrency(stats.totalRevenue) }}

                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Additional Statistics Section -->
    <div class="row g-5 mt-2 mb-8">
      <!-- Chart Card -->
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
          <canvas ref="mixedChart" height="350"></canvas>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import axios from "@/libs/axios";
import Chart from 'chart.js/auto';

export default {
  data() {
    return {
      stats: {
        activeRentals: 0,
        totalCustomers: 0,
        completedRentals: 0,
        totalRevenue: 0
      },
      previousStats: {
        activeRentals: 0,
        totalCustomers: 0,
        completedRentals: 0,
        totalRevenue: 0
      },
      loading: true,
      chart: null,
      rentalData: []
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

    calculatePercentageChange(current, previous) {
      if (previous === 0) return 0;
      return (((current - previous) / previous) * 100).toFixed(1);
    },

    async fetchDashboardStats() {
      try {
        // Fetch current stats
        const currentResponse = await axios.get('/penyewaan/get', {
          params: {
            per: 9999 // Get all records for accurate counting
          }
        });

        // Calculate stats from the response
        const rentals = currentResponse.data.data;
        this.rentalData = rentals; // Save for chart use

        this.stats.activeRentals = rentals.filter(rental => rental.status === 'aktif').length;
        this.stats.completedRentals = rentals.filter(rental => rental.status === 'selesai').length;

        // Get unique customers
        const uniqueCustomers = new Set(rentals.map(rental => rental.user_id));
        this.stats.totalCustomers = uniqueCustomers.size;

        // Calculate total revenue from completed rentals
        this.stats.totalRevenue = rentals
          .filter(rental => rental.status === 'selesai')
          .reduce((sum, rental) => sum + (rental.total_biaya || 0), 0);

        // Update chart after getting new data
        this.initializeChart();

      } catch (error) {
        console.error('Error fetching dashboard stats:', error);
      } finally {
        this.loading = false;
      }
    },

    processChartData() {
  // Group rentals by month
  const monthlyData = {};

  this.rentalData.forEach(rental => {
    const date = new Date(rental.tanggal_mulai);
    const monthYear = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

    if (!monthlyData[monthYear]) {
      monthlyData[monthYear] = {
        active: 0,
        completed: 0
      };
    }

    if (rental.status === 'aktif') {
      monthlyData[monthYear].active++;
    } else if (rental.status === 'selesai') {
      monthlyData[monthYear].completed++;
    }
  });

  // Sort months
  const sortedMonths = Object.keys(monthlyData).sort();

  return {
    labels: sortedMonths.map(month => {
      const [year, monthNum] = month.split('-');
      return `${new Date(year, monthNum - 1).toLocaleString('id-ID', { month: 'short' })}`;
    }),
    datasets: [
      {
        label: 'Rental Aktif',
        data: sortedMonths.map(month => monthlyData[month].active),
        borderColor: '#3699FF',
        backgroundColor: 'rgba(54, 153, 255, 0.2)',
        tension: 0.4,
        fill: true,
        pointRadius: 4,
        pointBackgroundColor: '#3699FF'
      },
      {
        label: 'Rental Selesai',
        data: sortedMonths.map(month => monthlyData[month].completed),
        borderColor: '#1BC5BD',
        backgroundColor: 'rgba(27, 197, 189, 0.2)',
        tension: 0.4,
        fill: true,
        pointRadius: 4,
        pointBackgroundColor: '#1BC5BD'
      }
    ]
  };
},

initializeChart() {
  if (this.chart) {
    this.chart.destroy();
  }

  const ctx = this.$refs.mixedChart.getContext('2d');
  const chartData = this.processChartData();

  this.chart = new Chart(ctx, {
    type: 'line',
    data: chartData,
    options: {
      responsive: true,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          position: 'top',
          align: 'start',
          labels: {
            usePointStyle: true,
            padding: 20,
            boxWidth: 10
          }
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          backgroundColor: 'white',
          titleColor: '#6e6b7b',
          bodyColor: '#6e6b7b',
          borderColor: '#e9ecef',
          borderWidth: 1,
          padding: 10,
          boxPadding: 5
        }
      },
      scales: {
        x: {
          grid: {
            display: true,
            drawBorder: false,
            color: '#f5f5f5'
          },
          ticks: {
            color: '#6e6b7b'
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            display: true,
            drawBorder: false,
            color: '#f5f5f5'
          },
          ticks: {
            color: '#6e6b7b',
            padding: 10,
            stepSize: 20
          }
        }
      }
    }
  });
},

    exportData() {
      // Implement export functionality if needed
      console.log('Export data clicked');
    }
  },
  mounted() {
    this.fetchDashboardStats();
    // Refresh stats and chart every 5 minutes
    setInterval(this.fetchDashboardStats, 300000);
  },
  beforeDestroy() {
    if (this.chart) {
      this.chart.destroy();
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