<template>

    <h3>Licenze</h3>

    <el-card class="box-card">

    <el-row>
      <el-col :span="4">
        <el-button type="success" :icon="Plus" @click="handleCreate()">Aggiungi</el-button>        
      </el-col>
    </el-row>

    <br>

    <el-table v-loading="loading" :data="filterTableData" style="width: 100%" :height="480" empty-text="Nessun risultato trovato">

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
              <el-button type="danger" :icon="Delete" size="small">Cancella</el-button>  
            </template>
          </el-popconfirm>

        </template>

      </el-table-column>

    </el-table>

    <el-drawer v-model="openDrawer" :title="drawerTitle" direction="rtl" size="75%">

      <el-form 
        ref="formModel"
        v-loading="form_loading"
        :model="objModel"
        :rules="{}/*{
            name: [
              { required: true, message: 'Campo richiesto', trigger: 'blur' },
            ],
            surname: [
              { required: true, message: 'Campo richiesto', trigger: 'blur' },
            ],
            username: [
              { required: true, message: 'Campo richiesto', trigger: 'blur' },
            ],
            email: [
              { required: true, message: 'Campo richiesto', trigger: 'blur' },
              { type: 'email' , message: 'Inserire un indirizzo email valido', trigger: 'blur' },
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

        <el-date-picker
          v-model="licenceValideTime"
          type="daterange"
          unlink-panels
          range-separator="fino"
          start-placeholder="Valida da"
          end-placeholder="Valida a"
          :shortcuts="shortcuts"
          format="DD/MM/YYYY"
          value-format="DD/MM/YYYY"
        />

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

  import {list, read, del} from '../../utils/service.js';

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

const form_loading = ref('');
const form_disable = ref(false);
const form_error   = ref({});

const activities_loading = ref(false);
const activities = ref([]);

const users_option  = ref([]);
const legals_option = ref([]);

const licenceValideTime = ref([]);

const shortcuts = [
  {
    text: '+ 3 mesi',
    value: () => {
      const end = new Date()
      const start = new Date()
      end.setTime(start.getTime() + 3600 * 1000 * 24 * 90)
      return [start, end]
    },
  },
  {
    text: '+ 6 mesi',
    value: () => {
      const end = new Date()
      const start = new Date()
      end.setTime(start.getTime() + 3600 * 1000 * 24 * 180)
      return [start, end]
    },
  },
  {
    text: '+ 12 mesi',
    value: () => {
      const end = new Date()
      const start = new Date()
      end.setTime(start.getTime() - 3600 * 1000 * 24 * 365)
      return [start, end]
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

const handleCreate = (() => {
  openDrawer.value   = true;
  drawerTitle.value  = drawerTitles.onCreate;
  form_disable.value = false;
  form_action.value  = 'create';
  form_error.value   = {};
  Object.keys(objModel).forEach(key => { objModel[key] = ''; })
  licenceValideTime.value = [];
})

const handleRead = (async(id, row) => {
  openDrawer.value   = true;
  drawerTitle.value  = drawerTitles.onRead;
  form_disable.value = true;
  form_action.value  = 'read';
  form_error.value   = {};
  Object.assign(objModel, objModels.value.find((obj) => {return obj.id === row.id}));
  licenceValideTime.value = [objModel.valida_da, objModel.valida_a];

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
  licenceValideTime.value = [objModel.valida_da, objModel.valida_a];
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

  objModel.valida_da = licenceValideTime.value[0];
  objModel.valida_a  = licenceValideTime.value[1];
    
  console.log('submit', objModel, licenceValideTime.value, formRef)
  return;
  
  /*
if (!formRef) return;
const val = await formRef.validate((valid) => valid);
if(!val) return false;

form_loading.value == true;
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

form_loading.value == false;
*/
})

onMounted(async ()=>{
  loading.value = true;
  let resp = await list(endpoints.onList);
  if(resp && !resp.errors){
    objModels.value = resp;
  }
  let resp1 = await list('sys_admin/available');
  if(resp1 && !resp1.errors){
    users_option.value  = resp1.users;
    legals_option.value = resp1.legals;
  }
  loading.value = false;
})

onUnmounted(()=>{});

  defineComponent({
      name: 'LicenceView',
  })

</script>
