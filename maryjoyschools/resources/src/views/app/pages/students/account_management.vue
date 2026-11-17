<template>
  <div class="main-content">
    <breadcumb :page="$t('Account_Management')" :folder="$t('Students')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    
    <div v-else>
      <!-- Search and Filter Section -->
      <b-card class="mb-4">
        <b-row>
          <b-col md="5">
            <b-form-group :label="$t('Search_by_Name')">
              <b-input-group>
                <b-form-input
                  v-model="searchTerm"
                  :placeholder="$t('Enter_student_name')"
                  @input="searchStudents"
                />
                <b-input-group-append>
                  <b-button variant="primary" @click="searchStudents">
                    <i class="i-Search"></i>
                  </b-button>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
          </b-col>
          
          <b-col md="4">
            <b-form-group :label="$t('Filter_by_Grade')">
              <v-select
                v-model="selectedClass"
                :reduce="label => label.value"
                :placeholder="$t('All_Grades')"
                :options="classes"
                @input="filterStudents"
                clearable
              />
            </b-form-group>
          </b-col>

          <b-col md="3">
            <b-form-group :label="$t('Actions')">
              <b-button variant="success" @click="showBulkUpdateModal" class="w-100">
                <i class="i-Money-2"></i> {{ $t('Bulk_Update') }}
              </b-button>
            </b-form-group>
          </b-col>
        </b-row>
      </b-card>

      <!-- Students Table -->
      <b-card>
        <vue-good-table
          mode="remote"
          :columns="columns"
          :totalRows="totalRows"
          :rows="students"
          @on-page-change="onPageChange"
          @on-per-page-change="onPerPageChange"
          @on-sort-change="onSortChange"
          :select-options="{
            enabled: true,
            selectOnCheckboxOnly: true,
            selectionInfoClass: 'custom-class',
            selectionText: 'rows selected',
            clearSelectionText: 'clear',
            disableSelectInfo: true,
            selectAllByGroup: true,
          }"
          :pagination-options="{
            enabled: true,
            mode: 'records',
            nextLabel: 'next',
            prevLabel: 'prev',
          }"
          styleClass="tableOne vgt-table"
          @on-selected-rows-change="onSelectionChanged"
        >
          <template slot="table-row" slot-scope="props">
            <span v-if="props.column.field == 'actions'">
              <b-button 
                size="sm" 
                variant="primary" 
                @click="showUpdateBalanceModal(props.row)"
                class="mr-2"
              >
                <i class="i-Money-2"></i> {{ $t('Update_Balance') }}
              </b-button>
              <b-button 
                size="sm" 
                variant="info" 
                @click="viewPurchaseHistory(props.row)"
              >
                <i class="i-Receipt"></i> {{ $t('History') }}
              </b-button>
            </span>

            <div v-else-if="props.column.field == 'student_info'">
              <div>
                <strong>{{ props.row.name }}</strong>
                <br>
                <small class="text-muted">{{ props.row.student_id }}</small>
              </div>
            </div>

            <div v-else-if="props.column.field == 'class_info'">
              <span class="badge badge-outline-primary">
                {{ props.row.class_grade }}{{ props.row.stream ? props.row.stream : '' }}
              </span>
            </div>

            <div v-else-if="props.column.field == 'account_balance'">
              <span :class="getBalanceClass(props.row.account_balance)">
                {{ formatCurrency(props.row.account_balance) }}
              </span>
            </div>

            <div v-else-if="props.column.field == 'available_credit'">
              <span class="text-success">
                {{ formatCurrency(props.row.available_credit) }}
              </span>
            </div>

            <div v-else-if="props.column.field == 'status'">
              <span 
                :class="{
                  'badge badge-outline-success': props.row.status === 'Active',
                  'badge badge-outline-warning': props.row.status === 'Inactive',
                  'badge badge-outline-info': props.row.status === 'Graduated',
                  'badge badge-outline-secondary': props.row.status === 'Transferred'
                }"
              >
                {{ props.row.status }}
              </span>
            </div>

            <span v-else>{{ props.formattedRow[props.column.field] }}</span>
          </template>
        </vue-good-table>
      </b-card>
    </div>

    <!-- Update Balance Modal -->
    <b-modal
      v-model="showUpdateModal"
      :title="$t('Update_Account_Balance')"
      @ok="updateBalance"
      @hidden="resetUpdateForm"
    >
      <b-form>
        <b-row>
          <b-col md="12">
            <h6>{{ selectedStudent.name }} ({{ selectedStudent.student_id }})</h6>
            <p class="text-muted">{{ $t('Current_Balance') }}: {{ formatCurrency(selectedStudent.account_balance) }}</p>
          </b-col>

          <b-col md="6">
            <b-form-group :label="$t('Transaction_Type') + ' *'">
              <v-select
                v-model="updateForm.type"
                :reduce="label => label.value"
                :placeholder="$t('Choose_Type')"
                :options="transactionTypes"
              />
            </b-form-group>
          </b-col>

          <b-col md="6">
            <b-form-group :label="$t('Amount') + ' *'">
              <b-form-input
                type="number"
                step="0.01"
                v-model="updateForm.amount"
                :placeholder="$t('Enter_Amount')"
              />
            </b-form-group>
          </b-col>

          <b-col md="12">
            <b-form-group :label="$t('Description')">
              <b-form-textarea
                v-model="updateForm.description"
                :placeholder="$t('Enter_Description')"
                rows="3"
              />
            </b-form-group>
          </b-col>
        </b-row>
      </b-form>
    </b-modal>

    <!-- Bulk Update Modal -->
    <b-modal
      v-model="showBulkModal"
      :title="$t('Bulk_Update_Balances')"
      @ok="bulkUpdateBalances"
      @hidden="resetBulkForm"
    >
      <b-form>
        <b-alert show variant="info">
          {{ selectedStudents.length }} {{ $t('students_selected') }}
        </b-alert>

        <b-row>
          <b-col md="6">
            <b-form-group :label="$t('Transaction_Type') + ' *'">
              <v-select
                v-model="bulkForm.type"
                :reduce="label => label.value"
                :placeholder="$t('Choose_Type')"
                :options="transactionTypes"
              />
            </b-form-group>
          </b-col>

          <b-col md="6">
            <b-form-group :label="$t('Amount') + ' *'">
              <b-form-input
                type="number"
                step="0.01"
                v-model="bulkForm.amount"
                :placeholder="$t('Enter_Amount')"
              />
            </b-form-group>
          </b-col>

          <b-col md="12">
            <b-form-group :label="$t('Description')">
              <b-form-textarea
                v-model="bulkForm.description"
                :placeholder="$t('Enter_Description')"
                rows="3"
              />
            </b-form-group>
          </b-col>
        </b-row>
      </b-form>
    </b-modal>
  </div>
