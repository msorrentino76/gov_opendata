<template>

    <el-form
    ref="ruleFormRef"
    v-loading="formLoading"
    :model="data"
    :disabled="formModel.disabled"
    :rules="formRules"
    label-position="top"
    status-icon
    >
    
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

                    <el-text        v-if="field.showMeter" class="mx-1" size="small">Complessita password: {{ score_pass_complex }}</el-text>
                    <password-meter v-if="field.showMeter" @score="onScore" :password="data[field.name]" />

                    <!-- CHECKBOX -->
                    <el-form-item
                        v-if="field.type=='switch'"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-switch v-model="data[field.name]" :active-text="field.activeText ? field.activeText : ''" :inactive-text="field.inactiveText ? field.inactiveText : ''" />
                    </el-form-item>

                    <!-- INPUT NUMERIC -->
                    <el-form-item
                        v-if="field.type=='input-numeric'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-input-number v-model="data[field.name]" :min="field.min" :max="field.max" :label="field.label"/>
                    </el-form-item>

                    <!-- DATA PICKER -->
                    <el-form-item
                        v-if="field.type=='datapicker'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-date-picker
                            v-model="data[field.name]"
                            type="date"
                            placeholder="Data attività"
                            format="DD/MM/YYYY"
                            value-format="YYYY-MM-DD"
                        />
                    </el-form-item>

                    <!-- TEXT -->
                    <el-form-item
                        v-if="field.type=='text'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-input
                            v-model="data[field.name]"
                            maxlength="512"
                            placeholder="Descrizione dell'attività svolta"
                            show-word-limit
                            type="textarea"
                        />
                    </el-form-item>

                    <!-- ALERT -->

                    <el-alert v-if="field.type=='alert-success'" :title="field.text" show-icon type="success" />
                    <el-alert v-if="field.type=='alert-info'"    :title="field.text" show-icon type="info" />
                    <el-alert v-if="field.type=='alert-warning'" :title="field.text" show-icon type="warning" />
                    <el-alert v-if="field.type=='alert-success'" :title="field.text" show-icon type="error" />


                </template>

            </el-col>

        </el-row>

        <br>
        
        <el-button v-if="!formModel.disabled" type="success" @click="submit(ruleFormRef)">Salva</el-button>

    </el-form>

</template>


<script setup>

import {ref, defineProps, defineComponent, onUpdated} from 'vue';

import PasswordMeter from 'vue-simple-password-meter';

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
        errors     : {},
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

    const score_pass_complex = ref('');

    const onScore = (payload) => {
      //console.log(payload.score); // from 0 to 4
      //console.log(payload.strength); // one of : 'risky', 'guessable', 'weak', 'safe' , 'secure'
      switch (payload.score) {
        case 0: score_pass_complex.value = 'rischiosa'; break;
        case 1: score_pass_complex.value = 'prevedibile'; break;
        case 2: score_pass_complex.value = 'debole'; break;
        case 3: score_pass_complex.value = 'sicura'; break;
        case 4: score_pass_complex.value = 'molto sicura'; break;
        default: score_pass_complex.value = ''; break;
      }
      
    };
    
</script>