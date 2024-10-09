
<template>

<el-row :gutter="16">
      
      <el-col :span="6">
          
        <el-table :data="files" style="width: 100%" v-loading="loadingFiles" >

            <el-table-column prop="filename" label="Nome file"/>
            
            <el-table-column width="128">
              
              <template #default="scope">
              
                <el-popconfirm title="Procedere con la cancellazione?" @confirm="handleDelete(scope.$index, scope.row)" confirm-button-text="Si">
                  <template #reference>
                    <el-button :icon="Delete"/>   
                  </template>
                </el-popconfirm>
              
                <el-button :icon="Download" @click="handleDownload(scope.$index, scope.row)"/>

              </template>
              
            </el-table-column>

          </el-table>

      </el-col>
    
      <el-col :span="18">

        <br><br>

        <el-button type="primary" @click="avviaElaborazione">Avvia Elaborazione</el-button>

        <el-table :data="esitoElaborazione" style="width: 100%" v-loading="loadingElaborazione" height="600" empty-text="Pronto per l'elaborazione">
            <el-table-column prop="timestamp" label="Timestamp"/>
            <el-table-column prop="message"   label="Attività" />
        </el-table>

      </el-col>

    </el-row>

</template>

<script setup>
  
import {defineComponent, defineProps, onMounted, ref} from 'vue';
import {Download, Delete} from '@element-plus/icons-vue';

import {list, download, del} from '../../../../utils/service.js'

import store  from '../../../../store/Store.js'; 

    const files        = ref([]);
    const loadingFiles = ref(false);
    
    const esitoElaborazione   = ref([]);
    const loadingElaborazione = ref(false);

    const handleDownload = (async (index, row) => {        
        loadingFiles.value = true;
        await download({
            'content': store.state.config.applicationBaseURL +'/api/sys_admin/manteinance/dwnld/' + props.type + '/' + row.filename,
            'name': row.filename
        });
        loadingFiles.value = false;
    });

    const handleDelete = (async (index, row) => {
        loadingFiles.value = true;    
        const resp = await del('sys_admin/manteinance/files/' + props.type , row.filename);
        if(resp){
            files.value = files.value.filter((obj) => obj.filename !== row.filename);
        }
        loadingFiles.value = false;
    });

    const avviaElaborazione = (async() => {
        loadingElaborazione.value = true;
        esitoElaborazione.value = await list('sys_admin/manteinance/' + props.type);
        loadingElaborazione.value = false;
        updateListaFile();
    });

    const updateListaFile = (async() => {
        loadingFiles.value = true;
        files.value = await list('sys_admin/manteinance/files/' + props.type);
        loadingFiles.value = false;
    });

    onMounted(async() =>{
        updateListaFile();
    });

const props = defineProps({
      type: String,
    });

defineComponent({
    name: 'ManteinanceFileHandleView',
})

</script>