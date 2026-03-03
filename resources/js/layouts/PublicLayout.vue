<template>
    <div class="min-h-screen font-['Montserrat']">
        <PublicHeader />
        <main>
            <router-view />
        </main>
        <Footer />
    </div>
</template>

<script>
import PublicHeader from '../components/public/PublicHeader.vue';
import Footer from '../components/public/Footer.vue';

export default {
    name: 'PublicLayout',
    components: {
        PublicHeader,
        Footer,
    },
};
</script>

