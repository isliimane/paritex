<template>
  <div class="sg-page-content">
    <section class="ragister-account text-center">
      <div class="container">
        <div class="account-content">
          <div class="thumb">
            <img :src="settings.sign_up_banner" loading="lazy" :alt="form.user_type" class="img-fluid img-fit"/>
          </div>
          <div class="form-content">
            <h1>{{ lang.sign_up }} </h1>
            <p>{{ lang.sign_to_continue_shopping }}</p>
            <form class="ragister-form" name="ragister-form" @submit.prevent="register">
              <div>
                <div class="form-group">
                  <span class="mdi mdi-name mdi-account-outline"></span>
                  <input type="text" v-model="form.first_name" class="form-control"
                         :class="{ 'error_border' : errors.first_name }"
                         :placeholder="lang.first_name"/>
                </div>
                <span class="validation_error" v-if="errors.first_name">{{
                    errors.first_name[0]
                  }}</span>
                <div class="form-group">
                  <span class="mdi mdi-name mdi-account-outline"></span>
                  <input type="text" v-model="form.last_name" class="form-control"
                         :class="{ 'error_border' : errors.last_name }"
                         :placeholder="lang.last_name"/>
                </div>
                <span class="validation_error" v-if="errors.last_name">{{ errors.last_name[0] }}</span>
                <div class="form-group">
                  <span class="mdi mdi-name mdi-card-account-details-outline"></span>
                  <input type="text" v-model="form.license_no" class="form-control"
                         :class="{ 'error_border' : errors.license_no }"
                         :placeholder="lang.license_no"/>
                </div>
                <span class="validation_error" v-if="errors.license_no">{{ errors.license_no[0] }}</span>
                <div class="form-group">
                  <span class="mdi mdi-name mdi-email-outline"></span>
                  <input type="email" v-model="form.email" class="form-control mb-0"
                         :class="{ 'error_border mb-0' : errors.email }" :placeholder="lang.email"/>
                </div>
                <span class="validation_error"
                      v-if="errors.email">{{ errors.email[0] }}</span>
                <div class="form-group mt-4">
                  <span class="mdi mdi-name mdi-lock-outline"></span>
                  <input type="password" v-model="form.password" class="form-control"
                         :class="{ 'error_border' : errors.password }" :placeholder="lang.Password"/>
                </div>
                <span class="validation_error"
                      v-if="errors.password">{{ errors.password[0] }}</span>
                <div class="form-group mt-4">
                  <span class="mdi mdi-name mdi-lock-outline"></span>
                  <input type="password" v-model="form.password_confirmation" class="form-control"
                         :class="{ 'error_border' : errors.password_confirmation }" :placeholder="lang.password_confirmation"/>
                </div>
                <span class="validation_error"
                      v-if="errors.password_confirmation">{{ errors.password_confirmation[0] }}</span>
              </div>
              <gdpr_page ref="customer_agreement" :agreements="settings.customer_agreement"></gdpr_page>

              <button type="submit" class="btn" v-if="!loading">
                {{ lang.sign_up }}
              </button>
              <loading_button v-if="loading" :class_name="'btn'"></loading_button>
             
              <p>{{ lang.have_an_account }}
                <router-link :to="{ name : 'login' }">{{ lang.sign_in }}</router-link>
              </p>
              
            </form>
            <!-- /.contact-form -->
          </div>
        </div>
        <!-- /.account-content -->
      </div>
      <!-- /.container -->
    </section>
    <!-- /.ragister-account -->
  </div>

</template>

<script>
import telePhone from "../partials/telephone";
import {FacebookAuthProvider, getAuth, GoogleAuthProvider, signInWithPopup, TwitterAuthProvider} from "firebase/auth";
import gdpr_page from "../partials/gdpr_page";

