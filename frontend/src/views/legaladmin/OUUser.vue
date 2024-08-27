
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
                </template>
                <template #left-footer>
                    <el-button class="transfer-footer" type="success" @click="handleOwnerToUser(ou.id)" size="small">Crea utenza da Responsabile</el-button>
                </template>

            </el-transfer>

        </div>

    </el-card>

</template>

<script setup>

    import {defineComponent, onMounted, ref} from 'vue';

    import {list, update /*create, read, del*/} from '../../utils/service.js';

    import Auth from '../../store/Auth.js';

    const ous      = ref([]);
    const users    = ref([]);
    const usersopt = ref([]);

    const checked = ref([]);

    const loading = ref(true);

    const handleChange = (async (ou_id) => {  
        loading.value = true; 
        await update('le_admin/user_ou', {id: ou_id, users: checked.value[ou_id]});   
        loading.value = false;
    });
    
    const handlePermission = ((ou_id) => {      
      console.log('handlePermission', ou_id)
    });
    
    const handleOwnerToUser = ((ou_id) => {      
      console.log('handleOwnerToUser', ou_id)
    });

    const filterMethod = (query, item) => {
        return item.label.toLowerCase().includes(query.toLowerCase())
    }

    const truncatedText = ((text) => {      
      return text.length > 58 ? text.slice(0, 58) + "..." : text;
    });

    onMounted(async ()=>{

        loading.value = true;

        let resp = await list('le_admin/ous');
        if(resp && !resp.errors){
            ous.value = resp;
        }

        let resp1 = await list('users');
        if(resp1 && !resp1.errors){
            users.value = resp1;
            resp1.map((u) => {                 
                usersopt.value.push({key: u.id, label: u.name + ' ' + u.surname, disabled: false});
            })
        }
        
        usersopt.value.push({key: Auth.state.user.id, label: Auth.state.user.name + ' ' + Auth.state.user.surname, disabled: false});

        let resp2 = await list('le_admin/users_ous');
        if(resp2 && !resp2.errors){
            resp2.map( u => { checked.value[u.ou_id] = u.userids})
        }

        loading.value = false;

    })

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