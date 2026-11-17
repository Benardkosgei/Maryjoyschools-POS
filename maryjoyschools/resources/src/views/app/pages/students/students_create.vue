<template>
  <div class="main-content">
    <breadcumb :page="$t('Add_Student')" :folder="$t('Students')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-else>
      <b-form @submit.prevent="Submit_Student" enctype="multipart/form-data">
        <b-row>
          <!-- Basic Information -->
          <b-col lg="12">
            <h5 class="mb-3">{{ $t('BasicInformation') }}</h5>
          </b-col>
          
          <b-col md="4">
            <validation-provider name="Student Name" :rules="{ required: true }" v-slot="validationContext">
              <b-form-group :label="$t('Name') + ' *'">
                <b-form-input
                  :state="getValidationState(validationContext)"
                  aria-describedby="Name-feedback"
                  label="Name"
                  v-model="student.name"
                  :placeholder="$t('Enter_Name')"
                ></b-form-input>
                <b-form-invalid-feedback id="Name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <b-col md="4">
            <validation-provider name="Student ID" :rules="{ required: true }" v-slot="validationContext">
              <b-form-group :label="$t('StudentID') + ' *'">
                <b-input-group>
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="StudentID-feedback"
                    v-model="student.student_id"
                    :placeholder="$t('Enter_StudentID')"
                  ></b-form-input>
                  <b-input-group-append>
                    <b-button variant="outline-primary" @click="generateStudentId">
                      {{ $t('Generate') }}
                    </b-button>
                  </b-input-group-append>
                </b-input-group>
                <b-form-invalid-feedback id="StudentID-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <b-col md="4">
            <validation-provider name="Admission Number" :rules="{ required: true }" v-slot="validationContext">
              <b-form-group :label="$t('AdmissionNumber') + ' *'">
                <b-form-input
                  :state="getValidationState(validationContext)"
                  aria-describedby="AdmissionNumber-feedback"
                  v-model="student.admission_number"
                  :placeholder="$t('Enter_AdmissionNumber')"
                ></b-form-input>
                <b-form-invalid-feedback id="AdmissionNumber-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <b-col md="4">
            <validation-provider name="Class Grade" :rules="{ required: true }" v-slot="validationContext">
              <b-form-group :label="$t('ClassGrade') + ' *'">
                <v-select
                  :state="getValidationState(validationContext)"
                  v-model="student.class_grade"
                  :reduce="label => label.value"
                  :placeholder="$t('Choose_ClassGrade')"
                  :options="classes"
                />
                <b-form-invalid-feedback id="ClassGrade-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('Stream')">
              <b-form-input
                v-model="student.stream"
                :placeholder="$t('Enter_Stream')"
              />
            </b-form-group>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('House')">
              <v-select
                v-model="student.house"
                :reduce="label => label.value"
                :placeholder="$t('Choose_House')"
                :options="houses"
              />
            </b-form-group>
          </b-col>

          <b-col md="4">
            <validation-provider name="Date of Birth" :rules="{ required: true }" v-slot="validationContext">
              <b-form-group :label="$t('DateOfBirth') + ' *'">
                <b-form-input
                  :state="getValidationState(validationContext)"
                  type="date"
                  v-model="student.date_of_birth"
                  :placeholder="$t('Enter_DateOfBirth')"
                ></b-form-input>
                <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <b-col md="4">
            <validation-provider name="Gender" :rules="{ required: true }" v-slot="validationContext">
              <b-form-group :label="$t('Gender') + ' *'">
                <v-select
                  :state="getValidationState(validationContext)"
                  v-model="student.gender"
                  :reduce="label => label.value"
                  :placeholder="$t('Choose_Gender')"
                  :options="genders"
                />
                <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('Phone')">
              <b-form-input
                v-model="student.phone"
                :placeholder="$t('Enter_Phone')"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Contact Information -->
          <b-col lg="12">
            <h5 class="mb-3 mt-4">{{ $t('ContactInformation') }}</h5>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('Email')">
              <b-form-input
                type="email"
                v-model="student.email"
                :placeholder="$t('Enter_Email')"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('City')">
              <b-form-input
                v-model="student.city"
                :placeholder="$t('Enter_City')"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('Country')">
              <b-form-input
                v-model="student.country"
                :placeholder="$t('Enter_Country')"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="12">
            <b-form-group :label="$t('Address')">
              <b-form-textarea
                v-model="student.adresse"
                :placeholder="$t('Enter_Address')"
                rows="3"
              ></b-form-textarea>
            </b-form-group>
          </b-col>

          <!-- Parent/Guardian Information -->
          <b-col lg="12">
            <h5 class="mb-3 mt-4">{{ $t('ParentGuardianInformation') }}</h5>
          </b-col>

          <b-col md="4">
            <validation-provider name="Parent Guardian Name" :rules="{ required: true }" v-slot="validationContext">
              <b-form-group :label="$t('ParentGuardianName') + ' *'">
                <b-form-input
                  :state="getValidationState(validationContext)"
                  v-model="student.parent_guardian_name"
                  :placeholder="$t('Enter_ParentGuardianName')"
                ></b-form-input>
                <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <b-col md="4">
            <validation-provider name="Parent Guardian Phone" :rules="{ required: true }" v-slot="validationContext">
              <b-form-group :label="$t('ParentGuardianPhone') + ' *'">
                <b-form-input
                  :state="getValidationState(validationContext)"
                  v-model="student.parent_guardian_phone"
                  :placeholder="$t('Enter_ParentGuardianPhone')"
                ></b-form-input>
                <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('ParentGuardianEmail')">
              <b-form-input
                type="email"
                v-model="student.parent_guardian_email"
                :placeholder="$t('Enter_ParentGuardianEmail')"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="12">
            <b-form-group :label="$t('EmergencyContact')">
              <b-form-textarea
                v-model="student.emergency_contact"
                :placeholder="$t('Enter_EmergencyContact')"
                rows="3"
              ></b-form-textarea>
            </b-form-group>
          </b-col>

          <!-- Financial Information -->
          <b-col lg="12">
            <h5 class="mb-3 mt-4">{{ $t('FinancialInformation') }}</h5>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('AccountBalance')">
              <b-form-input
                type="number"
                step="0.01"
                v-model="student.account_balance"
                :placeholder="$t('Enter_AccountBalance')"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('CreditLimit')">
              <b-form-input
                type="number"
                step="0.01"
                v-model="student.credit_limit"
                :placeholder="$t('Enter_CreditLimit')"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="4">
            <b-form-group :label="$t('Status')">
              <v-select
                v-model="student.status"
                :reduce="label => label.value"
                :placeholder="$t('Choose_Status')"
                :options="statuses"
              />
            </b-form-group>
          </b-col>

          <b-col md="12">
            <b-form-group :label="$t('Notes')">
              <b-form-textarea
                v-model="student.notes"
                :placeholder="$t('Enter_Notes')"
                rows="3"
              ></b-form-textarea>
            </b-form-group>
          </b-col>

          <b-col md="12">
            <b-form-group class="mt-4">
              <b-button variant="primary" type="submit" :disabled="SubmitProcessing">
                <i class="i-Yes me-2 font-weight-bold"></i> {{ $t("submit") }}
              </b-button>
              <div v-once class="typo__p" v-if="SubmitProcessing">
                <div class="spinner sm spinner-primary mt-3"></div>
              </div>
            </b-form-group>
          </b-col>
        </b-row>
      </b-form>
    </b-card>
  </div>
