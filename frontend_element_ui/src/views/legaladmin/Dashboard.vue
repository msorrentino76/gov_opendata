<template>

    <h3>Dashboard</h3>

    <el-card class="box-card">

      <div style="min-height: 600px" v-if="Auth.state.licence === null" >
        <el-alert title="Non sono disponibili licenze attive. Contattare l'Assistenza per un nuovo acquisto o rinnovo" type="error" :closable="false" />
      </div>

    </el-card>

</template>

<script setup>

  import {defineComponent} from 'vue';

  import Auth from '../../store/Auth.js';

  defineComponent({
      name: 'LeAdminDashboardView',
  })

</script>
