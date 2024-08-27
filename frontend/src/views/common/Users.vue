<template>

    <h3>{{Auth.state.user.abilities.includes('system:admin') ? 'Amministratori di Ente' : 'Utenti'}}</h3>

    <el-card class="box-card">

    <el-row>
      <el-col :span="4">
        <el-button type="success" :icon="Plus" @click="handleCreate()">Aggiungi</el-button>        
      </el-col>
    </el-row>

    <br>

    <el-table v-loading="loading" :data="filterTableData" style="width: 100%" :height="480" empty-text="Nessun risultato trovato" :row-class-name="tableRowClassName">

      <el-table-column label="Cognome"  prop="surname" :sortable="true"/>
      <el-table-column label="Nome"     prop="name"    :sortable="true"/>
      <el-table-column label="Username" prop="username" />
      <el-table-column label="Email"    prop="email"   :sortable="true"/>
      
      <el-table-column label="Ultimo login" prop="last_login" />

      <el-table-column align="right">

        <template #header>
          <el-input v-model="search" size="small" placeholder="Cerca..." />
        </template>
        
        <template #default="scope">

          <el-tooltip class="box-item" effect="dark" content="Dettagli" placement="top-start">
            <el-button type="primary" :icon="Search" size="small" @click="handleRead(scope.$index, scope.row)"   />
          </el-tooltip>

          <el-tooltip class="box-item" effect="dark" content="Modifica" placement="top-start">
            <el-button type="warning" :icon="Edit" size="small" @click="handleUpdate(scope.$index, scope.row)" />
          </el-tooltip>

          <el-tooltip class="box-item" effect="dark" :content="scope.row.enabled ? 'Disabilita' : 'Riabilita'" placement="top-start">
            <el-button :type="scope.row.enabled ? 'info' : 'success'" :icon="scope.row.enabled ? CircleCloseFilled : CircleCheckFilled" size="small" @click="handleToggle(scope.$index, scope.row)" />
          </el-tooltip>

          <el-popconfirm title="Resettare ed inviare nuovamente password?" @confirm="handleReset(scope.$index, scope.row)" confirm-button-text="Si">
            <template #reference>
              <el-button type="success" :icon="Message" size="small" />  
            </template>
          </el-popconfirm>

          <el-popconfirm title="Sicuro di voler procedere?" @confirm="handleDelete(scope.$index, scope.row)" confirm-button-text="Si">
            <template #reference>
              <el-button type="danger" :icon="Delete" size="small" />
            </template>
          </el-popconfirm>

        </template>

      </el-table-column>

    </el-table>

    <div style="padding: 16px 0 16px 0;">Risultati: {{ filterTableData.length }}</div>


    <el-drawer v-model="openDrawer" :title="drawerTitle" direction="rtl" size="75%">

      <el-form 
        ref="userForm"
        v-if="form_action != 'read'"
        v-loading="form_loading"
        :model="user"
        :rules="{
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
        }"
        :disabled="form_disable"
        label-position="top"
        status-icon
      >

        <div v-if="form_action=='create'">
          <el-alert
            title="Avviso:"
            type="info"
            description="L'utente creato potrà modificare i dati inseriti dal suo Profilo."
            show-icon
          />
          <br>
        </div>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Nome" :error="form_error.name" prop="name">
              <el-input v-model="user.name" placeholder="Nome"><template #prepend><el-button :icon="User"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Cognome" :error="form_error.surname" prop="surname">
              <el-input v-model="user.surname" placeholder="Cognome"><template #prepend><el-button :icon="User"/></template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Username" :error="form_error.username" prop="username">
              <el-input v-model="user.username" placeholder="Username"><template #prepend><el-button :icon="CreditCard"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Email" :error="form_error.email" prop="email">
              <el-input v-model="user.email" placeholder="Email"><template #prepend>@</template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <br>

        <el-button type="success" @click="submit(userForm)">Salva</el-button>

      </el-form>

      <template v-if="form_action == 'read'">

        <el-descriptions border>
          <el-descriptions-item label="Username: ">{{ user.username }}</el-descriptions-item>
          <el-descriptions-item label="Email: ">{{ user.email }}</el-descriptions-item>
          <el-descriptions-item label="Utente: ">{{ user.name }} {{ user.surname }}</el-descriptions-item>
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

  import {Plus, User, CreditCard, Search, Edit, Delete, CircleCloseFilled, CircleCheckFilled, Message} from '@element-plus/icons-vue'

  import {list, create, read, update, del} from '../../utils/service.js';

  import Auth from '../../store/Auth.js';

