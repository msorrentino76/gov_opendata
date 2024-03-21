<template>

    <h3>Dashboard</h3>

    <el-card class="box-card">
      Benvenuto {{ Auth.state.user.name }} {{ Auth.state.user.surname }}
      <br><br>
      <el-alert v-if="Auth.state.user.password_changed != true" title="La tua password è ancora quella di default. Si invita a cambiarla prima possibile." type="error" />
    </el-card>

</template>

<script setup>

  import Auth from '@/store/Auth';
  import {ref, onMounted, defineComponent} from 'vue';

  import {list} from '../utils/service.js';

  const loading = ref(false);

  const stats = ref();

  onMounted(async ()=>{
    loading.value = true;
    stats.value = await list('user/dashboard');
    loading.value = false;
  });

  defineComponent({
      name: 'DashboardView',
  })

</script>