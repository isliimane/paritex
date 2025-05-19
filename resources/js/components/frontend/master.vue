<template>
    <div class="main-container">
        <headNav></headNav>
        <router-view></router-view>
        <bottom></bottom>
    </div>
</template>

<script>
import headNav from "./partials/header";
import bottom from "./partials/bottom";

export default {
    name: "master",

    components: {
        headNav, bottom
    },
    watch: {
        $route(to, from) {
            if (to.meta.title && to.meta && typeof to.meta.title != 'undefined')
                document.title = to.meta.title
        }
    },
    mounted() {
        document.title = this.$route.meta.title && typeof this.$route.meta.title != 'undefined' ? this.$route.meta.title : this.settings.system_name
    }
}
</script>

<style scoped>
.main-container {
 
    background-color:rgb(255, 255, 255);
    
  
    
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    
    min-height: 100vh; 
}
</style>