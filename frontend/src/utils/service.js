
import {ElMessage, ElNotification } from 'element-plus';

import axios from 'axios';

import auth  from '../store/Auth.js'; 

const applicationBaseURL = auth.state.config.applicationBaseURL;
                            
const axiosInstance = axios.create({
  baseURL: applicationBaseURL + '/api/',
  withCredentials: true,
  withXSRFToken  : true,
})


axiosInstance.interceptors.request.use(req => {

  req.headers.Accept          = 'application/json';
  req.headers['Content-Type'] = 'application/json';
  req.headers.Authorization   = 'Bearer ' + auth.state.token;

  return req;
});


export const elNotifySuccess = () => {
    ElNotification({
      title: 'Operazione riuscita',
      message: 'Operazione conclusa con successo',
      type: 'success',
    })
  }
  
export const elNotifyWarning = (text) => {
    ElNotification({
      title: 'Attenzione',
      message: text,
      type: 'warning',
    })
  }
  
export const elNotifyInfo = (text) => {
    ElNotification({
      title: 'Info',
      message: text,
      type: 'info',
    })
  }
  
export const elNotifyError = (text = 'Si è verificato un errore. Contattare l\'Assistenza') => {
    ElNotification({
      title: 'Errore',
      message: text,
      type: 'error',
    })
  }

  export const token = async (endpoint, data) => {
    try {
        // COME PRIMA COSA CHIEDO L'X-CSRF TOKEN sanctum/csrf-cookie
        /*
        await axiosInstance.get(applicationBaseURL + '/sanctum/csrf-cookie').then(response => {
          console.log('csrf-cookie', response);
        });
        */
        await axiosInstance.get(applicationBaseURL + '/sanctum/csrf-cookie');

        const response = await axiosInstance.post(endpoint, data);
        auth.commit('login', response.data);
        return await response.data;
    } catch (error) {
        return errorHandler(error, 'token');
    }
  }

  export const revoke = async (endpoint) => {     
    try {
        await axiosInstance.delete(endpoint);
        auth.commit('logout');
        return await true;
    } catch (error) {
        return errorHandler(error, 'revoke');
    }
}

 export const list = async (endpoint) => {  
    //console.log('list', endpoint)   
      try {
        const response = await axiosInstance.get(endpoint);
        //console.log('resp', endpoint)  
        return await response.data;
      } catch (error) {
        return errorHandler(error, 'list');
      }
  }

  export const filteredList = async (endpoint, filter) => {  
      try {
        const response = await axiosInstance.get(endpoint, { params: filter });
        //console.log('filteredList', endpoint)  
        return await response.data;
      } catch (error) {
        return errorHandler(error, 'filteredList');
      }
  }

  export const create = async (endpoint, data) => {
    try {
        const response = await axiosInstance.post(endpoint, data);
        elNotifySuccess();
        return await response.data;
    } catch (error) {
        return errorHandler(error, 'create');
    }
}

  export const read = async (endpoint, id) => {     
    try {
        const response = await axiosInstance.get(endpoint + '/' + id);
        return await response.data;
    } catch (error) {
        return errorHandler(error, 'read');
    }
}

  export const update = async (endpoint, data, mute = false) => {
    try {
        const response = await axiosInstance.put(endpoint + '/' + data.id  /*+ '?XDEBUG_SESSION_START=netbeans-xdebug'*/, data);
        if(!mute) elNotifySuccess();
        return await response.data;
    } catch (error) {
        return errorHandler(error, 'update');
    }
}

  export const del = async (endpoint, id, mute = false) => {     
    try {
        await axiosInstance.delete(endpoint + '/' + id);
        if(!mute) elNotifySuccess();
        return await true;
    } catch (error) {
        return errorHandler(error, 'del');
    }
}

const errorHandler = ((error, api_call) => {

  // Handler del non autenticato
  if(error.response && error.response.status == 401){
      if(api_call != 'token') {
        auth.commit('logout');
        ElMessage({
          showClose: true,
          message: 'Sessione scaduta o non valida. Procedere nuovamente con il login',
          type: 'warning',
          duration: 8000
        });
        return;
      } else {
        ElMessage({
          showClose: true,
          message: 'Credenziali non valide',
          type: 'error',
          duration: 8000
        });
        return;
      }
  }

  // Handler degli errori FORM
  if(error.response && error.response.status == 422){
    //console.log('error.response.data', error.response.data)
    return error.response.data;
  }

  elNotifyError();

  console.error('Errore durante la chiamata API ' + api_call + ' :', error.code, error.message);

  return api_call== 'list' ? [] : false;

});


