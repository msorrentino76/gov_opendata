<template>

    <h3>Dashboard</h3>

    <el-row :gutter="16">      
      <el-col :span="6">
        <div class="statistic-card">
          <el-statistic title="Numero totale di attività" :value="actsTotalcountTrans" />
        </div> 
      </el-col> 
      <el-col :span="6">
        <div class="statistic-card">
          <el-statistic :value="actsTotalhoursTrans">
            <template #title>
              <div style="display: inline-flex; align-items: center">
                Ore totali di attività
              </div>
            </template>
            <template #suffix> ore</template>
          </el-statistic>
        </div> 
      </el-col> 
      <el-col :span="6">  
        <div class="statistic-card">
          <el-statistic title="Numero totale di vendite" :value="sellsTotalcountTrans" />
        </div> 
      </el-col> 
      <el-col :span="6">  
        <div class="statistic-card">
          <el-statistic :value="sellsTotalamountTrans">
            <template #title>
              <div style="display: inline-flex; align-items: center">
                Importo totale di vendite
              </div>
            </template>
            <template #suffix>&euro;</template>
          </el-statistic>
        </div>                          
      </el-col>
    </el-row>

    <br><br><br>

    <el-card class="box-card">      
      <b>Benvenuto {{ Auth.state.user.name }} {{ Auth.state.user.surname }}</b>      
      <br><br>      
      <el-alert v-if="Auth.state.user.password_changed != true" title="La tua password è ancora quella di default. Si invita a cambiarla prima possibile." type="error" />
      <br><br>
      <el-row type="flex" justify="center" :gutter="20">
        <el-col v-for="user in last_stats" :key="user.id + 'last_login'" :span="8">
          <el-text class="mx-1" type="success" v-if="Auth.state.user.id == user.id">Ultimi accessi: {{ user.subject }}</el-text>
          <el-text class="mx-1" type="warning" v-if="Auth.state.user.id != user.id">Ultimi accessi: {{ user.subject }}</el-text>
          <el-table :data="user.last_logins" :row-class-name="tableRowClassName">
            <el-table-column prop="data_ora" label="Data" :formatter="dataTimeFormatter"/>
            <el-table-column prop="so"       label="Sistema Operativo" />
            <el-table-column prop="browser"  label="Browser" />
          </el-table>
        </el-col>
      </el-row>
    </el-card>

    <br>

    <el-card class="box-card">      
      <b>Ultime attività</b>
      <br><br>
      <el-row type="flex" justify="center" :gutter="20">
        <el-col v-for="user in last_stats" :key="user.id + 'last_login'" :span="8">
          <el-text class="mx-1" type="success" v-if="Auth.state.user.id == user.id">{{ user.subject }}</el-text>
          <el-text class="mx-1" type="warning" v-if="Auth.state.user.id != user.id">{{ user.subject }}</el-text>
          <el-table :data="user.last_acts" :row-class-name="tableRowClassName">
            <el-table-column prop="data"        label="Data"        :formatter="dataFormatter"/>
            <el-table-column prop="descrizione" label="Descrizione" :formatter="readMore" width="320"/>
            <el-table-column prop="ore"         label="Ore"                               align="right"/>
          </el-table>
        </el-col>
      </el-row>
    </el-card>

    <br>

    <el-card class="box-card">      
      <b>Ultime vendite</b>
      <br><br>
      <el-row type="flex" justify="center" :gutter="20">
        <el-col v-for="user in last_stats" :key="user.id + 'last_login'" :span="8">
          <el-text class="mx-1" type="success" v-if="Auth.state.user.id == user.id">{{ user.subject }}</el-text>
          <el-text class="mx-1" type="warning" v-if="Auth.state.user.id != user.id">{{ user.subject }}</el-text>
          <el-table :data="user.last_sells" :row-class-name="tableRowClassName">
            <el-table-column prop="data"        label="Data"        :formatter="dataFormatter"/>
            <el-table-column prop="descrizione" label="Descrizione" :formatter="readMore" width="320"/>
            <el-table-column prop="importo"     label="Importo €"   :formatter="amount"   align="right"/>
          </el-table>
        </el-col>
      </el-row>
    </el-card>

</template>

<script setup>

  import Auth from '@/store/Auth';
  import {ref, onMounted, defineComponent} from 'vue';

  import {list} from '../utils/service.js';

  import { useTransition } from '@vueuse/core'

  const loading = ref(false);

  const stats = ref();

  const actsTotalcount   = ref(0);
  const actsTotalhours   = ref(0);
  const sellsTotalcount  = ref(0);
  const sellsTotalamount = ref(0);

  const actsTotalcountTrans   = useTransition(actsTotalcount  , {duration: 1500,})
  const actsTotalhoursTrans   = useTransition(actsTotalhours  , {duration: 1500,})
  const sellsTotalcountTrans  = useTransition(sellsTotalcount , {duration: 1500,})
  const sellsTotalamountTrans = useTransition(sellsTotalamount, {duration: 1500,})

  const last_stats = ref({});

  const tableRowClassName =( ( { row, rowIndex } ) => {
    console.log(row);
    return rowIndex == 0 ? 'success-row' : '';
  });

  const dataTimeFormatter = (row) => {
    const data = new Date(row.data_ora ? row.data_ora : row.data);
    const opzioniFormattazione = 
            {
                year: 'numeric',
                month: '2-digit', //'long',
                day: '2-digit',
                hour: 'numeric',
                minute: 'numeric',
                hour12: false,
            };
    return data.toLocaleString('it-IT', opzioniFormattazione);
  }

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

  const readMore = (row) => {
    let str       = row.descrizione;
    let maxLength = 48;
    if (str.length > maxLength) {
      return str.slice(0, maxLength) + '...';
    } else {
      return str;
    }
  }

  const amount = (row) => {
    let value = row.importo;
    return value !== null ? new Intl.NumberFormat('it-IT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value) : value;
  }

  onMounted(async ()=>{

    loading.value = true;
    
    const resp  = await list('user/dashboard');
    
    stats.value = resp;

    actsTotalcount.value   = resp.acts.total.count;
    actsTotalhours.value   = resp.acts.total.hours;
    sellsTotalcount.value  = resp.sells.total.count;
    sellsTotalamount.value = resp.sells.total.amount;
    
    last_stats.value = resp.last_stats;

    loading.value = false;

  });

  defineComponent({
      name: 'DashboardView',
  })

</script>

<style scoped>
:global(h2#card-usage ~ .example .example-showcase) {
  background-color: var(--el-fill-color) !important;
}

.el-statistic {
  --el-statistic-content-font-size: 28px;
}

.statistic-card {
  height: 100%;
  padding: 20px;
  border-radius: 4px;
  background-color: var(--el-bg-color-overlay);
}

.statistic-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  font-size: 12px;
  color: var(--el-text-color-regular);
  margin-top: 16px;
}

.statistic-footer .footer-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.statistic-footer .footer-item span:last-child {
  display: inline-flex;
  align-items: center;
  margin-left: 4px;
}

.green {
  color: var(--el-color-success);
}
.red {
  color: var(--el-color-error);
}

.el-table .warning-row {
  --el-table-tr-bg-color: var(--el-color-warning-light-9);
}
.el-table .success-row {
  --el-table-tr-bg-color: var(--el-color-success-light-9);
}

</style>