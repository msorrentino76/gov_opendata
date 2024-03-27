<template>

    <h3>Dividendi</h3>

    <el-card class="box-card">

      <el-tabs v-model="activeName" class="act-tabs"  type="border-card" >

          <el-tab-pane label="Corrente" key="current" name="current">

              <br><br>

              <el-row>
                <el-col :span="4">
                  Da: <el-date-picker v-model="data_value[0]" type="month" readonly format="MMMM YYYY" value-format="YYYY-MM" />
                </el-col>
                <el-col :span="4">
                  A: <el-date-picker v-model="data_value[1]" type="month"           format="MMMM YYYY" value-format="YYYY-MM" @change="handleDateChange"/>
                </el-col>
                <el-col :span="4">
                  <el-button type="primary" @click="setDataFilter">Imposta intervallo come filtro per le Attività o Vendite</el-button>
                </el-col>
              </el-row>  

              <br><br>

              <el-card shadow="hover">

                <template #header>
                  <div class="card-header">
                    <span><b>Dividendi per vendite</b></span>
                  </div>
                </template>

                <el-row :gutter="16">

                  <el-col :span="18">
                  
                    <el-table :data="users" v-loading="loading">
                      <el-table-column prop="subject" label="Soggetto"/>
                      <el-table-column prop="role"    label="Ruolo Numie"/>
                      <el-table-column prop="amount"  label="Importo per Vendite" align="right" :formatter="amount"/>        

                      <el-table-column>
                        <template #header>Percentuale riconosciuta</template>
                        <template #default="scope">
                          <el-input-number :disabled="saving" v-model="scope.row.sell_perc" :min="0" :max="100" label="%" @change="sellPercFunct(scope.row)"/>          
                        </template>
                      </el-table-column>

                      <el-table-column prop="sell_amount"  label="Dividendi per Vendite" align="right" :formatter="amount"/>

                    </el-table>

                    <el-table :data="
                      [
                        {label: 'Totale Importo per Vendite'  , amount: totals.amount},
                        {label: 'Totale Dividendi per Vendite', amount: totale_dividendi_per_vendita},
                        {label: 'Importo Residuo per Attività', amount: importo_residuo_attivita},
                      ]
                      "
                      v-if="!loading"
                      style="width: 50%"
                      :row-class-name="tableRowClassName"
                      >
                        <el-table-column prop="label" :formatter="formatBold" />
                        <el-table-column prop="amount" align="right" :formatter="amount" />
                    </el-table>

                  </el-col>

                  <el-col :span="6">
                    <v-chart :option="pieSellsAmountDiagram" style="height: 232px;" autoresize></v-chart>
                  </el-col>

                </el-row>

              </el-card>

              <br>

              <el-card shadow="hover">

                <template #header>
                  <div class="card-header">
                    <span><b>Dividendi per Attività</b></span>
                  </div>
                </template>

                <el-row :gutter="16">

                  <el-col :span="18">

                    <el-table :data="users" v-loading="loading">

                      <el-table-column type="expand">
                        <template #default="props">
                            <el-table :data="props.row.activities">
                            <el-table-column label="#ID"                  prop="id"   :width="64"/>
                            <el-table-column label="Data attività"        prop="data" :width="128" :formatter="dataFormatter"/>
                            <el-table-column label="Ore"                  prop="ore"  :width="64"/>
                            <el-table-column label="Descrizione attività" prop="descrizione" />
                          </el-table>
                        </template>
                      </el-table-column>

                      <el-table-column prop="subject" label="Soggetto"/>
                      <el-table-column prop="hours"   label="Ore complessive per attività" align="right"/>        

                      <el-table-column>
                        <template #header>Percentuale riconosciuta</template>
                        <template #default="scope">
                          <el-input-number :disabled="saving" v-model="scope.row.acts_perc" :min="0" :max="100" label="%" @change="sellActsFunct(scope.row)"/>          
                        </template>
                      </el-table-column>

                      <el-table-column prop="acts_amount"  label="Dividendi per attività" align="right" :formatter="amount"/>

                    </el-table>


                    <el-table :data="
                      [
                        {label: 'Importo Residuo per Attività' , amount: importo_residuo_attivita},
                        {label: 'Totale Dividendi per Attività', amount: totale_dividendi_per_attivita},
                        {label: 'Importo Residuo per Cassa'    , amount: importo_residuo_cassa},
                      ]
                      "
                      v-if="!loading"
                      style="width: 50%"
                      :row-class-name="tableRowClassName"
                      >
                        <el-table-column prop="label" :formatter="formatBold" />
                        <el-table-column prop="amount" align="right" :formatter="amount" />
                    </el-table>

                  </el-col>

                  <el-col :span="6">
                    <v-chart :option="pieActsHoursDiagram" style="height: 232px;" autoresize></v-chart>
                  </el-col>

                </el-row>

              </el-card>

              <br>

              <el-card shadow="hover">

                <template #header>
                  <div class="card-header">
                    <span><b>Quadro di sintesi</b></span>
                  </div>
                </template>

                <el-row>

                  <el-col :span="18">

                    <el-table :data="users" v-loading="loading">
                      <el-table-column prop="subject" label="Soggetto"/>
                      <el-table-column                label="Dividendo complessivo" align="right" :formatter="amount">
                        <template #default="scope">
                          {{ new Intl.NumberFormat('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2,}).format(scope.row.sell_amount +  scope.row.acts_amount) }}      
                        </template>
                      </el-table-column> 
                    </el-table>

                    <el-table :data="
                      [
                        {label: 'Totale Dividendi complessivi' , amount: totale_dividendi_complessivi},
                        {label: 'Importo Residuo per Cassa'    , amount: importo_residuo_cassa},
                        {label: 'Totale Dividendi + Residuo per cassa', amount: totale_dividendi_complessivi + importo_residuo_cassa},                
                      ]
                      "
                      v-if="!loading"
                      style="width: 50%"
                      :row-class-name="tableRowClassName"
                      >
                        <el-table-column prop="label" :formatter="formatBold" />
                        <el-table-column prop="amount" align="right" :formatter="amount" />
                    </el-table>

                  </el-col>

                </el-row>

              </el-card>

              <br>

              <el-alert title="Attenzione! Dopo il salvataggio non sarà più possibile modificare o cancellare il Quadro dei dividendi. Verifica i dati prima di procedere." show-icon type="warning" />

              <br><br>

              <el-row>
                <el-col :span="24" style="text-align:center">
                  <el-popconfirm title="Procedere con il salvataggio?" @confirm="handleSubmit" confirm-button-text="Si">
                    <template #reference>
                      <el-button type="success" :loading="saving">Salva</el-button>  
                    </template>
                  </el-popconfirm>
                </el-col>
              </el-row>

              <br><br>

          </el-tab-pane>

          <el-tab-pane label="Storico" key="history" name="history">

            <el-table :data="history">

              <el-table-column type="expand">
                <template #default="props">
                  <el-table :data="props.row.details">
                    <el-table-column label="Soggetto">
                        <template #default="scope">
                          {{ scope.row.user.name }} {{ scope.row.user.surname }}      
                        </template>
                    </el-table-column>
                    <el-table-column label="Percentuale vendita"  prop="percentuale_vendita"                      align="right"/>
                    <el-table-column label="Dividendo vendita"    prop="dividendo_vendita"    :formatter="amount" align="right"/>
                    <el-table-column label="Percentuale attivita" prop="percentuale_attivita"                     align="right"/>
                    <el-table-column label="Dividendo attivita"   prop="dividendo_attivita"   :formatter="amount" align="right"/>        
                  </el-table>
                </template>
              </el-table-column>

              <el-table-column prop="periodo_da"                sortable label="Da" :formatter="dataFormatter"/>
              <el-table-column prop="periodo_a"                 sortable label="A"  :formatter="dataFormatter"/>
              <el-table-column prop="importo_totale"            sortable label="Importo Totale"            align="right" :formatter="amount"/>
              <el-table-column prop="dividendo_vendita_totale"  sortable label="Dividendo vendita totale"  align="right" :formatter="amount"/>
              <el-table-column prop="dividendo_attivita_totale" sortable label="Dividendo attivita totale" align="right" :formatter="amount"/>
              <el-table-column prop="importo_residuo_cassa"     sortable label="Importo residuo cassa"     align="right" :formatter="amount"/>

            </el-table>

          </el-tab-pane>
          
      </el-tabs>

    </el-card>

</template>

<script setup>

  import Auth from '@/store/Auth';

  import {defineComponent, onMounted, ref, computed, h} from 'vue';

  import {list, filteredList, create} from '../utils/service.js';

  import { use } from 'echarts/core';
  import { PieChart, BarChart } from 'echarts/charts';
  import { TitleComponent, TooltipComponent, LegendComponent, GridComponent  } from 'echarts/components';
  import { CanvasRenderer } from 'echarts/renderers';
  import VChart from 'vue-echarts';
  
  // Registra i componenti necessari di ECharts
  use([TitleComponent, TooltipComponent, LegendComponent, PieChart, CanvasRenderer, BarChart, GridComponent ]);

  const data_value = ref([]);

  const activeName = ref('current');

  const loading = ref(false);
  const users   = ref([]);
  const totals  = ref({});  

  const saving = ref(false);

  const history = ref([]);

  const pieActsHoursDiagram   = ref(null);  
  const pieSellsAmountDiagram = ref(null);  

  const totale_dividendi_per_vendita = computed(
    () => {
      let sum = 0;
      users.value.forEach((obj) => { sum += obj.sell_amount });
      return sum;
    }
  );

  
  const totale_dividendi_per_attivita = computed(
    () => {
      let sum = 0;
      users.value.forEach((obj) => { sum += obj.acts_amount });
      return sum;
    }
  );

  const totale_dividendi_complessivi = computed(
    () => {
      let sum = 0;
      users.value.forEach((obj) => { sum += obj.acts_amount + obj.sell_amount });
      return sum;
    }
  );

  const importo_residuo_attivita = computed(() => {return totals.value.amount - totale_dividendi_per_vendita.value})

  const importo_residuo_cassa    = computed(() => {return importo_residuo_attivita.value - totale_dividendi_per_attivita.value})

  const sellPercFunct = ( (r) => { 
    r.sell_amount = ( r.amount * r.sell_perc ) / 100;
    // i computed non sono watcher... devo richiarmarli per farli aggiornare!
    r.acts_amount = ( importo_residuo_attivita.value * r.acts_perc ) / 100;
  });

  const sellActsFunct = ( (r) => {r.acts_amount = ( importo_residuo_attivita.value * r.acts_perc ) / 100})

  const amount = (row, column) => {
    let value = row[column.property];
    if(row.role == 'developer' && column.property == 'amount') return '-';
    return value !== null ? new Intl.NumberFormat('it-IT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value) : value;
  }

  const formatBold = (row, column) => {
      return h('strong', {}, row[column.property]);
  };

  const dataFormatter = (row, column) => {
    const data = new Date(row[column.property]);
    const opzioniFormattazione = 
            {
                year: 'numeric',
                month: '2-digit', //'long',
                day: '2-digit',
            };
    return data.toLocaleString('it-IT', opzioniFormattazione);
  }

  const tableRowClassName = ( ({ row, rowIndex }) => {if ((rowIndex == 2) && (row.amount < 0)) return 'danger-row'; if (rowIndex == 2) return 'success-row';})

  const handleSubmit = (async() => {
    
    saving.value = true;

    //let reduced_users = {...users.value};

    const data = {
      quote: {
        data_value: data_value.value,
        totals    : {
               amount: totals.value.amount,
          amount_sell: totale_dividendi_per_vendita.value,
          amount_acts: totale_dividendi_per_attivita.value,
          amount_cash: importo_residuo_cassa.value,
        },
      },
      details: users.value.map(
        (u) => {
          return {
                     id: u.id,
            acts_amount: u.acts_amount,
            acts_perc  : u.acts_perc,
            sell_amount: u.sell_amount,
            sell_perc  : u.sell_perc,
          }
        }
      ),
    };

    let resp = await create('admin/quote', data);

    if(resp){
      window.scrollTo({top: 0, behavior: 'smooth' });
      data_value.value = await list('admin/quote/period');
      Auth.commit('setDataFilterQuote', data_value.value);
      filterData(data_value.value[0], data_value.value[1]);
      history.value = await list('admin/quotes');
    }

    saving.value = false;

  });

  const handleDateChange = ()  => {
    Auth.commit('setDataFilterQuote', data_value.value);
    filterData(data_value.value[0], data_value.value[1]);
  }

  const filterData = (async (from, to)=>{

    loading.value = true;

    const resp  = await filteredList('admin/quote', {from: from, to: to}); 

    // init percentuali:

    const sell_perc = 35;
    const acts_perc = 0;

    users.value = resp.users.map((u) => {      
      return { ...u,
        sell_perc: sell_perc,
        sell_amount: ( u.amount * sell_perc ) / 100,
        acts_perc: acts_perc,
        acts_amount: 0,//( importo_residuo_attivita.value * acts_perc ) / 100,
      }
    });

    totals.value = resp.totals; 
    
    loading.value = false;

  });

  const setDataFilter = ()  => {
    Auth.commit('setDataFilter', data_value.value);
  }

  onMounted(async ()=>{   

    // chiedo e setto intanto il periodo di riferimento
    data_value.value = Auth.state.data_filter_quote ? Auth.state.data_filter_quote : await list('admin/quote/period');
    
    filterData(data_value.value[0], data_value.value[1]);

    history.value = await list('admin/quotes');

    pieActsHoursDiagram.value = {
        title: {
          text: 'Ore complessive per attività',
          left: 'center',
        },
        tooltip: {
          trigger: 'item',
          //formatter: '{a} <br/>{b} : {c} ({d}%)',
          formatter: '{b} : {c} ({d}%)',
        },
        /*legend: {
          orient: 'vertical',
          left: 'left',
          data: x,
        },*/
        series: [
          {
            name: 'Spazio',
            type: 'pie',
            radius: '64%',
            center: ['50%', '60%'],
            data: users.value.map( u => { return { value: u.hours, name: u.subject } } ),
            emphasis: {
              itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)',
              },
            },
          },
        ],
      };
      
      pieSellsAmountDiagram.value = {
        title: {
          text: 'Importo per Vendite',
          left: 'center',
        },
        tooltip: {
          trigger: 'item',
          //formatter: '{a} <br/>{b} : {c} ({d}%)',
          formatter: '{b} : {c} ({d}%)',
        },
        /*legend: {
          orient: 'vertical',
          left: 'left',
          data: x,
        },*/
        series: [
          {
            name: 'Spazio',
            type: 'pie',
            radius: '64%',
            center: ['50%', '60%'],
            data: users.value.map( u => { if( u.role != 'developer') return { value: u.amount, name: u.subject } } ),
            emphasis: {
              itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)',
              },
            },
          },
        ],
      };

  });

  defineComponent({
      name: 'QuoteView',
  })

</script>

<style>
    .el-table .warning-row {
    --el-table-tr-bg-color: var(--el-color-warning-light-9);
    }
    .el-table .success-row {
    --el-table-tr-bg-color: var(--el-color-success-light-9);
    }
    .el-table .danger-row {
    --el-table-tr-bg-color: var(--el-color-danger-light-9);
    }
    .el-table .primary-row {
    --el-table-tr-bg-color: var(--el-color-primary-light-9);
    }
</style>