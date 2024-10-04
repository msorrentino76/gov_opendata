<template>

  <h4>Anagrafica e dati di account</h4>

  <br>

  <FormEl :formModel="formModel" :rules="rules" :errors="errorsForm" :formData="store.state.login.user" :action="[]" :formLoading="formLoading"/>

  <br><br><br>

  <h4>Cambio Password</h4>

  <br>

  <FormEl :formModel="formModelPass" :rules="rulesPass" :errors="errorsFormPass" :formData="dataFormPass" :action="[]" :formLoading="formPassLoading"/>

</template>

<script setup>

  //import Auth from '@/store/Store';
  import { useStore } from 'vuex';

  import {defineComponent, ref} from 'vue';
  import FormEl from '../../components/Form.vue';

  import {update} from '../../utils/service.js';

  const store = useStore();

  const errorsForm     = ref([]);
  const errorsFormPass = ref([]);

  const dataFormPass = ref([]);

  const formLoading = ref(false);
  const formPassLoading = ref(false);

  const formModel = {

    submit: (data, formRef) => {onSubmitAnagrafica(data, formRef)},

    disabled: false,

    fields: [
      
      // row 1
      {
        row: [
              {
                type: 'input',
                label: 'Nome',
                name: 'name',
                prepend: {
                  icon: 'avatar'
                },
                space: 8,                  
              },
              {
                type: 'input',
                label: 'Cognome',
                name: 'surname',
                prepend: {
                  icon: 'avatar'
                },
                space: 8,
              }
        ]
      },

      // row 2
      {
        row: [
              {
                type: 'input',
                label: 'Username',
                name: 'username',
                prepend: {
                  icon: 'user-filled'
                },
                space: 8,                  
              },
              {
                type: 'input',
                label: 'Email',
                name: 'email',
                prepend: {
                  icon: 'message'
                },
                space: 8,
              }
        ]
      },

      // row 2
      {
        row: [
              {
                type: 'switch',
                activeText: 'Ricevi notifiche anche via email',
                name: 'notify_email',
                space: 8,                  
              },
        ]
      },

    ]

  };

  const formModelPass = {

    submit: (data, formRef) => {onSubmitChangePass(data, formRef)},

    disabled: false,

    fields: [
    
    // row 1
    {
      row: [
            {
              type: 'password',
              label: 'Password attuale',
              name: 'password',
              prepend: {
                icon: 'key'
              },
              space: 8,                  
            }
      ]
    },

    // row 2
    {
      row: [
            {
              type: 'password',
              label: 'Nuova password',
              name: 'new_password',
              showMeter: true,
              prepend: {
                icon: 'key'
              },
              space: 8,                  
            }
      ]
    },
    
    // row 3
    {
      row: [
            {
              type: 'password',
              label: 'Conferma nuova password',
              name: 'confirm_new_password',
              prepend: {
                icon: 'key'
              },
              space: 8,                  
            }
      ]
    },

  ]

  };


    const rules = {
      name: [
        { required: true, message: 'Campo richiesto', trigger: 'change' },
      ],     
      surname: [
        { required: true, message: 'Campo richiesto', trigger: 'change' },
      ], 
      username: [
        { required: true, message: 'Campo richiesto', trigger: 'change' },
      ], 
      email: [
        { required: true, message: 'Campo richiesto', trigger: 'change' },
        { type: 'email',  message: 'Inserire un indirizzo email valido', trigger: ['blur', 'change'] },
      ],                   
    }

    const rulesPass = {
      password: [
        { required: true, message: 'Campo richiesto', trigger: 'change' },
      ],     
      new_password: [
        { required: true, message: 'Campo richiesto', trigger: 'change' },
      ], 
      confirm_new_password: [
        { required: true, message: 'Campo richiesto', trigger: 'change' },
      ],                  
    }

    const onSubmitAnagrafica = (async(data, formRef) => {

      errorsForm.value = {};

      const val = await formRef.validate((valid) => valid);
      if(!val) return false;

      formLoading.value = true;
      
      data = {...data, id: store.state.login.user.id};

      let resp = await update('profile/account', data);
      if(resp){ 
        if(resp.errors){
          errorsForm.value = resp.errors;
        } else {
          store.commit('login/updateUser', resp);
        }
      }

      formLoading.value = false;

    })

    const onSubmitChangePass = (async(data, formRef) => {

      errorsFormPass.value = {};

      const val = await formRef.validate((valid) => valid);
      if(!val) return false;

      if(data.new_password != data.confirm_new_password) {
        errorsFormPass.value = {'confirm_new_password': 'La nuova password non coincide con il campo di conferma'}
        return false;
      }

      formPassLoading.value = true;

      data = {...data, id: store.state.login.user.id};

      let resp = await update('profile/password', data);
      if(resp){ 
        if(resp.errors){
          errorsFormPass.value = resp.errors;
        } else {
          store.commit('login/updateUser', resp);
        }
      }

      formPassLoading.value = false;

    })  
    
    defineComponent({
      name: 'ProfiloView',
    })

</script>