</template>

<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Account Management"
  },
  data() {
    return {
      isLoading: true,
      searchTerm: "",
      selectedClass: null,
      students: [],
      selectedStudents: [],
      totalRows: 0,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      showUpdateModal: false,
      showBulkModal: false,
      selectedStudent: {},
      updateForm: {
        type: "",
        amount: "",
        description: ""
      },
      bulkForm: {
        type: "",
        amount: "",
        description: ""
      },
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
      ],
      transactionTypes: [
        { label: this.$t('Credit_Add_Money'), value: "credit" },
        { label: this.$t('Debit_Subtract_Money'), value: "debit" }
      ]
    };
  },

  computed: {
    columns() {
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
          field: "class_info",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Account_Balance"),
          field: "account_balance",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Credit_Limit"),
          field: "credit_limit",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Available_Credit"),
          field: "available_credit",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Status"),
          field: "status",
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
    }
  },

  methods: {
    //---- Get Students
    getStudents(page = 1) {
      NProgress.start();
      NProgress.set(0.1);
      
      let params = {
        page: page,
        SortField: this.serverParams.sort.field,
        SortType: this.serverParams.sort.type,
        search: this.searchTerm,
        limit: this.serverParams.perPage
      };

      if (this.selectedClass) {
        params.class_grade = this.selectedClass;
      }

      axios.get("clients", { params })
        .then(response => {
          this.students = response.data.clients.data;
          this.totalRows = response.data.totalRows;
          this.isLoading = false;
          NProgress.done();
        })
        .catch(error => {
          this.isLoading = false;
          NProgress.done();
          this.makeToast("danger", this.$t("Failed_to_load_students"), this.$t("Failed"));
        });
    },

    //---- Search Students
    searchStudents() {
      this.getStudents(1);
    },

    //---- Filter Students
    filterStudents() {
      this.getStudents(1);
    },

    //---- Page Change
    onPageChange(params) {
      this.serverParams.page = params.currentPage;
      this.getStudents(params.currentPage);
    },

    //---- Per Page Change
    onPerPageChange(params) {
      this.serverParams.perPage = params.currentPerPage;
      this.getStudents(1);
    },

    //---- Sort Change
    onSortChange(params) {
      this.serverParams.sort = {
        type: params[0].type,
        field: params[0].field
      };
      this.getStudents(this.serverParams.page);
    },

    //---- Selection Changed
    onSelectionChanged(params) {
      this.selectedStudents = params.selectedRows;
    },

    //---- Show Update Balance Modal
    showUpdateBalanceModal(student) {
      this.selectedStudent = student;
      this.showUpdateModal = true;
    },

    //---- Show Bulk Update Modal
    showBulkUpdateModal() {
      if (this.selectedStudents.length === 0) {
        this.makeToast("warning", this.$t("Please_select_students"), this.$t("Warning"));
        return;
      }
      this.showBulkModal = true;
    },

    //---- Update Balance
    updateBalance() {
      if (!this.updateForm.type || !this.updateForm.amount) {
        this.makeToast("danger", this.$t("Please_fill_required_fields"), this.$t("Failed"));
        return;
      }

      axios.put(`students/${this.selectedStudent.id}/account-balance`, this.updateForm)
        .then(response => {
          this.makeToast("success", this.$t("Balance_updated_successfully"), this.$t("Success"));
          this.getStudents(this.serverParams.page);
          this.showUpdateModal = false;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("Failed_to_update_balance"), this.$t("Failed"));
        });
    },

    //---- Bulk Update Balances
    bulkUpdateBalances() {
      if (!this.bulkForm.type || !this.bulkForm.amount) {
        this.makeToast("danger", this.$t("Please_fill_required_fields"), this.$t("Failed"));
        return;
      }

      const promises = this.selectedStudents.map(student => {
        return axios.put(`students/${student.id}/account-balance`, this.bulkForm);
      });

      Promise.all(promises)
        .then(responses => {
          this.makeToast("success", this.$t("Balances_updated_successfully"), this.$t("Success"));
          this.getStudents(this.serverParams.page);
          this.showBulkModal = false;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("Failed_to_update_balances"), this.$t("Failed"));
        });
    },

    //---- View Purchase History
    viewPurchaseHistory(student) {
      this.$router.push({ 
        name: "students_reports", 
        query: { student_id: student.id } 
      });
    },

    //---- Reset Update Form
    resetUpdateForm() {
      this.updateForm = {
        type: "",
        amount: "",
        description: ""
      };
      this.selectedStudent = {};
    },

    //---- Reset Bulk Form
    resetBulkForm() {
      this.bulkForm = {
        type: "",
        amount: "",
        description: ""
      };
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
    this.getStudents(1);
  }
};
</script>