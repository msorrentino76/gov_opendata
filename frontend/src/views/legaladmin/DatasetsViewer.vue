
<template>

    <el-alert v-if="props.datasets.error" :title="props.datasets.error"     type="error"   :closable="false" />

    <el-alert v-if="props.datasets.empty" title="Nessun record disponibile. Rimuovere qualche filtro." type="warning" :closable="false" />

    <div v-if="!props.datasets.empty">

        <span v-for="(dataset, dataset_index) in props.datasets.datasets" :key="dataset_index">
        
            <el-descriptions border :title="`Criteri Serie N° ${dataset_index + 1}`" :column="3">
                <el-descriptions-item v-for="(title, title_index) in dataset.titles" :key="title_index" :label="title.label">{{ title.value }}</el-descriptions-item>
            </el-descriptions>

            <br><br>

            <el-tag v-if="props.datasets.isTest" type="danger">Dati ISTAT di test </el-tag>
            
            <el-table :data="dataset.observations" style="width: 64%">
                <el-table-column prop="x" label="Ascissa"/>
                <el-table-column prop="y_0" label="Ordinata 1"/>
                <el-table-column prop="y_1" label="Ordinata 2"/>
                <el-table-column prop="y_2" label="Ordinata 3"/>
                <el-table-column prop="y_3" label="Ordinata 4"/>
            </el-table>

            <el-divider />

        </span>

    </div>

</template>

<script setup>
  
import {defineComponent, defineProps} from 'vue';


const props = defineProps({
      datasets: Object,
    });

defineComponent({
    name: 'DatasetsViewerView',
})

</script>