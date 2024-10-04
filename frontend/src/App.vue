
<template>

    <el-container v-if="store.state.login.logged">

        <el-aside :class="classMenu">
          <AsideComponent :collapseMenu="collapseMenu" :menuToggle="menuToggle"/>
        </el-aside>

        <el-container>

          <el-header>
            <HeaderComponent :menuToggle="menuToggle"/>
          </el-header>
        
          <el-main>
            <MainComponent/>
          </el-main>

        </el-container>
      
    </el-container>
    
    <el-container v-if="!store.state.login.logged">
      <LoginView />
    </el-container>
    
    <el-footer>
      <FooterComponent/>
    </el-footer>
      
</template>

<style>
@import './assets/css/global.css';
</style>


<script setup>

import HeaderComponent from './layout/Header.vue';
import AsideComponent  from './layout/Aside.vue';
import MainComponent   from './layout/Main.vue';
import FooterComponent from './layout/Footer.vue';

import LoginView from './views/auth/Login.vue';

import { ref } from 'vue';

import { useStore } from 'vuex';
const store = useStore();

const collapseMenu = ref(true);
const classMenu    = ref('start-menu');

function menuToggle(){
  collapseMenu.value = !collapseMenu.value;
  classMenu.value = collapseMenu.value ? 'close-menu' : 'open-menu';
}

</script>

<style>
  .start-menu {
    width: 64px !important;
  }
  .open-menu {
    width: 200px !important;
    transition: width 0.1s ease;
  }
  .close-menu {
    width: 64px !important;
    transition: width 0.1s ease;
  } 
</style>
