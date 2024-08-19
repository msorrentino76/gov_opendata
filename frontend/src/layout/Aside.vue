<template>
  
  <el-menu :default-active="$route.path" :router=true :collapse="collapseMenu">

    <el-menu-item>
      <el-icon @click="menuToggle()" v-if="collapseMenu"><Expand /></el-icon>
      <el-icon @click="menuToggle()" v-if="!collapseMenu"><Fold /></el-icon>
    </el-menu-item>

    <br><br>

    <el-menu-item index="/sysadmin" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><Odometer /></el-icon>
      <template #title>Dashboard</template>
    </el-menu-item>

    <el-menu-item index="/users" v-if="Auth.state.user.abilities.includes('system:admin') || Auth.state.user.abilities.includes('legal_entity:admin')">
      <el-icon><User /></el-icon>
      <template #title>Amministratori di Ente</template>
    </el-menu-item>

    <el-menu-item index="/sysadmin/legal_entity" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><OfficeBuilding /></el-icon>
      <template #title>Enti</template>
    </el-menu-item>
    
    <el-menu-item index="/sysadmin/licence" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><Management /></el-icon>
      <template #title>Licenze</template>
    </el-menu-item>

    <el-menu-item index="/sysadmin/logs" v-if="Auth.state.user.abilities.includes('system:admin')">
      <el-icon><Cpu /></el-icon>
      <template #title>System Log</template>
    </el-menu-item>

  </el-menu>

</template>


  <script setup>

  import Auth from '../store/Auth.js';

  import {defineProps, defineComponent} from 'vue';

  import { Expand, Fold, Odometer, User, OfficeBuilding, Management, Cpu} from '@element-plus/icons-vue'

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
