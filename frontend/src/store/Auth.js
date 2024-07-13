import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';

export default createStore({
    state: {
      logged: false,
      token : null,
      user  : null,
      data_filter: false,
      data_filter_quote: false,
      config: {
        applicationBaseURL: 'http://localhost/gestione_utenti/backend/public',
      }
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
        state.data_filter = false;
        state.data_filter_quote = false;
      },
      updateUser(state, user) {
        state.user   = user;
      },
      setDataFilter(state, data_filter) {
        state.data_filter = data_filter;
      },
      setDataFilterQuote(state, data_filter_quote) {
        state.data_filter_quote = data_filter_quote;
      },
    },    
    plugins: [createPersistedState()]
});