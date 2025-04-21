<!-- <template>
  <div class="claim-form">
   <h2>Formulaire de réclamation</h2>
    <h3>{{ $t('submit_claim') }}</h3>
    <form @submit.prevent="submitClaim">
      <div class="form-group">
        <label>{{ $t('subject') }}</label>
        <input v-model="form.subject" type="text" class="form-control" required>
      </div>
      
      <div class="form-group">
        <label>{{ $t('order') }} ({{ $t('optional') }})</label>
        <select v-model="form.order_id" class="form-control">
          <option value="">{{ $t('select_order') }}</option>
          <option v-for="order in userOrders" :value="order.id">
            #{{ order.code }} - {{ order.date }}
          </option>
        </select>
      </div>
      
      <div class="form-group">
        <label>{{ $t('description') }}</label>
        <textarea v-model="form.description" class="form-control" rows="5" required></textarea>
      </div>
      
      <button type="submit" class="btn btn-primary">
        {{ $t('submit_claim') }}
      </button>
    </form>
  </div>
</template>

<script>
export default {
    name: 'CreateClaim',
  data() {
    return {
      form: {
        subject: '',
        description: '',
        order_id: null
      },
      userOrders: []
    }
  },
  async created() {
    // Charger les commandes de l'utilisateur
    const response = await this.$axios.get('/user/orders');
    this.userOrders = response.data.orders;
  },
  methods: {
    async submitClaim() {
      try {
        const response = await this.$axios.post('/claims', this.form);
        this.$toast.success(response.data.message);
        this.$router.push('/claims');
      } catch (error) {
        this.$toast.error(error.response?.data?.error || this.$t('error_occurred'));
      }
    }
  }
}
</script>
-->

<!-- resources/js/components/frontend/claims/CreateClaim.vue 
<template>
  <div class="claim-form">
   <h2>Formulaire de réclamation</h2>
    <form @submit.prevent="">
      <input type="text" placeholder="Subject" v-model="subject">
      <textarea placeholder="Description" v-model="description"></textarea>
      <button type="button" @click="fakeSubmit">Simulate Submit</button>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      subject: '',
      description: ''
    };
  },
  methods: {
    fakeSubmit() {
      alert("Form would submit here (no backend connected)");
    }
  }
};
</script>

