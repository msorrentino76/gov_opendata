  <template>

    <router-view></router-view>

  </template>
  
  <script setup>

  import {list} from '../utils/service.js'

  import {defineComponent, onMounted} from 'vue';

  import { useStore } from 'vuex';
  const store = useStore();

  onMounted(async() => {
        // lo stub NON per admin
        if(store.state.login.logged && store.state.login.user.abilities.includes('legal_entity:admin')) {
          let dataset = await list('le_admin/dataset');
          store.commit('stub/setStub', {
            'dataflow'  : dataset.dataflow,
            'categories': dataset.categories,
            'available_territory_filter': dataset.available_territory_filter
          });    
        }    
  })

  defineComponent({
      name: 'MainComponent',
  })

</script>
