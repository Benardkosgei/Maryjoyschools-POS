<template>
  <div class="main-content">
    <breadcumb :page="$t('Students_List')" :folder="$t('Students')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="students"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
          enabled: true,
          placeholder: $t('Search_this_table'),
        }"
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
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">
            {{ $t("Del") }}
          </button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button @click="New_Student()" class="btn-rounded" variant="btn btn-primary btn-icon m-1">
            <i class="i-Add"></i>
            {{ $t("Add_Student") }}
          </b-button>
          <b-button @click="Student_Dashboard()" class="btn-rounded" variant="btn btn-info btn-icon m-1">
            <i class="i-Bar-Chart"></i>
            {{ $t("Students_Dashboard") }}
          </b-button>
          <b-button @click="Import_Students()" class="btn-rounded" variant="btn btn-success btn-icon m-1">
            <i class="i-Upload"></i>
            {{ $t("Import_Students") }}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="Edit_Student(props.row)" title="Edit" v-b-tooltip.hover>
              <i class="i-Edit text-25 text-success"></i>
            </a>
            <a title="Delete" class="cursor-pointer" v-b-tooltip.hover @click="Remove_Student(props.row.id)">
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>

          <div v-else-if="props.column.field == 'student_id'">
            <span class="badge badge-outline-info">{{ props.row.student_id }}</span>
          </div>

          <div v-else-if="props.column.field == 'class_info'">
            <span class="badge badge-outline-primary">
              {{ props.row.class_grade }}{{ props.row.stream ? props.row.stream : '' }}
            </span>
          </div>

          <div v-else-if="props.column.field == 'account_balance'">
            <span :class="props.row.account_balance < 0 ? 'text-danger' : 'text-success'">
              {{ formatNumber(props.row.account_balance, 2) }}
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
    </div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";

export default {
  metaInfo: {
    title: "Students List"
  },
  data() {
    return {
      isLoading: true,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      selectedIds: [],
      totalRows: "",
      search: "",
      students: [],
      limit: "10",
      Filter_Name: "",
      Filter_Phone: "",
      Filter_Email: ""
    };
  },

  computed: {
    ...mapGetters(["currentUserPermissions", "currentUser"]),
    columns() {
      return [
        {
          label: this.$t("StudentID"),
          field: "student_id",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Name"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("AdmissionNumber"),
          field: "admission_number",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Class"),
          field: "class_info",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("House"),
          field: "house",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Phone"),
          field: "phone",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("AccountBalance"),
          field: "account_balance",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
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
    ...mapActions(["refreshUserPermissions"]),

    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange(params) {
      this.updateParams({ page: params.currentPage });
      this.Get_Students(params.currentPage);
    },

    //---- Event Per Page Change
    onPerPageChange(params) {
      this.limit = params.currentPerPage;
      this.updateParams({ page: 1, perPage: params.currentPerPage });
      this.Get_Students(1);
    },

    //---- Event Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Students(this.serverParams.page);
    },

    //---- Event Search
    onSearch(params) {
      this.search = params.searchTerm;
      this.Get_Students(this.serverParams.page);
    },

    //---- Validation State Form
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //---- Get All Students
    Get_Students(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "clients?page=" +
            page +
            "&SortField=" +
            this.serverParams.sort.field +
            "&SortType=" +
            this.serverParams.sort.type +
            "&search=" +
            this.search +
            "&limit=" +
            this.limit
        )
        .then(response => {
          this.students = response.data.clients.data;
          this.totalRows = response.data.totalRows;
          this.isLoading = false;

          // Complete the animation of theprogress bar.
          NProgress.done();
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //---- Delete Student
    Remove_Student(id) {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          axios
            .delete("clients/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.StudentDeleted"),
                "success"
              );

              this.Get_Students(this.serverParams.page);
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    },

    //---- Delete Students by selection
    delete_by_selected() {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          // Start the progress bar.
          NProgress.start();
          NProgress.set(0.1);
          axios
            .post("clients/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.StudentsDeleted"),
                "success"
              );

              this.Get_Students(this.serverParams.page);
            })
            .catch(() => {
              // Complete the animation of theprogress bar.
              setTimeout(() => NProgress.done(), 500);
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    },

    //---- New Student
    New_Student() {
      this.$router.push({ name: "students_store" });
    },

    //---- Edit Student
    Edit_Student(student) {
      this.$router.push({ name: "students_edit", params: { id: student.id } });
    },

    //---- Student Dashboard
    Student_Dashboard() {
      this.$router.push({ name: "students_dashboard" });
    },

    //---- Import Students
    Import_Students() {
      this.$router.push({ name: "students_import" });
    },

    //---- Format Number
    formatNumber(number, dec) {
      const value = (typeof number === "string" ? number : number.toString()).split(".");
      if (dec <= 0) return value[0];
      let formated = value[1] || "";
      if (formated.length > dec) return `${value[0]}.${formated.substr(0, dec)}`;
      while (formated.length < dec) formated += "0";
      return `${value[0]}.${formated}`;
    }
  },

  //---- Mounted function
  mounted() {
    this.$root.$on("bv::table::row-selected", (item, index) => {
      this.selectedIds = [];
      this.selectedIds.push(item[0].id);
    });
  },

  //---- Created function
  created() {
    this.Get_Students(1);
  }
};
</script>