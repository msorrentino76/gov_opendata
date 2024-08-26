<template>

    <header class="main-header">

      <el-popover placement="bottom" :width="480" trigger="click">
        <template #reference>
          <el-button :icon="BellFilled" round>
            <el-badge is-dot class="item" :hidden="!newNotification">
              Notifiche
            </el-badge>
          </el-button>
        </template>
        <NotificheView :notifications="notifications"/>
      </el-popover>      

      <el-button @click="setMode('profile')"      :icon="UserFilled" round>Profilo</el-button>

      <el-button v-if="Auth.state.user.abilities.includes('legal_entity:admin')"  @click="setMode('assistance')"   :icon="HelpFilled" round>Assistenza</el-button>
      
      <el-button @click="logout()" :icon="SwitchButton" round>Logout</el-button>

      <div v-if="Auth.state.licence !== null" class="centered-text-container">
        <span class="centered-text">{{ Auth.state.licence.des_amm }}
          <el-tooltip v-if="Auth.state.user.licence.expired_days > 30" class="box-item" effect="dark" :content="`La licenza scadrà tra ${Auth.state.user.licence.expired_days} giorni`" placement="top-start">
            <el-button type="success" :icon="SuccessFilled" circle size="small"/>
          </el-tooltip>
          <el-tooltip v-if="Auth.state.user.licence.expired_days <= 30 && Auth.state.user.licence.expired_days > 10" class="box-item" effect="dark" :content="`La licenza scadrà tra ${Auth.state.user.licence.expired_days} giorni. Contattare l'Assistenza per rinnovare.`" placement="top-start">
            <el-button type="warning" :icon="WarnTriangleFilled" circle size="small"/>
          </el-tooltip>
          <el-tooltip v-if="Auth.state.user.licence.expired_days <= 10" class="box-item" effect="dark" :content="`La licenza scadrà tra ${Auth.state.user.licence.expired_days} giorni. Contattare l'Assistenza per rinnovare.`" placement="top-start">
            <el-button type="danger" :icon="WarningFilled" circle size="small"/>
          </el-tooltip>                    
        </span>
      </div>

    </header>
    
    <el-drawer v-model="openDrawer" direction="rtl" :size="mode=='notification'? '25%' : '75%'">

        <template #header>
          <h4 v-if="mode=='profile'">Profilo</h4>
          <!--h4 v-if="mode=='notification'">Notifiche</h4-->
          <h4 v-if="mode=='assistance'">Assistenza</h4>
        </template>

        <ProfiloView    v-if="mode=='profile'"/>
        <!--NotificheView  v-if="mode=='notification'" :notifications="notifications"/-->
        <AssistenzaView v-if="mode=='assistance'"/>

    </el-drawer>

</template>
  
<script setup>

  import {ref, defineProps, defineComponent, onMounted, onBeforeUnmount } from 'vue';
  import {list, revoke} from '../utils/service.js'; 

  import {/*Expand,*/ UserFilled, BellFilled, HelpFilled, SwitchButton, SuccessFilled, WarnTriangleFilled, WarningFilled } from '@element-plus/icons-vue';

  import ProfiloView from '../views/common/Profilo.vue';
  import NotificheView from '../views/common/Notifiche.vue';
  import AssistenzaView from '../views/common/Assistenza.vue';

  import Auth from '../store/Auth.js';

  const openDrawer    = ref(false);
  const mode          = ref('');

  const notifications = ref([]);
  const newNotification = ref(false);
  
  const logout = (async ()=>{await revoke('revoke')})

  const setMode = ((m) => {mode.value = m; openDrawer.value = true; });

  defineProps({
    menuToggle: {
        type: Function
    },
  });

  defineComponent({
      name: 'HeaderComponent',
  });

  onMounted(async ()=>{
    getAllNotifications();
  });

  const getAllNotifications = (async() => {
    const resp = await list('notifications/all'); 
    if(resp){   
      notifications.value = resp.map((r) => {
        if(!newNotification.value && (r.read_at == null)) newNotification.value = true;
        return {'id': r.id, 'message': r.data.message, 'seen' : (r.read_at != null), 'created_at' : r.created_at}
      });
    }
  });

  const notifTimerId = setInterval(() => {
      getUnreadNotifications();
    }, 300000);

  const getUnreadNotifications = (async() => {
    const resp = await list('notifications/unread');
    newNotification.value = resp.length != 0;
    const unread = resp.map((r) => {
      return {'id': r.id, 'message': r.data.message, 'seen' : (r.read_at != null), 'created_at' : r.created_at}
    });
   
    // mergio le notifiche lette con quelle non lette
    const setUnion = [...unread, ...notifications.value];

    // elimino i duplicati e ordino
    notifications.value = setUnion.filter((obj, index, self) =>
        index === self.findIndex((t) => (
          t.id === obj.id
        ))
      ).sort((a, b) => {return (new Date(b.created_at)) - (new Date(a.created_at))});

  });

  onBeforeUnmount (() => {
    clearInterval(notifTimerId);
  });

</script>

<style scoped>
  h4 {
    margin: auto;
  }

.centered-text-container {
  text-align: center; /* Centra il testo all'interno del contenitore */
  position: absolute; /* Permette di posizionare il testo */
  left: 50%;
  top: 0;
}

.centered-text {
  display: inline-block;
}
</style>