<template>
  <div>
    <h2>Demandes de retour en attente</h2>
    
    <table class="table">
      <thead>
        <tr>
          <th>Commande</th>
          <th>Client</th>
          <th>Raison</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="request in pendingRequests" :key="request.id">
          <td>#{{ request.order.id }}</td>
          <td>{{ request.user.name }}</td>
          <td>{{ request.reason }}</td>
          <td>
            <button @click="processRequest(request.id, 'refund')" class="btn btn-success">
              Rembourser
            </button>
            <button @click="processRequest(request.id, 'exchange')" class="btn btn-primary">
              Échanger
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      pendingRequests: []
    }
  },
  async created() {
    const response = await axios.get('/admin/return-requests/pending');
    this.pendingRequests = response.data;
  },
  methods: {
    async processRequest(requestId, resolution) {
      try {
        const notes = prompt("Ajoutez des notes si nécessaire:");
        await axios.post(`/admin/return-requests/${requestId}/process`, {
          resolution,
          notes
        });
        alert('Demande traitée avec succès!');
        this.pendingRequests = this.pendingRequests.filter(r => r.id !== requestId);
      } catch (error) {
        alert('Erreur: ' + error.response.data.message);
      }
    }
  }
}
</script>