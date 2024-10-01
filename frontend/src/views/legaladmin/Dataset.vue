<template>

    <h3>Dataset disponibili</h3>
    
    <el-card class="box-card">

      <div style="padding: 16px 0 16px 0;">Risultati: {{ filterTableData.length }}</div>

      <el-table :data="filterTableData" style="width: 100%" v-loading="loading" empty-text="Nessun risultato trovato" :default-sort="{ prop: 'name', order: 'asc' }" >

        <!--el-table-column prop="id"    label="id"/-->
        <el-table-column prop="name"  label="Categoria"/>

        <el-table-column align="right">
          <template #header>
            <el-input v-model="search" size="small" placeholder="Cerca..." />
          </template>
        </el-table-column>

      </el-table>

    </el-card>

</template>

<script setup>

  import {defineComponent, onMounted, ref, computed} from 'vue';

  import {list} from '../../utils/service.js'

  const loading   = ref(false);
  const search    = ref('');
  const dataset = ref([]);

    const filterTableData = computed(() =>
      dataset.value.filter(
        (data) => !search.value || data.name.toLowerCase().includes(search.value.toLowerCase())
      )
    )

    onMounted(async ()=>{
      loading.value = true;

      dataset.value = await list('le_admin/dataset');

      loading.value = false;
    });

  defineComponent({
      name: 'DatasetView',
  })

</script>
