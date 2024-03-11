import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';

export default createStore({
    state: {
      logged: false,
      token : null,
      user  : null,
    },
    mutations: {
      login(state, data) {
        data.user.abilities = JSON.parse(data.user.abilities);
        state.logged = true;
        state.token  = data.token;
        state.user   = data.user;
      },
      logout(state) {
        state.logged = false;
        state.token  = null;
        state.user   = null;
      }
    },    
    plugins: [createPersistedState()]
});