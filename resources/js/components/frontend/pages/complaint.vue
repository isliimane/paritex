<template>
 <div class="sg-page-content">
  <section class="contact-section mb-5">
    <div class="container">
      <div class="page-title">
        <h1>{{ complaint.title }}</h1>
      </div>
      <div class="contact-content">
        <div class="row">
          <div class="col-md-8 offset-md-2 shadow p-5 rounded-5">
            <div class="title b-0">
              <h1>{{ lang.make_complaint }}</h1>
            </div>
            <form @submit.prevent="submit">
              <div class="form-group">
                <label>{{ lang.name }}</label>
                <input type="text" v-model="form.name" class="form-control"
                       :class="{ 'error_border' : errors.name }" :placeholder="lang.name">
              </div>
              <span class="validation_error"
                    v-if="errors.name">{{ errors.name[0] }}</span>
              <div class="form-group">
                <label>{{ lang.email }}</label>
                <input type="email" v-model="form.email" class="form-control"
                       :class="{ 'error_border' : errors.email }" :placeholder="lang.email">
              </div>
              <span class="validation_error"
                    v-if="errors.email">{{ errors.email[0] }}</span>
              
              <!-- Department Dropdown -->
               <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>{{ lang.department || 'Department' }}</label>
                    <select v-model="form.support_department_id" class="form-control"
                          :class="{ 'error_border' : errors.support_department_id }">
                      <option value="">{{ lang.select_department || 'Select Department' }}</option>
                      <option v-for="department in departments" :key="department.id" :value="department.id">
                        {{ getTranslation(department, 'title', department.title || 'Unknown Department') }}
                      </option>
                    </select>
                  </div>
                  <span class="validation_error"
                      v-if="errors.support_department_id">{{ errors.support_department_id[0] }}</span>
                </div>
                <div class="col-md-6">
                     <!-- Priority Dropdown -->
                  <div class="form-group">
                    <label>{{ lang.priority || 'Priority' }}</label>
                    <select v-model="form.priority" class="form-control"
                          :class="{ 'error_border' : errors.priority }">
                      <option value="">{{ lang.select_priority || 'Select Priority' }}</option>
                      <option value="low">{{ lang.low || 'Low' }}</option>
                      <option value="medium">{{ lang.medium || 'Medium' }}</option>
                      <option value="high">{{ lang.high || 'High' }}</option>
                    </select>
                  </div>
                  <span class="validation_error"
                        v-if="errors.priority">{{ errors.priority[0] }}</span>
                </div>
               </div>
          
              
              <div class="form-group">
                <label>{{ lang.subject }}</label>
                <input type="text" v-model="form.subject" class="form-control"
                       :class="{ 'error_border' : errors.subject }" :placeholder="lang.subject">
              </div>
              <span class="validation_error"
                    v-if="errors.subject">{{ errors.subject[0] }}</span>
              <div class="form-group">
                <label>{{ lang.message }}</label>
                <textarea class="form-control" v-model="form.message"
                          :class="{ 'error_border' : errors.message }"
                          :placeholder="lang.write_your_message"></textarea>
              </div>
              <span class="validation_error"
                    v-if="errors.message">{{ errors.message[0] }}</span>
              <div>
                <loading_button v-if="loading" :class_name="'btn btn-primary'"></loading_button>
                <button type="submit" v-else class="btn btn-primary">
                  {{ lang.send }}
                </button>
              </div>
            </form><!-- /.contact-form -->
          </div>
        </div>
      </div><!-- /.contact-content -->
    </div><!-- /.container -->
  </section><!-- /.contact-section -->
</div>
</template>

<script>
import shimmer from "../partials/shimmer";

export default {
  name: "complaint",
  components: {
    shimmer
  },
  mounted() {
    let that = this;
    if (!that.complaint) {
      that.$store.dispatch('complaintPage');
    }
    
    this.fetchDepartments();

  },
  data() {
    return {
      loading: false,
      complaint: {
        title: ""
      },
      form: {
        name: "",
        email: "",
        subject: "",
        message: "",
        support_department_id: "", // Added for department dropdown
        priority: "" 
      },
      map: '',
      departments: [], // This will store the departments fetched from API
      currentLocale: document.documentElement.lang || 'en', // Default to 'en' if not set

      
    }
  },

  computed: {
    complaint() {
      return this.$store.getters.getComplaintPage;
    },
   
    shimmer() {
      return this.$store.state.module.shimmer
    }
  },
  methods: { 
    getTranslation(department, field, fallback = '') {
      // Check if department has translations
      if (department && department.support_department_languages && department.support_department_languages.length > 0) {
        // Find the translation that matches the current locale
        const translation = department.support_department_languages.find(
          trans => trans.lang === this.currentLocale && trans[field]
        );
        
        if (translation) {
          return translation[field];
        }
      }
      
      // If no translation found or no translations exist, return the default field or fallback
      return department[field] || fallback;
    },
    fetchDepartments() {
      axios.get('home/departments')
        .then(response => {
          console.log(response);
          
          if (response.data.success) {
            this.departments = response.data.data;
            console.log(this.departments);
          } else {
            // If your API returns departments directly without a success flag
            // this.departments = response.data;
            console.error('Failed to fetch departments');
          }
        })
        .catch(error => {
          console.error('Error fetching departments:', error);
        });
    },
    submit() {
      this.loading = true
      let url = this.getUrl('send-complaint');
      axios.post(url, this.form)
          .then((response) => {
            this.loading = false;
            if (response.data.success) {
              toastr.success(this.lang.message_sent_successfully, this.lang.Success + ' !!');
              this.errors = [];
              this.form.email = '';
              this.form.name = '';
              this.form.subject = '';
              this.form.message = '';
            } else {
              if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + ' !!');
              }
            }
          }).catch((error) => {
        this.loading = false;
        if (error.response.status == 422) {
          this.errors = error.response.data.errors;
        }
      })
    }
  }
}
</script>