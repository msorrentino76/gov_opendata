<template>

  <h4>Anagrafica e dati di account</h4>

  <br>

  <FormEl :formModel="formModel" :rules="rules" :errors="[]" :formData="Auth.state.user" :action="[]" :formLoading="false"/>

  <br><br><br>

  <h4>Cambio Password</h4>

  <br>

  <FormEl :formModel="formModelPass" :rules="rulesPass" :errors="[]" :formData="[]" :action="[]" :formLoading="false"/>

</template>

<script setup>

  import Auth from '@/store/Auth';

  import {defineComponent} from 'vue';
  import FormEl from '../../components/Form.vue';

  const formModel = {

    submit: (data) => {onSubmit(data);},

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
                  icon: 'Avatar'
                },
                space: 8,                  
              },
              {
                type: 'input',
                label: 'Cognome',
                name: 'surname',
                prepend: {
                  icon: 'Avatar'
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

    ]

  };

  const formModelPass = {

    submit: (data) => {onSubmit(data);},

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
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
      ],     
      surname: [
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
      ], 
      username: [
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
      ], 
      email: [
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
        { type: 'email',  message: 'Inserire un indirizzo email valido', trigger: ['blur', 'change'] },
      ],                   
    }

    const rulesPass = {
      password: [
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
      ],     
      new_password: [
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
      ], 
      confirm_new_password: [
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
      ],                  
    }

    const onSubmit = (data) => {
      console.log('onSubmit PADRE:', data);
    }

    defineComponent({
      name: 'ProfiloView',
    })

</script>