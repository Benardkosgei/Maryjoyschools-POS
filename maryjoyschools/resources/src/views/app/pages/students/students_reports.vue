<template>
  <div class="main-content">
    <breadcumb :page="$t('Students_Reports')" :folder="$t('Students')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    
    <div v-else>
      <!-- Filter Section -->
      <b-card class="mb-4">
        <b-row>
          <b-col md="3">
            <b-form-group :label="$t('Select_Student')">
              <v-select
                v-model="selectedStudent"
                :reduce="student => student.id"
                label="name"
                :placeholder="$t('Choose_Student')"
                :options="students"
                @input="loadStudentReport"
                clearable
              >
                <template #option="option">
                  <div>
                    <strong>{{ option.name }}</strong>
                    <br>
                    <small class="text-muted">{{ option.student_id }} - {{ option.class_grade }}{{ option.stream }}</small>
                  </div>
                </template>
              </v-select>
            </b-form-group>
          </b-col>

          <b-col md="2">
            <b-form-group :label="$t('Class')">
              <v-select
                v-model="selectedClass"
                :reduce="label => label.value"
                :placeholder="$t('All_Classes')"
                :options="classes"
                @input="filterByClass"
                clearable
              />
            </b-form-group>
          </b-col>

          <b-col md="2">
            <b-form-group :label="$t('Date_From')">
              <b-form-input
                type="date"
                v-model="dateFrom"
                @change="loadStudentReport"
              />
            </b-form-group>
          </b-col>

          <b-col md="2">
            <b-form-group :label="$t('Date_To')">
              <b-form-input
                type="date"
                v-model="dateTo"
                @change="loadStudentReport"
              />
            </b-form-group>
          </b-col>

          <b-col md="3">
            <b-form-group :label="$t('Actions')">
              <b-button-group class="w-100">
                <b-button variant="primary" @click="exportReport" :disabled="!selectedStudent">
                  <i class="i-Download"></i> {{ $t('Export') }}
                </b-button>
                <b-button variant="info" @click="printReport" :disabled="!selectedStudent">
                  <i class="i-Print"></i> {{ $t('Print') }}
                </b-button>
              </b-button-group>
            </b-form-group>
          </b-col>
        </b-row>
      </b-card>

      <!-- Student Summary Card -->
      <b-card v-if="selectedStudent && studentReport" class="mb-4">
        <b-row>
          <b-col md="8">
            <h5>{{ studentReport.student.name }}</h5>
            <p class="mb-1"><strong>{{ $t('StudentID') }}:</strong> {{ studentReport.student.student_id }}</p>
            <p class="mb-1"><strong>{{ $t('AdmissionNumber') }}:</strong> {{ studentReport.student.admission_number }}</p>
            <p class="mb-1"><strong>{{ $t('Class') }}:</strong> {{ studentReport.student.class_grade }}{{ studentReport.student.stream }}</p>
            <p class="mb-1"><strong>{{ $t('House') }}:</strong> {{ studentReport.student.house }}</p>
          </b-col>
          <b-col md="4">
            <div class="text-right">
              <h6>{{ $t('Account_Summary') }}</h6>
              <p class="mb-1">
                <strong>{{ $t('Current_Balance') }}:</strong> 
                <span :class="getBalanceClass(studentReport.student.account_balance)">
                  {{ formatCurrency(studentReport.student.account_balance) }}
                </span>
              </p>
              <p class="mb-1">
                <strong>{{ $t('Credit_Limit') }}:</strong> 
                {{ formatCurrency(studentReport.student.credit_limit) }}
              </p>
              <p class="mb-1">
                <strong>{{ $t('Available_Credit') }}:</strong> 
                <span class="text-success">{{ formatCurrency(studentReport.student.available_credit) }}</span>
              </p>
            </div>
          </b-col>
        </b-row>
      </b-card>

      <!-- Statistics Cards -->
      <b-row v-if="selectedStudent && studentReport" class="mb-4">
        <b-col md="3">
          <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
            <div class="card-body text-center">
              <i class="i-Shopping-Cart"></i>
              <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t('Total_Purchases') }}</p>
                <p class="text-primary text-24 line-height-1 mb-2">{{ studentReport.statistics.total_purchases }}</p>
              </div>
            </div>
          </div>
        </b-col>

        <b-col md="3">
          <div class="card card-icon-bg card-icon-bg-success o-hidden mb-4">
            <div class="card-body text-center">
              <i class="i-Money-2"></i>
              <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t('Total_Spent') }}</p>
                <p class="text-success text-24 line-height-1 mb-2">{{ formatCurrency(studentReport.statistics.total_amount) }}</p>
              </div>
            </div>
          </div>
        </b-col>

        <b-col md="3">
          <div class="card card-icon-bg card-icon-bg-warning o-hidden mb-4">
            <div class="card-body text-center">
              <i class="i-Receipt"></i>
              <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t('Pending_Amount') }}</p>
                <p class="text-warning text-24 line-height-1 mb-2">{{ formatCurrency(studentReport.statistics.pending_amount) }}</p>
              </div>
            </div>
          </div>
        </b-col>

        <b-col md="3">
          <div class="card card-icon-bg card-icon-bg-info o-hidden mb-4">
            <div class="card-body text-center">
              <i class="i-Calendar"></i>
              <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t('Last_Purchase') }}</p>
                <p class="text-info text-16 line-height-1 mb-2">{{ formatDate(studentReport.statistics.last_purchase_date) }}</p>
              </div>
            </div>
          </div>
        </b-col>
      </b-row>

      <!-- Purchase History Table -->
      <b-card v-if="selectedStudent && studentReport">
        <b-card-header>
          <h5 class="card-title">{{ $t('Purchase_History') }}</h5>
        </b-card-header>
        <b-card-body>
          <vue-good-table
            :columns="purchaseColumns"
            :rows="studentReport.purchases"
            :pagination-options="{
              enabled: true,
              mode: 'records',
              perPage: 10,
              nextLabel: 'next',
              prevLabel: 'prev',
            }"
            styleClass="tableOne vgt-table"
          >
            <template slot="table-row" slot-scope="props">
              <span v-if="props.column.field == 'actions'">
                <b-button 
                  size="sm" 
                  variant="info" 
                  @click="viewPurchaseDetails(props.row)"
                >
                  <i class="i-Eye"></i> {{ $t('View') }}
                </b-button>
              </span>

              <div v-else-if="props.column.field == 'date'">
                {{ formatDate(props.row.date) }}
              </div>

              <div v-else-if="props.column.field == 'GrandTotal'">
                {{ formatCurrency(props.row.GrandTotal) }}
              </div>

              <div v-else-if="props.column.field == 'paid_amount'">
                {{ formatCurrency(props.row.paid_amount) }}
              </div>

              <div v-else-if="props.column.field == 'due_amount'">
                {{ formatCurrency(props.row.GrandTotal - props.row.paid_amount) }}
              </div>

              <div v-else-if="props.column.field == 'payment_statut'">
                <span 
                  :class="{
                    'badge badge-success': props.row.payment_statut === 'paid',
                    'badge badge-warning': props.row.payment_statut === 'partial',
                    'badge badge-danger': props.row.payment_statut === 'unpaid'
                  }"
                >
                  {{ props.row.payment_statut }}
                </span>
              </div>

              <span v-else>{{ props.formattedRow[props.column.field] }}</span>
            </template>
          </vue-good-table>
        </b-card-body>
      </b-card>

      <!-- Class Summary (when no specific student selected) -->
      <b-card v-if="!selectedStudent && classSummary">
        <b-card-header>
          <h5 class="card-title">{{ $t('Class_Summary') }}</h5>
        </b-card-header>
        <b-card-body>
          <vue-good-table
            :columns="classSummaryColumns"
            :rows="classSummary"
            :pagination-options="{
              enabled: true,
              mode: 'records',
              perPage: 20,
              nextLabel: 'next',
              prevLabel: 'prev',
            }"
            styleClass="tableOne vgt-table"
          >
            <template slot="table-row" slot-scope="props">
              <div v-if="props.column.field == 'student_info'">
                <div>
                  <strong>{{ props.row.name }}</strong>
                  <br>
                  <small class="text-muted">{{ props.row.student_id }}</small>
                </div>
              </div>

              <div v-else-if="props.column.field == 'account_balance'">
                <span :class="getBalanceClass(props.row.account_balance)">
                  {{ formatCurrency(props.row.account_balance) }}
                </span>
              </div>

              <div v-else-if="props.column.field == 'total_spent'">
                {{ formatCurrency(props.row.total_spent || 0) }}
              </div>

              <span v-else>{{ props.formattedRow[props.column.field] }}</span>
            </template>
          </vue-good-table>
        </b-card-body>
      </b-card>
    </div>
  </div>
