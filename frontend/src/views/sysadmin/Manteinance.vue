<template>

    <h3>Aggiornamento sistema</h3>
    
    <el-card class="box-card">

      <!--el-tabs type="border-card" tab-position="left"-->
      <el-tabs type="border-card">

        <el-tab-pane label="Codelist">
          <CodelistView />
        </el-tab-pane>

        <el-tab-pane label="Category">
          <CategoryView />
        </el-tab-pane>
        
        <el-tab-pane label="Data Flow">
          <DataflowView />
        </el-tab-pane>
        
        <el-tab-pane label="Available Constraint">
          <AvailableConstraintView />
        </el-tab-pane>

        <el-tab-pane label="Data Structure">
          <DataStructureView />
        </el-tab-pane>

      </el-tabs>

    </el-card>

</template>

<script setup>

  import {defineComponent} from 'vue';
  import AvailableConstraintView from './manteinance_modules/AvailableConstraint.vue';
  import DataStructureView       from './manteinance_modules/DataStructure.vue';
  import CodelistView            from './manteinance_modules/Codelist.vue';
  import DataflowView            from './manteinance_modules/Dataflow.vue';
  import CategoryView            from './manteinance_modules/Category.vue';

  defineComponent({
      name: 'ManteinanceView',
  })

</script>
