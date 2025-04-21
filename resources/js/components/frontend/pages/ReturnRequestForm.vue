<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h4>Demande de retour</h4>
          </div>
          
          <div class="card-body">
            <form @submit.prevent="submitReturnRequest">
              <div class="form-group">
                <label>Votre commande</label>
                <select 
                  v-model="orderId"
                  class="form-control" 
                  @change="onOrderSelect()"
                  :disabled="loadingOrders" 
                  required
                >
                  <option value="" disabled>Sélectionnez une commande</option>
                  <option 
                    v-for="order in userOrders" 
                    :value="order.id"
                    :key="order.id"
                  >
                    Commande #{{ order.code }} - {{ order.date }} ({{ order.total_payable }}€)
                  </option>
                </select>
                
                <div v-if="loadingOrders" class="mt-2 text-muted">
                  <i class="fas fa-spinner fa-spin"></i> Chargement des commandes...
                </div>
                <div v-else-if="userOrders && userOrders.length === 0" class="mt-2 text-warning">
                  Aucune commande éligible trouvée.
                </div>

                <!-- Détails de la commande sélectionnée -->
                <div v-if="selectedOrder" class="card mt-3">
                  <div class="card-header bg-light">
                    <h5 class="mb-0">Détails de la commande #{{ selectedOrder.code }}</h5>
                  </div>
                  <div class="card-body">
                    <ul class="list-group">
                      <li 
                        v-for="(order_detail, index) in selectedOrder.order_details" 
                        :key="index"
                        class="list-group-item d-flex justify-content-between align-items-center"
                      >
                        <span>
                          {{ order_detail.quantity }} × {{ order_detail.product.product_name }}
                        </span>
                        <span 
                          v-if="!order_detail.is_returnable"
                          class="badge bg-warning"
                        >
                          Non retournable
                        </span>
                      </li>
                    </ul>
                    <div class="mt-3 fw-bold">
                      Total : {{ selectedOrder.total_payable }}€
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group mt-3">
                <label>Raison du retour <small class="text-muted">(minimum 20 caractères)</small></label>
                <textarea 
                  v-model="reason" 
                  class="form-control"
                  rows="5"
                  required
                  placeholder="Décrivez en détail la raison de votre retour..."
                ></textarea>
                <div class="text-end small" :class="{'text-danger': reason.length < 20}">
                  {{ reason.length }}/20 caractères
                </div>
              </div>

              <button 
                type="submit" 
                class="btn btn-primary mt-3"
                :disabled="isLoading || !isFormValid"
              >
                <template v-if="isLoading">
                  <i class="fas fa-spinner fa-spin"></i> Envoi en cours...
                </template>
                <template v-else>
                  <i class="fas fa-paper-plane"></i> Envoyer la demande
                </template>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReturnRequestForm',
  data() {
    return {
      orderId: null,
      reason: '',
      isLoading: false,
      loadingOrders: false,
      selectedOrder: null
    };
  },
  mounted() {
        this.getUserOrders();
  },
  computed: {
    userOrders() {
            return this.$store.getters.getUserOrders;
        },
    isFormValid() {
      return this.orderId && this.reason.length >= 20;
    }
  },
 
  methods: {

    getUserOrders() {
        let url = this.getUrl('user/user-orders')
        axios.get(url).then((response) => {
        console.log("orders:",response.data.orders);

        this.$store.commit('setShimmer', false);
        this.$store.commit("getUserOrders", response.data.orders);
        }).catch(e=>console.log("err:",e));
        },


    onOrderSelect() {
      this.selectedOrder = this.userOrders.find(o => o.id == this.orderId);
      console.log("Commande sélectionnée:", this.selectedOrder.order_details[0].product);
    },
    async submitReturnRequest() {
      if (!this.validateReturn()) return;
      
      this.isLoading = true;
      try {
        const response = await axios.post('return-request', {
          order_id: this.orderId,
          reason: this.reason,
        }, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        }).then(response=>{
            console.log(response);
            toastr.success("Demande envoyée avec succès");
                  window.location.reload();

        });
      } catch (error) {
        this.handleSubmitError(error);
      } finally {
        this.isLoading = false;
      }
    },
    validateReturn() {
      if (!this.orderId) {
        this.$toast.error("Veuillez sélectionner une commande valide");
        return false;
      }
      if (this.reason.length < 20) {
        this.$toast.error("La raison doit contenir au moins 20 caractères");
        return false;
      }
      return true;
    },
    handleSubmitError(error) {
      let message = "Erreur lors de l'envoi de la demande";
      if (error.response) {
        if (error.response.status === 422) {
          message = "Données invalides: " + 
            Object.values(error.response.data.errors).join(' ');
        } else if (error.response.data?.message) {
          message = error.response.data.message;
        }
      }
      this.$toast.error(message);
    }
  },
  watch: {
    orderId(newVal) {
      this.selectedOrder = this.userOrders.find(o => o.id == newVal);
    }
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.form-control {
  border-radius: 0.375rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.list-group-item {
  transition: background-color 0.2s ease;
}

.list-group-item:hover {
  background-color: #f8f9fa;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-primary:disabled {
  background-color: #6c757d;
  border-color: #6c757d;
}

.badge {
  font-size: 0.85em;
  padding: 0.35em 0.65em;
}

.text-muted {
  color: #6c757d !important;
}
</style>