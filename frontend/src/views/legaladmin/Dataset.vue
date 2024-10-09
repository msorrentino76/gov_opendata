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

        <el-col :span="12">
          <div class="content">Filtra per disponibilità sul Territorio:</div>
          <el-select  
                v-model="filter_by_territory"
                multiple
                filterable
                remote
                reserve-keyword
                placeholder="Digitare almeno 3 caratteri..."
                style="width: 100%"   
                :remote-method="searchDataflowByTerritory"                                        
              >
                <el-option
                  v-for="item in options"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
          </el-select>
        </el-col>

      <el-row :gutter="20">

      </el-row>
            
      <br><br>

      <div class="content">Risultati: {{ filterTableData.length }}</div>

      <!-- el-table :data="filterTableData" style="width: 100%" v-loading="loading" empty-text="Nessun risultato trovato" :default-sort="{ prop: 'name', order: 'asc' }" -->
      <el-table :data="filterTableData" style="width: 100%" v-loading="dataflow.length == 0" empty-text="Attendere...">
 
        <!--
        <el-table-column prop="id"           label="id"/>
        <el-table-column prop="category"     label="Categoria"/>
        <el-table-column prop="data_struct"  label="Data struct"/>
        <el-table-column prop="is_final"     label="Finale"/>
        -->

        <el-table-column prop="version"      width="120" align="right" label="Versione"/>
        <el-table-column prop="name"                                   label="Dati disponibili"/>
        <el-table-column prop="filter_count"             align="right" label="Filtri di ricerca applicabili"/>

        <el-table-column>
          <template #default="scope">
            <el-button size="small" type="primary" @click="handleQuery(scope.$index, scope.row)">
              Interroga
            </el-button>
          </template>
        </el-table-column> 
      </el-table>

      <el-drawer v-model="openDrawer" :title="flow_name" direction="rtl" size="90%">
       
        <!-- DatasetFilterView :flow_name="flow_name" :flow_ref="flow_ref" :id_datastructure="id_datastructure"/-->
       
        <!--el-alert title="Se non viene selezionato il valore di un filtro esso verrà escluso" show-icon type="info" /-->

        <el-row :gutter="20" v-loading="loadingDrawer">

          <el-col :span="4">
          
            <b>Filtri di ricerca applicabili:</b>

            <div v-for="posix in datafilter" :key="posix.name">
              
              <p>{{ posix.label }}</p>

              <!-- SELECT SEMPLICE -->
              <el-select  
                v-if="posix.type == 'select'"          
                v-model="selectedfilter[posix.name]"
                multiple
                filterable
                placeholder="Seleziona"
                style="width: 100%"            
              >
                <el-option
                  v-for="item in posix.options"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
          
              <!-- SELECT TERRITORIALE CON POCHI RISULTATI -->
              <el-select  
                v-if="posix.type == 'territory' && available_territory_query_filter.length < 64"          
                v-model="selectedfilter[posix.name]"
                multiple
                filterable
                placeholder="Seleziona"
                style="width: 100%"            
              >
                <el-option
                  v-for="item in available_territory_query_filter"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>

              <!-- SELECT TERRITORIALE CON MOLTISSIMI RISULTATI -->
              <el-select  
                v-if="posix.type == 'territory' && available_territory_query_filter.length >= 64"          
                v-model="selectedfilter[posix.name]"
                multiple
                filterable
                placeholder="Digitare almeno 3 caratteri..."
                style="width: 100%"                      
                remote
                reserve-keyword
                :remote-method="queryFilterTerritory"   
              >
                <el-option
                  v-for="item in query_filter_options"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>

            </div>

          </el-col>
          
          <el-col :span="20">

            <el-text class="mx-1" size="default" v-if="filter_by_territory.length > 0">
              Vuoi settare 
              <el-tag type="info" v-for="i in filter_by_territory" :key="i">
                {{ valueToLabelTerritory(i) }} 
              </el-tag>
              anche su questo filtro "Territorio"?
              <el-button type="primary" size="small" @click="presetFiltro">SI</el-button>
            </el-text>

            <br><br><br>

            <!--div style="text-align: center;"-->
              <el-button type="success" @click="submit">Invia richiesta</el-button>
            <!--/div-->

          </el-col>

        </el-row>

      </el-drawer>

    </el-card>

</template>

