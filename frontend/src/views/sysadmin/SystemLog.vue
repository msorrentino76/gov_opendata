<template>

    <h3>System Log</h3>

    <el-card class="box-card">
      
      <el-row :gutter="16">
      
        <el-col :span="6">
            
          <el-table :data="logsFile" style="width: 100%" v-loading="loadingList" >

              <el-table-column prop="filename" label="Log File"/>
              
              <el-table-column width="128">
                
                <template #default="scope">
                
                  <el-popconfirm title="Procedere con la cancellazione?" @confirm="handleDelete(scope.$index, scope.row)" confirm-button-text="Si">
                    <template #reference>
                      <el-button :icon="Delete"/>   
                    </template>
                  </el-popconfirm>
                
                  <el-button :icon="Search" @click="handleDetail(scope.$index, scope.row)"/>

                </template>
                
              </el-table-column>

            </el-table>

        </el-col>
      
        <el-col :span="18">

          <el-table :data="logContent" style="width: 100%" v-loading="loadingContent" height="600" empty-text="Seleziona un file">
            <el-table-column prop="content" :label="selectedFile">
              <template #default="scope">
                <div v-html="scope.row.content"></div>
              </template>
            </el-table-column>
          </el-table>

        </el-col>

      </el-row>

    </el-card>

</template>

<script setup>

  import {ref, onMounted, defineComponent} from 'vue';
  import {list, read, del} from '../../utils/service.js'

  import {Search, Delete} from '@element-plus/icons-vue';

  const loadingList    = ref(false);
  const loadingContent = ref(false);

  const selectedFile = ref('');
  const logsFile     = ref([]);
  const logContent   = ref(null);

  const handleDetail = (async (index, row) => {
    loadingContent.value = true;    
    const resp  = await read('sys_admin/log', row.filename);
    logContent.value = resp;
    selectedFile.value = row.filename;
    loadingContent.value = false;
  });

  const handleDelete = (async (index, row) => {
    loadingList.value = true;    
    const resp  = await del('sys_admin/log', row.filename);
    if(resp){
      logsFile.value = logsFile.value.filter((obj) => obj.filename !== row.filename);
    }
    loadingList.value = false;
  });

  onMounted(async ()=>{
    loadingList.value = true;
    const resp  = await list('sys_admin/logs');
    logsFile.value = resp;
    loadingList.value = false;
  });

  defineComponent({
      name: 'SystemLogView',
  })

</script>