<style scoped>
.claim-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}
</style>
-->
<template>
  <div class="form-wrapper">
    <div class="form-container">
      <form  @submit.prevent="handleSubmit" class="form">
        <!-- Sujet -->
        <div class="form-group">
          <label for="subject">Sujet</label>
          <input
            type="text"
            id="subject"
            v-model.trim="form.subject"
            required
            maxlength="100"
            placeholder="Ex: Problème de livraison"
            :class="{ 'input-error': errors.subject }"
          >
          <div class="error-message" v-if="errors.subject">{{ errors.subject }}</div>
          <div class="char-counter">{{ 100 - form.subject.length }} restants</div>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label for="description">Description détaillée </label>
          <textarea
            id="description"
            v-model.trim="form.description"
            required
            rows="5"
            maxlength="500"
            placeholder="Décrivez votre réclamation..."
            :class="{ 'input-error': errors.description }"
          ></textarea>
          <div class="error-message" v-if="errors.description">{{ errors.description }}</div>
          <div class="char-counter">{{ 500 - form.description.length }} restants</div>
        </div>

        <!-- Pièce jointe -->
        <div class="form-group">
          <label>Pièce jointe (Optionnel)</label>
          <div class="file-upload-wrapper">
            <input 
              type="file"
              id="attachment"
              @change="handleFileUpload"
              accept="image/*,.pdf,.doc,.docx"
              class="file-input"
            >
            <label for="attachment" class="file-upload-label">
              <i class="mdi mdi-cloud-upload"></i>
              <div>{{ form.attachment ? form.attachment.name : 'Choisir un fichier' }}</div>
            </label>
          </div>
        </div>

        <!-- Bouton Submit -->
        <button 
          type="submit" 
          class="form-submit-btn"
          :disabled="isSubmitting"
        >
          <div v-if="isSubmitting">
            <i class="mdi mdi-loading mdi-spin"></i> Envoi...
          </div>
          <div v-else>Soumettre</div>
        </button>
      </form>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
        // Chemin vers votre image (à modifier)
      backgroundImage: require('/images/default/dark-logo.png'),
      form: {
        user_id: '',
        subject: '',
        description: '',
        attachment: null
      },
      errors: {
        subject: '',
        description: ''
      },
      isSubmitting: false
    }
  },
  methods: {

    handleSubmit() {
      if (this.validateForm()) {
        this.isSubmitting = true;
        let url = this.getUrl('send-claim');
        this.form.user_id = this.authUser.id;
                console.log(url, this.form);
axios.post(url, this.form)
          .then((response) => {
            this.loading = false;
             console.log(response);
            if (response.data.success) {
              toastr.success(this.lang.message_sent_successfully, this.lang.Success + ' !!');
             
              this.errors = [];
             this.resetForm()
             this.isSubmitting = false
            } else {
              if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + ' !!');
              this.isSubmitting = false
              }

            }
          }).catch((error) => {
        this.loading = false;
        if (error.response.status == 422) {
          this.errors = error.response.data.errors;
           this.isSubmitting = false
        }
      })
      }
    },
    validateForm() {
      let isValid = true
      
      if (!this.form.subject) {
        this.errors.subject = 'Requis'
        isValid = false
      } else if (this.form.subject.length < 5) {
        this.errors.subject = '5 caractères min'
        isValid = false
      }
      
      if (!this.form.description) {
        this.errors.description = 'Requis'
        isValid = false
      } else if (this.form.description.length < 20) {
        this.errors.description = '20 caractères min'
        isValid = false
      }
      
      return isValid
    },
    resetForm() {
      this.form = {
        subject: '',
        description: '',
        attachment: null
      }
    },
    handleFileUpload(e) {
      const file = e.target.files[0]
      if (file && file.size <= 2 * 1024 * 1024) {
        this.form.attachment = file
      } else if (file) {
        alert('Max 2MB autorisés')
      }
    }
  }
}
</script>

<style scoped>
.form-wrapper {
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.form-container {
  background: rgba(255, 255, 255, 0.9); /* Fond semi-transparent */
  backdrop-filter: blur(5px); /* Effet de flou */}

/* Style original Uiverse.io - Taille 400px */
.form-container {
 width: 500px;
  height:900;
  background: linear-gradient(rgb(196, 202, 198),rgb(255, 255, 255)) padding-box,
              linear-gradient(135deg, transparent 35%,rgb(140, 225, 142),rgb(160, 228, 34)) border-box;
  border: 2px solid transparent;
  padding: 32px 24px;
  font-size: 14px;
  font-family: inherit;
  color: white;
  display: flex;
  flex-direction: column;
  gap: 20px;
  box-sizing: border-box;
  border-radius: 16px;
  margin: 0 auto;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color:rgb(4, 20, 12);
  font-weight: 600;
  font-size: 12px;
}

input, textarea {
  width: 100%;
  padding: 12px 16px;
  border-radius: 8px;
  color: rgb(1, 8, 4);
  font-family: inherit;
  background-color: rgb(247, 242, 242);
  border: 1px solidrgb(214, 24, 24);
}

textarea {
  resize: none;
  height: 120px;
}

.input-error {
  border-color: #ff4d4d !important;
}

.error-message {
  color: #ff4d4d;
  font-size: 11px;
  margin-top: 2px;
}

.char-counter {
  font-size: 11px;
  color: #717171;
  text-align: right;
}



.form-submit-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 30%;
  padding: 12px 16px;
  background:rgb(21, 187, 87);
  border: 1px solidrgb(18, 18, 18);
  border-radius: 16px;
  color:rgb(24, 20, 20);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
    margin: 0 auto; /* Ajoutez cette ligne */
  display: block; /* Changez flex en block */
}

.form-submit-btn:hover {
  background-color: #fff;
  border-color: #fff;
}

.form-submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Upload File Style */
.file-upload-wrapper {
  margin-top: 5px;
}

.file-input {
  display: none;
}

.file-upload-label {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
 
  border: 1px solid #414141;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
}



.file-upload-label i {
  color: #40c9ff;
}
</style>