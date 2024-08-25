<template>

    <h3>Licenze</h3>

    <el-card class="box-card">

    <el-row>
      <el-col :span="4">
        <el-button type="success" :icon="Plus" @click="handleCreate()">Aggiungi</el-button>        
      </el-col>
    </el-row>

    <br>

    <el-table v-loading="loading" :data="filterTableData" style="width: 100%" :height="480" empty-text="Nessun risultato trovato" :row-class-name="tableRowClassName">

      <!-- FILL -->
      <el-table-column label="Amministratore"  prop="user"  :sortable="true"/>
      <el-table-column label="Ente"            prop="legal" :sortable="true"/>
      <el-table-column label="Valida Da"       prop="valida_da"    />
      <el-table-column label="Valida A"        prop="valida_a"     />
      <el-table-column label="Giorni scadenza" prop="expired_days" />      

      <el-table-column align="right">        

        <template #header>
          <el-input v-model="search" size="small" placeholder="Cerca..." />
        </template>
        
        <template #default="scope">

          <el-button type="primary" :icon="Search"   size="small" @click="handleRead(scope.$index, scope.row)">Dettagli</el-button>
          <el-button type="warning" :icon="Edit"     size="small" @click="handleUpdate(scope.$index, scope.row)">Modifica</el-button>

          <el-popconfirm title="Si confermadi voler procedere con la cancellazione?" @confirm="handleDelete(scope.$index, scope.row)" confirm-button-text="Si">
            <template #reference>
              <el-button type="danger" :icon="Delete" size="small"></el-button>  
            </template>
          </el-popconfirm>

        </template>

      </el-table-column>

    </el-table>

    <div style="padding: 16px 0 16px 0;">Risultati: {{ filterTableData.length }}</div> 

    <el-drawer v-model="openDrawer" :title="drawerTitle" direction="rtl" size="75%">

      <el-form 
        ref="formModel"
        v-loading="form_loading"
        :model="objModel"
        :rules="{}/*{
            user: [
              { required: true, message: 'Campo richiesto', trigger: 'blur' },
            ],
            legal: [
              { required: true, message: 'Campo richiesto', trigger: 'blur' },
            ],
            valida_da: [
              { required: true, message: 'Campo richiesto', trigger: 'blur' },
            ],
            valida_a: [
              { required: true, message: 'Campo richiesto', trigger: 'blur' },
            ],        
        }*/"
        :disabled="form_disable"
        label-position="top"
        status-icon
      >

      <!-- FILL -->

        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="Seleziona Utente (se assente censirlo da apposita area)" :error="form_error.user" prop="user">
              <el-select v-model="objModel.user" placeholder="Seleziona Utente" :disabled="form_action=='update'">
                <el-option v-for="item in users_option" :key="item.id" :label="item.surname + ' ' + item.name + ' - ' + item.email " :value="item.id" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="Seleziona Ente (se assente censirlo da apposita area)" :error="form_error.legal" prop="legal">
              <el-select v-model="objModel.legal" placeholder="Seleziona Ente" :disabled="form_action=='update'">
                <el-option v-for="item in legals_option" :key="item.id" :label="item.des_amm + ' (CF: ' + item.cf + ')'" :value="item.id" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">

          <el-col :span="12">
            <el-form-item label="Data inizio licenza" :error="form_error.valida_da" prop="valida_da">
              <el-date-picker
                v-model="objModel.valida_da"
                type="date"
                placeholder="Data inizio licenza"
                format="DD/MM/YYYY"
                value-format="DD/MM/YYYY"
              />
            </el-form-item>  
          </el-col>

          <el-col :span="12">
            <el-form-item label="Data fine licenza" :error="form_error.valida_a" prop="valida_a">
              <el-date-picker
                v-model="objModel.valida_a"
                type="date"
                placeholder="Data fine licenza"
                format="DD/MM/YYYY"
                value-format="DD/MM/YYYY"
                :shortcuts="shortcuts"
              />
            </el-form-item>
          </el-col>

        </el-row>

        <br><br>

        <el-button v-if="form_action != 'read'" type="success" @click="submit(formModel)">Salva</el-button>

      </el-form>

      <br><br><br>

      <template v-if="form_action == 'read'">
        <el-timeline v-loading="activities_loading">
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

    </el-drawer>

  </el-card>

</template>

<script setup>

  import {ref, reactive, computed, onMounted, onUnmounted, defineComponent} from 'vue';

  import {Plus, Search, Edit, Delete/*, User, CreditCard, , CircleCloseFilled, CircleCheckFilled, Message*/} from '@element-plus/icons-vue'

  import {list, create, read, update, del} from '../../utils/service.js';

  //import Auth from '../../store/Auth.js';

const drawerTitles = {
  onCreate: 'Aggiungi Licenza',
  onRead:   'Dettaglio Licenza',
  onUpdate: 'Modifica Licenza',
  onDelete: 'Cancella Licenza',
  onHistory:'Storico Licenza',
};

const endpoints = {
  onList:   'sys_admin/licences',
  onCreate: 'sys_admin/licence',
  onRead:   'sys_admin/licence',
  onUpdate: 'sys_admin/licence',
  onDelete: 'sys_admin/licence',
  onHistory:'sys_admin/licence_activities',
};

const objModels = ref([]);

const objModel  = reactive({});

const search       = ref('');
const loading      = ref(true);

const openDrawer   = ref(false);
const drawerTitle  = ref('');

const formModel    = ref();

const form_action  = ref();

const form_loading = ref(false);
const form_disable = ref(false);
const form_error   = ref({});

const activities_loading = ref(false);
const activities = ref([]);

