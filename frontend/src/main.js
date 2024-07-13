import { createApp } from 'vue'
import App from './App.vue'

import ElementPlus from 'element-plus';
import it from 'element-plus/dist/locale/it.mjs'

import 'dayjs/locale/it';

import 'element-plus/dist/index.css';

import './assets/css/app.css'

import { createRouter, createWebHistory } from 'vue-router';

import SystemDashboardView  from './views/sysadmin/Dashboard.vue';
import LegalEntityAdminView from './views/sysadmin/LegalEntityAdmin.vue';
import LegalEntityView      from './views/sysadmin/LegalEntity.vue';
import LicenceView          from './views/sysadmin/Licence.vue';
import SystemLogView        from './views/sysadmin/SystemLog.vue';

import auth from './store/Auth.js'; 

const routes = [
    { path: '/sysadmin'                    , component: SystemDashboardView },
    { path: '/sysadmin/legal_entity_admin' , component: LegalEntityAdminView },
    { path: '/sysadmin/legal_entity'       , component: LegalEntityView },
    { path: '/sysadmin/licence'            , component: LicenceView },
    { path: '/sysadmin/logs'               , component: SystemLogView },
  ];
  
  const router = createRouter({
    history: createWebHistory(),
    routes,
  });

  const app = createApp(App);

  app.use(ElementPlus, {
    locale: it,
  })
  app.use(router);
  app.use(auth);

  app.mount('#app');