</template>

<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Students Reports"
  },
  data() {
    return {
      isLoading: true,
      selectedStudent: null,
      selectedClass: null,
      dateFrom: "",
      dateTo: "",
      students: [],
      studentReport: null,
      classSummary: [],
      classes: [
        { label: "PP1", value: "PP1" },
        { label: "PP2", value: "PP2" },
        { label: "Grade 1", value: "Grade 1" },
        { label: "Grade 2", value: "Grade 2" },
        { label: "Grade 3", value: "Grade 3" },
        { label: "Grade 4", value: "Grade 4" },
        { label: "Grade 5", value: "Grade 5" },
        { label: "Grade 6", value: "Grade 6" },
        { label: "Grade 7", value: "Grade 7" },
        { label: "Grade 8", value: "Grade 8" },
        { label: "Grade 9", value: "Grade 9" },
        { label: "Grade 10", value: "Grade 10" },
        { label: "Grade 11", value: "Grade 11" },
        { label: "Grade 12", value: "Grade 12" },
        { label: "Grade 13", value: "Grade 13" }
      ]
    };
  },

  computed: {
    purchaseColumns() {
      return [
        {
          label: this.$t("Date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Total_Amount"),
          field: "GrandTotal",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Paid_Amount"),
          field: "paid_amount",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Due_Amount"),
          field: "due_amount",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Status"),
          field: "payment_statut",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Action"),
          field: "actions",
          html: true,
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    },

    classSummaryColumns() {
      return [
        {
          label: this.$t("Student"),
          field: "student_info",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Class"),
          field: "class_grade",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Account_Balance"),
          field: "account_balance",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Total_Spent"),
          field: "total_spent",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Status"),
          field: "status",
          tdClass: "text-left",
          thClass: "text-left"
        }
      ];
    }
  },

  methods: {
    //---- Load Students
    loadStudents() {
      axios.get("clients", {
        params: {
          limit: 1000,
          student_only: true
        }
      })
      .then(response => {
        this.students = response.data.clients.data;
        this.isLoading = false;
        
        // Check if student_id is in query params
        if (this.$route.query.student_id) {
          this.selectedStudent = parseInt(this.$route.query.student_id);
          this.loadStudentReport();
        } else {
          this.loadClassSummary();
        }
      })
      .catch(error => {
        this.isLoading = false;
        this.makeToast("danger", this.$t("Failed_to_load_students"), this.$t("Failed"));
      });
    },

    //---- Load Student Report
    loadStudentReport() {
      if (!this.selectedStudent) {
        this.studentReport = null;
        this.loadClassSummary();
        return;
      }

      NProgress.start();
      NProgress.set(0.1);

      let params = {};
      if (this.dateFrom) params.date_from = this.dateFrom;
      if (this.dateTo) params.date_to = this.dateTo;

      axios.get(`students/${this.selectedStudent}/purchase-history`, { params })
        .then(response => {
          this.studentReport = {
            student: this.students.find(s => s.id === this.selectedStudent),
            purchases: response.data.data,
            statistics: this.calculateStatistics(response.data.data)
          };
          NProgress.done();
        })
        .catch(error => {
          NProgress.done();
          this.makeToast("danger", this.$t("Failed_to_load_report"), this.$t("Failed"));
        });
    },

    //---- Load Class Summary
    loadClassSummary() {
      let params = {};
      if (this.selectedClass) params.class_grade = this.selectedClass;

      axios.get("clients", { params })
        .then(response => {
          this.classSummary = response.data.clients.data;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("Failed_to_load_summary"), this.$t("Failed"));
        });
    },

    //---- Filter by Class
    filterByClass() {
      if (!this.selectedStudent) {
        this.loadClassSummary();
      }
    },

    //---- Calculate Statistics
    calculateStatistics(purchases) {
      const stats = {
        total_purchases: purchases.length,
        total_amount: 0,
        pending_amount: 0,
        last_purchase_date: null
      };

      purchases.forEach(purchase => {
        stats.total_amount += parseFloat(purchase.GrandTotal || 0);
        stats.pending_amount += parseFloat(purchase.GrandTotal || 0) - parseFloat(purchase.paid_amount || 0);
        
        if (!stats.last_purchase_date || purchase.date > stats.last_purchase_date) {
          stats.last_purchase_date = purchase.date;
        }
      });

      return stats;
    },

    //---- View Purchase Details
    viewPurchaseDetails(purchase) {
      this.$router.push({ 
        name: "detail_sale", 
        params: { id: purchase.id } 
      });
    },

    //---- Export Report
    exportReport() {
      if (!this.selectedStudent) return;

      let params = {
        student_id: this.selectedStudent
      };
      if (this.dateFrom) params.date_from = this.dateFrom;
      if (this.dateTo) params.date_to = this.dateTo;

      window.open(`/api/students/export?${new URLSearchParams(params)}`);
    },

    //---- Print Report
    printReport() {
      window.print();
    },

    //---- Get Balance Class
    getBalanceClass(balance) {
      if (balance < 0) return "text-danger font-weight-bold";
      if (balance < 100) return "text-warning";
      return "text-success";
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
      if (!date) return '-';
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

  //---- Created function
  created() {
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    this.dateTo = today.toISOString().split('T')[0];
    this.dateFrom = thirtyDaysAgo.toISOString().split('T')[0];
    
    this.loadStudents();
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

@media print {
  .no-print {
    display: none !important;
  }
}
</style>