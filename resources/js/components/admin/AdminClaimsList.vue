<template>
  <div class="admin-claims-container">
    <!-- Header avec stats -->
    <div class="claims-header mb-5">
      <h2>Gestion des Réclamations</h2>
      <div class="stats-grid">
        <stat-card 
          v-for="stat in stats" 
          :key="stat.status"
          :title="stat.label"
          :value="stat.count"
          :color="stat.color"
          :icon="stat.icon"
        />
      </div>
    </div>

    <!-- Filtres -->
    <div class="filters-section mb-4">
      <div class="row">
        <div class="col-md-4">
          <select v-model="filters.status" class="form-control" @change="fetchClaims">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="in_progress">En cours</option>
            <option value="resolved">Résolu</option>
            <option value="rejected">Rejeté</option>
          </select>
        </div>
        <div class="col-md-4">
          <input 
            v-model="filters.search" 
            type="text" 
            class="form-control" 
            placeholder="Rechercher..."
            @keyup.enter="fetchClaims"
          >
        </div>
        <div class="col-md-4">
          <button @click="resetFilters" class="btn btn-outline-secondary">
            Réinitialiser
          </button>
        </div>
      </div>
    </div>

    <!-- Liste des réclamations -->
    <div class="claims-list">
      <div v-if="loading" class="text-center py-5">
        <loading-spinner />
      </div>

      <template v-else>
        <div v-if="claims.data && claims.data.length > 0">
          <div 
            v-for="claim in claims.data" 
            :key="claim.id"
            class="claim-item card mb-3"
            :class="'status-' + claim.status"
          >
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h5 class="card-title">{{ claim.subject }}</h5>
                  <p class="card-subtitle mb-2 text-muted">
                    <strong>Client:</strong> {{ claim.user.name }} ({{ claim.user.email }})
                  </p>
                  <p class="card-subtitle mb-2 text-muted">
                    <strong>Date:</strong> {{ formatDate(claim.created_at) }}
                  </p>
                </div>
                <span class="badge" :class="'badge-' + getStatusClass(claim.status)">
                  {{ getStatusLabel(claim.status) }}
                </span>
              </div>

              <div class="claim-content mt-3">
                <p class="card-text">{{ claim.description }}</p>
                
                <div v-if="claim.order" class="order-info mb-3">
                  <strong>Commande associée:</strong> 
                  #{{ claim.order.code }} - {{ formatDate(claim.order.date) }}
                </div>
              </div>

              <div class="actions mt-3">
                <button 
                  @click="openDetailsModal(claim)"
                  class="btn btn-sm btn-outline-primary mr-2"
                >
                  Détails
                </button>
                <button 
                  @click="openResponseModal(claim)"
                  class="btn btn-sm btn-primary"
                >
                  Répondre
                </button>
              </div>
            </div>
          </div>

          <pagination 
            :data="claims" 
            @pagination-change-page="fetchClaims"
            class="mt-4"
          />
        </div>
        <div v-else class="empty-state text-center py-5">
          <i class="mdi mdi-inbox-remove-outline display-4 text-muted"></i>
          <h4 class="mt-3">Aucune réclamation trouvée</h4>
          <p class="text-muted">Aucune réclamation ne correspond à vos critères de recherche</p>
        </div>
      </template>
    </div>

    <!-- Modals -->
    <claim-details-modal 
      v-if="selectedClaim"
      :claim="selectedClaim"
      :show="showDetailsModal"
      @close="showDetailsModal = false"
    />

    <claim-response-modal 
      v-if="selectedClaim"
      :claim="selectedClaim"
      :show="showResponseModal"
      @close="showResponseModal = false"
      @saved="handleResponseSaved"
    />
  </div>
</template>

<script>

import ClaimDetailsModal from './AdminClaimDetails';
import ClaimResponseModal from './AdminClaimResponse';

export default {
   
  components: {
    Pagination,
    LoadingSpinner,
    StatCard,
    ClaimDetailsModal,
    ClaimResponseModal
  },

  data() {
    return {
      claims: {},
      stats: [],
      loading: false,
      filters: {
        status: '',
        search: ''
      },
      selectedClaim: null,
      showDetailsModal: false,
      showResponseModal: false
    }
  },

  computed: {
    ...mapGetters(['isAdmin'])
  },

  created() {
    this.fetchClaims()
    this.fetchStats()
  },

  methods: {
    async fetchClaims(page = 1) {
      this.loading = true
      try {
        const params = {
          page,
          ...this.filters
        }

        const response = await this.$axios.get('/admin/claims', { params })
        this.claims = response.data.claims
      } catch (error) {
        this.$toast.error(this.$getErrorMessage(error))
      } finally {
        this.loading = false
      }
    },

    async fetchStats() {
      try {
        const response = await this.$axios.get('/admin/claims-stats')
        this.stats = [
          { status: 'total', label: 'Total', count: response.data.stats.total, color: 'info', icon: 'mdi-inbox' },
          { status: 'pending', label: 'En attente', count: response.data.stats.pending, color: 'warning', icon: 'mdi-clock' },
          { status: 'in_progress', label: 'En cours', count: response.data.stats.in_progress, color: 'primary', icon: 'mdi-progress-wrench' },
          { status: 'resolved', label: 'Résolues', count: response.data.stats.resolved, color: 'success', icon: 'mdi-check-circle' },
          { status: 'rejected', label: 'Rejetées', count: response.data.stats.rejected, color: 'danger', icon: 'mdi-close-circle' }
        ]
      } catch (error) {
        this.$toast.error(this.$getErrorMessage(error))
      }
    },

    resetFilters() {
      this.filters = {
        status: '',
        search: ''
      }
      this.fetchClaims()
    },

    openDetailsModal(claim) {
      this.selectedClaim = claim
      this.showDetailsModal = true
    },

    openResponseModal(claim) {
      this.selectedClaim = claim
      this.showResponseModal = true
    },

    handleResponseSaved() {
      this.fetchClaims()
      this.fetchStats()
      this.showResponseModal = false
    },

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
.claims-header {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.claim-item {
  transition: all 0.3s ease;
}

.claim-item:hover {
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.status-pending {
  border-left: 4px solid #ffc107;
}

.status-in_progress {
  border-left: 4px solid #17a2b8;
}

.status-resolved {
  border-left: 4px solid #28a745;
}

.status-rejected {
  border-left: 4px solid #dc3545;
}

.empty-state {
  background: #f8f9fa;
  border-radius: 8px;
}
</style>