</template>

<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Add Student"
  },
  data() {
    return {
      isLoading: true,
      SubmitProcessing: false,
      student: {
        name: "",
        student_id: "",
        admission_number: "",
        class_grade: "",
        stream: "",
        house: "",
        date_of_birth: "",
        gender: "",
        phone: "",
        email: "",
        adresse: "",
        city: "Nairobi",
        country: "Kenya",
        parent_guardian_name: "",
        parent_guardian_phone: "",
        parent_guardian_email: "",
        emergency_contact: "",
        account_balance: 0.00,
        credit_limit: 5000.00,
        status: "Active",
        notes: ""
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
      houses: [
        { label: "Kenyatta House", value: "Kenyatta House" },
        { label: "Nyerere House", value: "Nyerere House" },
        { label: "Mandela House", value: "Mandela House" },
        { label: "Nkrumah House", value: "Nkrumah House" }
      ],
      genders: [
        { label: "Male", value: "Male" },
        { label: "Female", value: "Female" }
      ],
      statuses: [
        { label: "Active", value: "Active" },
        { label: "Inactive", value: "Inactive" },
        { label: "Graduated", value: "Graduated" },
        { label: "Transferred", value: "Transferred" }
      ]
    };
  },

  methods: {
    //---- Validation State Form
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //---- Generate Student ID
    generateStudentId() {
      axios.get("students/generate-ids")
        .then(response => {
          this.student.student_id = response.data.student_id;
          this.student.admission_number = response.data.admission_number;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---- Submit Student
    Submit_Student() {
      this.$refs.form.validate().then(success => {
        if (!success) {
          this.makeToast("danger", this.$t("Please_fill_the_form_correctly"), this.$t("Failed"));
          return;
        }
        if (!this.SubmitProcessing) {
          this.SubmitProcessing = true;
          NProgress.start();
          NProgress.set(0.1);
          
          axios.post("clients", this.student)
            .then(response => {
              this.SubmitProcessing = false;
              this.$router.push({ name: "students_list" });
              this.makeToast("success", this.$t("Create.TitleStudent"), this.$t("Success"));
              NProgress.done();
            })
            .catch(error => {
              this.SubmitProcessing = false;
              NProgress.done();
              this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
            });
        }
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