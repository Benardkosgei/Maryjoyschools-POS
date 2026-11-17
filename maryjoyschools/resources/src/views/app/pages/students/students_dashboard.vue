<template>
  <div class="main-content">
    <breadcumb :page="$t('Students_Dashboard')" :folder="$t('Students')"/>
    
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    
    <div v-else>
      <!-- Statistics Cards -->
      <b-row class="mb-4">
        <b-col xl="3" md="6" sm="6">
          <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
            <div class="card-body text-center">
              <i class="i-Student"></i>
              <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t('Total_Students') }}</p>
                <p class="text-primary text-24 line-height-1 mb-2">{{ dashboardData.statistics.total_students }}</p>
              </div>
            </div>
          </div>
        </b-col>

        <b-col xl="3" md="6" sm="6">
          <div class="card card-icon-bg card-icon-bg-success o-hidden mb-4">
            <div class="card-body text-center">
              <i class="i-Check"></i>
              <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t('Active_Students') }}</p>
                <p class="text-success text-24 line-height-1 mb-2">{{ dashboardData.statistics.active_students }}</p>
              </div>
            </div>
          </div>
        </b-col>

        <b-col xl="3" md="6" sm="6">
          <div class="card card-icon-bg card-icon-bg-warning o-hidden mb-4">
            <div class="card-body text-center">
              <i class="i-Money-2"></i>
              <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t('Total_Balance') }}</p>
                <p class="text-warning text-24 line-height-1 mb-2">{{ formatCurrency(dashboardData.statistics.total_account_balance) }}</p>
              </div>
            </div>
          </div>
        </b-col>

        <b-col xl="3" md="6" sm="6">
          <div class="card card-icon-bg card-icon-bg-info o-hidden mb-4">
            <div class="card-body text-center">
              <i class="i-Shopping-Cart"></i>
              <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t('Today_Sales') }}</p>
                <p class="text-info text-24 line-height-1 mb-2">{{ dashboardData.today.sales_count }}</p>
              </div>
            </div>
          </div>
        </b-col>
      </b-row>

      <!-- Alerts Row -->
      <b-row class="mb-4" v-if="dashboardData.alerts.low_balance_count > 0 || dashboardData.alerts.negative_balance_count > 0">
        <b-col md="6" v-if="dashboardData.alerts.low_balance_count > 0">
          <b-alert show variant="warning" class="mb-3">
            <h6 class="alert-heading">{{ $t('Low_Balance_Alert') }}</h6>
            <p class="mb-0">
              {{ dashboardData.alerts.low_balance_count }} {{ $t('students_have_low_balance') }}
              <b-button size="sm" variant="outline-warning" @click="viewLowBalanceStudents" class="ml-2">
                {{ $t('View_Details') }}
              </b-button>
            </p>
          </b-alert>
        </b-col>

        <b-col md="6" v-if="dashboardData.alerts.negative_balance_count > 0">
          <b-alert show variant="danger" class="mb-3">
            <h6 class="alert-heading">{{ $t('Negative_Balance_Alert') }}</h6>
            <p class="mb-0">
              {{ dashboardData.alerts.negative_balance_count }} {{ $t('students_have_negative_balance') }}
              <b-button size="sm" variant="outline-danger" @click="viewNegativeBalanceStudents" class="ml-2">
                {{ $t('View_Details') }}
              </b-button>
            </p>
          </b-alert>
        </b-col>
      </b-row>

      <!-- Charts and Tables Row -->
      <b-row>
        <!-- Sales Chart -->
        <b-col lg="8">
          <b-card class="mb-4">
            <b-card-header>
              <h5 class="card-title">{{ $t('Student_Sales_Chart') }}</h5>
              <div class="card-header-right">
                <b-button-group size="sm">
                  <b-button 
                    :variant="chartPeriod === 'week' ? 'primary' : 'outline-primary'"
                    @click="changeChartPeriod('week')"
                  >
                    {{ $t('Week') }}
                  </b-button>
                  <b-button 
                    :variant="chartPeriod === 'month' ? 'primary' : 'outline-primary'"
                    @click="changeChartPeriod('month')"
                  >
                    {{ $t('Month') }}
                  </b-button>
                  <b-button 
                    :variant="chartPeriod === 'year' ? 'primary' : 'outline-primary'"
                    @click="changeChartPeriod('year')"
                  >
                    {{ $t('Year') }}
                  </b-button>
                </b-button-group>
              </div>
            </b-card-header>
            <b-card-body>
              <div id="salesChart" style="height: 300px;"></div>
            </b-card-body>
          </b-card>
        </b-col>

        <!-- Statistics Breakdown -->
        <b-col lg="4">
          <b-card class="mb-4">
            <b-card-header>
              <h5 class="card-title">{{ $t('Students_by_Status') }}</h5>
            </b-card-header>
            <b-card-body>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span>{{ $t('Active') }}</span>
                <span class="badge badge-success">{{ dashboardData.statistics.active_students }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span>{{ $t('Inactive') }}</span>
                <span class="badge badge-warning">{{ dashboardData.statistics.inactive_students }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span>{{ $t('Graduated') }}</span>
                <span class="badge badge-info">{{ dashboardData.statistics.graduated_students }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <span>{{ $t('Transferred') }}</span>
                <span class="badge badge-secondary">{{ dashboardData.statistics.transferred_students }}</span>
              </div>
            </b-card-body>
          </b-card>

          <b-card class="mb-4">
            <b-card-header>
              <h5 class="card-title">{{ $t('Students_by_Class') }}</h5>
            </b-card-header>
            <b-card-body>
              <div 
                v-for="(count, className) in dashboardData.statistics.students_by_class" 
                :key="className"
                class="d-flex justify-content-between align-items-center mb-3"
              >
                <span>{{ className }}</span>
                <span class="badge badge-primary">{{ count }}</span>
              </div>
            </b-card-body>
          </b-card>
        </b-col>
      </b-row>

      <!-- Recent Transactions and Top Students -->
      <b-row>
        <!-- Recent Transactions -->
        <b-col lg="6">
          <b-card class="mb-4">
            <b-card-header>
              <h5 class="card-title">{{ $t('Recent_Transactions') }}</h5>
            </b-card-header>
            <b-card-body>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>{{ $t('Student') }}</th>
                      <th>{{ $t('Amount') }}</th>
                      <th>{{ $t('Date') }}</th>
                      <th>{{ $t('Status') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="transaction in dashboardData.recent_transactions" :key="transaction.id">
                      <td>
                        <div>
                          <strong>{{ transaction.client.name }}</strong>
                          <br>
                          <small class="text-muted">{{ transaction.client.student_id }}</small>
                        </div>
                      </td>
                      <td>{{ formatCurrency(transaction.GrandTotal) }}</td>
                      <td>{{ formatDate(transaction.date) }}</td>
                      <td>
                        <span 
                          :class="{
                            'badge badge-success': transaction.payment_statut === 'paid',
                            'badge badge-warning': transaction.payment_statut === 'partial',
                            'badge badge-danger': transaction.payment_statut === 'unpaid'
                          }"
                        >
                          {{ transaction.payment_statut }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </b-card-body>
          </b-card>
        </b-col>

        <!-- Top Students -->
        <b-col lg="6">
          <b-card class="mb-4">
            <b-card-header>
              <h5 class="card-title">{{ $t('Top_Spending_Students') }}</h5>
            </b-card-header>
            <b-card-body>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>{{ $t('Student') }}</th>
                      <th>{{ $t('Class') }}</th>
                      <th>{{ $t('Total_Spent') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="student in dashboardData.top_students" :key="student.id">
                      <td>
                        <div>
                          <strong>{{ student.name }}</strong>
                          <br>
                          <small class="text-muted">{{ student.student_id }}</small>
                        </div>
                      </td>
                      <td>{{ student.class_grade }}{{ student.stream }}</td>
                      <td>{{ formatCurrency(student.total_spent || 0) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </b-card-body>
          </b-card>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Students Dashboard"
  },
  data() {
    return {
      isLoading: true,
      chartPeriod: 'month',
      dashboardData: {
        statistics: {},
        today: {},
        monthly: {},
        top_students: [],
        recent_transactions: [],
        low_balance_students: [],
        negative_balance_students: [],
        sales_by_class: [],
        alerts: {}
      },
      salesChart: null
    };
  },

  methods: {
    //---- Get Dashboard Data
    getDashboardData() {
      NProgress.start();
      NProgress.set(0.1);
      
      axios.get("students/dashboard")
        .then(response => {
          this.dashboardData = response.data;
          this.isLoading = false;
          this.$nextTick(() => {
            this.initSalesChart();
          });
          NProgress.done();
        })
        .catch(error => {
          this.isLoading = false;
          NProgress.done();
          this.makeToast("danger", this.$t("Failed_to_load_dashboard"), this.$t("Failed"));
        });
    },

    //---- Get Sales Chart Data
    getSalesChartData() {
      axios.get("students/dashboard/sales-chart", {
        params: { period: this.chartPeriod }
      })
      .then(response => {
        this.updateSalesChart(response.data);
      })
      .catch(error => {
        this.makeToast("danger", this.$t("Failed_to_load_chart"), this.$t("Failed"));
      });
    },

    //---- Change Chart Period
    changeChartPeriod(period) {
      this.chartPeriod = period;
      this.getSalesChartData();
    },

    //---- Initialize Sales Chart
    initSalesChart() {
      if (typeof echarts !== 'undefined') {
        const chartDom = document.getElementById('salesChart');
        this.salesChart = echarts.init(chartDom);
        this.getSalesChartData();
      }
    },

    //---- Update Sales Chart
    updateSalesChart(data) {
      if (!this.salesChart) return;

      const option = {
        tooltip: {
          trigger: 'axis'
        },
        xAxis: {
          type: 'category',
          data: data.map(item => item.period)
        },
        yAxis: [
          {
            type: 'value',
            name: this.$t('Sales_Count'),
            position: 'left'
          },
          {
            type: 'value',
            name: this.$t('Sales_Amount'),
            position: 'right'
          }
        ],
        series: [
          {
            name: this.$t('Sales_Count'),
            type: 'bar',
            data: data.map(item => item.count),
            itemStyle: {
              color: '#007bff'
            }
          },
          {
            name: this.$t('Sales_Amount'),
            type: 'line',
            yAxisIndex: 1,
            data: data.map(item => item.total),
            itemStyle: {
              color: '#28a745'
            }
          }
        ]
      };

      this.salesChart.setOption(option);
    },

    //---- View Low Balance Students
    viewLowBalanceStudents() {
      // Navigate to filtered students list or show modal
      this.$router.push({ 
        name: "students_list", 
        query: { filter: "low_balance" } 
      });
    },

    //---- View Negative Balance Students
    viewNegativeBalanceStudents() {
      // Navigate to filtered students list or show modal
      this.$router.push({ 
        name: "students_list", 
        query: { filter: "negative_balance" } 
      });
    },

    //---- Format Currency
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
      }).format(amount || 0);
    },

    //---- Format Date
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },

    //---- Toast
    makeToast(variant, msg, title) {
      this.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    }
  },

  //---- Mounted
  mounted() {
    // Resize chart on window resize
    window.addEventListener('resize', () => {
      if (this.salesChart) {
        this.salesChart.resize();
      }
    });
  },

  //---- Before Destroy
  beforeDestroy() {
    if (this.salesChart) {
      this.salesChart.dispose();
    }
  },

  //---- Created function
  created() {
    this.getDashboardData();
  }
};
</script>

<style scoped>
.card-icon-bg {
  position: relative;
}

.card-icon-bg i {
  position: absolute;
  top: 20px;
  right: 20px;
  font-size: 40px;
  opacity: 0.3;
}

.card-header-right {
  position: absolute;
  right: 20px;
  top: 15px;
}
</style>