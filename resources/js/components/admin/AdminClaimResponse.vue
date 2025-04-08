<template>
  <b-modal 
    :title="`Répondre à la réclamation #${claim.id}`" 
    :visible="show" 
    size="lg"
    @hidden="$emit('close')"
    @ok="handleSubmit"
    :ok-disabled="formInvalid"
  >
    <div class="claim-response">
      <b-form-group label="Statut:" label-for="status">
        <b-form-select
          v-model="form.status"
          :options="statusOptions"
          required
        />
      </b-form-group>

      <b-form-group 
        label="Réponse:" 
        label-for="response"
        :invalid-feedback="responseFeedback"
        :state="responseState"
      >
        <b-form-textarea
          v-model="form.admin_response"
          id="response"
          rows="6"
          :state="responseState"
          required
        />
        <small class="text-muted">
          Cette réponse sera envoyée par email au client
        </small>
      </b-form-group>
    </div>

    <template #modal-footer>
      <b-button variant="secondary" @click="$emit('close')">
        Annuler
      </b-button>
      <b-button variant="primary" @click="handleSubmit" :disabled="formInvalid">
        Enregistrer
      </b-button>
    </template>
  </b-modal>
</template>

<script>
export default {
  props: {
    claim: {
      type: Object,
      required: true
    },
    show: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      form: {
        status: this.claim.status,
        admin_response: this.claim.admin_response || ''
      },
      statusOptions: [
        { value: 'in_progress', text: 'En cours' },
        { value: 'resolved', text: 'Résolu' },
        { value: 'rejected', text: 'Rejeté' }
      ],
      submitting: false
    }
  },

  computed: {
    responseState() {
      if (['resolved', 'rejected'].includes(this.form.status)) {
        return this.form.admin_response.length > 0
      }
      return null
    },

    responseFeedback() {
      if (['resolved', 'rejected'].includes(this.form.status) && !this.form.admin_response) {
        return 'Une réponse est requise pour ce statut'
      }
      return ''
    },

    formInvalid() {
      if (['resolved', 'rejected'].includes(this.form.status)) {
        return !this.form.admin_response
      }
      return false
    }
  },

  methods: {
    async handleSubmit() {
      this.submitting = true
      try {
        await this.$axios.put(`/admin/claims/${this.claim.id}`, this.form)
        this.$toast.success('Réponse enregistrée avec succès')
        this.$emit('saved')
        this.$emit('close')
      } catch (error) {
        this.$toast.error(this.$getErrorMessage(error))
      } finally {
        this.submitting = false
      }
    }
  }
}
</script>