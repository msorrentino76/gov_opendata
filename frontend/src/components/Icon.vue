<template>
    
    <User       v-if="icon=='user'" />
    <UserFilled v-if="icon=='user-filled'" />
    <key        v-if="icon=='key'" />
    <Odometer   v-if="icon=='odometer'" />
    
</template>

<script setup>
    
    import {defineComponent, defineProps} from 'vue';
    import {User, UserFilled, Key, Odometer} from '@element-plus/icons-vue';

    defineProps({
        icon  : {},
    });    

    defineComponent({
      name: 'IconEl',
    })

</script>