<template>
    <el-timeline style="max-width: 600px" v-loading="loading">
        <el-timeline-item
        v-for="(activity, index) in activities"
        :key="index"
        :timestamp="activity.timestamp"
        :type="activity.type"
        >
        {{ activity.content }}
        </el-timeline-item>
    </el-timeline>

</template>

<script setup>
    
    import {defineComponent, defineProps, ref, onMounted, onUpdated} from 'vue';

    import { read } from '../utils/service.js';

    const props = defineProps({
        id               : null,
        history_endpoint : null,
    });    

    //const id         = ref(props.id);
    const loading    = ref(false);
    const activities = ref([]);

    onMounted(async ()=>{await getHistory()});

    onUpdated(async ()=>{await getHistory()});

    const getHistory = async ()=>{
        //loading.value = true;
        activities.value = [{'content': 'Caricamento in corso...'}];
        activities.value = await read(props.history_endpoint, /*id.value*/ props.id);
        //loading.value = false;
    };

    defineComponent({
      name: 'HistoryEl',
    })

</script>