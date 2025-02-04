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
          <h3 class="card-title fw-bold">Statistik Rental & Pengguna</h3>
          <!-- <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light" @click="exportData">
              Export Data
            </button>
          </div> -->
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
import Chart from 'chart.js/auto';
import ApexCharts from 'apexcharts';
import { format, parseISO } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';

export default {
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
        // Fetch rental data
        const rentalResponse = await axios.get('/penyewaan/get');
        const rentals = rentalResponse.data.data;
        this.rentalData = rentals;

        // Fetch user data 
        const userResponse = await axios.get('master/users'); // Adjust endpoint as needed
        const users = userResponse.data.data;
        this.userData = users;

        this.stats.activeRentals = rentals.filter(rental => rental.status === 'aktif').length;
        this.stats.completedRentals = rentals.filter(rental => rental.status === 'selesai').length;

        const uniqueCustomers = new Set(rentals.map(rental => rental.user_id));
        this.stats.totalCustomers = uniqueCustomers.size;

        this.stats.totalRevenue = rentals
          .filter(rental => rental.status === 'selesai')
          .reduce((sum, rental) => sum + (rental.total_biaya || 0), 0);

        this.initializeChart();

      } catch (error) {
        console.error('Error fetching dashboard stats:', error);
      } finally {
        this.loading = false;
      }
    },

    processChartData() {
      const monthlyData = {};

      // Process rental data
      this.rentalData.forEach(rental => {
        const date = parseISO(rental.tanggal_mulai);
        const monthYear = format(date, 'yyyy-MM');

        if (!monthlyData[monthYear]) {
          monthlyData[monthYear] = {
            totalRentals: 0
          };
        }

        monthlyData[monthYear].totalRentals++;
      });

      // Process user data (only users with 'user' role)
      const userRoleUsers = this.userData.filter(user =>
        user.roles && user.roles.some(role => role.name === 'user')
      );

      userRoleUsers.forEach(user => {
        const date = parseISO(user.created_at);
        const monthYear = format(date, 'yyyy-MM');

        if (!monthlyData[monthYear]) {
          monthlyData[monthYear] = {
            totalRentals: 0
          };
        }
      });

      const sortedMonths = Object.keys(monthlyData).sort();

      return {
        labels: sortedMonths.map(month => {
          const [year, monthNum] = month.split('-');
          return format(new Date(year, monthNum - 1), 'MMM', { locale: idLocale });
        }),
        totalRentals: sortedMonths.map(month => monthlyData[month].totalRentals),
        userCount: sortedMonths.map(month =>
          this.userData.filter(user =>
            user.roles && user.roles.some(role => role.name === 'user') &&
            format(parseISO(user.created_at), 'yyyy-MM') === month
          ).length
        )
      };
    },



    initializeChart() {
      if (this.chart) {
        this.chart.destroy();
      }

      const chartData = this.processChartData();

      const options = {
        series: [
          {
            name: 'Total Penyewaan',
            data: chartData.totalRentals
          },
          {
            name: 'Jumlah Customer',
            data: chartData.userCount
          }
        ],
        chart: {
          type: 'area',
          height: 350,
          toolbar: {
            show: false
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
          categories: chartData.labels,
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
            }
          }
        },
        tooltip: {
          theme: 'light'
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left'
        }
      };

      this.chart = new ApexCharts(this.$refs.apexChart, options);
      this.chart.render();
    },

    exportData() {
      console.log('Export data clicked');
    }
  },
  mounted() {
    this.fetchDashboardStats();
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