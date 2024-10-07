<template>

    <h3>Aggiornamento sistema</h3>
    
    <el-card class="box-card">

      <!--el-tabs type="border-card" tab-position="left"-->
      <el-tabs type="border-card">

        <el-tab-pane label="Codelist"></el-tab-pane>

        <el-tab-pane label="Category"></el-tab-pane>
        
        <el-tab-pane label="Data Flow"></el-tab-pane>
        
        <el-tab-pane label="Available Constraint">
          <AvailableConstraintView />
        </el-tab-pane>

      </el-tabs>

    </el-card>

</template>

<script setup>

  import {defineComponent} from 'vue';
  import AvailableConstraintView from './manteinance_modules/AvailableConstraint.vue';

  defineComponent({
      name: 'ManteinanceView',
  })

</script>
