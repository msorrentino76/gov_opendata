<template>

    <el-form
    ref="ruleFormRef"
    v-loading="formLoading"
    :model="data"
    :disabled="formModel.disabled"
    :rules="formRules"
    label-position="top"
    :scroll-to-error="true"
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
                        :maxlength="field.maxlength ? field.maxlength : 128"
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

                    <!-- AUTOCOMPLETE -->
                    <el-form-item
                        v-if="field.type=='autocomplete'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-autocomplete
                            v-model="data[field.name]"
                            :fetch-suggestions="function(queryString, cb) {
                                const results = queryString ? field.autocompleteList.filter(item => item.value.toLowerCase().includes(queryString.toLowerCase())) : field.autocompleteList;
                                cb(results);
                            }"
                            :trigger-on-focus="true"
                            clearable
                            class="inline-input w-100"
                            :placeholder="field.placeholder ? field.placeholder : ''"
                        />
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
                        <el-input-number v-model="data[field.name]" :min="field.min" :max="field.max" :label="field.label" :controls-position="field.controlsPosition ? field.controlsPosition : ''"/>
                    </el-form-item>

                    <!-- INPUT DECIMAL -->
                    <el-form-item
                        v-if="field.type=='input-decimal'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-input type="number" v-model="data[field.name]" :precision="2" :min="0" :step="0.01" :label="field.label" />
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
                            :placeholder="field.placeholder ? field.placeholder : ''"
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
                            :maxlength="field.maxlength ? field.maxlength : 612"
                            :placeholder="field.placeholder ? field.placeholder : ''"
                            show-word-limit
                            type="textarea"
                        />
                    </el-form-item>

                    <!-- SELECT MULTIPLE-->
                    <el-form-item
                        v-if="field.type=='multiselect'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-select
                            v-model="data[field.name]"
                            multiple
                            :placeholder="field.placeholder ? field.placeholder : ''"
                        >
                            <el-option
                                v-for="item in field.options"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                            />
                        </el-select>
                    </el-form-item>

                    <!-- SELECT SINGLE-->
                    <el-form-item
                        v-if="field.type=='singleselect'"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >
                        <el-select
                            v-model="data[field.name]"
                            :placeholder="field.placeholder ? field.placeholder : ''"
                        >
                            <el-option
                                v-for="item in field.options"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                            />
                        </el-select>
                    </el-form-item>

                    <!-- UPLOAD -->
                    <!--el-divider v-if="field.type=='upload'"/-->
                    <el-form-item
                        v-if="field.type=='upload' && !formModel.disabled"
                        :label="field.label"
                        :prop="field.name"
                        :error="formErrors[field.name] ? formErrors[field.name] : ''"
                    >                                            
                            <el-upload                      
                                ref="upload"
                                :action="Auth.state.config.applicationBaseURL + '/api' + field.uploadEndpoint"
                                :headers="uploadHeader"
                                :accept="field.accept.mime"
                                multiple
                                drag
                                :limit="field.limit"
                                v-model:file-list="data[field.name]"
                                :before-upload="(rawFile)=>{                                    
                                    if(field.maxmbsize && (rawFile.size / 1024 / 1024) > field.maxmbsize) {
                                        formErrors[field.name] = 'Il file ' + rawFile.name + ' supera la dimensione massima consentita di ' + field.maxmbsize + ' Mb';
                                        return false;
                                    }
                                    return true;
                                }"
                                :before-remove="(rawFile)=>{removeFile(field.removeEndpoint, rawFile && rawFile.response ? rawFile.response.id : false)}"
                                >
                                <el-icon class="el-icon--upload"><IconEl icon="upload-filled" /></el-icon>
                                <div class="el-upload__text">
                                Trascina qui i files (massimo {{field.limit}}) oppure <em>Clicca qui per caricarli</em>
                                </div>
                                <template #tip>
                                <div class="el-upload__tip">
                                    Accettati file {{ field.accept.label }} di massimo {{ field.maxmbsize }} Megabyte
                                </div>
                                </template>
                            </el-upload>                            
                    </el-form-item>
                    <!--el-divider v-if="field.type=='upload'"/-->

                    <!-- ALERT -->

                    <el-alert v-if="field.type=='alert-success'" :title="field.text" show-icon type="success" />
                    <el-alert v-if="field.type=='alert-info'"    :title="field.text" show-icon type="info" />
                    <el-alert v-if="field.type=='alert-warning'" :title="field.text" show-icon type="warning" />
                    <el-alert v-if="field.type=='alert-error'"   :title="field.text" show-icon type="error" />

                    <br>

                </template>

            </el-col>

        </el-row>

        <br>

        <el-button v-if="!formModel.disabled" type="success" @click="submit(ruleFormRef)">Invia</el-button>

    </el-form>

    <template v-if="action != 'create' && props.attachments && props.attachments.field">
        <div v-for="(f, i) in props.formData[props.attachments.field]" :key="i">
            <el-button type="primary" :loading="isDownloading" text @click="downloadFile(f)"><el-icon v-if="!isDownloading"><IconEl icon="download" /></el-icon> {{f.name}}</el-button>
        </div>
    </template>
    
</template>

<script setup>

import Auth from '@/store/Auth';

import {ref, defineProps, defineComponent, onUpdated, onMounted} from 'vue';
import { del, download  } from '../utils/service.js';

import PasswordMeter from 'vue-simple-password-meter';

import IconEl from './Icon.vue'; 

    const uploadHeader ={
        'Authorization': `Bearer ${ Auth.state.token }` // Assicurati di modificare questo in base al tuo metodo di autenticazione
    };

    const removeFile = (async(ep, uid) => {
        if(uid !== false){
            await del(ep, uid, true);
        }
        return true;
    })

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
        formData   : Object,
        action     : String, // 'create', 'read', 'update', 'delete';
        formLoading: Boolean,
        rules      : Object,
        errors     : {},
        attachments: {},
    });

    const ruleFormRef = ref({});

    // data è uno stato array in modo da utilizzare la dinamicità della sua chiave.
    // Ricordo infatti che i campi 'name' sono dinamici perchè configurati dal padre
    const data        = ref(props.formData);
    const formLoading = ref(props.formLoading);
    const formErrors  = ref(props.errors);    
    const formRules   = ref(props.rules);
    const attachments = ref({});

    const isDownloading = ref(false);

    const downloadFile = (async (f) => {
        isDownloading.value = true;
        await download(f);
        isDownloading.value = false;
    });
    
    defineComponent({
        name: 'FormEl',
    })

    onMounted( () => {
        //attachments.value = props.attachments && props.attachments.field && props.formData[props.attachments.field] ? props.formData[props.attachments.field] : {};
    })
    
    onUpdated( () => {
        data.value        = props.formData
        formLoading.value = props.formLoading
        formErrors.value  = props.errors
        formRules.value   = props.rules;
        attachments.value = props.attachments;
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