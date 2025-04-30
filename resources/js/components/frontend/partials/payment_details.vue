<template>
  <div>
    <ul class="global-list" v-if="isLicenseVerified">
      <li>{{ lang.subtotal }} <span>{{ priceFormat(sub_total) }}</span></li>
      <li v-if="settings.tax_type == 'before_tax' || settings.vat_and_tax_type == 'product_base'">{{ lang.tax }} <span>{{ priceFormat(tax) }}</span></li>
      <li>{{ lang.discount }}<span>{{ priceFormat(discount_offer) }}</span>
      </li>
      <li v-if="settings.shipping_cost != 'area_base' || $route.name != 'cart'">{{ lang.shipping_cost }}<span>{{
          priceFormat(shipping_tax)
        }}</span></li>
      <li v-if="settings.coupon_system == 1">{{ lang.coupon_discount }}<span>{{
          priceFormat(coupon_discount)
        }}</span></li>
    </ul>
    <div class="order-total" v-if="isLicenseVerified && settings.tax_type == 'after_tax' && settings.vat_and_tax_type == 'order_base'">
      <p class="font_weight_400">{{ lang.total }} <span>{{ priceFormat(total - tax) }}</span></p>
      <p class="font_weight_400">{{ lang.tax }} <span>{{ priceFormat(tax) }}</span></p>
      <p class="grand_total_style">{{ lang.grand_total }} <span>{{ priceFormat(total) }}</span></p>
    </div>
    <div class="order-total" v-else-if="isLicenseVerified">
      <p>{{ lang.total }} <span>{{ priceFormat(total) }}</span></p>
    </div>
    <div class="text-danger mb-5" v-else>
      {{ lang.verify_license_to_see_price }}
    </div>
  </div>
</template>

<script>
export default {
  name: "payment_details",
  props : ['sub_total','tax','discount_offer','shipping_tax','coupon_discount','total'],
  computed: {
    isLicenseVerified() {
          return (this.authUser && this.authUser.user_type === 'admin') || (this.authUser && this.authUser.user_type === 'customer' && this.authUser.license_verified);
    }
  },
  mounted() {
  }
}
</script>

<style scoped>

</style>