const users_option  = ref([]);
const legals_option = ref([]);

const shortcuts = [
  {
    text: '+ 90 giorni',
    value: () => {
      const date = new Date()
      date.setTime(date.getTime() + 3600 * 1000 * 24 * 90)
      return date;
    },
  },
  {
    text: '+ 180 giorni',
    value: () => {
      const date = new Date()
      date.setTime(date.getTime() + 3600 * 1000 * 24 * 180)
      return date;
    },
  },
  {
    text: '+ 365 giorni',
    value: () => {
      const date = new Date()
      date.setTime(date.getTime() + 3600 * 1000 * 24 * 365)
      return date;
    },
  },
]

const filterTableData = computed(() =>
objModels.value.filter(
    (data) =>
      !search.value ||
      data.user.toLowerCase().includes(search.value.toLowerCase()) ||
      data.legal.toLowerCase().includes(search.value.toLowerCase())
  )
)

const handleCreate = (async() => {
  
  openDrawer.value   = true;
  drawerTitle.value  = drawerTitles.onCreate;
  form_disable.value = false;
  form_action.value  = 'create';
  form_error.value   = {};
  Object.keys(objModel).forEach(key => { objModel[key] = ''; })

  form_loading.value = true;

  let resp1 = await list('sys_admin/available');
  if(resp1 && !resp1.errors){
    users_option.value  = resp1.users;
    legals_option.value = resp1.legals;
  }
  
  form_loading.value = false;

})

const handleRead = (async(id, row) => {
  openDrawer.value   = true;
  drawerTitle.value  = drawerTitles.onRead;
  form_disable.value = true;
  form_action.value  = 'read';
  form_error.value   = {};
  Object.assign(objModel, objModels.value.find((obj) => {return obj.id === row.id}));

  activities_loading.value = true;
  activities.value = [];
  let resp = await read(endpoints.onHistory, objModel.id);
  if(resp){
    if(!resp.errors){
      activities.value = resp;
    }    
  }
  activities_loading.value = false;

})

const handleUpdate = ((id, row) => {
  openDrawer.value  = true;
  drawerTitle.value = drawerTitles.onUpdate;
  form_disable.value = false;
  form_action.value  = 'update';
  form_error.value   = {};
  Object.assign(objModel, objModels.value.find((obj) => {return obj.id === row.id}));
})

const handleDelete = (async(id, row) => {
    loading.value = true;    
    form_action.value  = 'delete'; 
    let resp = await del(endpoints.onDelete, row.id);
    if(resp) {
      objModels.value = objModels.value.filter((obj) => {return obj.id !== row.id});
    }
    loading.value = false; 
})

const submit = (async(formRef) => {
    
if (!formRef) return;
const val = await formRef.validate((valid) => valid);
if(!val) return false;

form_loading.value = true;
form_error.value   = {};

if(form_action.value == 'create'){
  let resp = await create(endpoints.onCreate, objModel);
  if(resp){
    if(resp.errors){
      form_error.value = resp.errors;
    //this.scrollToTop();
    } else {
      objModels.value.push(resp);     
      openDrawer.value = false;
    }    
  }
}

if(form_action.value == 'update'){
  let resp = await update(endpoints.onUpdate, objModel);
  if(resp){
    if(resp.errors){
      form_error.value = resp.errors;
      //this.scrollToTop();
    } else {
      objModels.value = objModels.value.map((obj) => {return obj.id == resp.id ? resp : obj});  
      openDrawer.value = false;
    }    
  }
}

form_loading.value = false;

})

const tableRowClassName = ({row}) => {
  let giorni_totali    = calculateDateDifference(row.valida_da, row.valida_a);
  let giorni_trascorsi = calculateDateDifference(row.valida_da, todayIt());
  let giorni_residui   = giorni_totali - giorni_trascorsi;
  let perc_residui     = Math.ceil((giorni_residui / giorni_totali) * 100);
  //console.log(giorni_totali, giorni_trascorsi, perc_residui);
  if(perc_residui <= 10) return 'danger-row';
  if(perc_residui <= 25) return 'warning-row';
}

const calculateDateDifference = (date1, date2) =>{
      const [day1, month1, year1] = date1.split('/').map(Number);
      const [day2, month2, year2] = date2.split('/').map(Number);

      const d1 = new Date(year1, month1 - 1, day1);
      const d2 = new Date(year2, month2 - 1, day2);

      // Differenza in millisecondi
      const diffInMs = d2 - d1;

      // Converti la differenza in giorni
      return Math.ceil(diffInMs / (1000 * 60 * 60 * 24));
    }

const todayIt = () => {
  const today = new Date();

  // Estrarre giorno, mese e anno
  let day = today.getDate();
  let month = today.getMonth() + 1; // I mesi partono da 0, quindi aggiungiamo 1
  const year = today.getFullYear();

  // Aggiungi uno 0 davanti a giorno e mese se sono a una cifra
  if (day < 10) {
    day = '0' + day;
  }

  if (month < 10) {
    month = '0' + month;
  }

  return `${day}/${month}/${year}`;
}

onMounted(async ()=>{
  loading.value = true;
  let resp = await list(endpoints.onList);
  if(resp && !resp.errors){
    objModels.value = resp;
  }
  loading.value = false;
})

onUnmounted(()=>{});

  defineComponent({
      name: 'LicenceView',
  })

</script>

<style>
.el-table .danger-row {
  --el-table-tr-bg-color: var(--el-color-danger-light-9);
}
.el-table .warning-row {
  --el-table-tr-bg-color: var(--el-color-warning-light-9);
}
.el-table .success-row {
  --el-table-tr-bg-color: var(--el-color-success-light-9);
}
</style>