<template>
  
  <el-menu default-active="/" :router=true :collapse="collapseMenu">

    <el-menu-item>
      <el-icon @click="menuToggle()" v-if="collapseMenu"><Expand /></el-icon>
      <el-icon @click="menuToggle()" v-if="!collapseMenu"><Fold /></el-icon>
    </el-menu-item>

    <br><br>

    <el-menu-item index="/">
      <el-icon><Odometer /></el-icon>
      <template #title>Dashboard</template>
    </el-menu-item>

    <el-menu-item index="/activity">
      <el-icon><Calendar /></el-icon>
      <template #title>Attività</template>
    </el-menu-item>

    <el-menu-item index="/sells">
      <el-icon><Sell /></el-icon>
      <template #title>Vendite</template>
    </el-menu-item>
    
    <el-menu-item index="/quote">
      <el-icon><Money /></el-icon>
      <template #title>Dividendi</template>
    </el-menu-item>

  </el-menu>

</template>

  <script setup>

  import {defineProps, defineComponent} from 'vue';

  import { Expand, Fold, Odometer, Money, Sell, Calendar } from '@element-plus/icons-vue'

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
