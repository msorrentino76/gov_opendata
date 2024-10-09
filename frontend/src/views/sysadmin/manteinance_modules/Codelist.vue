<template>

<el-alert title="Viene scaricato (se assente) il file codelist.xml da http://sdmx.istat.it/SDMXWS/rest/codelist. Completata l'elaborazione viene archiviato con codelist_<yyyymmdd>.xml" type="info" />

<ManteinanceFileHandleView type="codelist" />

</template>

<script setup>

  import {defineComponent} from 'vue';

  import ManteinanceFileHandleView from './common/ManteinanceFileHandle.vue';

  defineComponent({
      name: 'CodelistView',
  })

</script>
