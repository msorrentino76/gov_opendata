import { createApp } from 'vue'
import App from './App.vue'

import ElementPlus from 'element-plus';
import it from 'element-plus/dist/locale/it.mjs'

import 'dayjs/locale/it';

import 'element-plus/dist/index.css';

import { createRouter, createWebHistory } from 'vue-router';

import DashboardView from './views/Dashboard.vue';
import ActivityView  from './views/Activity.vue';
import SellView      from './views/Sell.vue';
import QuoteView     from './views/Quote.vue';

//import AdminsView from './views/Admins.vue';

import auth from './store/Auth.js'; 

const routes = [
    { path: '/'        , component: DashboardView },
    { path: '/activity', component: ActivityView },
    { path: '/sells'   , component: SellView },
    { path: '/quote'   , component: QuoteView },
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
