<template>

    <h3>Dataset disponibili</h3>
    
    <el-card class="box-card">

      <el-row :gutter="20">

        <el-col :span="12">
          <div class="content">Ricerca testo:</div>
          <el-input v-model="search" placeholder="Inserisci testo..." />
        </el-col>

        <el-col :span="12">
          <div class="content">Filtra per categoria:</div>
          <el-tree-select
            v-model="filter_by_cat"
            :data="categories"
            multiple
            :render-after-expand="false"
            show-checkbox
            check-strictly
            check-on-click-node
            popper-append-to-body="false"
            style="width: 100%"
          />
        </el-col>

      </el-row>

      <br><br>

      <div class="content">Risultati: {{ filterTableData.length }}</div>

      <el-table :data="filterTableData" style="width: 100%" v-loading="loading" empty-text="Nessun risultato trovato" :default-sort="{ prop: 'name', order: 'asc' }" >

        <!--
        <el-table-column prop="id"           label="id"/>
        <el-table-column prop="category"     label="Categoria"/>
        <el-table-column prop="data_struct"  label="Data struct"/>
        <el-table-column prop="is_final"     label="Finale"/>
        -->

        <el-table-column prop="name"         label="Dati disponibili"/>

      </el-table>

    </el-card>

</template>

<script setup>

  import {defineComponent, onMounted, ref, computed} from 'vue';

  import {list} from '../../utils/service.js'

  const loading    = ref(false);
  const search     = ref('');
  const dataflow   = ref([]);
  const categories = ref([]);

  const filter_by_cat = ref();

    const filterTableData = computed(() =>
      dataflow.value.filter(
        (data) => {
          if (!search.value && filter_by_cat.value.length == 0) {
            return data;
          }
          if (search.value && filter_by_cat.value.length != 0) {
            if (data.name.toLowerCase().includes(search.value.toLowerCase()) && filter_by_cat.value.includes(data.category) ){
              return data;
            }
          }
          if (search.value && filter_by_cat.value.length == 0) {
            if (data.name.toLowerCase().includes(search.value.toLowerCase())){
              return data;
            }
          }
          if (!search.value && filter_by_cat.value.length != 0) {
            if (filter_by_cat.value.includes(data.category) ){
              return data;
            }
          }
        }
      )
    )

    onMounted(async ()=>{
      loading.value = true;

      let dataset = await list('le_admin/dataset');
      dataflow.value   = dataset.dataflow;
      categories.value = dataset.categories;

      loading.value = false;
    });

  defineComponent({
      name: 'DatasetView',
  })

</script>
