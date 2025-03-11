<template>
  <li>
    <div class="input-checkbox">
      <input type="radio" :value="value" @change="$parent.razorPayRemove"
             v-model="$parent.payment_form.payment_type"
             :id="value" name="radio">
      <label :for="value">
        <img :src="image" :alt="value"
             class="img-fluid">
        {{ label }}
      </label>
    </div>
  </li>
</template>

<script>
export default {
  name: "payment_method",
  props: [
    'value',
    'label',
    'image',
  ],
  data() {
    return {
    };
  },
}
</script>