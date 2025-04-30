<template>
  <footer class="modern-footer">
    <div class="footer-main">
      <div class="container">
        <div class="footer-grid">
          <!-- Logo et description -->
          <div class="footer-brand">
            <router-link :to="{ name: 'home' }" class="footer-logo">
			  <img  :src="settings.logo01_logo" alt="Logo" class="img-fluid" width="138"/>
            </router-link>
            <div class="footer-about" v-html="settings.about_description"></div>
            <div class="social-links" v-if="settings.show_social_links && settings.show_social_links == 1">
              <a v-if="settings.facebook_link" :href="settings.facebook_link" target="_blank" class="social-icon">
                <i class="mdi mdi-facebook"></i>
              </a>
              <a v-if="settings.twitter_link" :href="settings.twitter_link" target="_blank" class="social-icon">
                <i class="mdi mdi-twitter"></i>
              </a>
              <a v-if="settings.instagram_link" :href="settings.instagram_link" target="_blank" class="social-icon">
                <i class="mdi mdi-instagram"></i>
              </a>
              <a v-if="settings.linkedin_link" :href="settings.linkedin_link" target="_blank" class="social-icon">
                <i class="mdi mdi-linkedin"></i>
              </a>
              <a v-if="settings.youtube_link" :href="settings.youtube_link" target="_blank" class="social-icon">
                <i class="mdi mdi-youtube"></i>
              </a>
            </div>
          </div>

          <!-- Liens rapides -->
          <div class="footer-links">
            <h3 class="footer-title">{{ lang.useful_links }}</h3>
            <ul class="links-list">
              <li v-for="(link, i) in usefulLinks" :key="i">
                <router-link :to="link.url" class="footer-link">{{ link.label }}</router-link>
              </li>
            </ul>
          </div>

          <!-- Mon compte -->
          <div class="footer-links">
            <h3 class="footer-title">{{ lang.my_account }}</h3>
            <ul class="links-list" v-if="!authUser">
              <li><router-link :to="{ name: 'login' }" class="footer-link">{{ lang.Login }}</router-link></li>
              <li><router-link :to="{ name: 'register' }" class="footer-link">{{ lang.create_account }}</router-link></li>
            </ul>
            <ul class="links-list" v-if="authUser && authUser.user_type == 'customer'">
              <li><router-link :to="{ name: 'dashboard' }" class="footer-link">{{ lang.my_profile }}</router-link></li>
              <li><router-link :to="{ name: 'change.password' }" class="footer-link">{{ lang.change_password }}</router-link></li>
              <li><router-link :to="{ name: 'order.history' }" class="footer-link">{{ lang.order_history }}</router-link></li>
              <li><router-link :to="{ name: 'wishlist' }" class="footer-link">{{ lang.my_wishlist }}</router-link></li>
            </ul>
            <ul class="links-list" v-else-if="authUser && (authUser.user_type == 'admin' || authUser.user_type == 'staff')">
              <li><a :href="getUrl('admin/dashboard')" target="_blank" class="footer-link">{{ lang.dashboard }}</a></li>
              <li><a :href="getUrl('admin/profile')" target="_blank" class="footer-link">{{ lang.my_profile }}</a></li>
            </ul>
            <ul class="links-list" v-else-if="authUser && authUser.user_type == 'seller'">
              <li><a :href="getUrl('seller/dashboard')" target="_blank" class="footer-link">{{ lang.dashboard }}</a></li>
              <li><a :href="getUrl('seller/profile')" target="_blank" class="footer-link">{{ lang.my_profile }}</a></li>
            </ul>
          </div>

          <!-- Contact -->
          <div class="footer-contact">
            <h3 class="footer-title">{{ lang.contact_us }}</h3>
            <div class="contact-info">
              <div class="contact-item">
                <i class="mdi mdi-map-marker-outline"></i>
                <p>{{ settings.footer_contact_address }}</p>
              </div>
              <div class="contact-item">
                <i class="mdi mdi-email-outline"></i>
                <a :href="'mailto:' + settings.footer_contact_email">{{ settings.footer_contact_email }}</a>
              </div>
              <div class="contact-item">
                <i class="mdi mdi-phone-outline"></i>
                <a :href="'tel:' + settings.footer_contact_phone">{{ settings.footer_contact_phone }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    
    <!-- Bouton retour en haut -->
    <button class="back-to-top" @click="scrollToTop">
      <i class="mdi mdi-chevron-up"></i>
    </button>

    <!-- Cookies GDPR -->
    <div class="cookie-consent" v-if="checkGDPR() && gdpr">
      <div class="cookie-content" v-html="settings.gdpr"></div>
      <button class="cookie-accept" @click="setGDPR">{{ lang.accept_all }}</button>
    </div>

    <chat_system v-if="addons.includes('chat_system')"></chat_system>
  </footer>
</template>

<script>
import chat_system from "../pages/addons/chat_system";

export default {
  name: "modern-footer",
  components: {
    chat_system
  },
  data() {
    return {
      gdpr: true
    };
  },
  computed: {
    usefulLinks() {
      return this.settings.useful_links;
    }
  },
  methods: {
    checkGDPR() {
      return !localStorage.getItem("gdpr") && this.settings.gdpr_enable == 1;
    },
    setGDPR() {
      this.gdpr = false;
      localStorage.setItem("gdpr", "1");
    },
    scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
  },
  mounted() {
    window.addEventListener('scroll', this.toggleBackToTop);
  },
  beforeDestroy() {
    window.removeEventListener('scroll', this.toggleBackToTop);
  }
};
</script>

<style scoped>
/* Styles de base */
.modern-footer {
  background-color:rgb(23, 131, 102);
  color: #ffffff;
  font-family: 'Roboto', sans-serif;
  position: relative;
}

.footer-main {
  padding: 60px 0 40px;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

/* Grille du footer */
.footer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 40px;
}

/* Section Brand */
.footer-brand {
  display: flex;
  flex-direction: column;
}

.footer-logo img {
  max-width: 150px;
  margin-bottom: 20px;
}

.footer-about {
  color: #b0b0b0;
  line-height: 1.6;
  margin-bottom: 20px;
}

/* Liens sociaux */
.social-links {
  display: flex;
  gap: 15px;
}

.social-icon {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.1);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.social-icon:hover {
  background: #40A578;
  transform: translateY(-3px);
}

/* Titres des sections */
.footer-title {
  color: #ffffff;
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 20px;
  position: relative;
  padding-bottom: 10px;
}

.footer-title::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 50px;
  height: 2px;
  background: #40A578;
}

