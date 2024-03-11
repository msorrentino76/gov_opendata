<template>

    <el-form
    ref="ruleFormRef"
    v-loading="formLoading"
    :model="data"
    :disabled="formModel.disabled"
    :rules="formRules"
    label-position="top">
    
        <el-row :gutter="20" v-for="(rows, rowidx) in formModel.fields" :key="rowidx">

            <el-col v-for="(field, colidx) in rows.row" :key="colidx" :span="field.space">
                
                <template v-if="!field.showOn || (field.showOn.indexOf(action) !== -1) ">

                    <!-- INPUT -->
                    <el-form-item
                        v-if="field.type=='input'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-input v-model="data[field.name]" :placeholder="field.placeholder ? field.placeholder : ''">
                            <template v-if="field.prepend" #prepend>
                                {{ field.prepend.text ? field.prepend.text : ''}}
                                <el-icon v-if="field.prepend && field.prepend.icon"><IconEl :icon="field.prepend.icon" /></el-icon>
                            </template>
                            <template v-if="field.append" #append>
                                {{ field.append.text ? field.append.text : ''}}
                                <el-icon v-if="field.append && field.append.icon"></el-icon>
                            </template>
                        </el-input>
                    </el-form-item>

                    <!-- PASSWORD -->
                    <el-form-item
                        v-if="field.type=='password'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-input v-model="data[field.name]" type="password" show-password  :placeholder="field.placeholder ? field.placeholder : ''">
                            <template v-if="field.prepend" #prepend>
                                {{ field.prepend.text ? field.prepend.text : ''}}
                                <el-icon v-if="field.prepend && field.prepend.icon"><IconEl :icon="field.prepend.icon" /></el-icon>
                            </template>
                            <template v-if="field.append" #append>
                                {{ field.append.text ? field.append.text : ''}}
                                <el-icon v-if="field.append && field.append.icon"></el-icon>
                            </template>
                        </el-input>
                    </el-form-item>

                </template>

            </el-col>

        </el-row>

        <el-button v-if="!formModel.disabled" type="success" @click="submit(ruleFormRef)">Invia</el-button>

    </el-form>

</template>


<script setup>

import {ref, defineProps, defineComponent, onUpdated} from 'vue';

import IconEl from './Icon.vue'; 

    const ruleFormRef = ref({});

    function getFormDataObj(){
        return Object.keys(data.value).reduce((acc, key) => {
            acc[key] = data.value[key];
            return acc;
        }, {});
    }

    function submit(formRef) {
        var objData = getFormDataObj();
        props.formModel.submit(objData, formRef);
    }

    const props = defineProps({
        formModel  : {},
        formData   : Array,
        action     : Array, // 'create', 'read', 'update', 'delete';
        formLoading: Boolean,
        rules      : Object,
        errors     : Array,
    });

    // data è uno stato array in modo da utilizzare la dinamicità della sua chiave.
    // Ricordo infatti che i campi 'name' sono dinamici perchè configurati dal padre
    const data        = ref(props.formData);
    const formLoading = ref(props.formLoading);
    const formErrors  = ref(props.errors);    
    const formRules   = ref(props.rules);

    defineComponent({
        name: 'FormEl',
    })

    onUpdated( () => {
        data.value        = props.formData
        formLoading.value = props.formLoading
        formErrors.value  = props.errors
        formRules.value   = props.rules;
    })

</script>