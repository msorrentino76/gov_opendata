<template>

    <div class="center-container">

            <el-card class="centered-card">

                <template #header>
                    <div class="card-header"><span>Accedi</span></div>
                </template>

                <FormEl 
                    :formModel="formModel"
                    :formData="formData"
                    :action="action"
                    :formLoading="formLoading"
                    :rules="rules"
                    :errors="errorsForm"
                />

            </el-card>    

    </div>

    <el-row>
    <el-col :span="8"><div class="grid-content ep-bg-purple" /></el-col>
    <el-col :span="8"><div class="grid-content ep-bg-purple-light" /></el-col>
    <el-col :span="8"><div class="grid-content ep-bg-purple" /></el-col>
  </el-row>

</template>

<script setup>

    import {ref, defineComponent} from 'vue';
    import FormEl from '../../components/Form.vue';
    import {token} from '../../utils/service.js';

    import { useRouter } from 'vue-router';

    import Auth from '../../store/Auth.js';

    const router = useRouter();

    const formData = ref([]);
    const action   = ref('');
    const formLoading = ref(false);
    const errorsForm = ref({});

    const rules = {
        /*
        email: [
            { required: true, message: 'Campo richiesto', trigger: 'change' },
            { type: 'email', message: 'Inserire una email valida', trigger: 'change' },
        ], 
        */
        username: [
            { required: true, message: 'Campo richiesto', trigger: 'change' },
        ],       
        password: [
            { required: true, message: 'Campo richiesto', trigger: 'change' },
        ],               
    }

    const formModel = {

        submit: (data, formRef) => {onSubmit(data, formRef)},

        disabled: false,

        fields: [
        
        // row 1
        {
            row: [
                {
                    type: 'input',
                    label: 'Username',
                    name: 'username',
                    placeholder: "Username",
                    prepend: {
                        icon: 'user-filled'
                    },
                    space: 24,                  
                },
            ]
        },
        
        // row 2
        {
            row: [
            {
                    type: 'password',
                    label: 'Password',
                    name: 'password',
                    placeholder: "Password",
                    space: 24,
                    prepend: {
                        icon: 'key'
                    },
                }
            ]
        },

        ]

    };

    const onSubmit = (async(data, formRef) => {

            errorsForm.value = {};

            const val = await formRef.validate((valid) => valid);
            if(!val) return false;

            formLoading.value = true; 
            let resp = await token('token', data);
            if(resp){
                if(resp.errors){
                    errorsForm.value = resp.errors;
                } else {
                    if(Auth.state.user.abilities.includes('system:admin')){
                        router.push('/sysadmin');
                    }
                    if(Auth.state.user.abilities.includes('legal_entity:admin')){
                        router.push('/le_admin');
                    }
                }
            }
            formLoading.value = false;
        }
    );

    defineComponent({
        name: 'LoginView',
    })

</script>


<style>

    .center-container {
        margin: auto;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh; /* Imposta l'altezza desiderata */
        width: 24%;
    }
    
    .centered-card {
        width: 100%;
    }

</style>