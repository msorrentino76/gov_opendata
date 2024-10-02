<template>

    <h3>{{ props.flow_name }}</h3>
    
    <!--el-alert title="Se non viene selezionato il valore di un filtro esso verrà escluso" show-icon type="info" /-->

    <el-row v-loading="loading">

      <el-col :span="4">
      
        <div v-for="posix in datafilter" :key="posix.name">
          <p>{{ posix.label }}</p>
          <el-select  
            v-if="posix.type == 'select'"          
            v-model="selectedfilter[posix.name]"
            multiple
            filterable
            placeholder="Seleziona"
            style="width: 100%"            
          >
            <el-option
              v-for="item in posix.options"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
      
        </div>

      </el-col>
      
      <el-col :span="20">

      </el-col>

    </el-row>

</template>

<script setup>

  import {defineComponent, defineProps, onMounted, onUpdated, ref} from 'vue';

  import {create} from '../../utils/service.js'

  const loading = ref(false);

  const datafilter = ref([]);
  const selectedfilter = ref([]);

    onMounted(async ()=>{
      loading.value = true;
      datafilter.value = await create('le_admin/datafilter', {'id_datastructure': props.id_datastructure, 'flow_ref': props.flow_ref}, true);
      loading.value = false;
    });

    onUpdated(async ()=>{
      loading.value = true;
      //datafilter.value = await create('le_admin/datafilter', {'id_datastructure': props.id_datastructure, 'flow_ref': props.flow_ref}, true);
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
