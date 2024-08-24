<template>

    <h3>Unità Organizzative</h3>

    <el-card class="box-card">

    <el-row>
      <el-col :span="4">
        <el-button type="success" :icon="Plus" @click="handleCreate()">Aggiungi</el-button>        
      </el-col>
      <el-col :span="4">
        <el-popconfirm title="Confermando verranno sovrascritte le informazioni delle Unità Organizzative, con quelle di indice PA (se presenti). Procedere?" @confirm="handleAutofill()" confirm-button-text="Si">
          <template #reference>
            <el-button type="primary" :icon="Edit">Autopopola da Indice PA</el-button>  
          </template>
        </el-popconfirm>              
      </el-col>
    </el-row>

    <br>

    <el-table v-loading="loading" :data="filterTableData" style="width: 100%" :height="480" empty-text="Nessun risultato trovato">

      <!-- FILL -->

      <el-table-column label="Descrizione" prop="des_ou" :sortable="true"/>
      <el-table-column label="Cognome responsabile"       prop="cogn_resp" />
      <el-table-column label="Nome responsabile"        prop="nome_resp" />

      <el-table-column align="right">

        <template #header>
          <el-input v-model="search" size="small" placeholder="Cerca..." />
        </template>
        
        <template #default="scope">

          <el-button type="primary" :icon="Search"   size="small" @click="handleRead(scope.$index, scope.row)">Dettagli</el-button>
          <el-button type="warning" :icon="Edit"     size="small" @click="handleUpdate(scope.$index, scope.row)">Modifica</el-button>
          <el-button type="info"    :icon="Notebook" size="small" @click="handleHistory(scope.$index, scope.row)">Storico</el-button>

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
        v-if="form_action != 'read' && form_action != 'history' "
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

      <el-button type="success" @click="submit()">Salva</el-button>

      </el-form>

      <template v-if="form_action == 'history'">

        <el-descriptions border>
          <el-descriptions-item label="Descrizione: ">{{ objModel.des_ou }}</el-descriptions-item>
          <el-descriptions-item label="Responsabile: ">{{ objModel.cogn_resp }} {{ objModel.nome_resp }}</el-descriptions-item>
        </el-descriptions>

        <br><br><br>

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

  import {Plus, Search, Edit, Delete, Notebook /*, User, CreditCard, , CircleCloseFilled, CircleCheckFilled, Message*/} from '@element-plus/icons-vue'

  import {list, create, read, update, del} from '../../utils/service.js';

  import {ElMessageBox } from 'element-plus';

  //import Auth from '../../store/Auth.js';

const drawerTitles = {
  onCreate: '',
  onRead:   '',
  onUpdate: '',
  onDelete: '',
  onHistory:'',
};

const endpoints = {
  onList:   'le_admin/ous',
  onCreate: 'le_admin/ou',
  onRead:   'le_admin/ou',
  onUpdate: 'le_admin/ou',
  onDelete: 'le_admin/ou',
  onHistory:'le_admin/ou_activities',
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

const filterTableData = computed(() =>
objModels.value.filter(
    (data) =>
      !search.value ||
      data.des_ou.toLowerCase().includes(search.value.toLowerCase()) ||
      data.nome_resp.toLowerCase().includes(search.value.toLowerCase())    ||
      data.cogn_resp.toLowerCase().includes(search.value.toLowerCase())
  )
)

const handleCreate = (() => {
  openDrawer.value   = true;
  drawerTitle.value  = drawerTitles.onCreate;
  form_disable.value = false;
  form_action.value  = 'create';
  form_error.value   = {};
  Object.keys(objModel).forEach(key => { objModel[key] = ''; })
})

const handleRead = ((id, row) => {
  openDrawer.value   = true;
  drawerTitle.value  = drawerTitles.onRead;
  form_disable.value = true;
  form_action.value  = 'read';
  form_error.value   = {};
  Object.assign(objModel, objModels.value.find((obj) => {return obj.id === row.id}));
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

})

const handleAutofill = (async() => {
  loading.value = true;
  let resp = await list('le_admin/ou_autofill');
  if(resp && !resp.errors){
    //objModels.value = objModels.value.map((obj) => {return obj.cod_uni_ou	 == resp.id ? resp : obj});
    open('', 'Sono state aggiornate ' + resp.length + ' Unità Organizzative');
  } else {
    open('Error', 'Errore durante l\'interrogazione di Indice PA');
  }
  loading.value = false; 
})

const open = (title, message) => {
  ElMessageBox.alert(message, title, {
    confirmButtonText: 'OK',
  })
}

const handleHistory = (async(id, row) => {
  openDrawer.value  = true;
  drawerTitle.value = drawerTitles.onHistory;
  form_disable.value = false;
  form_action.value  = 'history';
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
      name: 'OUView',
  })

</script>
