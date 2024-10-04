import { createApp } from 'vue'
import App from './App.vue'

import ElementPlus from 'element-plus';
import it from 'element-plus/dist/locale/it.mjs'

import 'dayjs/locale/it';

import 'element-plus/dist/index.css';

import './assets/css/app.css'

import { createRouter, createWebHistory } from 'vue-router';

import DispatcherView       from './views/common/Dispatcher.vue';

import SystemDashboardView  from './views/sysadmin/Dashboard.vue';
import UsersView            from './views/common/Users.vue';
import LegalEntityView      from './views/sysadmin/LegalEntity.vue';
import LicenceView          from './views/sysadmin/Licence.vue';
import AccessiView          from './views/sysadmin/Accessi.vue';
import SystemLogView        from './views/sysadmin/SystemLog.vue';

import LeAdminDashboardView  from './views/legaladmin/Dashboard.vue';
import OUView                from './views/legaladmin/OU.vue';
import OUUserView            from './views/legaladmin/OUUser.vue';
import DatasetView           from './views/legaladmin/Dataset.vue';

import store from './store/Store.js'; 

const routes = [

    { path: '/'                            , meta: { transition: 'slide-left' }, component: DispatcherView },

    { path: '/sysadmin'                    , meta: { transition: 'slide-left' }, component: SystemDashboardView },
    { path: '/users'                       , meta: { transition: 'slide-left' }, component: UsersView },
    { path: '/sysadmin/legal_entity'       , meta: { transition: 'slide-left' }, component: LegalEntityView },
    { path: '/sysadmin/licence'            , meta: { transition: 'slide-left' }, component: LicenceView },
    { path: '/sysadmin/accessi'            , meta: { transition: 'slide-left' }, component: AccessiView },
    { path: '/sysadmin/logs'               , meta: { transition: 'slide-left' }, component: SystemLogView },

    { path: '/le_admin'                    , meta: { transition: 'slide-left' }, component: LeAdminDashboardView },
    { path: '/le_admin/ou'                 , meta: { transition: 'slide-left' }, component: OUView },
    { path: '/le_admin/ou_users'           , meta: { transition: 'slide-left' }, component: OUUserView },
    { path: '/le_admin/dataset'            , meta: { transition: 'slide-left' }, component: DatasetView },

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
  app.use(store);

  app.mount('#app');
