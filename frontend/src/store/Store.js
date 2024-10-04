import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';

const store = createStore({
  
  modules : {

    // Store Persistente
    config: {
      state: {
        applicationBaseURL: 'http://localhost/gov_opendata/backend/public',
      }
    },

    // Store Persistente
    login : {
      namespaced: true, // con questa opzione devo usare store.commit('login/login', response.data); altrimenti store.commit('login', response.data);
      state: {
        logged  : false,
        token   : null,
        user    : null,
        licence : null,
      },
      mutations: {
        login(state, data) {
          data.user.abilities = JSON.parse(data.user.abilities);
          state.logged  = true;
          state.token   = data.token;
          state.user    = data.user;
          state.licence = data.licence_for;
        },
        logout(state) {
          state.logged  = false;
          state.token   = null;
          state.user    = null;
          state.licence = null;
        },
        updateUser(state, user) {
          state.user   = user;
        },
        updateLicence(state, licence) {
          state.licence = licence;
        },
      },    
    },

  // Non persistente
    stub: {
      namespaced: true, // con questa opzione devo usare store.commit('stub/setStub', response.data); altrimenti store.commit('setStub', response.data);
      state: {
        dataflow  : [],
        categories: [],
      },
      mutations: {
        setStub(state, stub) {
          state.dataflow   = stub.dataflow;
          state.categories = stub.categories;
        }
      }
    }

  }, // End modules

  plugins: [
    createPersistedState({
      paths: ['login', 'config'], // specifica i moduli da persistire
    }),
  ],
  
});

export default store;

/*
import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';

export default createStore({
    state: {
      logged  : false,
      token   : null,
      user    : null,
      licence : null,
      config: {
        applicationBaseURL: 'http://localhost/gov_opendata/backend/public',
      }
    },
    mutations: {
      login(state, data) {
        data.user.abilities = JSON.parse(data.user.abilities);
        state.logged  = true;
        state.token   = data.token;
        state.user    = data.user;
        state.licence = data.licence_for;
      },
      logout(state) {
        state.logged  = false;
        state.token   = null;
        state.user    = null;
        state.licence = null;
      },
      updateUser(state, user) {
        state.user   = user;
      },
      updateLicence(state, licence) {
        state.licence = licence;
      },
    },    
    plugins: [createPersistedState()]
});
*/