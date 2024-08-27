
<template>
    <span></span>
</template>

<script setup>

    import {defineComponent, onMounted, } from 'vue';

    import Auth from '../../store/Auth.js';

    import { useRouter } from 'vue-router';

    const router = useRouter();

    onMounted(()=>{
        if(Auth.state.user.abilities.includes('system:admin')){
            router.push('/sysadmin');
        }
        if(Auth.state.user.abilities.includes('legal_entity:admin')){
            router.push('/le_admin');
        }
    });

    defineComponent({
      name: 'DispatcherView',
    })

</script>
