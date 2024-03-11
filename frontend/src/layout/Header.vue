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

      <!--el-button @click="setMode('assistance')"   :icon="HelpFilled" round>Assistenza</el-button-->
      
      <el-button @click="logout()" :icon="SwitchButton" round>Logout</el-button>

    </header>
    
    <el-drawer v-model="openDrawer" direction="rtl" :size="mode=='notification'? '25%' : '50%'">

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

  import {/*Expand,*/ UserFilled, BellFilled /*, HelpFilled*/, SwitchButton } from '@element-plus/icons-vue';

  import ProfiloView from '../views/common/Profilo.vue';
  import NotificheView from '../views/common/Notifiche.vue';
  import AssistenzaView from '../views/common/Assistenza.vue';

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
    notifications.value = resp.map((r) => {
      if(!newNotification.value && (r.read_at == null)) newNotification.value = true;
      return {'id': r.id, 'message': r.data.message, 'seen' : (r.read_at != null), 'created_at' : r.created_at}
    });
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

<style>
  h4 {
    margin: auto;
  }
</style>