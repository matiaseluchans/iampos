

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Nuevo Parte">                
        <VDivider />
        <VCardText>
          <!-- 👉 Form -->
          <VForm ref="form" v-model="valid"
							lazy-validation>
            <VRow>
              <VCol
                cols="12"
                md="12"
              >
                <VAutocomplete
                  v-model="editedItem.clasificacion_id"
                  :items="clasificaciones"
                  item-value="id"
                  item-title="detalle"
                  label="Clasificacion del parte"                                
                  :rules="[$rulesRequerido]"
                  return-object
                />
              </VCol>
            </VRow>
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
               <VTextField
												v-model="editedItem.fecha_parte"
												label="Fecha y Hora del Parte"
												:rules="[$rulesRequerido, $rulesFechaMenorAHoy]"
												v-mask="mask"
												placeholder="dd/mm/yyyy hh:mm"
												maxLength="16"
                        @focus="setDate(editedItem.fecha_parte, 'parte')"
											/>
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
               <VTextField
												v-model="editedItem.fecha_hecho"
												label="Fecha y Hora del Hecho"
												:rules="[$rulesRequerido, $rulesFechaMenorAHoy]"
												v-mask="mask"
												placeholder="dd/mm/yyyy hh:mm"
												maxLength="16"
                        @focus="setDate(editedItem.fecha_hecho, 'hecho')"
											/>
              </VCol>
              
            </VRow>     

            <v-row>
              <!-- 👉 First Name -->
              <VCol
                md="12"
                sm="12"
                cols="12"                                
              >
                <VTextarea
                    v-model="editedItem.relato"
                    counter
                    label="Relato Sucinto"
                    auto-grow
                    placeholder="Ingrese la descripción del hecho"
                    :rules="[$rulesRequerido]"
                  />
              </VCol>

            </v-row>
            <v-row dense>             
              <VCol
                md="12"
                sm="12"
                cols="12"
              >
                <VTextarea
                    v-model="editedItem.observaciones"
                    counter
                    label="Observaciones"  
                    auto-grow                  
                  />
              </VCol>

            </v-row>            
            <VDivider />
            <br>
            <VRow class="justify-center">
              <!-- 👉 First Name -->
              <VCol cols="12" md="12" sm="12">
                <VCard title="Oficiales Intervinientes">                        
                  <VCardText>               
                    <oficialesRow                        
                        ref="oficiales"
                        modulo="oficiales"
                    ></oficialesRow>
                  </VCardText>
                </VCard>               
              </VCol>
            </VRow>
            <br>
            <VDivider />
            <br>
            <VRow>                                                                 
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn :disabled="!valid"  @click="save()">Guardar Cambios</VBtn>
                <VBtn color="secondary" variant="outlined" @click="clear()" type="reset">
                  Reset
                </VBtn>
                <VBtn color="error" class="d-flex flex-wrap gap-4">
                  Cancelar
                </VBtn>
              </VCol>
              
              <!--
              <VCol cols="12" >
      
                <VCard title="Confirmacion de parte">
                  <VCardText>
                    <div>
                      <VCheckbox
                        v-model="isAccountDeactivated"
                        label="Confirmo el ingreso de datos"
                      />
                    </div>

                    <VBtn
                      :disabled="!isAccountDeactivated"
                      color="success"
                     
                    >
                      Guardar Cambios
                    </VBtn>
                    
                    <VBtn
                            color="secondary"
                            variant="outlined"
                            type="reset"
                            @click.prevent="resetForm"
                          >
                            Reset
                          </VBtn>
                  </VCardText>
                </VCard>
              </VCol>
              -->
            </VRow>
            
          </VForm>
        </VCardText>
      </VCard>
    </VCol>    
  </VRow>
</template>

<script>
import oficialesRow from "../components/PaymentsRow.vue";
import axios from "axios";
function title() {
  return "Parte";
}
export default {
  components: {
        oficialesRow,
    },
  data: (vm) => ({    
    mask: "##/##/#### ##:##",    
    valid: false,                    
    route: "api/partes",
    title:title(),        
    editedIndex: -1,
    editedItem: {      
    },
    defaultItem: {
      
    },
    clasificaciones:[],    
  }),  

  watch: {
    dialog(val) {
      val || this.$close();
    },
  },

  created() {
    this.$initialize();
    this.$getListForSelect("clasificaciones");
    this.editedItem.fecha_parte = (this.editedIndex == -1)?this.getDateTimeNow():this.editedItem.fecha_parte;
  },

  methods: {
    clear(){
        let vm = this;      
        vm.valid = true;

        vm.$resetValidation();
        vm.$nextTick(() => {
            vm.editedItem = Object.assign({}, vm.defaultItem);
            vm.editedIndex = -1;
        });

        this.$refs.oficiales.reset();
    },
    resetValidation(){
      let vm = this;
      vm.$refs.form.resetValidation();

    },
    async save(){
      if(this.$refs.oficiales.personas.length<=0){
        Swal.alertError('Error al Registrar Nuevo Parte', "Debe incluir oficiales");
        return ;
      }
      Swal.fire({
              title: "Registrar nuevo parte ",
              text: "¿Confirmar registro de parte?",
              icon: "question",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Confirmar",
              cancelButtonText: "Cancelar"
            }).then((result) => {
              if (result.isConfirmed) {
                Swal.alertGetInfo("Registrando información");
                let form = {parte: this.editedItem, oficiales: this.$refs.oficiales.personas};
                 axios.post(this.route, form)
                  .then((r) => {
                    let parte = r.data.data.parte.numero+'/'+r.data.data.parte.anio;          
                    Swal.close();
                    Swal.fire({
                      title:"Se ha registrado el parte "+parte,
                      text: "Presione Imprimir para ver mas información o Aceptar para continuar",
                      icon: "success",
                      showCancelButton: true,
                      confirmButtonColor: "#3085d6",
                      cancelButtonColor: "#d33",
                      confirmButtonText: "Aceptar",
                      cancelButtonText: "Imprimir",                                  
                      },                                
                    ).then((result) => {                             
                      if (result.isConfirmed) {                            
                        this.clear();
                      }
                      else{
                        this.getPdf('denuncia', r.data.id);                        
                      }
                      
                    })                      
                  }).catch((e) => {
                    Swal.close();
                    console.log(e.response.data.message);
                    let error ="Se ha producido un error. "+e.response.data.message;
                    Swal.alertError('Error al Registrar Nuevo Parte', error);                    
                  });   
              }
            });
      
        

    },
    setDate(value, tipo){
      if(value) return;
      let now = this.getDateTimeNow();
      switch(tipo){
        case 'parte': this.editedItem.fecha_parte = now; break;
        case 'hecho': this.editedItem.fecha_hecho = now; break;
      }
      
    },    
  },
  mounted() {
    console.log("Componente partes creado");
  },
};

</script>