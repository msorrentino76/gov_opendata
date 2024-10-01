<template>

    <h3>{{ props.flow_name }}</h3>
    


</template>

<script setup>

  import {defineComponent, defineProps, onMounted, onUpdated, ref} from 'vue';

  import {create} from '../../utils/service.js'

  const loading = ref(false);

  const datafilter = ref(false);

    onMounted(async ()=>{
      loading.value = true;
      datafilter.value = await create('le_admin/datafilter', {'id_datastructure': props.id_datastructure, 'flow_ref': props.flow_ref}, true);
      loading.value = false;
    });

    onUpdated(async ()=>{
      loading.value = true;
      datafilter.value = await create('le_admin/datafilter', {'id_datastructure': props.id_datastructure, 'flow_ref': props.flow_ref}, true);
      loading.value = false;
    });

    const props = defineProps({
      flow_name        : String,
      flow_ref         : String,
      id_datastructure : String,
    });

  defineComponent({
      name: 'DatasetFilterView',
  })

</script>
