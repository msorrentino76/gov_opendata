
<template>

    <h3>Utenze Unità Organizzative</h3>


    <el-card class="box-card">

        <div v-for="ou in ous" :key="ou.id" class="transfer-container">

            <!--
            <el-descriptions border>
                <el-descriptions-item label="Descrizione: ">{{ ou.des_ou }}</el-descriptions-item>
                <el-descriptions-item label="Responsabile: ">{{ ou.cogn_resp }} {{ ou.nome_resp }}</el-descriptions-item>
            </el-descriptions>
            <br>
            <div v-for="user in users" :key="user.id">
                <el-checkbox v-model="checked[ou.id]" :label="`${user.name} ${user.surname}`" border />
            </div>
            
            -->

            <el-transfer 
                v-loading="loading"
                v-model="checked[ou.id]"
                :titles="['Utenti censiti', truncatedText(ou.des_ou)]"
                :button-texts="['Rimuovi utenze', 'Aggiungi utenza']"
                :data="usersopt"
                :key="`transfert_${ou.id}`"
                filterable
                :filter-method="filterMethod"
                filter-placeholder="Cerca utenza..."
                 @change="handleChange(ou.id)"
            >
                <template #right-footer>                    
                    <el-button class="transfer-footer" type="primary" @click="handlePermission(ou.id)" size="small">Gestisci permessi</el-button>
                    <el-button class="transfer-footer" type="info"    @click="handleHistory(ou.id)" size="small">Storico</el-button>
                    &nbsp;&nbsp;
                    <b>Resp: </b> {{ ou.nome_resp }} {{ ou.cogn_resp }}
                </template>
                <template #left-footer>                    
                    <el-button v-if="ownerNotInUser(ou.mail_resp)" class="transfer-footer" type="success" @click="handleOwnerToUser(ou.id)" size="small">
                        Crea utenza per Resp. {{ ou.nome_resp }} {{ ou.cogn_resp }}
                    </el-button>
                </template>

            </el-transfer>

        </div>

        <el-drawer v-model="openDrawer" :title="drawerTitle" direction="rtl" size="75%">

            <el-descriptions border>
                <el-descriptions-item label="Descrizione: ">{{ selected_ou.des_ou }}</el-descriptions-item>
                <el-descriptions-item label="Responsabile: ">{{ selected_ou.cogn_resp }} {{ selected_ou.nome_resp }}</el-descriptions-item>
            </el-descriptions>

            <br><br>

            <el-table v-if="drawerAction == 'permissions'" :data="selected_ou_users" empty-text="Nessun risultato trovato">
                <el-table-column label="Cognome"  prop="surname" :sortable="true"/>
                <el-table-column label="Nome"     prop="name"    :sortable="true"/>
                <el-table-column label="Username" prop="username" />
                <el-table-column label="Email"    prop="email"   :sortable="true"/>
            </el-table>

            <el-timeline v-if="drawerAction == 'history'" v-loading="activities_loading">
                <el-timeline-item
                    v-for="(activity, index) in activities"
                    :key="index"
                    :timestamp="activity.timestamp"
                    :type="activity.type"
                >
                    {{ activity.content }}
                </el-timeline-item>
            </el-timeline>

            <el-form 
                ref="userForm"
                v-if="drawerAction == 'create'"
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
                label-position="top"
                status-icon
            >

                <el-alert
                    title="Avviso:"
                    type="info"
                    description="L'utente creato potrà modificare i dati inseriti dal suo Profilo."
                    show-icon
                />

                <br>

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

        </el-drawer>

    </el-card>

</template>

