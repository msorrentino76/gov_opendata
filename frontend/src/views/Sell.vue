
<template>

  <h3>Vendite</h3>

  <el-card class="box-card">
    
      <div class="sell-date-picker">
        <el-date-picker
          v-model="data_value"
          type="monthrange"
          unlink-panels
          range-separator="A"
          start-placeholder="Dal mese"
          end-placeholder="Al mese"
          :shortcuts="shortcuts"
          format="MMMM YYYY"
          value-format="YYYY-MM"
          @change="handleDateChange"
        />
      </div>

      <br><br>

      <el-tabs v-model="activeName" class="sell-tabs" @tab-click="handleClick" type="border-card" v-loading="loading">
        <el-tab-pane :label="usr.subject" :key="idx" :name="usr.id" v-for="(usr, idx) in sells">
          <TableEl
            entity="Attività"
            :header="[
                  {
                    field: 'id',
                    label: '#ID',
                    align: 'right',
                    sortable: true,
                    width: 128
                  },                
                  {
                    field: 'data',
                    label: 'Data vendita',
                    format: 'data',
                    sortable: true,
                  },
                  {
                    field: 'importo',
                    label: 'Importo €',
                    format: 'decimal',
                    align: 'right',
                  },
                  {
                    field: 'descrizione',
                    label: 'Descrizione vendita',
                    width: 728
                  },     
                              
              ]"  
            :attachments="{
                    field: 'documents',
                    label: 'Allegati',
                    width: 412,
                    no_data: 'Nessun allegato',
                  }"      
            :external_row="usr.sells"  
            :actions="{...(Auth.state.user.id == usr.id ? { create:{} } : {} ),}"  
            :form="{
              fields: [
                {
                  row: [
                    {
                      type: 'datapicker',
                      label: 'Data vendita',
                      name: 'data',
                      space: 12,                  
                    },
                    {
                      type: 'input-decimal',
                      label: 'Importo',
                      name: 'importo',
                      space: 12,
                    }
                  ]
                },
                {
                  row: [
                    {
                      type: 'upload',
                      label: 'Allegati',
                      name: 'allegati',
                      space: 24,
                      limit: 3,
                      uploadEndpoint: '/document/upload',
                      removeEndpoint: '/document/remove',
                      maxmbsize: 2,
                      accept: {
                        label: 'pdf',
                        mime: 'application/pdf'
                      }
                    }
                  ]
                },
                {
                  row: [
                    {
                      type: 'text',
                      label: 'Descrizione vendita',
                      name: 'descrizione',
                      space: 24,
                    }
                  ]
                },                
                {
                  row: [
                    {
                      type: 'alert-warning',
                      text: 'Attenzione! Dopo il salvataggio non sarà più possibile modificare o cancellare la vendita inserita. Verifica i dati prima di procedere.',
                      space: 24,
                    }
                  ]
                }
              ]
              }"
              :rules="{
                data: [
                  { required: true, message: 'Campo richiesto', trigger: 'blur' },
                  { type: 'date',  message: 'Inserire una data valida', trigger: ['blur', 'change'] },
                ],     
                importo: [
                  { required: true, message: 'Campo richiesto', trigger: 'blur' },
                  {/* type: 'number',  message: 'Inserire valore corretto', trigger: ['blur', 'change'] */},
                ], 
                file: [
                  { required: true, message: 'Campo richiesto', trigger: 'blur' },
                  {/* type: 'number',  message: 'Inserire valore corretto', trigger: ['blur', 'change'] */},
                ], 
                /*
                allegati : [
                  { required: true, message: 'Campo richiesto', trigger: 'blur' },
                  { type: 'string',  message: 'Inserire un testo valido', trigger: ['blur', 'change'] },
                ],
                */
                descrizione: [
                  { required: true, message: 'Campo richiesto', trigger: 'blur' },
                  { type: 'string',  message: 'Inserire un testo valido', trigger: ['blur', 'change'] },
                ],                    
              }"
              :endpoints="{create: 'user/sell',}" 
              :tableCellStyle="(( { row, column, rowIndex, columnIndex } ) => {
                console.log(row, column, rowIndex, columnIndex)
                if(columnIndex == 0 || columnIndex == 2) {
                  return {'text-align': 'right'};
                }
                if(columnIndex == 1) {
                  return {'text-align': 'center'};
                }                    
                return {'text-align': 'left'}              
                })"

          />
        </el-tab-pane>
      </el-tabs>

  </el-card>

</template>

<script setup>

import Auth from '@/store/Auth';

import {defineComponent, ref, onMounted, onUnmounted} from 'vue';

import {filteredList} from '../utils/service.js';

import TableEl from '../components/Table.vue';

const activeName = ref(Auth.state.user.id);

const loading = ref(true);
const sells   = ref([]);

const data_value = ref(Auth.state.data_filter ? Auth.state.data_filter : 
    [
      (new Date()).getFullYear() + '-' + ((new Date()).getMonth() + 1).toString().padStart(2, '0'), 
      (new Date()).getFullYear() + '-' + ((new Date()).getMonth() + 1).toString().padStart(2, '0')
    ]);

const shortcuts = [
  {
    text: 'Mese corrente',
    value: [new Date(), new Date()],
  },
  {
    text: 'Ultimi 3 mesi',
    value: () => {
      const end = new Date()
      const start = new Date()
      start.setMonth(start.getMonth() - 3)
      return [start, end]
    },
  },    
  {
    text: 'Ultimi 6 mesi',
    value: () => {
      const end = new Date()
      const start = new Date()
      start.setMonth(start.getMonth() - 6)
      return [start, end]
    },
  },    
  {
    text: 'Ultimo anno',
    value: () => {
      const end = new Date()
      const start = new Date()
      start.setMonth(start.getMonth() - 12)
      return [start, end]
    },
  },

]

const handleClick = (tab, event) => {
  console.log(tab, event)
}

const handleDateChange = (v)  => {
  Auth.commit('setDataFilter', data_value.value);
  filterData(v[0], v[1]);
}

const filterData = (async (from, to) => {
  loading.value = true;
  let resp = await filteredList('user/sells', {from: from, to: to});
  if(resp && !resp.errors){
    sells.value = resp;
  }
  loading.value = false;
})

onMounted(async ()=>{
  filterData(data_value.value[0], data_value.value[1]);
})

onUnmounted(()=>{});

defineComponent({
    name: 'SellView',
})

</script>

<style scoped>
.sell-tabs > .el-tabs__content {
padding: 32px;
color: #6b778c;
}

.sell-date-picker {
display: flex;
width: 40%;
padding: 0;
flex-wrap: wrap;
}
.sell-date-picker .block {
padding: 30px 0;
text-align: center;
border-right: solid 1px var(--el-border-color);
flex: 1;
}
.sell-date-picker .block:last-child {
border-right: none;
}
.sell-date-picker .demonstration {
display: block;
color: var(--el-text-color-secondary);
font-size: 14px;
margin-bottom: 20px;
}
</style>