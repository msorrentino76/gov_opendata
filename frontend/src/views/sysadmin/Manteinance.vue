<template>

    <h3>Aggiornamento sistema</h3>
    
    <el-card class="box-card">

      <!--el-tabs type="border-card" tab-position="left"-->
      <el-tabs type="border-card">

        <el-tab-pane label="User">
          
        </el-tab-pane>

        <el-tab-pane label="Config">Config</el-tab-pane>
        
        <el-tab-pane label="Role">Role</el-tab-pane>
        
        <el-tab-pane label="Available Constraint">
          
          <el-button @click="getDataflow('all')" type="primary" :loading="processing" :disabled="processing">Analizza tutti i dataflow</el-button>
          <el-button @click="getDataflow('new')" type="success" :loading="processing" :disabled="processing">Analizza i nuovi dataflow</el-button>
          <el-button @click="getDataflow('err')" type="danger"  :loading="processing" :disabled="processing">Analizza i dataflow in errore</el-button>

          <div v-if="true" class="process-screen">
            Processing: {{  processed }} / {{ total_row }} - Errori: {{ processed_err }}
            <br><br>
            <el-progress type="circle" :percentage="progress_percent" />
          </div>

          <el-button @click="process" type="primary" :loading="processing" :disabled="dataflow.length == 0">Inizia importazione</el-button>

          <el-table :data="dataflow" style="width: 75%" v-loading="loading" empty-text="Nessun risultato trovato"  height="600" >
            <el-table-column prop="id"   label="ID" width="48" />
            <el-table-column prop="name" label="Dataflow"/>
            <el-table-column label="Stato">
              <template v-slot="scope">
                <span    v-if="getStatus(scope.row.id) == 'ready'">ready...   </span>
                <el-icon v-if="getStatus(scope.row.id) == 'processing'" class="is-loading"><Loading /></el-icon>
                <el-icon v-if="getStatus(scope.row.id) == 'ok'"    color="#67C23A" class="no-inherit"><Check /></el-icon>                
                <el-icon v-if="getStatus(scope.row.id) == 'error'" color="#F56C6C" class="no-inherit"><Close /></el-icon>
              </template>
            </el-table-column>
            <el-table-column prop="error_msg" label="Messaggio d'errore"/>
          </el-table>

        </el-tab-pane>

      </el-tabs>

    </el-card>

</template>

<script setup>

  import {ref, computed, onMounted, defineComponent} from 'vue';
  import {list, read} from '../../utils/service.js'

  import {Check, Loading, Close} from '@element-plus/icons-vue';

  const loading     = ref(false);

  const processing  = ref(false);
  const processed   = ref(0);
  const processed_err = ref(0);
  const total_row     = ref(0); //così non ottengo NaN dividendo per 0
  const progress_percent = computed(() => {
    return total_row.value == 0 ? 0 : Number(((processed.value / total_row.value) * 100).toFixed(2));
  });

  const dataflow    = ref([]);
  const dataflow_index_status = ref([]);

  const process = (async() => {
    processing.value = true;
    for (const current of dataflow.value) {
      setStatus(current.id, 'processing');
      let resp = await read('sys_admin/manteinance/available_process', current.id);
      if(resp == 'error') processed_err.value++;
      setStatus(current.id, resp);
      processed.value++;
    }    
    processing.value = false;    
  });

  /*
  status:
  - ready
  - processing
  - ok
  - error
  */
  const getStatus =((dataflow_id) => {
    let local_status = dataflow_index_status.value.find(item => item.id === dataflow_id);
    return local_status.status;
  });

  const setStatus =((dataflow_id, status) => {
    let local_status = dataflow_index_status.value.find(item => item.id === dataflow_id);
    local_status.status = status;
  });

  const getDataflow = (async (type)=>{   
      processed_err.value = 0; 
      processed.value = 0;  
      loading.value  = true;
      let dataset    = await list('sys_admin/manteinance/available_dataflow/' + type);
      dataflow.value = dataset;
      loading.value  = false;  
      dataflow_index_status.value = dataflow.value.map((d) => ({'id': d.id, 'status': 'ready'}));
      total_row.value = dataflow_index_status.value.length;
    });

  onMounted(async ()=>{  
    /*    
      loading.value  = true;
      let dataset    = await list('sys_admin/manteinance/available_dataflow/all');
      dataflow.value = dataset;
      loading.value  = false;  
      dataflow_index_status.value = dataflow.value.map((d) => ({'id': d.id, 'status': 'ready'}));
      total_row.value = dataflow_index_status.value.length;
      */
    });

  defineComponent({
      name: 'ManteinanceView',
  })

</script>

<style>
.process-screen{
  padding: 20px;
  color: #909399;
}
</style>