export default {
  name: "register",
  components: {
    telePhone,gdpr_page
  },
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        phone: '',
        address: '',
        phone_no: '',
        user_type: this.$route.params.type,
        country_id: '',
        license_no: ''
      },
      social_login_active: false,
      loading: false,
      buttonText: 'Sign Up',
      phone_no: '',
      minute: 1,
      second: 60,
      agreement: '',
      country_code: []
    }
  },
  watch: {
    $route(from, to) {
      this.form.user_type = from.params.type;
      console.log(this.form.user_type);
    }
  },

  mounted() {
      console.log(this.form.user_type,this.$route.params.type);
  },
  computed: {
    loginRedirect() {
      return this.$store.getters.getLoginRedirection;
    }
  },
  methods: {
    countDownTimer() {
      this.minute = 1;
      this.second = 60;
      setInterval(() => {
        this.second--;
        if (this.second == 0) {
          this.minute--;
          this.second = 60;
          if (this.minute == 0) {
            this.minute = 0;
          }
        }
      }, 1000);
    },
    register() {
      if (!this.$refs.customer_agreement.checkAgreements()) {
        return toastr.info(this.lang.accept_terms, this.lang.Error + ' !!');
      }
      this.loading = true;
      let url = this.getUrl('register');
      axios.post(url, this.form).then((response) => {
        this.loading = false;
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }
        if (response.data.success) {
          if (response.data.type == 1) {
            this.$store.dispatch('user', response.data.auth_user);
            this.$router.push({name: 'dashboard'});
          } else {
            this.$router.push({name: 'login'});
          }

          this.errors = [];
          toastr.success(response.data.success, this.lang.Success + ' !!');
        }
      })
          .catch((error) => {
            this.loading = false;
            this.$Progress.fail();
            if (error.response && error.response.status == 422) {
              this.errors = error.response.data.errors;
            }
          })
    },
    socialLogin(form) {
      this.social_login_active = true;
      let url = this.getUrl('social-login');
      axios.post(url, form).then((response) => {
        this.loading = false;
        this.social_login_active = false;
        if (response.data.success) {
          this.errors = [];
          if (this.loginRedirect) {
            this.$router.push({name: this.loginRedirect});
          } else {
            this.$router.push({name: 'dashboard'});

            this.$store.dispatch('carts', response.data.carts);
            this.$store.dispatch('user', response.data.user);
            this.$store.dispatch('compareList', response.data.compare_list);
            this.$store.dispatch('wishlists', response.data.wishlists);
          }
        } else {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }
      }).catch((error) => {
        this.loading = false;
        this.social_login_active = false;
        toastr.error('Something Went Wrong, Please Try Again', this.lang.Error + ' !!');
      })
    },
    loginWithSocial(type) {
      let provider = '';
      if (type == 'fb') {
        provider = new FacebookAuthProvider();
        provider.addScope('user_birthday');
        provider.addScope('user_gender');
        provider.addScope('public_profile');
      } else if(type == 'google') {
        provider = new GoogleAuthProvider();
        provider.addScope('profile');
        provider.addScope('email');
      }
      else if(type == 'twitter') {
        provider = new TwitterAuthProvider();
      }

      const auth = getAuth();

      signInWithPopup(auth, provider)
          .then((result) => {
            let raw_user = JSON.parse(result._tokenResponse.rawUserInfo);

            let credential = '';
            let picture = '';

            if (type == 'fb') {
              credential = FacebookAuthProvider.credentialFromResult(result);
              picture = raw_user.picture ? raw_user.picture.data.url : '';
            } else if(type == 'google') {
              credential = GoogleAuthProvider.credentialFromResult(result);
              picture = raw_user.picture ? raw_user.picture : '';
            }
            else if(type == 'twitter')
            {
              credential = TwitterAuthProvider.credentialFromResult(result);
              picture = raw_user.picture ? raw_user.picture : '';
            }

            const token = credential.accessToken;
            // The signed-in user info.
            const user = result.user;

            let form = {
              name: raw_user.name ? raw_user.name : '',
              email: raw_user.email ? raw_user.email : '',
              phone: raw_user.phoneNumber ? raw_user.phoneNumber : '',
              uid: user.uid,
              dob: raw_user.birthday,
              gender: raw_user.gender,
              image: picture,
            };

            this.socialLogin(form);

          }).catch((error) => {
        // Handle Errors here.
        const errorCode = error.code;
        const errorMessage = error.message;
        // The email of the user's account used.
        const email = error.customData.email;
        // The AuthCredential type that was used.
        const credential = GoogleAuthProvider.credentialFromError(error);
        // ...
      });
    },
    getNumber(number) {
      alert(number);
      this.form.phone = number;
    },
    setCountry(id) {
      this.form.country_id = id;
    }
  },
}
</script>