/* Listes de liens */
.links-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.links-list li {
  margin-bottom: 12px;
}

.footer-link {
  color: #b0b0b0;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
}

.footer-link:hover {
  color: #40A578;
  transform: translateX(5px);
}

/* Section Contact */
.contact-info {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.contact-item i {
  color: #40A578;
  font-size: 20px;
  margin-top: 2px;
}

.contact-item p, 
.contact-item a {
  color: #b0b0b0;
  margin: 0;
  line-height: 1.6;
}

.contact-item a:hover {
  color: #40A578;
}

/* Footer bottom */
.footer-bottom {
  background:rgb(166, 22, 22);
  padding: 20px 0;
  text-align: center;
}

.footer-copyright p {
  color: #b0b0b0;
  margin: 0;
  font-size: 14px;
}

/* Bouton retour en haut */
.back-to-top {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 50px;
  height: 50px;
  background: #40A578;
  color: white;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 99;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.back-to-top.visible {
  opacity: 1;
  visibility: visible;
}

.back-to-top:hover {
  background: #369268;
  transform: translateY(-3px);
}

/* Cookie consent */
.cookie-consent {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #2a2a2a;
  color: #ffffff;
  padding: 15px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 100;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
}

.cookie-content {
  flex: 1;
  color: #b0b0b0;
  margin-right: 20px;
}

.cookie-accept {
  background: #40A578;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cookie-accept:hover {
  background: #369268;
}

/* Responsive */
@media (max-width: 768px) {
  .footer-grid {
    grid-template-columns: 1fr;
    gap: 30px;
  }
  
  .footer-main {
    padding: 40px 0 30px;
  }
  
  .cookie-consent {
    flex-direction: column;
    text-align: center;
  }
  
  .cookie-content {
    margin-right: 0;
    margin-bottom: 15px;
  }
}

@media (max-width: 480px) {
  .back-to-top {
    width: 40px;
    height: 40px;
    bottom: 20px;
    right: 20px;
  }
}
</style>