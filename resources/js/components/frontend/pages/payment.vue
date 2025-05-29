<template>
  <div class="sg-page-content">
    <div class="sg-breadcrumb">
      <div class="container">
        <ol class="breadcrumb justify-content-center">
          <li class="breadcrumb-item">
            <router-link :to="{ name : 'cart' }">{{ lang.view_cart }}</router-link>
          </li>
          <li class="breadcrumb-item">
            <router-link :to="{ name : 'checkout' }">{{ lang.check_out }}</router-link>
          </li>
          <li class="breadcrumb-item">{{ lang.confirm_order }}</li>
        </ol>
      </div>
    </div><!-- /.sg-breadcrumb -->
    <section class="shopping-cart">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 pl-lg-5" v-if="lengthCounter(settings)>0">
            <div class="sg-shipping">
              <div class="title">
                <h1>{{ lang.payment_method }}</h1>
              </div>
              <div class="card-list">
                <ul class="global-list grid-3">
                  <payment_method v-if="settings.is_paypal_activated == 1 && settings.paypal_key"
                                  :value="'paypal'" :label="lang.pay_with_payPal" :image="getUrl('public/images/payment-method/paypal.svg')">
                  </payment_method>
                  <payment_method v-if="settings.is_stripe_activated == 1"
                                  :value="'stripe'" :label="lang.pay_with_stripe" :image="getUrl('public/images/payment-method/stripe.svg')">
                  </payment_method>
                 
                  <payment_method v-if="settings.is_google_pay_activated"
                                  :value="'google_pay'" :label="lang.pay_with_google_pay" :image="getUrl('public/images/payment-method/google_pay.svg')">
                  </payment_method>
                
                  <payment_method v-if="payment_form.total > 0 && !code && !check_cod"
                                  :value="'cash_on_delivery'" :label="lang.cash_on_delivery" :image="getUrl('public/images/payment-method/cash.svg')">
                  </payment_method>
                  <li v-if="!code && addons.includes('offline_payment')"
                      v-for="(offline,index) in offline_methods" :key="index">
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
                <div class="row text-center"
                     v-if="payment_form.total > 0 && authUser && authUser.balance >= payment_form.total && settings.wallet_system == 1">
                  <div class="col-lg-12">
                    <div class="separator mb-3">
                                    <span class="bg-white px-3">
                                        <span class="opacity-60">{{ lang.or }}</span>
                                    </span>
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <p>{{ lang.your_wallet_balance }} : {{ priceFormat(authUser.balance) }}</p>
                    <a href="javascript:void(0)" @click="payment('wallet')" v-if="!wallet_loading"
                       class="btn btn-primary">{{ lang.pay_with_wallet }}</a>
                    <loading_button v-if="wallet_loading"
                                    :class_name="'btn btn-primary'"></loading_button>
                  </div>
                </div>
              </div>
            </div>
            <gdpr_page ref="payment_agreement" :agreements="settings.payment_agreement" :class="{ 'mt-5 pt-5' : !(payment_form.total > 0 && authUser && authUser.balance >= payment_form.total && settings.wallet_system == 1) }"></gdpr_page>
          </div>
          <div class="col-lg-8" v-else-if="shimmer">
            <shimmer class="mb-3" :height="200" v-for="(payment,i) in 3" :key="i"></shimmer>
          </div>
          <div class="col-lg-4 pl-lg-5">
            <div class="order-summary">
              <h6>{{ lang.price_details }}</h6>

              <div class="sg-card">
                <form
                    :action="authUser ? getUrl('user/complete-order?code='+code) :getUrl('user/complete-order?code='+code+'&guest=1')"
                    method="post">
                  <input type="hidden" name="_token" :value="token">
                  <input type="hidden" name="trx_id" :value="trx_id">
                  <input type="hidden" name="payment_type" :value="payment_form.payment_type">
                  <input type="hidden" name="amount" :value="payment_form.total">
                  <payment_details
                      :sub_total="payment_form.sub_total"
                      :tax="payment_form.tax"
                      :discount_offer="payment_form.discount_offer"
                      :shipping_tax="payment_form.shipping_tax"
                      :coupon_discount="payment_form.coupon_discount"
                      :total="payment_form.total"
                  ></payment_details>
                </form>

                <loading_button
                    v-if="loading && (payment_form.payment_type == 'cash_on_delivery' || payment_form.payment_type == 'pay_later')"
                    :class_name="'btn btn-primary w-100'"></loading_button>

                <a @click="payment" href="javascript:void(0)" class="btn btn-primary w-100"
                   v-if="payment_form.payment_type == 'stripe' ">
                  {{ lang.pay_now }}</a>

                <a href="javascript:void(0)" class="btn btn-primary disable_btn"
                   v-if="!payment_form.payment_type">
                  {{ lang.pay_now }}</a>


                <a href="javascript:void(0)" @click="payment" class="btn btn-primary w-100"
                   v-show="!loading"
                   v-if="payment_form.payment_type == 'cash_on_delivery' || payment_form.payment_type == 'pay_later'">{{
                    lang.confirm
                  }}</a>

                <offline_method v-if="offline_methods.length > 0 && !code && addons.includes('offline_payment')" :trx_id="trx_id"
                                :code="code" :amount="payment_form.total" :offline_method="offline_method"
                                :loading="loading"></offline_method>

                <google_pay v-if="payment_form.payment_type == 'google_pay'" :trx_id="trx_id" :code="code"
                            :amount="payment_form.total"></google_pay>


              
              
                <paypal v-if="settings.is_paypal_activated == 1 && settings.paypal_key && payment_form.payment_type == 'paypal'" :trx_id="trx_id" :code="code"
                        :amount="payment_form.total" :payment_type="payment_form.payment_type" :type="'order'"></paypal>
              </div>

            </div>
          </div>
        </div><!-- /.row -->
      </div>
    </section><!-- /.shopping-cart -->
  </div>
