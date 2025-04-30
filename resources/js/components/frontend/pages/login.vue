<template>
  <div class="container" id="container" :class="{ 'active': isRegisterActive }">
    <!-- Sign In Form -->
    <div class="form-container sign-in">
      <form class="login-form" @submit.prevent="login">
        <h1>{{ lang.sign_in }}</h1>
        
        <!-- Social Login Icons -->
        <div class="social-icons" v-if="settings.is_facebook_login_activated == 1 || 
                                      settings.is_google_login_activated == 1 || 
                                      settings.is_twitter_login_activated == 1">
          <a href="javascript:void(0)" class="icon" @click="loginWithSocial('google')" v-if="settings.is_google_login_activated == 1">
            <i class="fa-brands fa-google-plus-g"></i>
          </a>
          <a href="javascript:void(0)" class="icon" @click="loginWithSocial('fb')" v-if="settings.is_facebook_login_activated == 1">
            <i class="fa-brands fa-facebook-f"></i>
          </a>
          <a href="javascript:void(0)" class="icon" @click="loginWithSocial('twitter')" v-if="settings.is_twitter_login_activated == 1">
            <i class="fa-brands fa-twitter"></i>
          </a>
        </div>
        
        <span v-if="settings.is_facebook_login_activated == 1 || 
                   settings.is_google_login_activated == 1 || 
                   settings.is_twitter_login_activated == 1">
          {{ lang.or_continue_with_email }}
        </span>
        
        <!-- Email/Phone Toggle -->
        <div class="toggle-options" v-if="addons.includes('otp_system') && !otp_field">
          <button type="button" 
                  :class="['toggle-option', { 'active': optionTo === 'phone' }]"
                  @click="loginOptions('phone')">
            {{ lang.use_email_instead }}
          </button>
          <button type="button" 
                  :class="['toggle-option', { 'active': optionTo === 'email' }]"
                  @click="loginOptions('email')">
            {{ lang.use_phone_instead }}
          </button>
        </div>
        
        <!-- Email Field -->
        <div class="form-group" v-if="optionTo=='phone'">
          <input type="email" v-model="form.email" 
                 :class="['form-input', { 'error-input': errors.email }]"
                 :placeholder="lang.email">
          <span class="error-message" v-if="errors.email">{{ errors.email[0] }}</span>
        </div>
        
        <!-- Phone Field -->
        <div v-if="optionTo=='email'">
          <telePhone @phone_no="getNumber" :phone_error="errors.phone ? errors.phone[0] : null"></telePhone>
          <span class="error-message" v-if="errors.phone">{{ errors.phone[0] }}</span>
        </div>
        
        <!-- OTP Field -->
        <div class="form-group" v-if="otp_field">
          <input type="text" v-model="phoneForm.otp" class="form-input"
                 :placeholder="lang.enter_your_otp">
        </div>
        
        <!-- Password Field -->
        <div class="form-group" v-if="optionTo=='phone'">
          <input type="password" v-model="form.password" 
                 :class="['form-input', { 'error-input': errors.password }]"
                 :placeholder="lang.Password">
          <span class="error-message" v-if="errors.password">{{ errors.password[0] }}</span>
        </div>
        
        <!-- Remember Me & Forgot Password -->
        <div class="form-options" v-if="optionTo == 'phone'">
          <label class="remember-me">
            <input type="checkbox" v-model="form.remember" value="1">
            <span>{{ lang.remember_me }}</span>
          </label>
          <router-link :to="{name:'reset.password'}" class="forgot-password">
            {{ lang.forgot_your_password }}
          </router-link>
        </div>
        
        <!-- reCAPTCHA -->
        <div v-if="settings.is_recaptcha_activated == 1" class="recaptcha-container"
             :class="optionTo == 'email' ? 'hidden': ''">
          <div class="g-recaptcha" data-callback="myCallback"
               :data-sitekey="settings.recaptcha_Site_key"></div>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="submit-btn" :disabled="loading">
          <span v-if="loading" class="btn-loading"></span>
          <span v-else>{{ buttonText }}</span>
        </button>
        
        <!-- Demo Accounts -->
        <div v-if="settings.demo_mode && !loading" class="demo-accounts">
          <button type="button" @click="copyLoginInfo('admin@spagreen.net')" class="demo-btn">
            Admin
          </button>
          <button v-if="settings.seller_system == 1" type="button" 
                  @click="copyLoginInfo('seller@spagreen.net')" class="demo-btn">
            Seller
          </button>
          <button type="button" @click="copyLoginInfo('customer@spagreen.net')" class="demo-btn">
            Customer
          </button>
          <button type="button" @click="copyLoginInfo('staff@spagreen.net')" class="demo-btn">
            Staff
          </button>
        </div>
      </form>
    </div>
    
    <!-- Sign Up Form -->
    <div class="form-container sign-up">
      <form class="register-form" @submit.prevent="register">
        <h1>{{ lang.sign_up }}</h1>
        <p v-if="otp && !settings.disable_otp">{{ lang.enter_to_complete_registration }}</p>
        
        <h5 class="registration-heading" v-if="form.user_type == 'seller'">{{ lang.personal_info }}</h5>
        
        <!-- Name Fields -->
        <div class="form-group">
          <input type="text" v-model="form.first_name" class="form-input"
                 :class="{ 'error-input': errors.first_name }"
                 :placeholder="lang.first_name"/>
          <span class="error-message" v-if="errors.first_name">{{ errors.first_name[0] }}</span>
        </div>
        
        <div class="form-group">
          <input type="text" v-model="form.last_name" class="form-input"
                 :class="{ 'error-input': errors.last_name }"
                 :placeholder="lang.last_name"/>
          <span class="error-message" v-if="errors.last_name">{{ errors.last_name[0] }}</span>
        </div>
        
        <!-- Email/Phone Toggle -->
        <div class="toggle-options" v-if="addons.includes('otp_system')">
          <button type="button" 
                  :class="['toggle-option', { 'active': optionTo === 'phone' }]"
                  @click="loginOptions('phone')">
            {{ lang.use_email_instead }}
          </button>
          <button type="button" 
                  :class="['toggle-option', { 'active': optionTo === 'email' }]"
                  @click="loginOptions('email')">
            {{ lang.use_phone_instead }}
          </button>
        </div>
        
        <!-- Email Field -->
        <div class="form-group" v-if="optionTo == 'phone'">
          <input type="email" v-model="form.email" class="form-input"
                 :class="{ 'error-input': errors.email }" 
                 :placeholder="lang.email"/>
          <span class="error-message" v-if="errors.email">{{ errors.email[0] }}</span>
        </div>
        
        <!-- Phone Field -->
        <div v-if="optionTo == 'email' && addons.includes('otp_system')">
          <telePhone @phone_no="getNumber" @country_id="setCountry" 
                    :phone_error="errors.phone ? errors.phone[0] : null"></telePhone>
          <span class="error-message" v-if="errors.phone">{{ errors.phone[0] }}</span>
        </div>
        
        <!-- Password Fields -->
        <div class="form-group" v-if="optionTo == 'phone'">
          <input type="password" v-model="form.password" class="form-input"
                 :class="{ 'error-input': errors.password }" 
                 :placeholder="lang.Password"/>
          <span class="error-message" v-if="errors.password">{{ errors.password[0] }}</span>
        </div>
        
        <div class="form-group" v-if="optionTo == 'phone'">
          <input type="password" v-model="form.password_confirmation" class="form-input"
                 :class="{ 'error-input': errors.password_confirmation }" 
                 :placeholder="lang.password_confirmation"/>
          <span class="error-message" v-if="errors.password_confirmation">{{ errors.password_confirmation[0] }}</span>
        </div>
        
        <!-- OTP Field -->
        <div class="form-group" v-if="addons.includes('otp_system') && otp && !settings.disable_otp">
          <input type="text" v-model="form.otp" class="form-input"
                 :class="{ 'error-input': errors.otp }" 
                 :placeholder="lang.enter_oTP"/>
          <span class="error-message" v-if="errors.otp">{{ errors.otp[0] }}</span>
        </div>
        
        <!-- OTP Timer/Resend -->
        <div v-if="addons.includes('otp_system') && otp">
          <p class="count-down-timer" v-if="!settings.disable_otp">
            <span v-if="otp && (minute >=0 && second >= 0)">0{{ minute }}:{{ second }}</span>
            <span @click="registerByPhone" v-else>{{ lang.otp_request }}</span>
          </p>
        </div>
        
        <!-- GDPR Agreement -->
        <gdpr_page ref="customer_agreement" :agreements="settings.customer_agreement"></gdpr_page>
        
        <!-- Submit Buttons -->
        <button type="submit" class="submit-btn" 
                v-if="otp && !loading"
                :disabled="form.otp.length != 5 && !settings.disable_otp">
          {{ lang.sign_up }}
        </button>
        
        <button type="submit" class="submit-btn" 
                v-else-if="optionTo == 'phone' && !loading">
          {{ lang.sign_up }}
        </button>
        
        <button type="button" @click="registerByPhone" class="submit-btn"
                v-else-if="optionTo == 'email' && !otp">
          {{ lang.get_oTP }}
        </button>
        
        <div v-if="loading" class="btn-loading"></div>
        
        <p class="account-prompt">
          {{ lang.have_an_account }}
          <router-link :to="{ name: 'login' }">{{ lang.sign_in }}</router-link>
        </p>
        
        <!-- Social Login -->
        <div class="social-login" v-if="settings.is_facebook_login_activated == 1 || 
                                      settings.is_google_login_activated == 1 || 
                                      settings.is_twitter_login_activated == 1">
          <p>{{ lang.or_continue_with }}</p>
          <div class="social-icons">
            <a href="javascript:void(0)" class="icon facebook" 
               @click="loginWithSocial('fb')" 
               v-if="settings.is_facebook_login_activated == 1">
              <i class="fa-brands fa-facebook-f"></i> {{ lang.facebook }}
            </a>
            <a href="javascript:void(0)" class="icon twitter" 
               @click="loginWithSocial('twitter')" 
               v-if="settings.is_twitter_login_activated == 1">
              <i class="fa-brands fa-twitter"></i> {{ lang.twitter }}
            </a>
            <a href="javascript:void(0)" class="icon google" 
               @click="loginWithSocial('google')" 
               v-if="settings.is_google_login_activated == 1">
              <i class="fa-brands fa-google"></i> {{ lang.google }}
            </a>
          </div>
        </div>
      </form>
    </div>
    
    <!-- Toggle Container -->
    <div class="toggle-container">
      <div class="toggle">
        <div class="toggle-panel toggle-left">
          <h1>{{ lang.welcome_back }}</h1>
          <p>{{ lang.sign_in_prompt }}</p>
          <button class="hidden" id="login" @click="toggleForm(false)">{{ lang.sign_in }}</button>
        </div>
        <div class="toggle-panel toggle-right">
          <h1>{{ lang.hello_friend }}</h1>
          <p>{{ lang.register_prompt }}</p>
          <button class="hidden" id="register" @click="toggleForm(true)">{{ lang.sign_up }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import telePhone from "../partials/telephone";
import { getAuth, signInWithPopup, GoogleAuthProvider, FacebookAuthProvider, TwitterAuthProvider } from "firebase/auth";

export default {
  name: "sign_in",
  components: {
    telePhone
  },
  data() {
    return {
      form: {
        email: '',
        password: '',
        _token: this.token,
        remember: 0,
        captcha: '',
        first_name: '',
        last_name: '',
        password_confirmation: '',
        otp: '',
        user_type: 'customer'
      },
      phoneForm: {
        phone: '',
        otp: '',
      },
      otp_field: false,
      otp: false,
      loading: false,
      optionTo: 'phone',
      buttonText: 'Sign In',
      social_login_active: false,
      isRegisterActive: false,
      minute: 1,
      second: 30,
      errors: {}
    }
  },
  computed: {
    loginRedirect() {
      return this.$store.getters.getLoginRedirection;
    },
    hasSocialLogin() {
      return this.settings.is_facebook_login_activated == 1 || 
             this.settings.is_google_login_activated == 1 || 
             this.settings.is_twitter_login_activated == 1;
    }
  },
  mounted() {
    if (this.authUser) {
      this.$router.go(-1);
    }
    if (this.settings.is_recaptcha_activated == 1) {
      this.captcha();
    }
    this.loginOptions();
  },
  watch: {
    lang() {
      this.loginOptions();
    }
  },
  methods: {
    toggleForm(showRegister) {
      this.isRegisterActive = showRegister;
    },

    async login(direct_login) {
      let form = this.form;
      let url = this.getUrl('login');
      
      if (direct_login == 'direct_login') {
        this.form.captcha = '1';
      } else if (this.settings.is_recaptcha_activated == 1 && this.optionTo == 'phone') {
        if (!window.captcha) {
          return toastr.warning(this.lang.verify_google_recaptcha, this.lang.Warning + ' !!');
        }
        this.form.captcha = window.captcha;
      }

      this.$store.commit('getCountCompare', true);
      this.loading = true;

      try {
        const axiosWithCredentials = axios.create({ withCredentials: true });
        let response;

        if (direct_login != 'direct_login') {
          if (this.optionTo == 'phone') {
            response = await axiosWithCredentials.post(url, form);
          } else if (this.optionTo == 'email' && !this.otp_field) {
            url = !this.settings.disable_otp ? this.getUrl('get-otp') : url;
            form = this.phoneForm;
            response = await axiosWithCredentials.post(url, form);
          } else if (this.otp_field) {
            url = this.getUrl('submit-otp');
            response = await axiosWithCredentials.post(url, this.phoneForm);
          }
        } else {
          response = await axiosWithCredentials.post(url, form);
        }

        this.handleLoginResponse(response, direct_login);
      } catch (error) {
        this.handleLoginError(error);
      } finally {
        this.loading = false;
      }
    },

    handleLoginResponse(response, direct_login) {
      if (response.data.error) {
        return toastr.error(response.data.error, this.lang.Error + ' !!');
      }

      if (response.data.success) {
        window.captcha = '';
        this.errors = [];

        if (this.optionTo == 'email' && !this.otp_field && direct_login != 'direct_login' && !this.settings.disable_otp) {
          this.otp_field = true;
          this.buttonText = this.lang.sign_in;
          return;
        }

        this.redirectAfterLogin(response.data.user);
        this.updateStore(response.data);
      }
    },

    redirectAfterLogin(user) {
      if (this.loginRedirect) {
        this.$router.push({ name: this.loginRedirect });
        return;
      }

      if (user.user_type == 'customer') {
        this.$router.push({ name: 'dashboard' });
      } else if (user.user_type == 'admin' || user.user_type == 'staff') {
        document.location.href = this.getUrl('admin/dashboard');
      } else if (user.user_type == 'seller') {
        document.location.href = this.getUrl('seller/dashboard');
      }
    },

    updateStore(data) {
      this.$store.dispatch('carts', data.carts);
      this.$store.dispatch('user', data.user);
      this.$store.dispatch('compareList', data.compare_list);
      this.$store.dispatch('wishlists', data.wishlists);
    },

    handleLoginError(error) {
      if (error.response && error.response.status == 422) {
        this.errors = error.response.data.errors;
      }
    },

    async register() {
      this.loading = true;
      try {
        const url = this.getUrl('register');
        const response = await axios.post(url, this.form);
        
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else if (response.data.success) {
          this.handleRegistrationSuccess(response.data);
        }
      } catch (error) {
        this.handleRegistrationError(error);
      } finally {
        this.loading = false;
      }
    },

    handleRegistrationSuccess(data) {
      this.errors = [];
      this.$store.dispatch('user', data.user);
      this.$router.push({ name: 'dashboard' });
    },

    handleRegistrationError(error) {
      if (error.response && error.response.status == 422) {
        this.errors = error.response.data.errors;
      }
    },

    async registerByPhone() {
      this.loading = true;
      try {
        const response = await axios.post(this.getUrl('register-by-phone'), this.phoneForm);
        if (response.data.success) {
          this.otp = true;
          this.startOTPTimer();
        } else if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }
      } catch (error) {
        this.handleRegistrationError(error);
      } finally {
        this.loading = false;
      }
    },

    startOTPTimer() {
      this.minute = 1;
      this.second = 30;
      
      const timer = setInterval(() => {
        if (this.second === 0) {
          if (this.minute === 0) {
            clearInterval(timer);
            return;
          }
          this.minute--;
          this.second = 59;
        } else {
          this.second--;
        }
      }, 1000);
    },

    async socialLogin(form) {
      this.social_login_active = true;
      try {
        const response = await axios.post(this.getUrl('social-login'), form);
        
        if (response.data.success) {
          this.handleSocialLoginSuccess(response.data);
        } else {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }
      } catch (error) {
        toastr.error(this.lang.something_went_wrong, this.lang.Error + ' !!');
      } finally {
        this.social_login_active = false;
      }
    },

    handleSocialLoginSuccess(data) {
      this.errors = [];
      this.$store.dispatch('carts', data.carts);
      this.$store.dispatch('user', data.user);
      this.$store.dispatch('compareList', data.compare_list);
      this.$store.dispatch('wishlists', data.wishlists);
      
      if (this.loginRedirect) {
        this.$router.push({ name: this.loginRedirect });
      } else {
        this.$router.push({ name: 'dashboard' });
      }
    },

    loginOptions(optionTo) {
      this.errors = [];
      
      if (optionTo) {
        this.optionTo = optionTo === 'phone' ? 'email' : 'phone';
        this.buttonText = this.optionTo === 'email' 
          ? (this.settings.disable_otp ? this.lang.sign_in : this.lang.get_oTP)
          : this.lang.sign_in;
      } else {
        this.optionTo = this.addons.includes('otp_system') ? 'email' : 'phone';
        this.buttonText = this.optionTo === 'email' 
          ? (this.settings.disable_otp ? this.lang.sign_in : this.lang.get_oTP)
          : this.lang.sign_in;
      }
    },

    captcha() {
      const script = document.createElement("script");
      script.src = "https://www.google.com/recaptcha/api.js";
      document.body.appendChild(script);
    },

    copyLoginInfo(email) {
      this.form.email = email;
      this.form.password = '123456';
      this.login('direct_login');
    },

    getNumber(number) {
      this.phoneForm.phone = number;
    },

    setCountry(countryId) {
      this.form.country_id = countryId;
    },

    async loginWithSocial(type) {
      let provider;
      switch (type) {
        case 'fb':
          provider = new FacebookAuthProvider();
          provider.addScope('user_birthday');
          provider.addScope('user_gender');
          provider.addScope('public_profile');
          break;
        case 'google':
          provider = new GoogleAuthProvider();
          provider.addScope('profile');
          provider.addScope('email');
          break;
        case 'twitter':
          provider = new TwitterAuthProvider();
          break;
        default:
          return;
      }

      try {
        const auth = getAuth();
        const result = await signInWithPopup(auth, provider);
        const raw_user = JSON.parse(result._tokenResponse.rawUserInfo);
        
        const form = {
          name: raw_user.name || '',
          email: raw_user.email || '',
          phone: raw_user.phoneNumber || '',
          uid: result.user.uid,
          dob: raw_user.birthday,
          gender: raw_user.gender,
          image: this.getSocialImage(type, raw_user)
        };

        this.socialLogin(form);
      } catch (error) {
        console.error("Social login error:", error);
      }
    },

    getSocialImage(type, raw_user) {
      switch (type) {
        case 'fb':
          return raw_user.picture?.data?.url || '';
        case 'google':
        case 'twitter':
          return raw_user.picture || '';
        default:
          return '';
      }
    },

    langKeywords() {
      axios.get(this.getUrl('language/keywords'))
        .then(response => {
          if (response.data.error) {
            toastr.info(response.data.error, this.lang.Info + ' !!');
          } else {
            this.$store.commit('setLangKeywords', response.data.lang);
            if (response.data.language.text_direction == 'rtl') {
              document.body.setAttribute('dir', 'rtl');
              this.settings.text_direction = 'rtl';
            }
          }
        });
    }
  }
}
</script>







