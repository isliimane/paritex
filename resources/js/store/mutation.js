export default {
    getBlogs(state, data) {
        return state.blogs = data;
    },
    blogDetails(state, data) {
        return state.blogsDetails = data;
    },
    getContactPage(state, data) {
        return state.contact_page = data;
    },
    getComplaintPage(state, data) {
        return state.complaint_page = data;
    },
    getSettings(state, data) {
        return state.settings = data;
    },

    getAllCampaign(state, data,) {
        let campaign = data.data;
        for (let i = 0; i < campaign.length; i++) {
            let found = state.all_campaigns.some(el => el.id === campaign[i].id);
            if (!found)
                state.all_campaigns.push(campaign[i])
        }
        state.campaign_paginate_url = data.next_page_url
        state.campaign_paginate_page++
        return state.all_campaigns
    },

    getCampaignProducts(state, data) {
        let campaigns = state.campaign_products;
        let products = {
            data: []
        };

        let index = campaigns.findIndex(campaign => campaign.slug == data.slug);

        if (index > -1) {
            products.data = campaigns[index].products.data;
            for (let j = 0; j < data.products.data.length; j++) {
                products.data.push(data.products.data[j]);
            }

            products.next_page_url = data.products.next_page_url;
            return state.campaign_products[index].products = products;
        }

        return state.campaign_products.push(data);
    },
    getCampaignBrands(state, data) {
        let campaigns = state.campaign_brands;
        let products = {
            data: []
        };
        let index = campaigns.findIndex(campaign => campaign.slug == data.slug);

        if (index > -1) {
            products.data = campaigns[index].brands.data;
            for (let j = 0; j < data.brands.data.length; j++) {
                products.data.push(data.brands.data[j]);
            }

            products.next_page_url = data.brands.next_page_url;
            return state.campaign_brands[index].brands = products;
        }

        return state.campaign_brands.push(data);
    },

    getBlogCategories(state, data) {
        return state.blogCategories = data;
    },
    getBlogTags(state, data) {
        return state.blogTags = data;
    },

    getDailyDeals(state, data) {
        return state.daily_deals = data;
    },
    getRecentPosts(state, data) {
        return state.recent_posts = data;
    },
    productDetails(state, data) {
        return state.product_details.push({slug: data.slug, product: data});
    },
    getReplyForm(state, data) {
        return state.reply_form = data;
    },
    getBlogComments(state, data) {
        return state.blog_comments = data;
    },
    getPagedData(state, data) {
        return state.get_page_data = data;
    },
    getCompareList(state, data) {
        return state.compare_list = data;
    },
    getProfileOrders(state, data) {
        return state.profile_orders = data;
    },
    getUserOrderList(state, data) {
        return state.userOrderList = data;
    },
    getProductAttributes(state, data) {
        return state.product_attributes = data;
    },
    getUserCoupons(state, data) {
        return state.userCoupons = data;
    },
    getWishlists(state, data) {
        return state.allWishlist = data;
    },
    getUserWishlist(state, data) {
        return state.wishlist_products.push(data);
    },
    getRemoveWishlist(state, data) {
        var index = state.wishlist_products.findIndex(c =>
            c.id == data.id
        );
        return state.wishlist_products.splice(index, 1);
    },
    getUserCompare(state, data) {
        return state.compare_products.push(data);
    },
    getRemoveCompare(state, data) {
        var index = state.compare_products.findIndex(c =>
            c.id == data.id
        );
        return state.compare_products.splice(index, 1);
    },
    getDefaultCurrency(state, data) {
        return state.default_currency = data;
    },
    getCarts(state, data) {
        return state.carts = data;
    },
    getHomeComponents(state, data) {
        return state.home_components = data;
    },
    getHomeResults(state, data) {
        return state.home_results = data;
    },
    getCountCompare(state, data) {
        return state.countCompare = data;
    },
    setShimmer(state, data) {
        return state.shimmer = data;
    },
    getAllCategories(state, data) {
        return state.allCategories = data;
    },
    getAllBrands(state, data) {
        let brands = data.data;
        for (let i = 0; i < brands.length; i++) {
            let found = state.allBrands.some(el => el.id === brands[i].id);
            if (!found)
                state.allBrands.push(brands[i])
        }
        state.brand_paginate_url = data.next_page_url
        state.brand_paginate_page++
        return state.allBrands;
    },
   
    getDefaultAssets(state, data) {
        return state.default_assets = data;
    },
    getFilterLoaded(state, data) {
        return state.filter_loaded = data;
    },
    getProducts(state, data) {
        return state.products = data;
    },
    getCategoryProducts(state, data) {
        var index = state.category_products.findIndex(c =>
            c.slug == data.slug
        );
        if (index > -1) {
            state.category_products.splice(index, 1);
        }
        return state.category_products.push(data);
    },
    getBrandProducts(state, data) {
        var index = state.brand_products.findIndex(c =>
            c.slug == data.slug
        );
        if (index > -1) {
            state.brand_products.splice(index, 1);
        }
        return state.brand_products.push(data);
    },
    getCategoryPage(state, data) {
        return state.category_page.push(data);
    },
    getBrandPage(state, data) {
        return state.brand_page.push(data);
    },
   
    getOfferProducts(state, data) {
        return state.offer_products = data;
    },
    getSellingProducts(state, data) {
        return state.selling_products = data;
    },
    getUserAddresses(state, data) {
        return state.userAddresses = data
    },
    getCountries(state, data) {
        return state.countries = data
    },
    getAddons(state, data) {
        return state.addons = data
    },
    getWalletRecharges(state, data) {
        for (let i = 0; i < data.data.length; i++) {
            let found = state.wallet_recharges.some(el => el.id === data.data[i].id);
            if (!found) {
                if (data.unshift == 1) {
                    state.wallet_recharges.unshift(data.data[i]);
                } else {
                    state.wallet_recharges.push(data.data[i])
                }
            }
        }
        return state.wallet_recharges;
    },
    getRewards(state, data) {
        for (let i = 0; i < data.length; i++) {
            let found = state.rewards.some(el => el.id === data[i].id);
            if (!found) {
                state.rewards.push(data[i])
            }
        }
        return state.rewards;
    },
    getOrderUrl(state, data) {
        return state.order_urls = data;
    },
    getInvoices(state, data) {
        return state.invoices = data;
    },
   

    setLoginRedirection(state, data) {
        return state.login_redirect = data;
    },
    setTotalReward(state, data) {
        return state.total_reward = data;
    },
    setNotifications(state, data) {
        return state.notifications = data;
    },
    setActiveModal(state, data) {
        return state.active_modal = data;
    },
    setLangKeywords(state, data) {
        return state.lang_keywords = data;
    },
    commonData(state, data) {
        return state.common_data = data;
    },
    setPaymentData(state, data) {
        return state.payment_data = data;
    },

    setActiveTab(state, data) {
        return state.active_tab = data;
    },
    setPriceRange(state, data) {
        return state.price_range = data;
    },
    setCountryList(state, data) {
        return state.countryList = data;
    },
    setResponseDone(state, data) {
        return state.response_done = data;
    },
    setResponseCheck(state, data) {
        return state.responseCheck = data;
    },
    setSliderBanner(state, data) {
        return state.slider_banners = data;
    },
    setMobileNo(state, data) {
        return state.phone = data;
    },
    setHomeScroller(state, data) {
        return state.home_scroller = data;
    },
    setSidebar(state, data) {
        return state.sidebar_category = data;
    },
    setSmCategory(state, data) {
        return state.show_sm_category = data;
    },
    setSmHomeMenu(state, data) {
        return state.show_sm_home_menu = data;
    },
    setCampaignStore(state, data) {
        return state.campaign_store.push(data);
    },

    getShopCategories(state, data) {
        return state.shop_categories = data;
    },
    getShopBrands(state, data) {
        return state.shop_brands = data;
    },
    getShopColors(state, data) {
        return state.shop_colors = data;
    },
    getShopAttributes(state, data) {
        return state.shop_attributes.push({ slug : data.slug,attributes : data.attributes })
    },
}
