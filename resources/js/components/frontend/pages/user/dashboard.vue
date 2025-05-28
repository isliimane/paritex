<template>
  <div class="sg-page-content" v-if="authUser && authUser.user_type == 'customer'">

    <section class="sg-global-content">
      <div class="container">
        <div class="row">
          <user_sidebar :current="current"></user_sidebar>
          <div class="col-lg-9">
            <div class="profile-banner-image">
              <a href="#"><img loading="lazy" :src="settings.user_dashboard_banner" alt="profile-banner"></a>
            </div>
            <div class="title justify-between">
              <h1>{{ lang.dashboard }}</h1>
            </div>
            <div class="row">
              <div class="col-md-4">
                <router-link :to="{ name : 'order.history' }">
                  <div class="card text-center profile-card d-flex justify-center profile-card-custom-color">
                    <div class="profile-card-title mb-3">{{ lang.total_order }}</div>
                    <h3 class="text-white ">{{ profileOrders ? profileOrders.total : 0 }}</h3>
                  </div>
                </router-link>
              </div>
              <div class="col-md-4">
                <router-link :to="{name : 'cart'}">
                  <div class="card text-center profile-card d-flex justify-center profile-card-custom-color">
                    <div class="profile-card-title mb-3">{{ lang.product_cart }}</div>
                    <h3 class="text-white"> {{ carts ? carts.length : 0 }}</h3>
                  </div>
                </router-link>
              </div>
              <div class="col-md-4">
                <router-link :to="{ name : 'wishlist' }">
                  <div class="card text-center profile-card d-flex justify-center profile-card-custom-color">
                    <div class="profile-card-title mb-3">{{ lang.product_you_love }}</div>
                    <h3 class="text-white">{{ wishlists }}</h3>
                  </div>
                </router-link>
              </div>
              <div class="col-md-4" v-if="settings.wallet_system == 1">
                <router-link :to="{ name : 'wallet.history' }">
                  <div class="card text-center profile-card d-flex justify-center profile-card-white">
                    <div class="profile-card-title text-black mb-3">{{ lang.wallet_balance }}</div>
                    <h3 class="text-black">{{ priceFormat(authUser.balance) }}</h3>
                  </div>
                </router-link>
              </div>
              <div class="col-md-4" v-if="settings.wallet_system == 1">
                <div class="card text-center profile-card d-flex justify-center profile-card-white">
                  <div class="profile-card-title mb-3 text-black">{{ lang.last_recharge }}</div>
                  <h3 class="text-black">{{ priceFormat(authUser.last_recharge) }}</h3>
                </div>
              </div>
              <div class="col-md-4" v-if="settings.wallet_system == 1">
                <a href="#" data-bs-target="#recharge_wallet" data-bs-toggle="modal">
                  <div
                      class="card text-center profile-card d-flex justify-center profile-card-white-outline-dashed">
                    <div class="profile-card-title mb-3">{{ lang.recharge_wallet }}</div>
                    <h3><i class="mdi mdi-plus"></i></h3>
                  </div>
                </a>
              </div>

              <div class="col-md-4" v-if="addons.includes('reward')">
                <router-link :to="{name : 'reward.history'}">
                  <div class="card text-center profile-card d-flex justify-center profile-card-white">
                    <div class="profile-card-title mb-3 text-black">{{ lang.total_rewards }}</div>
                    <h3 class="text-black"> {{ totalReward ? totalReward.reward_sum : 0 }}</h3>
                  </div>
                </router-link>
              </div>
              <div class="col-md-4" v-if="addons.includes('reward')">
                <router-link :to="{name : 'reward.history'}">
                  <div class="card text-center profile-card d-flex justify-center profile-card-white">
                    <div class="profile-card-title mb-3 text-black">{{ lang.current_rewards }}</div>
                    <h3 class="text-black"> {{ totalReward ? totalReward.rewards : 0 }}</h3>
                  </div>
                </router-link>
              </div>
              <div class="col-md-4"
                   v-if="settings.reward_convert_rate > 0 && totalReward && totalReward.rewards > 0 && addons.includes('reward') && settings.wallet_system == 1">
                <a href="#" data-bs-target="#convert_reward" data-bs-toggle="modal">
                  <div class="card text-center profile-card d-flex justify-center profile-card-white">
                    <div class="profile-card-title mb-3">{{ lang.convert_reward_wallet }}</div>
                    <h3><i class="mdi mdi-transfer"></i></h3>
                  </div>
                </a>
              </div>
            </div>

            <div class="sg-shipping" v-if="authUser">
              <div class="row">
                <div class="col-md-6">
                  <div class="title mt-3 mb-0 b-0">
                    <h1>{{ lang.default_shipping }}</h1>
                  </div>
                  <div class="sg-card address" v-if="default_shipping">
                    <div class="justify-content-between d-flex">
                      <div class="text">
                        <ul class="global-list">
                          <li>{{ lang.name }}: {{ default_shipping.name }}</li>
                          <li>{{ lang.email }}: {{ default_shipping.email }}</li>
                          <li>{{ lang.phone }}: {{ default_shipping.phone_no }}</li>
                          <li>{{ lang.street_address }}: {{ default_shipping.default_shipping }}</li>
                          <li>{{ lang.city }}: {{ default_shipping.city }}</li>
                          <li>{{ lang.country }}: {{ default_shipping.country }}</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="title mt-3 mb-0 b-0">
                    <h1>{{ lang.default_billing }}</h1>
                  </div>
                  <div class="sg-card address" v-if="default_billing">
                    <div class="justify-content-between d-flex">
                      <div class="text">
                        <ul class="global-list">
                          <li>{{ lang.name }}: {{ default_billing.name }}</li>
                          <li>{{ lang.email }}: {{ default_billing.email }}</li>
                          <li>{{ lang.phone }}: {{ default_billing.phone_no }}</li>
                          <li>{{ lang.street_address }}: {{ default_billing.default_shipping }}</li>
                          <li>{{ lang.city }}: {{ default_billing.city }}</li>
                          <li>{{ lang.country }}: {{ default_billing.country }}</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-12" v-if="profileOrders && profileOrders.total > 0">
              <div class="sg-table">
                <div class="title justify-content-between">
                  <h1>{{ lang.order_history }}</h1>
                </div>
                <orders :orders="profileOrders.data" :user_dashboard="false"></orders>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /.container -->
    </section>
    <div class="modal fade" id="recharge_wallet" tabindex="-1" aria-labelledby="recharge_wallet"
         aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ lang.wallet_recharge }}</h5>
            <button type="button" class="close modal_close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="sg-shipping">
                  <div class="sg-card">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="amount">{{ lang.amount }}</label>
                          <input type="text" class="form-control" id="amount" @input="removeData"
                                 v-model="form.total" :placeholder="lang.enter_amount">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card-list" :class="{ 'disable_section' : form.total == 0 }">
                    <ul class="global-list grid-3">
                      <li v-if="settings.is_paypal_activated == 1">
                        <div class="input-checkbox">
                          <input type="radio" value="paypal" @change="paymentChanged"
                                 v-model="payment_form.payment_type"
                                 id="paypal" name="radio">
                          <label for="paypal">
                            <img :src="getUrl('public/images/payment-method/paypal.svg')" :alt="payment_form.payment_type"
                                 class="img-fluid">
                            {{ lang.pay_with_payPal }}
                          </label>
                        </div>
                      </li>
                      <li v-if="settings.is_stripe_activated == 1">
                        <div class="input-checkbox">
                          <input type="radio" id="stripe" @change="paymentChanged" value="stripe"
                                 v-model="payment_form.payment_type" name="radio">
                          <label for="stripe">
                            <img :src="getUrl('public/images/payment-method/stripe.svg')" :alt="payment_form.payment_type"
                                 class="img-fluid">
                            {{ lang.pay_with_stripe }}
                          </label>
                        </div>
                      </li>
                 
                      <li v-if="settings.is_google_pay_activated">
                        <div class="input-checkbox">
                          <input type="radio" id="google_pay" @change="paymentChanged" value="google_pay"
                                 v-model="payment_form.payment_type" name="radio">
                          <label for="google_pay">
                            <img :src="getUrl('public/images/payment-method/google_pay.svg')"
                                 :alt="payment_form.payment_type"
                                 width="90" class="img-fluid">{{ lang.pay_with_google_pay }}</label>
                        </div>
                      </li>
                      
                     
                      <li v-if="addons.includes('offline_payment')" v-for="(offline,index) in offline_methods"
                          :key="index">
                        <div class="input-checkbox">
                          <input type="radio" :id="'offline'+offline.id"
                                 @change="offlineCheck(offline)"
                                 value="offline_method"
                                 name="radio" v-model="payment_form.payment_type">
                          <label :for="'offline'+offline.id">
                            <img loading="lazy" :src="offline.image" :alt="offline.name"
                                 class="img-fluid">
                            {{ offline.name }}
                          </label>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="row justify-content-center text-end mt-3" :class="{ 'disable_section' : !form.total }">
                  <div class="col-lg-5" id="payment_buttons">
                    <div class="mx-auto" v-show="payment_form.payment_type == 'paypal'"
                         id="paypal-button-container" ref="paypal"></div>

                    <paypal v-if="settings.is_paypal_activated == 1 && settings.paypal_key && payment_form.payment_type == 'paypal'" :trx_id="trx_id" :code="code"
                            :amount="form.total" :payment_type="payment_form.payment_type" :type="'wallet'"></paypal>

                    <a href="javascript:void(0)" class="btn btn-primary w-100 disable_btn"
                       v-if="!payment_form.payment_type">{{ lang.pay_now }}</a>

                    <a :href="getUrl('stripe/redirect?amount='+ form.total+'&type=wallet')" class="btn btn-primary w-100"
                       v-if="payment_form.payment_type == 'stripe' ">{{ lang.pay_now }}</a>


                    <a href="javascript:void(0)" class="btn btn-primary w-100" data-bs-toggle="modal"
                       data-bs-target="#offline"
                       v-if="offline_method.name">{{ lang.pay_now }}</a>

                 

                    <google_pay v-if="payment_form.payment_type == 'google_pay'" :trx_id="trx_id" :code="code" :type="wallet_recharge"
                                :amount="form.total"></google_pay>

                  </div>
                </div>
              </div>
            </div>
          </div><!-- /.modal-body -->
        </div>
      </div>
    </div>
    <div class="modal fade" id="offline" tabindex="-1" aria-labelledby="offline_modal"
         aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ lang.pay_with }}{{ offline_method.name }}</h5>
            <button type="button" class="close modal_close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="form-group">
                  <label>{{ lang.upload_file }}</label>
                  <div class="input-group">
                    <div class="custom-file d-flex">
                      <label class="upload-image form-control" for="upload-1">
                        <input type="file" id="upload-1" @change="imageUp($event)">
                        <i id="upload-image">{{ lang.upload_file }}</i>
                      </label>
                      <label class="upload-image upload-text" for="upload-2">
                        <input type="file" id="upload-2" @change="imageUp($event)">
                        <img loading="lazy" :src="getUrl('public/images/others/env.svg')" alt="file up icon"
                             class="img-fluid">
                        {{ lang.upload }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-12" v-if="offline_method.instructions">
                <label>{{ lang.instructions }}</label>
                <div class="instruction" v-html="offline_method.instructions"></div>
              </div>
              <div class="col-lg-12 text-center mt-3">
                <button @click="payment" class="btn btn-primary" v-show="!loading">{{ lang.proceed }}</button>
                <loading_button v-show="loading" :class_name="'btn btn-primary'"></loading_button>
              </div>
            </div>
          </div><!-- /.modal-body -->
        </div>
      </div>
    </div>
  
  </div>
</template>

<script>
import orders from "./../../partials/orders";
import user_sidebar from "../../partials/user_sidebar";
import shimmer from "../../partials/shimmer";
import google_pay from "../../payment_partials/google_pay";
import paypal from "../../payment_partials/paypal";


export default {
  name: "dashboard",
  data() {
    return {
      current: 'dashboard',
      amount: 0,
      offline_methods: [],
      indian_currency: {},
      form: {
        total: ''
      },
      ssl: {
        name: null,
        email: null,
        phone: null,
      },
      loading: false,
      offline_method: {
        id: '',
        name: '',
        image: '',
        instructions: '',
      },
      trx_id: "",
      code: "",
      payment_component_load: false,
      xof: '',
    }
  },
  components: {
    user_sidebar, orders, shimmer,google_pay,paypal
  },
  mounted() {
    this.getProfileOrders();
    this.takeData();
  },

  computed: {
    profileOrders() {
      return this.$store.getters.getProfileOrders;
    },
    carts() {
      return this.$store.getters.getCarts;
    },
    wishlists() {
      return this.$store.getters.getTotalWishlists;
    },
    activeCurrency() {
      return this.$store.getters.getActiveCurrency;
    },
    totalReward() {
      return this.$store.getters.getTotalReward === '' ? null : this.$store.getters.getTotalReward;
    },
    shimmer() {
      return this.$store.state.module.shimmer
    },
    default_shipping() {
      return this.authUser ? this.$store.getters.getUser.shipping_address : null
    },
    default_billing() {
      return this.authUser ? this.$store.getters.getUser.billing_address : null

    }

  },
  methods: {
    getProfileOrders() {
      let url = this.getUrl('user/profile-orders')
      axios.get(url).then((response) => {
        this.$store.commit('setShimmer', false);
        this.xof = response.data.xof;
        this.$store.commit("getProfileOrders", response.data.orders);
      })
    },
    payment() {
      let payment_type = this.payment_form.payment_type;

      if (!payment_type) {
        toastr.warning(this.lang.please_choose_a_payment_method, this.lang.Warning + ' !!');

        return false;
      }
      let form = {
        id: this.offline_method.id,
        file: this.product_form.image,
        payment_type: payment_type,
        amount: this.form.total,
      };

      let url = this.getUrl('user/recharge-wallet');

      if (this.offline_method.id) {
        this.loading = true;

        axios.post(url, form, {

          transformRequest: [function (data, headers) {
            return objectToFormData(data)
          }]
        }).then((response) => {
          this.loading = false;
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            $('#offline').modal('hide');
            let unshift = 1;
            this.$store.commit("getWalletRecharges", {unshift: unshift, data: response.data.recharges.data});
            let image_selector = document.getElementById('upload-image');
            if (image_selector) {
              image_selector.innerHTML = '';
            }
            this.$router.push({name: 'wallet.history'});
          }
        }).catch((error) => {
          this.loading = false;
        })
        ;
      }
    },
    takeData() {
      this.$Progress.start();
      let url = this.getUrl('user/wallet-data');
      axios.get(url).then((response) => {
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$Progress.finish();
          this.indian_currency = response.data.indian_currency;
          this.offline_methods = response.data.offline_methods;
        }
      })
    },
    paymentChanged() {
      this.offline_method.name = '';
      this.offline_method.image = '';
      this.offline_method.instructions = '';
    },
    removeData() {
      this.payment_form.payment_type = '';
      this.paymentChanged();
    },
    offlineCheck(offline) {
      this.paymentChanged();
      this.offline_method.id = offline.id;
      this.offline_method.name = offline.name;
      this.offline_method.image = offline.image;
      this.offline_method.instructions = offline.instructions;
    },
    checkCurrency(code){
      let currency = this.$store.getters.getCurrencies;
      let find = currency.findIndex(c=>c.code == code);
      if(find > -1){
        return true
      }else{
        return false
      }
    }
  }
}
</script>
