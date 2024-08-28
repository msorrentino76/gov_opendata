<template>

        <el-form 
          ref="formAssistance"
          v-loading="form_loading"
          :model="objModel"
          :rules="{
              oggetto: [
                { required: true, message: 'Campo richiesto', trigger: 'blur' },
              ],
              testo: [
                { required: true, message: 'Campo richiesto', trigger: 'blur' },
              ],     
          }"
          :disabled="form_disable"
          label-position="top"
          status-icon
        >

          <el-row :gutter="20">
            <el-col :span="24">
              <el-form-item label="Oggetto" :error="form_error.oggetto" prop="oggetto">
                <el-input v-model="objModel.oggetto" placeholder="Oggetto"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="24">
              <el-form-item label="Richiedi assistenza per:" :error="form_error.testo" prop="testo">
                <el-input 
                  v-model="objModel.testo"
                  placeholder="Testo"
                  maxlength="640"
                  :rows="10"
                  show-word-limit
                  type="textarea"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-button type="success" @click="submit(formAssistance)">Invia richiesta</el-button>

        </el-form>

</template>

<script setup>

  import {defineComponent, ref, reactive, onMounted} from 'vue';

  import {create} from '../../utils/service.js';

  const objModel       = reactive({});
  const formAssistance = ref({});
  const form_loading   = ref(false);
  const form_error     = ref({});
  const form_disable   = ref(false);

  const submit = (async(formRef) => {

    if (!formRef) return;
    const val = await formRef.validate((valid) => valid);
    if(!val) return false;

    form_loading.value = true;
    form_error.value   = {};

      let resp = await create('assistance', objModel);
      if(resp){
        if(resp.errors){
          form_error.value = resp.errors;
        } else {
          form_disable.value = true;
        }    
      }

    form_loading.value = false;

})

  onMounted(()=>{form_disable.value = false});

  defineComponent({
    name: 'AssistenzaView',
  })

</script>