</template>

<script>

import shimmer from "../partials/shimmer";
import paypal from "../payment_partials/paypal";
import offline_method from "../payment_partials/offline_method";
import payment_details from "../partials/payment_details";
import google_pay from "../payment_partials/google_pay";
import apple_pay from "../payment_partials/apple_pay";
import gdpr_page from "../partials/gdpr_page";
import Payment_method from "../payment_partials/payment_method";

export default {
  name: "payment",
  data() {
    return {
      offline_methods: [],
      indian_currency: {},
      check_cod: false,
      ssl: {
        name: null,
        email: null,
        phone: null,
      },
      trx_id: '',
      offline_method: {
        id: '',
        name: '',
        image: '',
        instructions: '',
      },
      wallet_loading: false,
      code: typeof this.$route.params.code != 'undefined' ? this.$route.params.code : '',
      loading: false,
      offline_modal: false,
      showStripeModal: false,
      xof: '',
      agreement: '',
      offline_method_file: '',
    }
  },
  components: {
    Payment_method, google_pay,apple_pay,
    shimmer, paypal, offline_method, payment_details,gdpr_page
  },
  mounted() {
    this.takeOrders();
  },
  watch: {
    carts(newValue, oldValue) {
      this.$router.go(-1);
    },
  },

  computed: {
    carts() {
      return this.$store.getters.getCarts;
    },
    shimmer() {
      return this.$store.state.module.shimmer
    },
    isLicenseVerified() {
          return (this.authUser && this.authUser.user_type == 'admin') || (this.authUser && this.authUser.user_type == 'customer' && this.authUser.license_verified);
    }
  },
  methods: {
    takeOrders() {
      let carts = this.carts;
      this.$Progress.start();
      let url = this.getUrl('user/payment-order?code=' + this.code);
      this.resetForm();
      axios.get(url).then((response) => {
        this.$store.commit('setShimmer', 0);
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$store.commit('setLoginRedirection', '');
          this.$Progress.finish();
          let orders = response.data.orders;
          let coupons = response.data.coupons;
          this.indian_currency = response.data.indian_currency;
          this.xof = response.data.xof;
          this.offline_methods = response.data.offline_methods;
          if (response.data.check_cod) {
            this.check_cod = response.data.check_cod;
          }
          
          if (orders) {
            for (let i = 0; i < orders.length; i++) {
              this.payment_form.sub_total += parseFloat(orders[i].sub_total);
              this.payment_form.discount_offer += parseFloat(orders[i].discount);
              this.payment_form.shipping_tax += parseFloat(orders[i].shipping_cost);
              this.payment_form.tax += parseFloat(orders[i].total_tax);
              if (this.settings.coupon_system == 1) {
                this.coupon_list = coupons;
                for (let i = 0; i < coupons.length; i++) {
                  this.payment_form.coupon_discount += parseFloat(coupons[i].discount);
                }
              }

              this.trx_id = orders[i].trx_id;
            }

            if (this.settings.tax_type == 'after_tax' && this.settings.vat_and_tax_type == 'order_base') {
              this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));
              this.payment_form.total += parseFloat(this.payment_form.tax);
              if(this.payment_form.total < 0){
                this.payment_form.total = 0;
              }
            } else {
              this.payment_form.total = parseFloat((parseFloat(this.payment_form.sub_total) + parseFloat(this.payment_form.tax) + parseFloat(this.payment_form.shipping_tax)) - (parseFloat(this.payment_form.discount_offer) + parseFloat(this.payment_form.coupon_discount)));
              if(this.payment_form.total < 0){
                this.payment_form.total = 0;
              }
            }
          }

          if (!this.trx_id) {
            toastr.warning(this.lang.something_went_wrong_try_chooing_address, this.lang.Warning + ' !!');
            this.$router.push({name: 'checkout'});
          }
        }
      })
    },
    offlineCheck(offline) {
      this.offline_method.id = offline.id;
      this.offline_method.name = offline.name;
      this.offline_method.image = offline.image;
      this.offline_method.instructions = offline.instructions;
    },
    payment(wallet) {
      if (!(this.authUser && this.authUser.user_type == 'admin') || (this.authUser && this.authUser.user_type == 'customer' && this.authUser.license_verified)) {
        toastr.error(this.lang.verify_license_to_continue, this.lang.Error + ' !!');
        return;
      }
      if (!this.$refs.payment_agreement.checkAgreements()) {
        return toastr.info(this.lang.accept_terms, this.lang.Error + ' !!');
      }
      let payment_type = '';
      if (wallet == 'wallet') {
        this.wallet_loading = true;
        payment_type = wallet;
      } else {
        payment_type = this.payment_form.payment_type;
      }

      if (!payment_type) {
        toastr.warning(this.lang.please_choose_a_payment_method, this.lang.Warning + ' !!');
        return false;
      }
      let form = {
        id: this.offline_method.id,
        file: this.offline_method_file,
        payment_type: payment_type,
        trx_id: this.trx_id,
        is_buy_now:this.$route.params.is_type ? this.$route.params.is_type : 0
      };

      let url = this.getUrl('user/complete-order?code=' + this.code);

      if (payment_type == 'cash_on_delivery' || payment_type == 'pay_later' || this.offline_method.id || payment_type == 'wallet') {

        if (wallet != 'wallet') {
          this.loading = true;
        }

        axios.post(url, form, {

          transformRequest: [function (data, headers) {
            return objectToFormData(data)
          }]
        }).then((response) => {
          this.wallet_loading = false;
          this.loading = false;
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            $('#offline').modal('hide');
            let image_selector = document.getElementById('upload-image');
            if (image_selector) {
              image_selector.innerHTML = '';
            }
            if (this.code) {
              this.$router.push({name: 'get.invoice', params: {orderCode: this.code}});
            } else {
              this.$router.push({name: 'invoice.list', params: {trx_id: this.trx_id}});
            }
          }
        }).catch((error) => {
          this.loading = false;
        });
      }else if (payment_type == 'stripe') {
        return window.location.href = this.getUrl('stripe/redirect?trx_id=' + this.trx_id + '&code=' + this.$route.params.code);
      }
    },
    checkCurrency(code){
      let currency = this.$store.getters.getCurrencies;
      let find = currency.findIndex(c=>c.code == code);
      return find > -1;
    },
    redirectToProfile() {
      toastr.error(this.lang.verify_license_to_continue, this.lang.Error + ' !!');
    }
  },

}
</script>
