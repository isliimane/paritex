<template>
  <div class="claims-list">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>{{ $t('my_claims') }}</h3>
      <router-link to="/create-claim" class="btn btn-primary">
        {{ $t('new_claim') }}
      </router-link>
    </div>
    
    <div v-if="loading" class="text-center">
      <loading-spinner />
    </div>
    
    <div v-else-if="claims.data.length > 0">
      <div v-for="claim in claims.data" :key="claim.id" class="claim-item card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h5>{{ claim.subject }}</h5>
            <span :class="`badge badge-${statusClass(claim.status)}`">
              {{ $t(claim.status) }}
            </span>
          </div>
          
          <p class="text-muted mb-2">
            {{ $t('created_at') }}: {{ claim.created_at }}
          </p>
          
          <p>{{ claim.description }}</p>
          
          <router-link :to="`/claims/${claim.id}`" class="btn btn-sm btn-outline-primary">
            {{ $t('view_details') }}
          </router-link>
        </div>
      </div>
      
      <pagination :data="claims" @pagination-change-page="getClaims" />
    </div>
    
    <div v-else class="text-center">
      <p>{{ $t('no_claims_found') }}</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      claims: {},
      loading: true
    }
  },
  async created() {
    await this.getClaims();
  },
  methods: {
    async getClaims(page = 1) {
      this.loading = true;
      try {
        const response = await this.$axios.get(`/claims?page=${page}`);
        this.claims = response.data.claims;
      } catch (error) {
        this.$toast.error(error.response?.data?.error || this.$t('error_occurred'));
      } finally {
        this.loading = false;
      }
    },
    statusClass(status) {
      const classes = {
        pending: 'warning',
        in_progress: 'info',
        resolved: 'success',
        rejected: 'danger'
      };
      return classes[status] || 'secondary';
    }
  }
}
</script>