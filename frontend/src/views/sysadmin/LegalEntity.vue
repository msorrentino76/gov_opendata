<template>

  <h3>Enti</h3>

  <el-card class="box-card">

    <el-row>
      <el-col :span="4">
        <el-button type="success" :icon="Plus" @click="handleCreate()">Aggiungi</el-button>        
      </el-col>
    </el-row>

    <br>

    <el-table v-loading="loading" :data="filterTableData" style="width: 100%" :height="480" empty-text="Nessun risultato trovato">

      <el-table-column label="Amministrazione" prop="des_amm" :sortable="true"/>
      <el-table-column label="Codice fiscale"  prop="cf" />
      <el-table-column label="Indirizzo"       prop="full_address" />
      <el-table-column label="Titolare"        prop="titolare" />

      <el-table-column align="right">

        <template #header>
          <el-input v-model="search" size="small" placeholder="Cerca..." />
        </template>
        
        <template #default="scope">

          <!--
          <el-tooltip class="box-item" effect="dark" content="Dettagli" placement="top-start">
            <el-button type="primary" :icon="Search" circle @click="handleRead(scope.$index, scope.row)"/>
          </el-tooltip>

          <el-tooltip class="box-item" effect="dark" content="Modifica" placement="top-start">
            <el-button type="warning" :icon="Edit" circle @click="handleUpdate(scope.$index, scope.row)"/>
          </el-tooltip>

          <el-popconfirm title="Cancellando questo ente verranno cancellate anche le relative licenze. Procedere?" @confirm="handleDelete(scope.$index, scope.row)" confirm-button-text="Si">
            <template #reference>
              <el-button type="danger" :icon="Delete" circle/>   
            </template>
          </el-popconfirm>
          -->

          <el-button type="primary" :icon="Search"   size="small" @click="handleRead(scope.$index, scope.row)">Dettagli</el-button>
          <el-button type="warning" :icon="Edit"     size="small" @click="handleUpdate(scope.$index, scope.row)">Modifica</el-button>
          <el-button type="info"    :icon="Notebook" size="small" @click="handleHistory(scope.$index, scope.row)">Storico</el-button>

          <el-popconfirm title="Cancellando questo ente verranno cancellate anche le relative licenze. Procedere?" @confirm="handleDelete(scope.$index, scope.row)" confirm-button-text="Si">
            <template #reference>
              <el-button type="danger" :icon="Delete" size="small"></el-button>  
            </template>
          </el-popconfirm>

        </template>

      </el-table-column>

    </el-table>

    <div style="padding: 16px 0 16px 0;">Risultati: {{ filterTableData.length }}</div>    

    <el-drawer v-model="openDrawer" :title="drawerTitle" direction="rtl" size="75%">

      <div v-if="form_action=='create'">
        <el-divider />
          <el-row :gutter="20">
            <el-col :span="20">
              <el-autocomplete v-model="query_search" :fetch-suggestions="querySearchAsync" placeholder="Cerca in indice PA..." @select="handleSelect" :trigger-on-focus="false"  style="width: 100%"/>
            </el-col>
            <!--
            <el-col :span="4">
              <el-button type="primary" @click="handleSelect">Importa</el-button>
            </el-col>
            -->
          </el-row>
        <el-divider />
      </div>

      <div v-if="form_action=='update'">
        <el-divider />
          <el-button type="primary" @click="handleSelect(legal_entity)">Aggiorna da Indice PA</el-button>
        <el-divider />
      </div>

      <el-form v-if="form_action != 'history'"  v-loading="form_loading" :model="legal_entity" :disabled="form_disable" label-position="top" status-icon>

      <!--  
        <el-form-item label="Logo" :prop="logo" :error="form_error.logo">                                            
          <el-upload ref="upload"
                                :action="Auth.state.config.applicationBaseURL + '/api' + field.uploadEndpoint"
                                :headers="uploadHeader"
                                :accept="field.accept.mime"
                                multiple
                                drag
                                :limit="field.limit"
                                v-model:file-list="data[field.name]"
                                :before-upload="(rawFile)=>{                                    
                                    if(field.maxmbsize && (rawFile.size / 1024 / 1024) > 1) {
                                        form_error.logo = 'Il file ' + rawFile.name + ' supera la dimensione massima consentita di ' + 1 + ' Mb';
                                        return false;
                                    }
                                    return true;
                                }"
                                :before-remove="(rawFile)=>{removeFile(field.removeEndpoint, rawFile && rawFile.response ? rawFile.response.id : false)}"
                                >
                                <el-icon class="el-icon--upload"><IconEl icon="upload-filled" /></el-icon>
                                <div class="el-upload__text">
                                Trascina qui i files (massimo {{field.limit}}) oppure <em>Clicca qui per caricarli</em>
                                </div>
                                <template #tip>
                                <div class="el-upload__tip">
                                    Accettati file {{ field.accept.label }} di massimo {{ field.maxmbsize }} Megabyte
                                </div>
                                </template>
            </el-upload>                            
        </el-form-item>
      -->

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Codice fiscale" :error="form_error.cf">
              <el-input v-model="legal_entity.cf" placeholder="Codice fiscale" :disabled="form_action=='update'"><template #prepend><el-button :icon="OfficeBuilding"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Codice IPA Amministrazione" :error="form_error.cod_amm">
              <el-input v-model="legal_entity.cod_amm" placeholder="Codice IPA Amministrazione" :disabled="form_action=='update'"><template #prepend><el-button :icon="OfficeBuilding"/></template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="Descrizione amministrazione" :error="form_error.des_amm">
              <el-input v-model="legal_entity.des_amm" placeholder="Descrizione amministrazione"><template #prepend><el-button :icon="OfficeBuilding"/></template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="18">
            <el-form-item label="Sito istituzionale" :error="form_error.sito_istituzionale">
              <el-input v-model="legal_entity.sito_istituzionale" placeholder="Sito istituzionale"><template #prepend><el-button :icon="Monitor"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="Data accreditamento (aaaa-mm-gg)" :error="form_error.data_accreditamento">
              <el-input v-model="legal_entity.data_accreditamento" placeholder="Data accreditamento"><template #prepend><el-button :icon="Calendar"/></template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="6">
            <el-form-item label="Email (1)" :error="form_error.mail1">
              <el-input v-model="legal_entity.mail1" placeholder="Email"><template #prepend>@</template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="Email (2)" :error="form_error.mail2">
              <el-input v-model="legal_entity.mail2" placeholder="Email"><template #prepend>@</template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="Email (3)" :error="form_error.mail3">
              <el-input v-model="legal_entity.mail3" placeholder="Email"><template #prepend>@</template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="Email (4)" :error="form_error.mail4">
              <el-input v-model="legal_entity.mail4" placeholder="Email"><template #prepend>@</template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="20">
            <el-form-item label="Indirizzo" :error="form_error.indirizzo">
              <el-input v-model="legal_entity.indirizzo" placeholder="Indirizzo"><template #prepend><el-button :icon="Place"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="4">
            <el-form-item label="CAP" :error="form_error.cap">
              <el-input v-model="legal_entity.cap" placeholder="CAP"><template #prepend><el-button :icon="Place"/></template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="Regione" :error="form_error.regione">
              <el-input v-model="legal_entity.regione" placeholder="Regione"><template #prepend><el-button :icon="Location"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Provincia (sigla)" :error="form_error.provincia">
              <el-input v-model="legal_entity.provincia" placeholder="Provincia (sigla)"><template #prepend><el-button :icon="Location"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Comune" :error="form_error.comune">
              <el-input v-model="legal_entity.comune" placeholder="Comune"><template #prepend><el-button :icon="Location"/></template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="Titolo Responsabile" :error="form_error.titolo_resp">
              <el-input v-model="legal_entity.titolo_resp" placeholder="Titolo Responsabile"><template #prepend><el-button :icon="User"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Nome Responsabile" :error="form_error.nome_resp">
              <el-input v-model="legal_entity.nome_resp" placeholder="Nome Responsabile"><template #prepend><el-button :icon="User"/></template></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Cognome Responsabile" :error="form_error.cogn_resp">
              <el-input v-model="legal_entity.cogn_resp" placeholder="Cognome Responsabile"><template #prepend><el-button :icon="User"/></template></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Tipologia" :error="form_error.tipologia">
              <el-input v-model="legal_entity.tipologia" placeholder="Tipologia"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Categoria" :error="form_error.categoria">
              <el-input v-model="legal_entity.categoria" placeholder="Categoria"></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <br>

        <el-button v-if="form_action != 'read'" type="success" @click="submit()">Salva</el-button>

      </el-form>

      <template v-if="form_action == 'history'">

        <el-descriptions border>
          <el-descriptions-item label="Amministrazione: ">{{ legal_entity.des_amm }}</el-descriptions-item>
          <el-descriptions-item label="Codice fiscale: "> {{ legal_entity.cf }}</el-descriptions-item>
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

