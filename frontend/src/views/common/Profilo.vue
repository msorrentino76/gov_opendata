<template>

  <FormEl :formModel="formModel" :rules="rules" :errors="[]" :formData="[]" :action="[]" :formLoading="false"
  />

</template>

<script setup>

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
                  text: 'http://'
                },
                space: 8,                  
              },
              {
                type: 'input',
                label: 'Cognome',
                name: 'surname',
                append: {
                  text: 'APP'
                },
                space: 8,
              },
              {
                type: 'input',
                label: 'Codice fiscale',
                name: 'fiscal_code',
                space: 8,
                showOn: ['read'], // array di 'create', 'read', 'update', 'delete';
              }
        ]
      },

    ]

  };

    const rules = {
      name: [
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
      ],      
    }

    const onSubmit = (data) => {
      console.log('onSubmit PADRE:', data, data.surname, data['surname']);
    }

    defineComponent({
      name: 'ProfiloView',
    })

</script>