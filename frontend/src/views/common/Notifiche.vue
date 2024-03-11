<template>
  <el-button type="primary" link @click="markAll()">Marca tutte come lette</el-button>
  <el-table :data="ntc" style="width: 100%" :row-class-name="unSeen" size="small" height="180" empty-text="Nessuna notifica">
    <el-table-column width="32">
      <template #default="scope">
        <el-tooltip class="box-item" effect="dark" content="Marca come letta" placement="top-start" :disabled="scope.row.seen">
          <el-button :type="scope.row.seen ? 'info' : 'success'" :icon="View" circle size="small" :disabled="scope.row.seen" @click="markOne(scope.row.id)"/>
        </el-tooltip>
      </template>
    </el-table-column>
    <el-table-column prop="created_at" label="" :formatter="dataFormat" width="120"/>
    <el-table-column prop="message" label=""/>
  </el-table>
  <el-text class="mx-1" size="small">Ultime 25 notifiche</el-text>
</template>

<script setup>

    import {ref, defineComponent, defineProps, onUpdated} from 'vue';

    import {update} from '../../utils/service.js'; 
    
    import {View} from '@element-plus/icons-vue'

    const ntc = ref([])

    const props = defineProps({
      notifications: Array,
    });

    onUpdated( () => {
      ntc.value = props.notifications;
    });

    defineComponent({
      name: 'NotificheView',
    });

    const unSeen =( ( { row } ) => {
      return row.seen ? '' : 'success-row';
    });

    const markAll =(async () => {
      await update('notifications/mark', {'id': 'all'}, true);
      ntc.value = ntc.value.map( (r) => {r.seen = true; return r});
    });
    
    const markOne =(async ( id ) => {
      await update('notifications/mark', {'id': id}, true);
      ntc.value = ntc.value.map( (r) => {r.seen = r.id == id ? true : r.seen; return r});
    });

    const dataFormat = (row) => {
      const opzioniFormattazione ={
                year: 'numeric',
                month: 'numeric', //'long',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: false,
            };      
      const data = new Date(row.created_at);
      return data.toLocaleString('it-IT', opzioniFormattazione);
    };

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