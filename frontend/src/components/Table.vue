
<template>
    
    <el-row>
        <el-col :span="4">
            <el-button v-if="actions && actions.create"
                type="success"
                :icon="Plus"
                @click="handleCreate()"
                :disabled="actions.create.disabled ? actions.create.disabled() : false"
            >Aggiungi</el-button>        
        </el-col>
        <el-col :span="4"></el-col>
        <el-col :span="4"></el-col>
        <el-col :span="4"></el-col>
        <el-col :span="4"></el-col>
        <el-col :span="4">
            <el-input v-model="search" placeholder="Cerca..." @input="handleSearch"></el-input>
        </el-col>
    </el-row>

    <br>

    <el-table
        v-loading="loading" 
        :data="filterTableData"
        style="width: 100%"
        height="400"
        empty-text="Nessun risultato trovato"
        :row-class-name="tableRowClassName"
        :cell-class-name="tableCellClassName"
        :cell-style="tableCellStyle"
        >
    
        <el-table-column
            v-for="(th, indexth) in header"
            :sortable="th.sortable"
            :formatter="formatter"
            :key="indexth"
            :prop="th.field"
            :label="th.label"            
            :align="th.align ? th.align : 'center'"
            :width="th.width ? th.width : ''"
            />
    
        <!-- ATTACHMENTS -->
        <el-table-column v-if="attachments && attachments.field"
            :label="attachments.label"
            :width="attachments.width ? attachments.width : ''"
        >
            <template #default="attachment">
            <div v-for="(f, i) in attachment.row[attachments.field]" :key="i">
                <el-button type="primary" :loading="isDownloading" text @click="downloadFile(f)"><el-icon v-if="!isDownloading"><Download /></el-icon> {{f.name}}</el-button>
                <!--el-link :href="f.content" :icon="Download" type="primary" :underline="false" target="_blank">{{f.name}}</el-link-->
            </div>
            <div v-if="attachment.row[attachments.field].length == 0">
                <i>{{ attachments.no_data }}</i>
            </div>
            </template>
        </el-table-column>

        <!-- ACTION -->    
        <el-table-column align="right" v-if="action && (action.read || action.update || action.delete)">
                
            <!--template #header>
                <el-input v-model="search" size="small" placeholder="Cerca" />
            </template-->

            <template #default="scope">

                <el-tooltip class="box-item" effect="dark" content="Dettagli" placement="top-start">
                    <el-button v-if="actions && actions.read"
                        type="primary"
                        :icon="Search"
                        circle 
                        @click="handleRead(scope.$index, scope.row)"
                        :disabled="actions.read.disabled ? actions.read.disabled(scope.$index, scope.row) : false"
                        />
                </el-tooltip>


                <el-tooltip class="box-item" effect="dark" content="Modifica" placement="top-start">
                    <el-button v-if="actions && actions.update"
                    type="warning"
                    :icon="Edit"
                    circle
                    @click="handleUpdate(scope.$index, scope.row)"
                    :disabled="actions.update.disabled ? actions.update.disabled(scope.$index, scope.row) : false"
                    />
                </el-tooltip>
                
                <el-popconfirm title="Procedere con la cancellazione?" @confirm="handleDelete(scope.$index, scope.row)" confirm-button-text="Si">
                    <template #reference>
                            <el-button v-if="actions && actions.delete"
                                type="danger" 
                                :icon="Delete"
                                circle
                                :disabled="actions.delete.disabled ? actions.delete.disabled(scope.$index, scope.row) : false"
                                />   
                    </template>
                </el-popconfirm>

            </template>

        </el-table-column>

    </el-table>

    <div style="padding: 16px 0 16px 0;">Risultati: {{ filterTableData.length }}</div>

    <el-drawer v-model="openDrawer" :title="drawerTitle" direction="rtl" size="50%">

        <FormEl 
            :formModel="formModel"
            :formData="formData"
            :action="action"
            :formLoading="formLoading"
            :rules="rules"
            :errors="errorsForm"/>

    </el-drawer>

</template>


<script setup>

import {list, create, read, update, del, download } from '../utils/service.js'; 
import {/*h,*/ ref, onMounted, onUpdated, computed, defineComponent, defineProps} from 'vue';

import { Delete, Edit, Search, Plus, Download} from '@element-plus/icons-vue'

import FormEl from './Form.vue';

const props = defineProps({ 
    details: {}, 
    entity   : {},
    endpoints: {},
    external_row: [],
    header   : {},
    attachments: {},
    actions  : {},
    form     : {},
    rules    : {},
    tableRowClassName  : {},
    tableCellClassName : {},
    tableCellStyle     : {},
})

const formModel = ref(props.form);

const openDrawer    = ref(false);
const drawerTitle   = ref('');
const loading       = ref(true);
const isDownloading = ref(false);

const rows        = ref([]);
//const external_row = ref([]);
const search      = ref('');

const formData = ref([]);
const action   = ref({});
const formLoading = ref(false);

const errorsForm = ref([])

const handleSearch = ((value) => {
  search.value = value;
});

const filterTableData = computed(() =>
    rows.value.filter( (data) => {
        for(const h of props.header){
            if (String(data[h.field]).toLowerCase().includes(search.value.toLowerCase())) {                
                return true;
            }
        }
        return false;
    })
)