<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');



* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Montserrat', sans-serif;
}

body {
  background-color: #c9d6ff;
  background: linear-gradient(to right, #e2e2e2, #c9d6ff);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  height: 100vh;
   display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
}

.container {
  background-color: #fff;
  border-radius: 30px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
  position: relative;
  overflow: hidden;
  width: 768px;
  max-width: 100%;
  min-height: 580px;
    margin: 0 auto;
  width: 70%;
  
}

.container p {
  font-size: 14px;
  line-height: 20px;
  letter-spacing: 0.3px;
  margin: 20px 0;
}

.container span {
  font-size: 12px;
  color: #666;
  margin: 5px 0;
  display: block;
}

.container a {
  color: #333;
  font-size: 13px;
  text-decoration: none;
  margin: 15px 0 10px;
}

.container button {
  background-color: #0bc196;
  color: #fff;
  font-size: 12px;
  padding: 10px 45px;
  border: 1px solid transparent;
  border-radius: 8px;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-top: 10px;
  cursor: pointer;
}



.container button.hidden {
  background-color: transparent;
  border-color: #fff;
}

.container form {
  background-color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 0 40px;
  height: 100%;
}

.form-group {
  width: 100%;
  margin-bottom: 1rem;
}

.form-input {
  background-color: #eee;
  border: none;
  margin: 8px 0;
  padding: 10px 15px;
  font-size: 13px;
  border-radius: 8px;
  width: 100%;
  outline: none;
}

.error-input {
  border: 1px solid #e74c3c !important;
}

.error-message {
  color: #e74c3c;
  font-size: 0.75rem;
  margin-top: -5px;
  margin-bottom: 5px;
}

.form-container {
  position: absolute;
  top: 0;
  height: 100%;
  width: 50%;
  transition: all 0.6s ease-in-out;
}

.sign-in {
  left: 0;
  width: 50%;
  z-index: 2;
}





.sign-up {
  left: 0;
  width: 100%;
  opacity: 0;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.register-panel {
  text-align: center;
  padding: 0 40px;
}

.container.active .sign-up {
  transform: translateX(100%);
   width: 50%;
  opacity: 1;
  z-index: 5;
  animation: move 0.6s;
}

@keyframes move {
  0%, 49.99% {
    opacity: 0;
    z-index: 1;
  }
  50%, 100% {
    opacity: 1;
    z-index: 5;
  }
}

.social-icons {
  margin: 20px 0;
}

.social-icons a {
  border: 1px solid #ccc;
  border-radius: 20%;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  margin: 0 3px;
  width: 40px;
  height: 40px;
  color: #333;
  transition: all 0.3s;
}

.social-icons a:hover {
  background-color: #f1f1f1;
}

.toggle-options {
  display: flex;
  width: 100%;
  margin-bottom: 1rem;
  border-radius: 8px;
  overflow: hidden;
  background-color: #f1f3f5;
}

.toggle-option {
  flex: 1;
  padding: 0.5rem;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.75rem;
  transition: all 0.3s ease;
}

.toggle-option.active {
  background-color: #0bc196;
  color: white;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  margin: 0.5rem 0;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: #666;
}

.forgot-password {
  font-size: 0.8rem;
  color: #0bc196;
}

.submit-btn {
  width: 100%;
  padding: 12px;
  margin: 1rem 0;
  position: relative;
}

.btn-loading {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
  margin: 0 auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.demo-accounts {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  width: 100%;
  margin: 1rem 0;
}

.demo-btn {
  flex: 1;
  min-width: 80px;
  padding: 8px;
  background-color: #f1f1f1;
  border: none;
  border-radius: 6px;
  font-size: 0.7rem;
  color: #333;
}

.toggle-container {
  position: absolute;
  top: 0;
  left: 50%;
  width: 50%;
  height: 100%;
  overflow: hidden;
  transition: all 0.6s ease-in-out;
  border-radius: 150px 0 0 100px;
  z-index: 1000;
}

.container.active .toggle-container {
  transform: translateX(-100%);
  border-radius: 0 150px 100px 0;
}

.toggle {
  background-color: #0bc196;
  height: 100%;
  background: linear-gradient(to right, #0bc196,rgb(29, 108, 90));
  color: #fff;
  position: relative;
  left: -100%;
  height: 100%;
  width: 200%;
  transform: translateX(0);
  transition: all 0.6s ease-in-out;
}

.container.active .toggle {
  transform: translateX(50%);
}

.toggle-panel {
  position: absolute;
  width: 50%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 0 30px;
  text-align: center;
  top: 0;
  transform: translateX(0);
  transition: all 0.6s ease-in-out;
}

.toggle-left {
  transform: translateX(-200%);
}

.container.active .toggle-left {
  transform: translateX(0);
}

.toggle-right {
  right: 0;
  transform: translateX(0);
}

.container.active .toggle-right {
  transform: translateX(200%);
}

.recaptcha-container {
  width: 100%;
  margin: 1rem 0;
}

.recaptcha-container.hidden {
  display: none;
}

@media (max-width: 768px) {
   body {
    padding: 10px;
    align-items: flex-start; /* Alignement en haut sur mobile */
  }
  .container {
      margin: 20px auto;
    flex-direction: column;
    min-height: 600px;
  }
  
  .sign-in,
  .sign-up {
    width: 100%;
  }
  
  .container.active .sign-in {
    transform: translateY(100%);
  }
  
  .container.active .sign-up {
    transform: translateY(-100%);
  }
  
  .toggle-container {
    width: 100%;
    height: 20%;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    border-radius: 0;
  }
  
  .container.active .toggle-container {
    transform: translateY(-150%) translateX(0);
  }
  
  .toggle {
    left: 0;
    width: 100%;
    height: 200%;
    transform: translateY(-50%);
  }
  
  .container.active .toggle {
    transform: translateY(0);
  }
  
  .toggle-panel {
    width: 100%;
    height: 50%;
    padding: 0 20px;
  }
  
  .toggle-left {
    transform: translateY(-100%);
  }
  
  .container.active .toggle-left {
    transform: translateY(0);
  }
  
  .toggle-right {
    transform: translateY(0);
  }
  
  .container.active .toggle-right {
    transform: translateY(100%);
  }







.register-form {
  width: 100%;
  max-width: 950px; /* Largeur maximale augmentée */
  margin: 2rem auto;
  padding: 3.5rem 8.5rem; /* Espacement interne généreux */
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
  border: 1px solid #eaeaea;
}

.register-form h1 {
  font-size: 8.2rem;
  color: #2c3e50 ;
  margin-bottom: 5.5rem;
  text-align: center;
  font-weight: 600;
}

.register-form .form-group {
  margin-bottom: 0.5rem; /* Espacement entre les champs augmenté */
}

.register-form .form-input {
  width: 100%;
  height:50%;
  padding: 1rem 1.2rem;
  font-size: 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.register-form .form-input:focus {
  border-color: #4285f4;
  box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
}

.register-form .submit-btn {
  width: 100%;
  padding: 1.1rem;
  font-size: 1.1rem;
  margin-top: 1rem;
}

.register-form .social-icons {
  justify-content: center;
  margin: 2rem 0;
}

.register-form .account-prompt {
  text-align: center;
  margin-top: 2rem;
  font-size: 1rem;
}
  
}
</style>