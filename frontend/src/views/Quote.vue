<template>

    <h3>Dividendi</h3>

    <el-card class="box-card">
      
      <el-table :data="users" v-loading="loading">
        <el-table-column prop="subject" label="Soggetto"/>
        <el-table-column prop="role"    label="Ruolo Numie"/>
        <el-table-column prop="amount"  label="Venduto" align="right" :formatter="amount"/>        

        <el-table-column>
          <template #header>Percentuale riconosciuta</template>
          <template #default="scope">
            <el-input-number v-model="scope.row.perc" :min="0" :max="100" label="%" @change="sellPercFunct(scope.row)"/>          
          </template>
        </el-table-column>

        <el-table-column prop="amount_perc_sell"  label="Dividendi per vendite" align="right" :formatter="amount"/>

      </el-table>
    </el-card>

</template>

<script setup>

  import {defineComponent, onMounted, ref} from 'vue';

  import {list} from '../utils/service.js';

  const loading = ref(false);
  const users   = ref([]);
  const totals  = ref({});
  
  const sellPercFunct = ( (r) => {console.log(r); r.amount_perc_sell = ( r.amount * r.perc ) / 100})

  const amount = (row, column) => {
    let value = row[column.property];
    if(row.role == 'developer' && column.property == 'amount') return '-';
    return value !== null ? new Intl.NumberFormat('it-IT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value) : value;
  }

  onMounted(async ()=>{

    loading.value = true;

    const resp  = await list('admin/quote'); 

    // init percentuali:

    const perc = 35;

    resp.users = resp.users.map((u) => {      
      return { ...u, perc: perc, amount_perc_sell: ( u.amount * perc ) / 100  }
    });

    users.value  = resp.users;
    totals.value = resp.totals; 
    
    loading.value = false;

  });

  defineComponent({
      name: 'QuoteView',
  })

</script>