const formatter = (row, column) => {

    //console.log('row:', row, 'column:', column);
    
    const header = props.header[column.rawColumnKey];
    const format = header.format;
    const value  = row[header.field];

    if(format == 'data' || format == 'datatime') {
        const data = new Date(value);
        const opzioniFormattazione = format == 'data' ?
            {
                year: 'numeric',
                month: '2-digit', //'long',
                day: '2-digit',
            } : {
                year: 'numeric',
                month: '2-digit', //'long',
                day: '2-digit',
                hour: 'numeric',
                minute: 'numeric',
                hour12: false,
            };
        return value ? data.toLocaleString('it-IT', opzioniFormattazione) : '';
    }
    
    if(format == 'decimal') {
        return value !== null ? new Intl.NumberFormat('it-IT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value) : value;
    }

    /* DON'T WORK... perchè non renderizza l'HTML
    if(format == 'attachments') {
        let allegati = '';
        allegati += value.map(
            (allegato) => {
                // return `<div>${allegato.name}</div>` + '<br>'
                // return h('div', { class: 'example' }, allegato.name);
                return `<mini-map :data="${allegato.name}"></mini-map>`;
            })
        console.log('download', value);
        return allegati != '' ? allegati : 'Nessun allegato';
    }
    */

    return value;

}

/* --------------------------------- */

function handleCreate(){
    props.actions.create.action ? props.actions.create.action() : defaultCreate();
}

function handleRead(id, row){
    props.actions.read.action ? props.actions.read.action(id, row) : defaultRead(id, row);
}

function handleUpdate(id, row){
    props.actions.update.action ? props.actions.update.action(id, row) : defaultUpdate(id, row);
}

function handleDelete(id, row){
    props.actions.delete.action ? props.actions.delete.action(id, row) : defaultDelete(id, row);
}

/* --------------------------------- */

const defaultCreate = (async() => {
    action.value = 'create';
    formModel.value.disabled = false;
    formData.value = {};
    drawerTitle.value = "Aggiungi " + props.entity;
    openDrawer.value  = true;

})

const defaultRead = (async(id, row) => {
    action.value = 'read';
    formModel.value.disabled = true;
    formData.value = {};
    drawerTitle.value = "Dettagli " + props.entity;
    openDrawer.value  = true;
    if(props.details == "server-side") {
        formLoading.value = true;
        formData.value = await read(props.endpoints.read, row.id);
        formLoading.value = false;
    } else {
        formData.value  = {...rows.value.find((r) => (r.id == row.id))};  
    }
     
})

const defaultUpdate = (async(id, row) => {
    action.value = 'update';
    formModel.value.disabled = false;
    formData.value = {};
    drawerTitle.value = "Modifica " + props.entity;
    openDrawer.value  = true;
    if(props.details == "server-side") {
        formLoading.value = true;
        formData.value  = await read(props.endpoints.read, row.id);
        formLoading.value = false;
    } else {
        formData.value  = {...rows.value.find((r) => (r.id == row.id))};   
    }
})

const defaultDelete = (async(id, row) => {
    action.value = 'delete';
    loading.value = true;     
    let resp = await del(props.endpoints.delete, row.id);
    if(resp) {
        rows.value = rows.value.filter((obj) => obj.id !== row.id);
    }
    loading.value = false; 
})

const defaultSubmit = (async(data, formRef) => {

    console.log('onSubmit Table:', data);

    const val = await formRef.validate((valid) => valid);
    if(!val) return false;

    formLoading.value = true;  

    if(action.value == 'create') {      
        let resp = await create(props.endpoints.create, data);
        if(resp){
            if(resp.errors){
                errorsForm.value = resp.errors;
            } else {
                rows.value.push(resp);     
                openDrawer.value  = false;
            }    
        } else {
            console.log('create resp KO: ', resp);      
        }
    }

    if(action.value == 'update') {    
        let resp = await update(props.endpoints.create, data);            
        if(resp){
            if(resp.errors){
                errorsForm.value = resp.errors;
            } else {
                rows.value = rows.value.map((obj) => {return obj.id == resp.id ? resp : obj});
                openDrawer.value  = false;
            }
        } else {
            console.log('create resp KO: ', resp);      
        }
    }

    formLoading.value = false; 

    });

    const downloadFile = (async (f) => {
        isDownloading.value = true;
        await download(f);
        isDownloading.value = false;
    });


onMounted(async ()=>{
   rows.value    = props.endpoints && props.endpoints.list ? await list(props.endpoints.list) : props.external_row;   
   loading.value = false;
   formModel.value.submit = props.form && props.form.submit ? props.form.submit : defaultSubmit;
 })

 onUpdated( () => {
    if(props.external_row){
        rows.value = props.external_row;
    }
})

defineComponent({
    name: 'TableEl',
 })

</script>


<style>
    .el-table .warning-row {
    --el-table-tr-bg-color: var(--el-color-warning-light-9);
    }
    .el-table .success-row {
    --el-table-tr-bg-color: var(--el-color-success-light-9);
    }
    .el-table .danger-row {
    --el-table-tr-bg-color: var(--el-color-danger-light-9);
    }
    .el-table .primary-row {
    --el-table-tr-bg-color: var(--el-color-primary-light-9);
    }

</style>