const users = ref([]);

const user  = reactive({});

const search       = ref('');
const loading      = ref(true);

const openDrawer   = ref(false);
const drawerTitle  = ref('');

const userForm     = ref();

const form_action  = ref();

const form_loading = ref(false);
const form_disable = ref(false);
const form_error   = ref({});

const activities_loading = ref(false);
const activities = ref([]);

const filterTableData = computed(() =>
  users.value.filter(
    (data) =>
      !search.value ||
      data.surname.toLowerCase().includes(search.value.toLowerCase()) ||
      data.name.toLowerCase().includes(search.value.toLowerCase())    ||
      data.username.toLowerCase().includes(search.value.toLowerCase())    ||
      data.email.toLowerCase().includes(search.value.toLowerCase())
  )
)

const handleToggle = (async(index, row) => {
  loading.value = true;    
  form_action.value  = 'toggle'; 
  let resp = await update('toggle', row);
  if(resp){
      users.value = users.value.map((obj) => {return obj.id == resp.id ? resp : obj});  
  }  
  loading.value = false;   
})

const tableRowClassName = ({row}) => {
  return row.enabled ? '' : 'warning-row';
}

const handleCreate = (() => {
  openDrawer.value   = true;
  drawerTitle.value  = 'Aggiungi ' + (Auth.state.user.abilities.includes('system:admin') ? 'Amministratore di Ente' : 'Utente');
  form_disable.value = false;
  form_action.value  = 'create';
  form_error.value   = {};
  Object.keys(user).forEach(key => { user[key] = ''; })
})

const handleRead = (async(id, row) => {
  openDrawer.value   = true;
  drawerTitle.value  = 'Dettaglio Utente';
  form_disable.value = true;
  form_action.value  = 'read';
  form_error.value   = {};
  Object.assign(user, users.value.find((obj) => {return obj.id === row.id}));

  activities_loading.value = true;
  activities.value = [];
  let resp = await read('user_activities', user.id);
  if(resp){
    if(!resp.errors){
      activities.value = resp;
    }    
  }
  activities_loading.value = false;

})

const handleUpdate = ((id, row) => {
  openDrawer.value  = true;
  drawerTitle.value = 'Modifica Utente';
  form_disable.value = false;
  form_action.value  = 'update';
  form_error.value   = {};
  Object.assign(user, users.value.find((obj) => {return obj.id === row.id}));
})

const handleReset = (async(id, row) => {
    loading.value = true;    
    form_action.value  = 'resetpwd'; 
    await update('resetpwd', row);
    loading.value = false; 
})

const handleDelete = (async(id, row) => {
    loading.value = true;    
    form_action.value  = 'delete'; 
    let resp = await del('user', row.id);
    if(resp) {
      users.value = users.value.filter((obj) => {return obj.id !== row.id});
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
    let resp = await create('user', user);
    if(resp){
      if(resp.errors){
        form_error.value = resp.errors;
        //this.scrollToTop();
      } else {
        users.value.push(resp);     
        openDrawer.value = false;
      }    
    }
  }

  if(form_action.value == 'update'){
    let resp = await update('user', user);
    if(resp){
      if(resp.errors){
        form_error.value = resp.errors;
        //this.scrollToTop();
      } else {
        users.value = users.value.map((obj) => {return obj.id == resp.id ? resp : obj});  
        openDrawer.value = false;
      }    
    }
  }

  form_loading.value = false;

})

onMounted(async ()=>{
  loading.value = true;
  let resp = await list('users');
  if(resp && !resp.errors){
    users.value = resp;
  }
  loading.value = false;
})

onUnmounted(()=>{});

  defineComponent({
      name: 'UserView',
  })

</script>

<style>
.el-table .warning-row {
  --el-table-tr-bg-color: var(--el-color-warning-light-9);
}
.el-table .success-row {
  --el-table-tr-bg-color: var(--el-color-success-light-9);
}
</style>