import {defineComponent, ref, reactive, onMounted, onUnmounted, computed} from 'vue';

import { Delete, Edit, Search, Plus, Location, Place, User, Monitor, OfficeBuilding, Calendar, Notebook} from '@element-plus/icons-vue'

import {list, create, read, update, del} from '../../utils/service.js';


const legal_entities = ref([]);

const legal_entity = reactive({});

const search       = ref('');
const loading      = ref(true);

const openDrawer   = ref(false);
const drawerTitle  = ref('');

const form_action  = ref();

const form_loading = ref('');
const form_disable = ref(false);
const form_error   = ref({});

const query_search = ref('');

const activities_loading = ref(false);
const activities = ref([]);

const filterTableData = computed(() =>
  legal_entities.value.filter(
    (data) =>
      !search.value ||
      data.des_amm.toLowerCase().includes(search.value.toLowerCase()) ||
      data.cf.toLowerCase().includes(search.value.toLowerCase())
  )
)

const handleCreate = (() => {
  openDrawer.value   = true;
  drawerTitle.value  = 'Aggiungi Ente';
  form_disable.value = false;
  form_action.value  = 'create';
  form_error.value   = {};
  query_search.value = '';
  Object.keys(legal_entity).forEach(key => {
    legal_entity[key] = '';
  })
})

