<template>
  <b-modal 
    :title="`Réclamation #${claim.id}`" 
    :visible="show" 
    size="lg"
    @hidden="$emit('close')"
    scrollable
  >
    <div class="claim-details">
      <div class="row mb-3">
        <div class="col-md-6">
          <h5>Informations</h5>
          <ul class="list-unstyled">
            <li><strong>Client:</strong> {{ claim.user.name }}</li>
            <li><strong>Email:</strong> {{ claim.user.email }}</li>
            <li><strong>Date:</strong> {{ formatDate(claim.created_at) }}</li>
            <li><strong>Statut:</strong> <span :class="'badge badge-' + getStatusClass(claim.status)">{{ getStatusLabel(claim.status) }}</span></li>
          </ul>
        </div>
        <div class="col-md-6" v-if="claim.order">
          <h5>Commande associée</h5>
          <ul class="list-unstyled">
            <li><strong>N° Commande:</strong> #{{ claim.order.code }}</li>
            <li><strong>Date Commande:</strong> {{ formatDate(claim.order.date) }}</li>
          </ul>
        </div>
      </div>

      <div class="mb-4">
        <h5>Sujet</h5>
        <p>{{ claim.subject }}</p>
      </div>

      <div class="mb-4">
        <h5>Description</h5>
        <p class="description-content">{{ claim.description }}</p>
      </div>

      <div v-if="claim.admin_response">
        <h5>Réponse de l'administrateur</h5>
        <div class="admin-response p-3 bg-light rounded mb-3">
          {{ claim.admin_response }}
        </div>
        <p class="text-muted">
          <small>Répondu le: {{ formatDate(claim.response_date) }}</small>
        </p>
      </div>
    </div>

    <template #modal-footer>
      <b-button variant="secondary" @click="$emit('close')">
        Fermer
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

  methods: {
    getStatusClass(status) {
      const classes = {
        pending: 'warning',
        in_progress: 'info',
        resolved: 'success',
        rejected: 'danger'
      }
      return classes[status] || 'secondary'
    },

    getStatusLabel(status) {
      const labels = {
        pending: 'En attente',
        in_progress: 'En cours',
        resolved: 'Résolu',
        rejected: 'Rejeté'
      }
      return labels[status] || status
    },

    formatDate(date) {
      return this.$dayjs(date).format('DD/MM/YYYY HH:mm')
    }
  }
}
</script>

<style scoped>
.description-content {
  white-space: pre-line;
}

.admin-response {
  white-space: pre-line;
}
</style>