<template>
    
    <User         v-if="icon=='user'" />
    <UserFilled   v-if="icon=='user-filled'" />
    <key          v-if="icon=='key'" />
    <Odometer     v-if="icon=='odometer'" />
    <Avatar       v-if="icon=='avatar'" />
    <Message      v-if="icon=='message'" />
    <UploadFilled v-if="icon=='upload-filled'" />
    <Download     v-if="icon=='download'" />

</template>

<script setup>
    
    import {defineComponent, defineProps} from 'vue';
    import {User, UserFilled, Key, Odometer, Avatar, Message, UploadFilled, Download} from '@element-plus/icons-vue';

    defineProps({
        icon  : {},
    });    

    defineComponent({
      name: 'IconEl',
    })

</script>