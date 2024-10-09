<template>

  <el-alert title="Se presente il file category.json viene archiviato come category_yyyymmdd.json e scaricato un nuovo category.json da https://sdmx.istat.it/SDMXWS/rest/categoryscheme/IT1. Il Model Categories è 'virtuale' poichè attinge dal json e non dal database" type="info" />
  
  <ManteinanceFileHandleView type="categories" />

  </template>
  
  <script setup>
  
    import {defineComponent} from 'vue';
  
    import ManteinanceFileHandleView from './common/ManteinanceFileHandle.vue';
  
    defineComponent({
        name: 'CategoryView',
    })
  
  </script>
  