<script setup>

    import {defineComponent, onMounted, ref, reactive} from 'vue';

    import {list, create, update, read} from '../../utils/service.js';

    import {User, CreditCard} from '@element-plus/icons-vue'

    import Auth from '../../store/Auth.js';

    const ous      = ref([]);
    const users    = ref([]);
    const usersopt = ref([]);

    const checked = ref([]);

    const loading = ref(true);

    const openDrawer   = ref(false);
    const drawerTitle  = ref('');
    const drawerAction = ref('');
    const selected_ou = ref({});
    const selected_ou_users = ref([]);

    const users_email  = ref([]);

    const activities_loading = ref(false);
    const activities = ref([]);

    const user     = reactive({});
    const userForm = ref();

    const form_loading = ref(false);
    const form_error   = ref({});

    const handleChange = (async (ou_id) => {  
        loading.value = true; 
        let resp = await update('le_admin/user_ou', {id: ou_id, users: checked.value[ou_id]});   
        if(!resp){
            init();
        }
        loading.value = false;
    });
    
    const handleHistory = (async(ou_id) => { 
        openDrawer.value   = true;
        drawerTitle.value  = 'Storico';
        drawerAction.value = 'history'; 
        selected_ou.value  = ous.value.find((o) => {return o.id == ou_id});   

        activities_loading.value = true;
        activities.value = [];
        let resp = await read('le_admin/user_ou_act', ou_id);
        if(resp){
            if(!resp.errors){
                activities.value = resp;
            }    
        }
        activities_loading.value = false;
    });

    const handlePermission = ((ou_id) => {   
        openDrawer.value   = true;
        drawerTitle.value  = 'Permessi';
        drawerAction.value = 'permissions';   
        selected_ou.value  = ous.value.find((o) => {return o.id == ou_id}); 
        selected_ou_users.value = [];
        users.value.map((o) => {
            if( checked.value[ou_id].includes (o.id)){
                selected_ou_users.value.push(o);
            }
        })
        selected_ou_users.value     
        console.log('handlePermission', ou_id)
    });
    
    const handleOwnerToUser = ((ou_id) => { 
        openDrawer.value   = true;
        drawerTitle.value  = 'Crea utenza';
        drawerAction.value = 'create';      
        selected_ou.value  = ous.value.find((o) => {return o.id == ou_id}); 
        user.name     = selected_ou.value.nome_resp;
        user.surname  = selected_ou.value.cogn_resp;
        user.username = (selected_ou.value.nome_resp.toLowerCase()[0] + '.' + selected_ou.value.cogn_resp.toLowerCase()).replace(/\s+/g, '');
        user.email    = selected_ou.value.mail_resp;    
        console.log('handleOwnerToUser', ou_id)
    });

    const filterMethod = (query, item) => {
        return item.label.toLowerCase().includes(query.toLowerCase())
    }

    const truncatedText = ((text) => {      
      return text.length > 58 ? text.slice(0, 58) + "..." : text;
    });

    const submit = (async(formRef) => {

        if (!formRef) return;
        const val = await formRef.validate((valid) => valid);
        if(!val) return false;

        form_loading.value = true;
        form_error.value   = {};

        let resp = await create('user', user);

        if(resp){
            if(resp.errors){
                console.log('resp', resp)
                form_error.value = resp.errors;
            } else {     
                openDrawer.value = false;
                init();
            }    
        }

        form_loading.value = false;

    })

    const ownerNotInUser = ((mail) => { return !users_email.value.includes(mail) });

    const init = (async ()=>{

        loading.value = true;

        let resp = await list('le_admin/ous');
        if(resp && !resp.errors){
            ous.value = resp;
        }        

        let resp1 = await list('users');
        usersopt.value = [];
        if(resp1 && !resp1.errors){
            users.value = resp1;
            users.value.push(Auth.state.user);
            resp1.map((u) => {                 
                usersopt.value.push({key: u.id, label: u.name + ' ' + u.surname, disabled: false});
                users_email.value.push(u.email);
            })
        }
        
        //usersopt.value.push({key: Auth.state.user.id, label: Auth.state.user.name + ' ' + Auth.state.user.surname, disabled: false});

        let resp2 = await list('le_admin/users_ous');
        checked.value = [];
        if(resp2 && !resp2.errors){
            resp2.map( u => { checked.value[u.ou_id] = u.userids})
        }

        loading.value = false;

    })

    onMounted(async ()=>{
        init();
    });

    defineComponent({
      name: 'OUUserView',
    })

</script>

<style>

.transfer-container{
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    margin-bottom: 32px;      
}

.el-transfer-panel {
  width: 500px;
}

.transfer-footer {
  margin-left: 15px;
  padding: 6px 5px;
}

</style>