<template>

  <el-alert title="Viene scaricato (se assente) il file dataflow.json da https://sdmx.istat.it/SDMXWS/rest/dataflow/IT1. Completata l'elaborazione viene archiviato con dataflow_<yyyymmdd>.json" type="info" />
  
    <ManteinanceFileHandleView type="dataflow" />
  
  </template>
  
  <script setup>
  
    import {defineComponent} from 'vue';
  
    import ManteinanceFileHandleView from './common/ManteinanceFileHandle.vue';

    defineComponent({
        name: 'DataflowView',
    })
  
  </script>
  