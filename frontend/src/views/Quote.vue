<template>

    <h3>Dividendi</h3>

    <el-card class="box-card">
      
      <el-card shadow="hover">

        <template #header>
          <div class="card-header">
            <span><b>Dividendi per vendite</b></span>
          </div>
        </template>

        <el-row>

          <el-col :span="18">
          
            <el-table :data="users" v-loading="loading">
              <el-table-column prop="subject" label="Soggetto"/>
              <el-table-column prop="role"    label="Ruolo Numie"/>
              <el-table-column prop="amount"  label="Importo per Vendite" align="right" :formatter="amount"/>        

              <el-table-column>
                <template #header>Percentuale riconosciuta</template>
                <template #default="scope">
                  <el-input-number v-model="scope.row.sell_perc" :min="0" :max="100" label="%" @change="sellPercFunct(scope.row)"/>          
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

        </el-row>

      </el-card>

      <br>

      <el-card shadow="hover">

        <template #header>
          <div class="card-header">
            <span><b>Dividendi per Attività</b></span>
          </div>
        </template>

        <el-row>

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
                  <el-input-number v-model="scope.row.acts_perc" :min="0" :max="100" label="%" @change="sellActsFunct(scope.row)"/>          
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
            <el-table :data="users" v-loading="loading" show-summary>
              <el-table-column prop="subject" label="Soggetto"/>
              <el-table-column                label="Dividendo complessivo" align="right" :formatter="amount">
                <template #default="scope">
                  {{ new Intl.NumberFormat('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2,}).format(scope.row.sell_amount +  scope.row.acts_amount) }}      
                </template>
              </el-table-column> 
            </el-table>
          </el-col>

        </el-row>

     </el-card>

    </el-card>

</template>

<script setup>

  import {defineComponent, onMounted, ref, computed, h} from 'vue';

  import {list} from '../utils/service.js';

  const loading = ref(false);
  const users   = ref([]);
  const totals  = ref({});
  
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

  const importo_residuo_attivita = computed(() => {return totals.value.amount - totale_dividendi_per_vendita.value})

  const importo_residuo_cassa    = computed(() => {return importo_residuo_attivita.value - totale_dividendi_per_attivita.value})

  const sellPercFunct = ( (r) => {
      r.sell_amount = ( r.amount * r.sell_perc ) / 100;
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

  const dataFormatter = (row) => {
    const data = new Date(row.data_ora ? row.data_ora : row.data);
    const opzioniFormattazione = 
            {
                year: 'numeric',
                month: '2-digit', //'long',
                day: '2-digit',
            };
    return data.toLocaleString('it-IT', opzioniFormattazione);
  }

  const tableRowClassName = ( ({ row, rowIndex }) => {if ((rowIndex == 2) && (row.amount < 0)) return 'danger-row'; if (rowIndex == 2) return 'success-row';})

  onMounted(async ()=>{

    loading.value = true;

    const resp  = await list('admin/quote'); 

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