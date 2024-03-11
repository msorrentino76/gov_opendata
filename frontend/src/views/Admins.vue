

<template>

    <h3>ADMINS</h3>

    <el-card class="box-card">

      <TableEl 
        details = "client-side"
        entity="Amministratore"
        :endpoints="endpoints"
        :header="header"
        :actions="actions"
        :tableRowClassName="tableRowClassName"
        :tableCellClassName="tableCellClassName"
        :tableCellStyle="tableCellStyle"
        :form="formModel"
        :rules="rules"/>

    </el-card>
    
</template>

<script setup>

  import {defineComponent} from 'vue';

  import TableEl from '../components/Table.vue';  

  import auth from '../store/Auth.js'; 

  const endpoints = {
    list  : 'superadmin/admins',
    create: 'superadmin/admin',
    read  : 'superadmin/admin',
    update: 'superadmin/admin',
    delete: 'superadmin/admin',
  }

  const tableRowClassName =( ( { row, rowIndex } ) => {

    if (rowIndex === 1 && row) {
      return 'warning-row'
    } else if (rowIndex === 3) {
      return 'success-row'
    }
      return ''
    }

  );

  const tableCellClassName = ( ( /*{ row, column, rowIndex, columnIndex }*/ ) => {
    //console.log('tableCellClassName', row, column, rowIndex, columnIndex);
    return '';
  });

  const tableCellStyle =( ( { row, column, rowIndex, columnIndex } ) => {

    if(columnIndex == 5 && row && column && rowIndex) {
      var def = {'text-align': 'right'};
      return row.picciuli > 1000 ? {...def, color : 'green'} : {...def, color : 'red'};
    }

  });

  const header = [
      {
        field: 'name',
        label: 'Nome',
        sortable: true,
      },
      {
        field: 'surname',
        label: 'Cognome',
      },
      {
        field: 'fiscal_code',
        label: 'Codice fiscale',
      },
      {
        field: 'denominazione',
        label: 'Denominazione',
        sortable: true,
      },
      {
        field: 'created_at',
        label: 'Data creazione',
        format: 'data',
        sortable: true,
      },
      {
        field: 'picciuli',
        label: 'Costo',
        format: 'numeric',
      },
    ];

    const actions = {
      
      ...(auth.state.user.abilities.includes('contact:add') ? { create:{
        //action  : () => {actionCreate()},
        //disabled: () => { return false;} 
      }} : {}),

      read:{
        //action  : (id, row) => {actionRead(row.id)},
        //disabled: () => { return false;} 
      },

      update:{
        //action: (id, row) => {actionUpdate(row.id)},
        //disabled: () => {return false},
      },

      delete:{
        action  : (id, row) => {actionDelete(row.id)},
        //disabled: () => {return false},
      }

    };

    /*
    function actionCreate(){
      console.log('actionCreate');
    }

    function actionRead(id){
      console.log('actionRead ', id);
    }

    function actionUpdate(id){
      console.log('actionUpdate ', id);
    }
    */
    function actionDelete(id){
      console.log('actionDelete ', id);
    }


    const formModel = {

      //submit: (data) => {onSubmit(data);},

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
        
        // row 2
        {
          row: [
            {
                  type: 'input',
                  label: 'Picciuli',
                  name: 'picciuli',
                  space: 12,
                  showOn: ['create', 'update'],
                  append: {
                    icon: 'el-icon-edit'
                  },
                }
          ]
        },

        // row 3
        {
          
        }

      ]

    };

    const rules = {
      name: [
        { required: true, message: 'Campo richiesto', trigger: 'blur' },
      ],      
    }

    /*
    const onSubmit = (data) => {
      console.log('onSubmit PADRE:', data, data.surname, data['surname']);
    }
    */

    defineComponent({
        name: 'AdminsView',
    })

</script>
