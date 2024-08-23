import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';

export default createStore({
    state: {
      logged  : false,
      token   : null,
      user    : null,
      licence : null,
      config: {
        applicationBaseURL: 'http://localhost/gestione_utenti/backend/public',
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