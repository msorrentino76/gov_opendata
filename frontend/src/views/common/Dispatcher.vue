
<template>
    <span></span>
</template>

<script setup>

    import {defineComponent, onMounted, } from 'vue';

    //import Auth from '../../store/Store.js';
    import { useStore } from 'vuex';
    const store = useStore();

    import { useRouter } from 'vue-router';

    const router = useRouter();

    onMounted(()=>{
        if(store.state.login.user.abilities.includes('system:admin')){
            router.push('/sysadmin');
        }
        if(store.state.login.user.abilities.includes('legal_entity:admin')){
            router.push('/le_admin');
        }
    });

    defineComponent({
      name: 'DispatcherView',
    })

</script>
