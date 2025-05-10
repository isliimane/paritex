<template>
	<footer class="footer-section">
		<div class="footer-top">
			<div class="container">
				<div class="footer-logo">
					<router-link :to="{ name: 'home' }"><img loading="lazy" :src="settings.footer_logo" alt="preloader" class="img-fluid" width="138"/> </router-link>
				</div>
				<div class="row">
					<div class="col-sm-6 col-md-6 col-lg-3">
						<div class="footer-widget widget-border">
							<h3>{{ lang.my_account }}</h3>
							<ul class="global-list" v-if="!authUser">
								<li>
									<router-link :to="{ name: 'login' }">{{ lang.Login }}</router-link>
								</li>
								<li>
									<router-link :to="{ name: 'register' }">{{ lang.create_account }}</router-link>
								</li>
							</ul>
							<ul class="global-list" v-if="authUser && authUser.user_type == 'customer'">
								<li>
									<router-link :to="{ name: 'dashboard' }">{{ lang.my_profile }}</router-link>
								</li>
								<li>
									<router-link :to="{ name: 'change.password' }">{{ lang.change_password }} </router-link>
								</li>
								<li>
									<router-link :to="{ name: 'order.history' }">{{ lang.order_history }}</router-link>
								</li>
								<li>
									<router-link :to="{ name: 'wishlist' }">{{ lang.my_wishlist }}</router-link>
								</li>
								<li>
									<router-link :to="{ name: 'addresses' }">{{ lang.addresses }}</router-link>
								</li>
								<li>
									<router-link :to="{ name: 'track.order' }">{{ lang.track_order }}</router-link>
								</li>
								<li>
									<router-link :to="{ name: 'gift.voucher' }">{{ lang.gift_voucher }}</router-link>
								</li>
							</ul>
							<ul class="global-list" v-else-if="authUser && (authUser.user_type == 'admin' || authUser.user_type == 'staff')">
								<li
									><a target="_blank" :href="getUrl('admin/dashboard')">{{ lang.dashboard }}</a></li
								>
								<li
									><a target="_blank" :href="getUrl('admin/profile')">{{ lang.my_profile }}</a></li
								>
								<li
									><a target="_blank" :href="getUrl('admin/password-change')">{{ lang.change_password }}</a></li
								>
							</ul>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-3">
						<div class="footer-widget widget-border">
							<h3>{{ lang.useful_links }}</h3>
							<ul class="global-list" v-for="(link, i) in usefulLinks" :key="i">
								<li>
									<router-link :to="link.url">{{ link.label }}</router-link>
								</li>
							</ul>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-3">
						<div class="footer-widget widget-border">
							<h3>{{ lang.contact_us }}</h3>
							<div class="address">
								<ul class="global-list">
									<li>
										<h4><span class="mdi mdi-home-outline"></span>{{ lang.address }}</h4>
										<p>{{ settings.footer_contact_address }}</p>
									</li>
									<li>
										<h4><span class="mdi mdi-email-outline"></span>{{ lang.email }}</h4>
										<a :href="'mailto:' + settings.footer_contact_email">{{ settings.footer_contact_email }}</a>
									</li>
									<li>
										<h4><span class="mdi mdi-phone-outline"></span>{{ lang.phone }}</h4>
										<a :href="'tel:' + settings.footer_contact_phone">{{ settings.footer_contact_phone }}</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-3">
						<div class="footer-widget">
							<h3>{{ lang.about }}</h3>
							<div v-html="settings.about_description"></div>
							<div class="social" v-if="settings.show_social_links && settings.show_social_links == 1">
								<ul class="global-list">
									<li v-if="settings.facebook_link">
										<a target="_blank" :href="settings.facebook_link"><span class="mdi mdi-name mdi-facebook"></span></a>
									</li>
									<li v-if="settings.twitter_link">
										<a target="_blank" :href="settings.twitter_link"><span class="mdi mdi-name mdi-twitter"></span></a>
									</li>
									<li v-if="settings.linkedin_link">
										<a target="_blank" :href="settings.linkedin_link"><span class="mdi mdi-linkedin"></span></a>
									</li>
									<li v-if="settings.instagram_link">
										<a target="_blank" :href="settings.instagram_link"><span class="mdi mdi-instagram"></span></a>
									</li>
									<li v-if="settings.youtube_link">
										<a target="_blank" :href="settings.youtube_link"><span class="mdi mdi-youtube"></span></a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.footer-top -->

		<div class="footer-social">
			<div class="container">
				<ul class="global-list">
					<li v-for="(menu, i) in footerMenu" :key="i">
						<a v-if="urlCheck(menu.url)" :href="menu.url">{{ menu.label }}</a>
						<router-link :to="menu.url">{{ menu.label }}</router-link>
					</li>
				</ul>
			</div>
		</div>

		<div class="footer-bottom">
			<div class="container">
				<div class="footer-bottom-content">
					<div class="copyright">
						<p>{{ settings.copyright }}</p>
					</div>
					<div class="payment-card">
						<ul class="global-list">
							<li v-if="settings.visa_pay_banner"><img :src="getUrl('public/images/payment-method/visa.svg')" alt="visa_pay_banner" class="img-fluid footer-payment-icon" /></li>
							<li v-if="settings.master_card_pay_banner"><img :src="getUrl('public/images/payment-method/master-card.svg')" alt="master_card_pay_banner" class="img-fluid footer-payment-icon" /></li>
							<li v-if="settings.american_express_pay_banner"><img :src="getUrl('public/images/payment-method/american-express.svg')" alt="american_express_pay_banner" class="img-fluid footer-payment-icon" /> </li>
							<li v-if="settings.paypal_payment_banner"><img :src="getUrl('public/images/payment-method/paypal.svg')" alt="paypal_payment_banner" class="img-fluid footer-payment-icon" /></li>
							<li v-if="settings.apple_pay_banner"><img :src="getUrl('public/images/payment-method/apple-pay.svg')" alt="apple_pay_banner" class="img-fluid footer-payment-icon" /></li>
							<li v-if="settings.amazon_pay_banner"><img :src="getUrl('public/images/payment-method/amazon-pay.svg')" alt="amazon_pay_banner" class="img-fluid footer-payment-icon" /></li>
							<li v-if="settings.after_pay_banner"><img :src="getUrl('public/images/payment-method/after-pay.svg')" alt="after_pay_banner" class="img-fluid footer-payment-icon" /></li>
							<li v-if="settings.payment_method_banner" class="full-payment-img"><img :src="settings.payment_method_banner" alt="payment_method_banner" class="img-fluid footer-payment-icon" /></li>
						</ul>
					</div>
				</div> </div
			><!-- /.container --> </div
		><!-- /.footer-bottom -->

		<div class="mb-bottom"></div>

		<div class="yoori--cookies" v-if="checkGDPR() && gdpr">
			<div class="cookie-content" v-html="settings.gdpr"> </div>
			<div class="cookie-btn">
				<button type="button" @click="setGDPR">{{ lang.accept_all }}</button>
			</div>
		</div>
		<div class="btnTOP"><span class="icon mdi mdi-name mdi-chevron-up"></span></div>
	</footer><!-- /.footer-section -->
</template>

<script>

export default {
  name: "modern-footer",
  components: {
  },
  data() {
    return {
      gdpr: true
    };
  },
  computed: {
		usefulLinks() {
			return this.settings.useful_links;
		},
		footerMenu() {
			return this.settings.footer_menu;
		},
	},
	methods: {
		checkGDPR() {
			return !localStorage.getItem("gdpr") && this.settings.gdpr_enable == 1;
		},
		setGDPR() {
			this.gdpr = false;
			return localStorage.setItem("gdpr", "1");
		},
	},
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