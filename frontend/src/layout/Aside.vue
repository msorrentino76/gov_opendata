<template>
  
  <el-menu :default-active="$route.path" :router=true :collapse="collapseMenu" style="min-height: 640px;">

    <el-menu-item>
      <el-icon @click="menuToggle()" v-if="collapseMenu"><Expand /></el-icon>
      <el-icon @click="menuToggle()" v-if="!collapseMenu"><Fold /></el-icon>
    </el-menu-item>

    <br><br>

    <el-menu-item index="/sysadmin" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><Odometer /></el-icon>
      <template #title>Dashboard</template>
    </el-menu-item>

    <el-menu-item index="/le_admin" v-if="Auth.state.user.abilities.includes('legal_entity:admin') && Auth.state.licence !== null">
      <el-icon><Odometer /></el-icon>
      <template #title>Dashboard</template>
    </el-menu-item>

    <el-menu-item index="/le_admin/ou" v-if="Auth.state.user.abilities.includes('legal_entity:admin') && Auth.state.licence !== null">
      <el-icon><OfficeBuilding /></el-icon>
      <template #title>Unità Organizzative</template>
    </el-menu-item>

    <el-menu-item index="/users" v-if="Auth.state.user.abilities.includes('system:admin') || (Auth.state.user.abilities.includes('legal_entity:admin') && Auth.state.licence !== null)">
      <el-icon><User /></el-icon>
      <template #title>{{ Auth.state.user.abilities.includes('system:admin') ? 'Amministratori di Ente' : 'Utenti' }}</template>
    </el-menu-item>

    <el-menu-item index="/sysadmin/legal_entity" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><OfficeBuilding /></el-icon>
      <template #title>Enti</template>
    </el-menu-item>
    
    <el-menu-item index="/sysadmin/licence" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><Management /></el-icon>
      <template #title>Licenze</template>
    </el-menu-item>

    <el-menu-item index="/sysadmin/accessi" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><Histogram /></el-icon>
      <template #title>Accessi</template>
    </el-menu-item>

    <el-menu-item index="/sysadmin/logs" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><Cpu /></el-icon>
      <template #title>System Log</template>
    </el-menu-item>

    <el-menu-item index="/le_admin/ou_users" v-if="Auth.state.user.abilities.includes('legal_entity:admin') && Auth.state.licence !== null">
      <el-icon><List /></el-icon>
      <template #title>Utenze Unità Org.</template>
    </el-menu-item>

    <el-menu-item index="/le_admin/dataset" v-if="Auth.state.user.abilities.includes('legal_entity:admin') && Auth.state.licence !== null">
      <el-icon><DataAnalysis /></el-icon>
      <template #title>Dataset disponibili</template>
    </el-menu-item>

  </el-menu>

</template>


  <script setup>

  import Auth from '../store/Auth.js';

  import {defineProps, defineComponent} from 'vue';

  import { Expand, Fold, Odometer, User, OfficeBuilding, Management, Cpu, List, Histogram, DataAnalysis} from '@element-plus/icons-vue'

  defineProps({
    collapseMenu: {},
    menuToggle: {
        type: Function
    },    
  });

  defineComponent({
      name: 'AsideComponent',
  })

</script>