<script setup>

  import {defineComponent, onMounted, ref, computed} from 'vue';

  import {create, read} from '../../utils/service.js'

  //import DatasetFilterView from './DatasetFilter.vue';

  //import Auth from '../../store/Store.js';
  import { useStore } from 'vuex';
  const store = useStore();

  //const loading    = ref(false);
  const search     = ref('');

  const dataflow   = computed(() => store.state.stub.dataflow);
  const categories = computed(() => store.state.stub.categories);

  const available_territory_filter = computed(() => store.state.stub.available_territory_filter);

  const filter_by_cat       = ref([]);
  const filter_by_territory = ref([]);
  
  const flow_name        = ref('');
  const flow_ref         = ref('');
  const id_datastructure = ref('');

  
  const openDrawer    = ref(false);
  const loadingDrawer = ref(false);
  
  const datafilter     = ref([]);
  const selectedfilter = ref([]);

  const available_territory_query_filter = ref([]);

  const query_filter_options = ref([]);

  const queryFilterTerritory = (query) => {
      if (query && query.length > 2) {
          query_filter_options.value = available_territory_query_filter.value.filter((item) => {
            return item.label.toLowerCase().includes(query.toLowerCase())
          })
      } else {
        query_filter_options.value = []
      }
    }

  const nPos                  = ref(0);

    const filterTableData = computed(() =>
      dataflow.value.filter(
        (data) => 
          (!search.value                   || data.name.toLowerCase().includes(search.value.toLowerCase()) )
          &&
          (filter_by_cat.value.length == 0 || filter_by_cat.value.includes(data.category))
          &&
          (filter_by_territory.value.length == 0 || filter_by_territory.value.some( item => data.available_territory && data.available_territory.json_value.includes(item)))
        )
    )

    const options = ref([]);

    const searchDataflowByTerritory = (query) => {
      if (query && query.length > 2) {
          options.value = available_territory_filter.value.filter((item) => {
            return item.label.toLowerCase().includes(query.toLowerCase())
          })
      } else {
        options.value = []
      }
    }

    const handleQuery = (async(i, r) => {
      // Conervo i dati
      flow_name.value  = r.name
      flow_ref.value   = r.flow_ref;
      id_datastructure.value = r.data_struct;      

      // inizializzo
      available_territory_query_filter.value = [];
      datafilter.value            = [];
      nPos.value                  = 0;

      selectedfilter.value        = [];

      openDrawer.value = true;
      loadingDrawer.value = true;

      let resp = await read('le_admin/datafilter', flow_ref.value, true);

      datafilter.value            = resp.filtersJson
      nPos.value                  = resp.nPos;

      // I dati territoriali li ho già transcodificati negli stub... perchè reinviare tutto? invio solo il codice e transcodifico qui
      let territory_opts = resp.filtersJson.filter((filter) => filter.type == 'territory');
          territory_opts = territory_opts[0] ? territory_opts[0].options : false;

      available_territory_query_filter.value = territory_opts ? store.state.stub.available_territory_filter.filter(obj => territory_opts.includes(obj.value)) : [];

      loadingDrawer.value = false;

    })

    const submit = (async() => {
      loadingDrawer.value = true;
      /*let resp =*/ await create('le_admin/dataquery', {'nPos': nPos.value, 'flow_ref': flow_ref.value, 'selectedfilter': getFormSelectedfilterObj()}, true);
      loadingDrawer.value = false;
    });

    function getFormSelectedfilterObj(){
      console.log(selectedfilter.value)
        return Object.keys(selectedfilter.value).reduce((acc, key) => {
            acc[key] = selectedfilter.value[key];
            return acc;
        }, {});
    }

    const valueToLabelTerritory = (
      (code_territory) => {
        let fil = store.state.stub.available_territory_filter.filter(obj => obj.value == code_territory );
        return fil[0] ? fil[0].label : '';
      }
    );

    const presetFiltro = (() => {

      query_filter_options.value = available_territory_query_filter.value.filter((item) => {
        return filter_by_territory.value.includes(item.value);
      });

      datafilter.value.map((d) => {
        if(d.type == 'territory'){
          selectedfilter.value[d.name] = filter_by_territory.value;
        }
      });

    });

    onMounted(async ()=>{

      /*      
      loading.value = true;
      
      dataflow.value   = store.state.stub.dataflow;
      categories.value = store.state.stub.categories;
      
      let dataset = await list('le_admin/dataset');
      dataflow.value   = dataset.dataflow;
      categories.value = dataset.categories;

      loading.value = false;
      */
      
    });

  defineComponent({
      name: 'DatasetView',
  })

</script>
