<template>

    <h3>Accessi</h3>
    
    <el-card class="box-card">

      <v-chart :option="stats" style="height: 480px;" autoresize v-loading="loading"></v-chart>

      <h1>Ultimi 64 Login</h1>
      <el-table :data="last" style="width: 100%" v-loading="loading" empty-text="Nessun risultato trovato" >
        <el-table-column prop="data_ora" label="Data"/>
        <el-table-column prop="user"    label="Utente"/>
        <el-table-column prop="so"      label="Sistema Operativo"/>
        <el-table-column prop="browser" label="Browser"/>
        <el-table-column prop="device"  label="Device"/>
        <el-table-column prop="ip"      label="IP"/>
      </el-table>

    </el-card>

</template>

<script setup>

  import {defineComponent, onMounted, ref} from 'vue';

  import {list} from '../../utils/service.js'

  import { use } from 'echarts/core';
  import { PieChart, BarChart } from 'echarts/charts';
  import { TitleComponent, TooltipComponent, LegendComponent, GridComponent  } from 'echarts/components';
  import { CanvasRenderer } from 'echarts/renderers';
  import VChart from 'vue-echarts';

  // Registra i componenti necessari di ECharts
  use([TitleComponent, TooltipComponent, LegendComponent, PieChart, CanvasRenderer, BarChart, GridComponent ]);

  const loading   = ref(false);

  const stats = ref({
        title: {
          text: 'Accessi di utenti distinti negli ultimi 12 mesi',
          left: 'center',
        },
        tooltip: {
          trigger: 'item',
          formatter: '{b} : {c}',
        },
        xAxis: {
          type: 'category',
          data: []
        },
        yAxis: {
          type: 'value'
        },
        series: [
          {
            data: [],
            type: 'bar'
          }
        ],
    });

    const last = ref([]);

    onMounted(async ()=>{
      loading.value = true;

      const stats_resp  = await list('sys_admin/accessi');
      stats.value.xAxis.data     = stats_resp.xAxis;
      stats.value.series[0].data = stats_resp.yAxis;

      const last_resp  = await list('sys_admin/ultimi');
      last.value = last_resp;

      loading.value = false;
    });

  defineComponent({
      name: 'AccessiView',
  })

</script>