const handleRead = ((id, row) => {
  openDrawer.value   = true;
  drawerTitle.value  = 'Dettaglio Ente';
  form_disable.value = true;
  form_action.value  = 'read';
  form_error.value   = {};
  Object.assign(legal_entity, legal_entities.value.find((obj) => {return obj.id === row.id}));
})

const handleUpdate = ((id, row) => {
  openDrawer.value  = true;
  drawerTitle.value = 'Modifica Ente';
  form_disable.value = false;
  form_action.value  = 'update';
  form_error.value   = {};
  Object.assign(legal_entity, legal_entities.value.find((obj) => {return obj.id === row.id}));
})

const handleHistory = (async(id, row) => {
  openDrawer.value  = true;
  drawerTitle.value = 'Storico Ente';
  form_disable.value = false;
  form_action.value  = 'history';
  form_error.value   = {};
  Object.assign(legal_entity, legal_entities.value.find((obj) => {return obj.id === row.id}));

  activities_loading.value = true;
  activities.value = [];
  let resp = await read('sys_admin/legal_activities', legal_entity.id);
  if(resp){
    if(!resp.errors){
      activities.value = resp;
    }    
  }
  activities_loading.value = false;

})

const handleDelete = (async(id, row) => {
    loading.value = true;    
    form_action.value  = 'delete'; 
    let resp = await del('sys_admin/legal', row.id);
    if(resp) {
      legal_entities.value = legal_entities.value.filter((obj) => {return obj.id !== row.id});
    }
    loading.value = false; 
})

const submit = (async() => {

  form_loading.value == true;

  if(form_action.value == 'create'){
    let resp = await create('sys_admin/legal', legal_entity);
    if(resp){
      if(resp.errors){
        form_error.value = resp.errors;
       //this.scrollToTop();
      } else {
        legal_entities.value.push(resp);     
        openDrawer.value = false;
      }    
    }
  }

  if(form_action.value == 'update'){
    let resp = await update('sys_admin/legal', legal_entity);
    if(resp){
      if(resp.errors){
        form_error.value = resp.errors;
        //this.scrollToTop();
      } else {
        legal_entities.value = legal_entities.value.map((obj) => {return obj.id == resp.id ? resp : obj});  
        openDrawer.value = false;
      }    
    }
  }

  form_loading.value == false;

})

const querySearchAsync = (async(looking_for, cb) => {
  let results = [];
  if(looking_for.length >= 7) {
    let resp = await create('sys_admin/legal_suggestions', {looking_for: looking_for}, true);
    if(resp && !resp.errors){
      results = resp;
    }    
  }
  cb(results);
})

const handleSelect = (async(selected_item) => {
  form_loading.value = true;
  form_error.value   = {};
  let resp = await create('sys_admin/legal_ipa_details', selected_item, true);
  if(resp && !resp.errors){
    Object.assign(legal_entity, resp);
  } 
  form_loading.value = false;  
})

onMounted(async ()=>{
  loading.value = true;
  let resp = await list('sys_admin/legals');
  if(resp && !resp.errors){
    legal_entities.value = resp;
  }
  loading.value = false;
})

onUnmounted(()=>{});

defineComponent({
    name: 'LegalEntity',
})

</script>
