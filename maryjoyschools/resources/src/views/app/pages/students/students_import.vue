<template>
  <div class="main-content">
    <breadcumb :page="$t('Import_Students')" :folder="$t('Students')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    
    <div v-else>
      <!-- Instructions Card -->
      <b-card class="mb-4">
        <b-card-header>
          <h5 class="card-title">{{ $t('Import_Instructions') }}</h5>
        </b-card-header>
        <b-card-body>
          <b-alert show variant="info">
            <h6 class="alert-heading">{{ $t('Before_You_Start') }}</h6>
            <ul class="mb-0">
              <li>{{ $t('Download_the_sample_CSV_template_below') }}</li>
              <li>{{ $t('Fill_in_your_student_data_following_the_template_format') }}</li>
              <li>{{ $t('Save_your_file_as_CSV_format') }}</li>
              <li>{{ $t('Upload_your_CSV_file_using_the_form_below') }}</li>
            </ul>
          </b-alert>

          <div class="mt-3">
            <b-button variant="success" @click="downloadTemplate">
              <i class="i-Download"></i> {{ $t('Download_CSV_Template') }}
            </b-button>
          </div>
        </b-card-body>
      </b-card>

      <!-- Upload Form Card -->
      <b-card class="mb-4">
        <b-card-header>
          <h5 class="card-title">{{ $t('Upload_Students_CSV') }}</h5>
        </b-card-header>
        <b-card-body>
          <b-form @submit.prevent="importStudents" enctype="multipart/form-data">
            <b-row>
              <b-col md="8">
                <b-form-group :label="$t('Select_CSV_File') + ' *'">
                  <b-form-file
                    v-model="csvFile"
                    :placeholder="$t('Choose_CSV_file')"
                    accept=".csv"
                    :browse-text="$t('Browse')"
                    :drop-placeholder="$t('Drop_CSV_file_here')"
                  ></b-form-file>
                  <small class="text-muted">{{ $t('Only_CSV_files_are_allowed') }}</small>
                </b-form-group>
              </b-col>

              <b-col md="4">
                <b-form-group :label="$t('Action')">
                  <b-button 
                    type="submit" 
                    variant="primary" 
                    :disabled="!csvFile || importProcessing"
                    class="w-100"
                  >
                    <i class="i-Upload"></i> {{ $t('Import_Students') }}
                  </b-button>
                  <div v-if="importProcessing" class="text-center mt-2">
                    <div class="spinner sm spinner-primary"></div>
                    <small class="text-muted">{{ $t('Processing_please_wait') }}</small>
                  </div>
                </b-form-group>
              </b-col>
            </b-row>
          </b-form>
        </b-card-body>
      </b-card>

      <!-- Import Results Card -->
      <b-card v-if="importResults" class="mb-4">
        <b-card-header>
          <h5 class="card-title">{{ $t('Import_Results') }}</h5>
        </b-card-header>
        <b-card-body>
          <b-alert 
            :show="importResults.success" 
            :variant="importResults.success ? 'success' : 'danger'"
          >
            <h6 class="alert-heading">
              {{ importResults.success ? $t('Import_Successful') : $t('Import_Failed') }}
            </h6>
            <p class="mb-0">{{ importResults.message }}</p>
          </b-alert>

          <div v-if="importResults.details" class="mt-3">
            <h6>{{ $t('Import_Summary') }}</h6>
            <ul>
              <li v-if="importResults.details.total">
                {{ $t('Total_Records') }}: {{ importResults.details.total }}
              </li>
              <li v-if="importResults.details.imported">
                {{ $t('Successfully_Imported') }}: {{ importResults.details.imported }}
              </li>
              <li v-if="importResults.details.failed">
                {{ $t('Failed_Records') }}: {{ importResults.details.failed }}
              </li>
            </ul>
          </div>

          <div v-if="importResults.errors && importResults.errors.length > 0" class="mt-3">
            <h6 class="text-danger">{{ $t('Import_Errors') }}</h6>
            <b-table
              :items="importResults.errors"
              :fields="errorFields"
              small
              striped
              responsive
            ></b-table>
          </div>
        </b-card-body>
      </b-card>

      <!-- CSV Format Guide -->
      <b-card>
        <b-card-header>
          <h5 class="card-title">{{ $t('CSV_Format_Guide') }}</h5>
        </b-card-header>
        <b-card-body>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead class="thead-light">
                <tr>
                  <th>{{ $t('Column_Name') }}</th>
                  <th>{{ $t('Required') }}</th>
                  <th>{{ $t('Description') }}</th>
                  <th>{{ $t('Example') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="field in csvFields" :key="field.name">
                  <td><code>{{ field.name }}</code></td>
                  <td>
                    <span :class="field.required ? 'text-danger' : 'text-muted'">
                      {{ field.required ? $t('Yes') : $t('No') }}
                    </span>
                  </td>
                  <td>{{ field.description }}</td>
                  <td><em>{{ field.example }}</em></td>
                </tr>
              </tbody>
            </table>
          </div>

          <b-alert show variant="warning" class="mt-3">
            <h6 class="alert-heading">{{ $t('Important_Notes') }}</h6>
            <ul class="mb-0">
              <li>{{ $t('Student_IDs_and_Admission_Numbers_must_be_unique') }}</li>
              <li>{{ $t('Date_format_should_be_YYYY_MM_DD') }}</li>
              <li>{{ $t('Phone_numbers_should_include_country_code') }}</li>
              <li>{{ $t('Account_balance_and_credit_limit_should_be_numeric') }}</li>
              <li>{{ $t('Status_must_be_Active_Inactive_Graduated_or_Transferred') }}</li>
            </ul>
          </b-alert>
        </b-card-body>
      </b-card>
    </div>
  </div>
</template>

<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Import Students"
  },
  data() {
    return {
      isLoading: false,
      csvFile: null,
      importProcessing: false,
      importResults: null,
      errorFields: [
        { key: 'row', label: this.$t('Row') },
        { key: 'field', label: this.$t('Field') },
        { key: 'error', label: this.$t('Error') }
      ],
      csvFields: [
        {
          name: 'name',
          required: true,
          description: this.$t('Student_full_name'),
          example: 'John Mwangi'
        },
        {
          name: 'studentid',
          required: false,
          description: this.$t('Unique_student_identifier'),
          example: 'STU0001'
        },
        {
          name: 'admissionnumber',
          required: false,
          description: this.$t('School_admission_number'),
          example: 'ADM2025001'
        },
        {
          name: 'classgrade',
          required: true,
          description: this.$t('Student_class_PP1_to_Grade_13'),
          example: 'Grade 10'
        },
        {
          name: 'stream',
          required: false,
          description: this.$t('Class_stream_text_input'),
          example: 'A'
        },
        {
          name: 'house',
          required: false,
          description: this.$t('School_house_name'),
          example: 'Kenyatta House'
        },
        {
          name: 'dateofbirth',
          required: true,
          description: this.$t('Date_of_birth_YYYY_MM_DD'),
          example: '2008-05-15'
        },
        {
          name: 'gender',
          required: true,
          description: this.$t('Student_gender_Male_or_Female'),
          example: 'Male'
        },
        {
          name: 'phone',
          required: false,
          description: this.$t('Student_phone_number'),
          example: '+254712345678'
        },
        {
          name: 'email',
          required: false,
          description: this.$t('Student_email_address'),
          example: 'john.mwangi@student.school.ke'
        },
        {
          name: 'parentguardianname',
          required: true,
          description: this.$t('Parent_or_guardian_name'),
          example: 'Mary Mwangi'
        },
        {
          name: 'parentguardianphone',
          required: true,
          description: this.$t('Parent_or_guardian_phone'),
          example: '+254722345678'
        },
        {
          name: 'parentguardianemail',
          required: false,
          description: this.$t('Parent_or_guardian_email'),
          example: 'mary.mwangi@email.com'
        },
        {
          name: 'accountbalance',
          required: false,
          description: this.$t('Initial_account_balance'),
          example: '1000.00'
        },
        {
          name: 'creditlimit',
          required: false,
          description: this.$t('Student_credit_limit'),
          example: '5000.00'
        },
        {
          name: 'status',
          required: false,
          description: this.$t('Student_status_Active_by_default'),
          example: 'Active'
        },
        {
          name: 'adresse',
          required: false,
          description: this.$t('Student_address'),
          example: 'P.O. Box 123, Nairobi'
        },
        {
          name: 'city',
          required: false,
          description: this.$t('City_or_town'),
          example: 'Nairobi'
        },
        {
          name: 'country',
          required: false,
          description: this.$t('Country_Kenya_by_default'),
          example: 'Kenya'
        }
      ]
    };
  },

  methods: {
    //---- Download CSV Template
    downloadTemplate() {
      const headers = this.csvFields.map(field => field.name);
      const sampleData = [
        'John Mwangi',
        'STU0001',
        'ADM2025001',
        'Grade 10',
        'A',
        'Kenyatta House',
        '2008-05-15',
        'Male',
        '+254712345678',
        'john.mwangi@student.school.ke',
        'Mary Mwangi',
        '+254722345678',
        'mary.mwangi@email.com',
        '1000.00',
        '5000.00',
        'Active',
        'P.O. Box 123, Nairobi',
        'Nairobi',
        'Kenya'
      ];

      let csvContent = headers.join(',') + '\n';
      csvContent += sampleData.join(',') + '\n';

      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', 'students_import_template.csv');
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },

    //---- Import Students
    importStudents() {
      if (!this.csvFile) {
        this.makeToast("danger", this.$t("Please_select_CSV_file"), this.$t("Failed"));
        return;
      }

      this.importProcessing = true;
      this.importResults = null;
      
      NProgress.start();
      NProgress.set(0.1);

      const formData = new FormData();
      formData.append('csv_file', this.csvFile);

      axios.post('clients/import/csv', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(response => {
        this.importProcessing = false;
        this.importResults = {
          success: true,
          message: this.$t('Students_imported_successfully'),
          details: response.data.details || null
        };
        this.csvFile = null;
        this.makeToast("success", this.$t("Import_completed_successfully"), this.$t("Success"));
        NProgress.done();
      })
      .catch(error => {
        this.importProcessing = false;
        this.importResults = {
          success: false,
          message: error.response?.data?.message || this.$t('Import_failed'),
          errors: error.response?.data?.errors || []
        };
        this.makeToast("danger", this.$t("Import_failed"), this.$t("Failed"));
        NProgress.done();
      });
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
    this.isLoading = false;
  }
};
</script>

<style scoped>
.table th {
  background-color: #f8f9fa;
}

code {
  background-color: #f8f9fa;
  padding: 2px 4px;
  border-radius: 3px;
  font-size: 0.9em;
}
</style>