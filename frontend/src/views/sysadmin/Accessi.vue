<template>

    <h3>Accessi</h3>
    
    <el-card class="box-card">

      <v-chart :option="stats" style="height: 480px;" autoresize v-loading="loading"></v-chart>

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
          text: 'Accessi di utenti distint negli ultimi 12 mesi',
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

  onMounted(async ()=>{
    loading.value = true;
    const resp  = await list('sys_admin/accessi');
    stats.value.xAxis.data     = resp.xAxis;
    stats.value.series[0].data = resp.yAxis;
    loading.value = false;
  });

  defineComponent({
      name: 'AccessiView',
  })

</script>
