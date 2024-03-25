<template>

    <h3>Attività</h3>

    <el-card class="box-card">
      
        <div class="act-date-picker">
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

        <el-tabs v-model="activeName" class="act-tabs" @tab-click="handleClick" type="border-card" v-loading="loading">
          <el-tab-pane :label="usr.subject" :key="idx" :name="usr.id" v-for="(usr, idx) in activities">
            <TableEl
              showSummary
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
                      label: 'Data attività',
                      format: 'data',
                      sortable: true,
                    },
                    {
                      field: 'ore',
                      label: 'Ore impiegate',
                      format: 'numeric',
                      align: 'right',
                    },
                    {
                      field: 'descrizione',
                      label: 'Descrizione attività',
                      width: 1024
                    }                
                ]"
              :external_row="usr.activities"  
              :actions="{...(Auth.state.user.id == usr.id ? { create:{} } : {} ),}"  
              :form="{
                fields: [
                  {
                    row: [
                      {
                        type: 'datapicker',
                        label: 'Data attività',
                        name: 'data',
                        space: 12,                  
                      },
                      {
                        type: 'input-numeric',
                        min: 1,
                        max:24,
                        label: 'Ore impiegate',
                        name: 'ore',
                        space: 12,
                        controlsPosition: 'right'
                      }
                    ]
                  },
                  {
                    row: [
                      {
                        type: 'text',
                        label: 'Descrizione dell\'attività',
                        name: 'descrizione',
                        space: 24,
                      }
                    ]
                  },
                  {
                    row: [
                      {
                        type: 'alert-warning',
                        text: 'Attenzione! Dopo il salvataggio non sarà più possibile modificare o cancellare l\'attività inserita. Verifica i dati prima di procedere.',
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
                  ore: [
                    { required: true, message: 'Campo richiesto', trigger: 'blur' },
                    { type: 'number',  message: 'Inserire valore intero', trigger: ['blur', 'change'] },
                  ], 
                  descrizione: [
                    { required: true, message: 'Campo richiesto', trigger: 'blur' },
                    { type: 'string',  message: 'Inserire un testo valido', trigger: ['blur', 'change'] },
                  ],                    
                }"
                :endpoints="{create: 'user/act',}" 
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

  const loading    = ref(true);
  const activities = ref([]);

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
    Auth.commit('setDataFilter', data_value.value)
    filterData(v[0], v[1]);
  }

  const filterData = (async (from, to) => {
    loading.value = true;
    let resp = await filteredList('user/acts', {from: from, to: to});
    if(resp && !resp.errors){
      activities.value = resp;
    }
    loading.value = false;
  })

  onMounted(async ()=>{
    filterData(data_value.value[0], data_value.value[1]);
  })

  onUnmounted(()=>{});

  defineComponent({
      name: 'ActivityView',
  })

</script>

<style scoped>
.act-tabs > .el-tabs__content {
  padding: 32px;
  color: #6b778c;
}

.act-date-picker {
  display: flex;
  width: 40%;
  padding: 0;
  flex-wrap: wrap;
}
.act-date-picker .block {
  padding: 30px 0;
  text-align: center;
  border-right: solid 1px var(--el-border-color);
  flex: 1;
}
.act-date-picker .block:last-child {
  border-right: none;
}
.act-date-picker .demonstration {
  display: block;
  color: var(--el-text-color-secondary);
  font-size: 14px;
  margin-bottom: 20px;
}
</style>