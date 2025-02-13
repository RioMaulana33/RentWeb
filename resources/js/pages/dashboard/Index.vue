<template>
  <main class="dashboard-container">
    <!-- Statistics Cards Row -->
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
                    {{ formatCurrency(stats.totalRevenue) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Chart Section -->
    <div class="row g-5 mt-2 mb-8">
      <div class="card shadow-sm">
        <div class="card-header border-0">
          <h3 class="card-title fw-bold">Statistik Rental & Pengguna</h3>
        </div>
        <div class="card-body pt-4">
          <div ref="apexChart" style="min-height: 350px;"></div>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import axios from "@/libs/axios";
import ApexCharts from 'apexcharts';
import { format, parseISO, startOfYear, endOfYear } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';

export default {
  name: 'DashboardComponent',
  
  data() {
    return {
      stats: {
        activeRentals: 0,
        totalCustomers: 0,
        completedRentals: 0,
        totalRevenue: 0
      },
      loading: true,
      chart: null,
      rentalData: [],
      userData: []
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

    async fetchDashboardStats() {
      try {
        const [rentalResponse, userResponse] = await Promise.all([
          axios.get('/penyewaan/get'),
          axios.get('master/users')
        ]);

        this.rentalData = rentalResponse.data.data;
        this.userData = userResponse.data.data;

        // Calculate statistics
        this.calculateStats();
        // Initialize or update chart
        this.initializeChart();

      } catch (error) {
        console.error('Error fetching dashboard stats:', error);
      } finally {
        this.loading = false;
      }
    },

    calculateStats() {
      // Calculate active rentals
      this.stats.activeRentals = this.rentalData.filter(
        rental => rental.status === 'aktif'
      ).length;

      // Calculate completed rentals
      this.stats.completedRentals = this.rentalData.filter(
        rental => rental.status === 'selesai'
      ).length;

      // Calculate unique customers
      const uniqueCustomers = new Set(
        this.rentalData.map(rental => rental.user_id)
      );
      this.stats.totalCustomers = uniqueCustomers.size;

      // Calculate total revenue
      this.stats.totalRevenue = this.rentalData
        .filter(rental => rental.status === 'selesai')
        .reduce((sum, rental) => sum + (rental.total_biaya || 0), 0);
    },

    generateMonthlyData() {
      const currentYear = new Date().getFullYear();
      const monthlyData = {};

      // Initialize all months
      for (let month = 0; month < 12; month++) {
        const date = new Date(currentYear, month, 1);
        const monthKey = format(date, 'yyyy-MM');
        monthlyData[monthKey] = {
          totalRentals: 0,
          userCount: 0
        };
      }

      // Process rental data for current year
      this.rentalData.forEach(rental => {
        const date = parseISO(rental.tanggal_mulai);
        if (date.getFullYear() === currentYear) {
          const monthKey = format(date, 'yyyy-MM');
          if (monthlyData[monthKey]) {
            monthlyData[monthKey].totalRentals++;
          }
        }
      });

      // Process user data for current year
      const userRoleUsers = this.userData.filter(user =>
        user.roles?.some(role => role.name === 'user')
      );

      userRoleUsers.forEach(user => {
        const date = parseISO(user.created_at);
        if (date.getFullYear() === currentYear) {
          const monthKey = format(date, 'yyyy-MM');
          if (monthlyData[monthKey]) {
            monthlyData[monthKey].userCount++;
          }
        }
      });

      return monthlyData;
    },

    initializeChart() {
      if (this.chart) {
        this.chart.destroy();
      }

      const monthlyData = this.generateMonthlyData();
      const sortedMonths = Object.keys(monthlyData).sort();

      const options = {
        series: [
          {
            name: 'Total Penyewaan',
            data: sortedMonths.map(month => monthlyData[month].totalRentals)
          },
          {
            name: 'Jumlah Customer',
            data: sortedMonths.map(month => monthlyData[month].userCount)
          }
        ],
        chart: {
          type: 'area',
          height: 350,
          toolbar: {
            show: false
          },
          zoom: {
            enabled: false
          }
        },
        colors: ['#3699FF', '#1BC5BD'],
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 3
        },
        xaxis: {
          categories: sortedMonths.map(month => 
            format(parseISO(month + '-01'), 'MMM', { locale: idLocale })
          ),
          labels: {
            style: {
              colors: '#6e6b7b'
            }
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: '#6e6b7b'
            },
            formatter: function(value) {
              return Math.round(value);
            }
          }
        },
        tooltip: {
          theme: 'light',
          x: {
            formatter: function(value, { series, seriesIndex, dataPointIndex }) {
              return format(parseISO(sortedMonths[dataPointIndex] + '-01'), 'MMMM yyyy', { locale: idLocale });
            }
          }
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left'
        },
        grid: {
          borderColor: '#f1f1f1',
          padding: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
          }
        },
        fill: {
          opacity: 0.3,
          type: 'gradient',
          gradient: {
            shade: 'light',
            type: 'vertical',
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 100]
          }
        }
      };

      this.chart = new ApexCharts(this.$refs.apexChart, options);
      this.chart.render();
    }
  },

  mounted() {
    this.fetchDashboardStats();
    // Refresh data every 5 minutes
    this.refreshInterval = setInterval(this.fetchDashboardStats, 300000);
  },

  beforeDestroy() {
    if (this.chart) {
      this.chart.destroy();
    }
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
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