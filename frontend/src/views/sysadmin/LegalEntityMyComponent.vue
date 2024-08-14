<template>

  <h3>Enti</h3>

  <el-card class="box-card">

    <el-autocomplete
        v-model="typing"
        :fetch-suggestions="getSuggestions"
        :trigger-on-focus="false"
        clearable
        class="inline-input w-50"
        placeholder="Please Input"
        @select="handleSelect"
      />

          <TableEl
            entity="Ente"
            :header="[
                  {
                    field: 'des_amm',
                    label: 'Amministrazione',
                    align: 'left',
                    sortable: true,
                  },                
                  {
                    field: 'cf',
                    label: 'Codice fiscale',
                    align: 'center',
                  },
                  {
                    field: 'full_address',
                    label: 'Indirizzo',
                    align: 'left',
                  },
                  {
                    field: 'titolare',
                    label: 'Titolare',
                    align: 'left',
                  },                  
                  
              ]"
            :external_row="legal_entities"  
            :actions="{
              create:{},
              read  :{},
              update:{},
              delete:{},
            }" 
            :form="{
              fields: [

                {
                  row: [
                    {
                      type: 'input',
                      label: 'Codice fiscale',
                      name: 'cf',
                      space: 12,                  
                    },
                    {
                      type: 'input',
                      label: 'Codice IPA Amministrazione',
                      name: 'cod_amm',
                      space: 12, 
                    },
                  ]
                },
                {
                  row: [
                    {
                      type: 'input',
                      label: 'Descrizione amministrazione',
                      name: 'des_amm',
                      space: 24,
                    }
                  ]
                },
                {
                  row: [
                    {
                      type: 'input',
                      label: 'Indirizzo',
                      name: 'indirizzo',
                      space: 20,                  
                    },
                    {
                      type: 'input',
                      label: 'CAP',
                      name: 'cap',
                      maxlength: 5,
                      space: 4, 
                    },
                  ]
                },
                {
                  row: [
                    {
                      type: 'input',
                      label: 'Comune',
                      name: 'comune',
                      space: 8,                  
                    },
                    {
                      type: 'input',
                      label: 'Provincia (sigla)',
                      name: 'provincia',
                      maxlength: 2,
                      space: 8, 
                    },
                    {
                      type: 'input',
                      label: 'Regione',
                      name: 'regione',
                      space: 8, 
                    },
                  ]
                },
                {
                  row: [
                    {
                      type: 'input',
                      label: 'Titolo Responsabile',
                      name: 'titolo_resp',
                      space: 8,                  
                    },
                    {
                      type: 'input',
                      label: 'Nome Responsabile',
                      name: 'nome_resp',
                      maxlength: 2,
                      space: 8, 
                    },
                    {
                      type: 'input',
                      label: 'Cognome Responsabile',
                      name: 'cognome_resp',
                      space: 8, 
                    },
                  ]
                },
              ]
              }"
              :rules="{
                cf: [
                  { required: true, message: 'Campo richiesto', trigger: 'blur' },
                ],     
                des_amm: [
                  { required: true, message: 'Campo richiesto', trigger: 'blur' },
                ],                   
              }"
              :endpoints="{
                create: 'sys_admin/legal',
                read:   'sys_admin/legal',
                update: 'sys_admin/legal',
                delete: 'sys_admin/legal',
                }" 
          />

  </el-card>

</template>

<script setup>

//import Auth from '@/store/Auth';

import {defineComponent, ref, onMounted, onUnmounted} from 'vue';

import {list} from '../../utils/service.js';

import TableEl from '../../components/Table.vue';


const loading        = ref(true);
const legal_entities = ref([]);

const typing         = ref(true);

//const getSuggestions( () => {})

onMounted(async ()=>{
  loading.value = true;
  let resp = await list('sys_admin/legals');
  if(resp && !resp.errors){
    legal_entities.value = resp.map(
      le => {return {
        ...le,
        'full_address' : (le.indirizzo ?? '')   + ' ' + (le.cap       ?? '') + ' ' + (le.provincia ?? '') + ' ' + (le.regione ?? ''),
        'titolare'     : (le.titolo_resp ?? '') + ' ' + (le.cogn_resp ?? '') + ' ' + (le.nome_resp ?? ''),
        };
      }
    );
  }
  loading.value = false;
})

onUnmounted(()=>{});

defineComponent({
    name: 'LegalEntityMyComponent',
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