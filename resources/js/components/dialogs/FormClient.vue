<template>
  <v-dialog
    v-model="dialog"
    fullscreen

    transition="custom-bottom-transition"
    persistent
  >
    <v-card>
      <v-toolbar color="primary" dark>
        <v-toolbar-title>Información del cliente</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text style="overflow-y: scroll; max-height: 78vh">

        <v-form ref="form" v-model="valid" lazy-validation>


          <v-card outlined class="pt-0 pb-1 mb-1 card-border">
            <span class="card-span">Datos personales</span>
            <v-card-text class="">

              <v-row class="mt-4 my-0 py-0 ">
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4"
                         :class="{ 'color-red': errors['tipoDocumento'] }">Tipo de documento</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0 ">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['numeroDocumento'] }">N° de documento</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4"
                         :class="{ 'color-red': errors['nombreApellido'] }"

                  >Nombre y Apellido</label>
                </v-col>
              </v-row>
              <v-row class="mt-1 py-0">
                <v-col cols="3" class="my-0 py-0 ">

                  <v-select
                    dense
                    outlined
                    :disabled="disableSelect"
                    :items="[
                      { text: 'CUIT', value: 80 },
                      { text: 'CDI Extranjero', value: 87 },
                      { text: 'Pasaporte', value: 94 }
                    ]"
                    v-model="form.tipoDocumento"
                    class="px-6 mx-4"
                    :rules="[ $rulesRequerido ]"
                    ref="tipoDocumento"
                    @input="validateField('tipoDocumento')"
                  ></v-select>
                </v-col>
                <v-col cols="3" class="my-0 py-0 ">
                  <v-text-field
                    v-model="form.numeroDocumento"
                    dense
                    outlined
                    class="px-6 mx-4"
                    :disabled="mode === 'view' || mode === 'edit'"
                    :rules="mode === 'create' ? numeroDocumentoRules : []"
                    :error-messages="mode === 'create' ? customErrorMessage : ''"
                    v-mask="documentMask"
                    ref="numeroDocumento"
                    @input="validateField('numeroDocumento')"
                  ></v-text-field>
                </v-col>

                <v-col cols="3" class="my-0 py-0 ">
                  <v-text-field
                    v-model="form.nombreApellido"
                    dense
                    outlined

                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesRequerido,
																	$rulesMax50,
																]"
                    ref="nombreApellido"
                    @input="validateField('nombreApellido')"

                  ></v-text-field>
                </v-col>

              </v-row>

            </v-card-text>
          </v-card>
          <br>
          <br>

          <v-card outlined class="pt-0 pb-1 mb-1 card-border">
            <span class="card-span">Datos de dirección</span>
            <v-card-text>
              <v-row class="mt-2 my-0 py-0 ">
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['calle'] }">Calle</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['numero'] }">Número</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['piso'] }">Piso</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['depto'] }">Depto</label>
                </v-col>
              </v-row>
              <v-row class="mt-1 py-0">
                <v-col cols="3" class="my-0 py-0 ">

                  <v-text-field
                    type="text"
                    v-model="form.calle"
                    dense
                    outlined

                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesRequerido,
																	$rulesMax50,
																]"
                    ref="calle"
                    @input="validateField('calle')"
                  ></v-text-field>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <v-text-field
                    type="number"
                    v-model="form.numero"
                    dense
                    outlined

                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesRequerido,
																	$rulesNumericos
																]"
                    ref="numero"
                    @keydown="permitirSoloNumeros"
                    @input="validateField('numero')"

                  ></v-text-field>
                </v-col>

                <v-col cols="3" class="my-0 py-0">
                  <v-text-field
                    type="number"
                    v-model="form.piso"
                    dense
                    outlined
                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesAlfaNum,
																	$rulesMax50,
																]"
                    ref="piso"
                    @keydown="permitirSoloNumeros"
                    @input="validateField('piso')"

                  ></v-text-field>
                </v-col>

                <v-col cols="3" class="my-0 py-0 ">
                  <v-text-field
                    v-model="form.depto"
                    dense
                    outlined

                    :disabled="disabled"
                    class="px-6 mx-4"
                    :rules="[
																	$rulesAlfaNum,
																	$rulesMax50,
																]"
                    ref="depto"
                    @input="validateField('depto')"
                  ></v-text-field>
                </v-col>
              </v-row>

              <v-row class="mt-6 my-0 pt-1">
                <v-col cols="3" class="my-0 py-0 ">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['pais'] }">Pais</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['provincia'] }">Provincia</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['ciudad'] }">Ciudad</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4 " :class="{ 'color-red': errors['cp'] }">C.P.</label>
                </v-col>
              </v-row>
              <v-row class="mt-1 py-0">
                <v-col cols="3" class="my-0 py-0">

                  <v-text-field

                    v-model="form.pais"
                    dense
                    outlined

                    class="px-6 mx-4"
                    :disabled="true"
                    :rules="[
																	$rulesRequerido,
																	$rulesAlfaNum,
																	$rulesMax50,
																]"
                    ref="pais"
                    @input="validateField('pais')"
                  ></v-text-field>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <v-autocomplete
                  class="px-6 mx-4"
                    :items="provincias"
                    item-value="id"
                    item-text="nombre"
                    v-model="form.provincia"
                    :rules="[$rulesRequerido]"
                    :disabled="disabled"
                    dense
                    outlined
                     ref="provincia"

                    @input="validateField('provincia')"
                  ></v-autocomplete>
                </v-col>

                <v-col cols="3" class="my-0 py-0">
                  <v-text-field
                    v-model="form.ciudad"
                    dense
                    outlined

                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesRequerido,
																	$rulesAlfaNum,
																	$rulesMax50,
																]"
                    ref="ciudad"
                    @input="validateField('ciudad')"
                  ></v-text-field>
                </v-col>

                <v-col cols="3" class="my-0 py-0">
                  <v-text-field
                    type="number"
                    v-model="form.cp"
                    dense
                    outlined
                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesRequerido,
																	$rulesNumericos,
																	$rulesMax50,
																]"
                    ref="cp"
                    @keydown="permitirSoloNumeros"
                    @input="validateField('cp')"
                  ></v-text-field>
                </v-col>
              </v-row>

            </v-card-text>
          </v-card>
          <br>
          <br>
          <v-card outlined class="pt-0 pb-1 mb-1 card-border">
            <span class="card-span">Datos de contacto</span>
            <v-card-text>

              <v-row class="mt-2 my-0 py-0 ">
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['telefono'] }">Teléfono</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['celular'] }">Celular</label>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <label class="px-6 mx-4" :class="{ 'color-red': errors['mail'] }">Mail</label>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="3" class="my-0 py-0">

                  <v-text-field
                    type="number"
                    v-model="form.telefono"
                    dense
                    outlined

                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesMax50,
																]"
                    ref="telefono"
                    @keydown="permitirSoloNumeros"
                    @input="validateField('telefono')"
                  ></v-text-field>
                </v-col>
                <v-col cols="3" class="my-0 py-0">
                  <v-text-field
                    type="number"
                    v-model="form.celular"
                    dense
                    outlined

                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesRequerido,
																	$rulesNumericos,
																	$rulesMax50,
																]"
                    ref="celular"
                    @keydown="permitirSoloNumeros"
                    @input="validateField('celular')"
                  ></v-text-field>
                </v-col>

                <v-col cols="3" class="my-0 py-0">
                  <v-text-field
                    v-model="form.mail"
                    dense
                    outlined

                    class="px-6 mx-4"
                    :disabled="disabled"
                    :rules="[
																	$rulesRequerido,
																	$rulesMail,
																	$rulesMax50,
																]"
                    ref="mail"
                    @input="validateField('mail')"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-form>
      </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="openDialogConfirm" v-show="!disabled">Guardar</v-btn>
        </v-card-actions>
        <dialog-form-validate
              v-model="dialogs['formValidate']"
              @input="dialogs['formValidate'] = $event"
              :bodyText="bodyText"
            />
            <dialog-form-completed
             v-model="dialogs['formCompleted']"
            @close="closeDialog"
            title="Acción completada"
            info="Los datos se guardaron con exito"
            icon="mdi-check"
            color="#0078D4"
            @confirm="closeDialog()"
      />
      <dialog-form-confirm
             v-model="dialogs['formConfirm']"

            @close="handleDialogClose"
      />
      </v-card>
    </v-dialog>
</template>

<style scoped>

.card-border {
  border: 1.5px solid #DCDCDC !important
}

.card-span {
  margin-top: -15px;
  position: absolute;
  margin-left: 23px;
  background: white;
  padding-left: 3px;
  padding-right: 3px;
  font-size: 18px;
  font-weight: 500;

}

.custom-btn {
  text-transform: none; /* Elimina la transformación de mayúsculas */
  font-family: inherit; /* Hereda el tipo de letra por defecto */
}

.capitalize-first::first-letter {
  text-transform: capitalize; /* Solo la primera letra en mayúscula */
}
</style>
<script>

import DialogFormValidate from "@/views/dialogs/FormValidate.vue";
import DialogFormCompleted from "@/views/dialogs/FormCompleted.vue";

import DialogFormConfirm from "@/views/dialogs/FormConfirmar.vue";


export default {
  components: {

    DialogFormValidate,
    DialogFormCompleted,
    DialogFormConfirm
  },
  props: {

    mode: {
      type: String,
      default: 'create'
    },
    form: {
      type: Object,
      required: true
    },
    errors: {
      type: Object,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    value: {
      type: Boolean,
      default: false,
    },
     db: {
      type: String,
      required: true
    },
    disableSelect: {
      type: Boolean,
      default: true,
    }
  },
  data() {
    return {
      customErrorMessage: "", // Mensaje de error personalizado
      dialog: this.value,
      dialogs: {

        formValidate: false,
        formCompleted: false,
        formConfirm: false,
      },
      valid: true,
      provincias: [],
      bodyText: "",
      verify: false,
    };
  },
  watch: {
    form: function () {
      this.form.pais = 'ARGENTINA';
    },

    value(val) {
      this.dialog = val;

      if (this.dialog) {
        this.resetAllValidations();
        this.$getData(["provincias"]);
      }

      if (this.mode === 'create') {
        this.form.tipoDocumento = 80;
        this.form.documento = "";
        this.verify = false;

        if (this.db == this.$db("ANMAC")) {
          this.disableSelect = false;
        }
      } else {
        this.disableSelect = true;
      }
    },
    dialog(val) {
      this.$emit('input', val);
    },
    mode(val) {
      if (val === 'create') {
        this.customErrorMessage = "";
        this.form.numeroDocumento = "";
      }
    },
  },
  created() {

    console.log("this.db");
    console.log(this.db);

    //this.setDatosPrueba();

  },
  mounted(){

  },
  computed: {
  documentMask() {
    switch (this.form.tipoDocumento) {
      case 80: // CUIT
        return "##-########-#";
      case 87: // CDI Extranjero
        return "###########";
      case 94: // Pasaporte
        return "XXXXXXXXX";
      default:
        return "##################";
    }
  },
  numeroDocumentoRules() {

      switch (this.form.tipoDocumento) {
        case 80: // CUIT
          return [this.$rulesRequerido, this.$rulesCuit, this.ruleUniqueCuit];
        case 87: // CDI Extranjero
          return [this.$rulesRequerido, this.$rulesCDI, this.ruleUniqueCuit];
        case 94: // Pasaporte
          return [this.$rulesRequerido, this.$rulesPasaporte, this.ruleUniqueCuit];
        default:
          return [this.$rulesRequerido];
      }

  }
},
  methods: {

    openDialogConfirm() {
      const result = this.$refs.form.validate();
      if (result) {
        this.dialogs.formConfirm = true;
      }
      else
      {

         for (const key in this.$refs) {
            const ref = this.$refs[key]; // Accede al objeto de referencia

            if (ref && ref.errorBucket) {
                const inputConError = ref.errorBucket[0];

                if (inputConError) {

                      if (key === 'numeroDocumento' && this.mode != 'edit') {
                      this.bodyText = "Por favor, revisa el campo 'Número de Documento'";
                      break;
                  }
                  if (key === 'nombreApellido') {
                      this.bodyText = "Por favor, revisa el campo 'Nombre y Apellido'";
                      break;
                  } else if (key === 'calle') {
                      this.bodyText = "Por favor, revisa el campo 'Calle'";
                      break;
                  } else if (key === 'numero') {
                      this.bodyText = "Por favor, revisa el campo 'Numero'";
                      break;
                  } else if (key === 'provincia') {
                      this.bodyText = "Por favor, revisa el campo 'Provincia'";
                      break;
                  } else if (key === 'ciudad') {
                      this.bodyText = "Por favor, revisa el campo 'Ciudad'";
                      break;
                  } else if (key === 'cp') {
                      this.bodyText = "Por favor, revisa el campo 'C.P.'";
                      break;
                  } else if (key === 'pais') {
                      this.bodyText = "Por favor, revisa el campo 'Pais'";
                      break;
                  } else if (key === 'celular') {
                      this.bodyText = "Por favor, revisa el campo 'Celular'";
                      break;
                  } else if (key === 'mail') {
                      this.bodyText = "Por favor, revisa el campo 'Mail'";
                      break;
                  }


                }
            }
        }
        this.dialogs.formValidate= true;
      }
    },
    async handleDialogClose(event) {

      if (event.confirmed) {
        this.validate();
      }
    },

    closeDialog() {
      console.log('cerrar');
      this.dialog = false;
      console.log(this.form.numeroDocumento.replace(/-/g, ""));
      this.$emit('close',this.form.numeroDocumento.replace(/-/g, ""));
    },
    async ruleUniqueCuit(value) {
      if (this.mode == 'create' && this.verify) {
        if (typeof value !== "undefined" && value != null && value !== "") {
          this.customErrorMessage = "";
          let resp = await this.$isClient(value, 'AR' + this.form.tipoDocumento, this.db);

          console.log(this.form.tipoDocumento);
          let documento = this.form.tipoDocumento === 80 ? "CUIT" : this.form.tipoDocumento === 87 ? "CDI Extranjero" : "Pasaporte";
          switch (resp) {
            case 1:

              console.log("Documento en uso");
              this.customErrorMessage = "Este " + documento + " ya está en uso";
              this.numeroDocumento = "";
              return false;

            case -1:
              if (this.form.tipoDocumento === 80) {

              console.log("Documento invalido");
              this.customErrorMessage = "" + documento + " inválido";
              this.numeroDocumento = "";
              return false;
              }
            default:
              this.customErrorMessage = "";
              return true;
          }

        } else {
          this.customErrorMessage = "";
        }

      }
      this.verify= true;
    },
    async validate() {
      //hacer validaciones sino cumple, muestra modal
      if (this.$refs.form.validate() && this.valid) {
        /*console.log("this.prueba");
        console.log(this.prueba);
        console.log("buscando locationExternalCode");*/
        let locationExternalCode = await this.$getLocationExternalCode(
          this.form.provincia
        );
        /*console.log("locationExternalCode");
        console.log(locationExternalCode);*/
        // Estructura base de los datos
        let data = {};

        // Campos que se envían en ambos casos (POST y PATCH)
        data.CardName = this.form.nombreApellido ? this.form.nombreApellido.toUpperCase() : null;
        data.FederalTaxID = this.form.numeroDocumento.replace(/-/g, "") || null;
        data.Cellular = this.form.celular || null;
        data.EmailAddress = this.form.mail || null;
        data.Phone1 = this.form.telefono || null;

        // Campos específicos para POST o PATCH
        if (this.mode == 'edit') {
          data.CardCode = this.form.cardCode;
        } else {
          data.U_B1SYS_FiscIdType = this.form.tipoDocumento || null;
          data.U_UnamePortal = `AR${this.form.tipoDocumento}${this.form.numeroDocumento.replace(/-/g, "")}` || null;
          data.U_NamePortal = `AR${this.form.tipoDocumento}${this.form.numeroDocumento.replace(/-/g, "")}` || null;
          data.CardCode = `AR${this.form.tipoDocumento}${this.form.numeroDocumento.replace(/-/g, "")}` || null;
          /** propiedades fijas para customers */
          data.CardType = "cCustomer";
          data.GroupCode = "106";
         (this.$db("ANMAC") == this.db) ? data.Properties21 = 'tYES' : data.Properties15 = 'tYES';
          data.Properties61 = 'tYES';
        }

        // Estructura de BPAddresses
        if (this.mode == 'edit') {
          // Para PATCH, necesitamos RowNum, BPCode y FederalTaxID
          data.BPAddresses = [
            {
              AddressName: "Entrega",
              Country: 'AR',
              State: locationExternalCode || null,
              City: this.form.ciudad || null,
              Street: this.form.calle || null,
              StreetNo: this.form.numero || null,
              ZipCode: this.form.cp || null,
              AddressType: "bo_ShipTo",
              RowNum: this.form.rowNum,
              BPCode: data.CardCode,
              FederalTaxID: data.FederalTaxID,
              BuildingFloorRoom: this.form.piso,
              Block: this.form.depto,
            }, {
              AddressName: "Fiscal",
              Country: 'AR',
              State: locationExternalCode || null,
              City: this.form.ciudad || null,
              Street: this.form.calle || null,
              StreetNo: this.form.numero || null,
              ZipCode: this.form.cp || null,
              AddressType: "bo_BillTo",
              RowNum: this.form.rowNum,
              BPCode: data.CardCode,
              FederalTaxID: data.FederalTaxID,
              BuildingFloorRoom: this.form.piso,
              Block: this.form.depto,
            },
          ];
        } else {
          data.BPAddresses = [
            {
              AddressName: "Entrega",
              Country: 'AR',
              State: locationExternalCode || null,
              City: this.form.ciudad || null,
              Street: this.form.calle || null,
              StreetNo: this.form.numero || null,
              ZipCode: this.form.cp || null,
              AddressType: "bo_ShipTo",
              BuildingFloorRoom: this.form.piso,
              Block: this.form.depto,
            },
            {
              AddressName: "Fiscal",
              Country: 'AR',
              State: locationExternalCode || null,
              City: this.form.ciudad || null,
              Street: this.form.calle || null,
              StreetNo: this.form.numero || null,
              ZipCode: this.form.cp || null,
              AddressType: "bo_BillTo",
              BuildingFloorRoom: this.form.piso,
              Block: this.form.depto,
            },
          ];
        }
        // Determinar el método (POST o PATCH)
        const method = this.mode == 'edit' ? "patch" : "post";

        Swal.alertGetInfo(
          this.mode === 'edit'
            ? "Actualizando datos del Cliente"
            : "Registrando datos del Cliente"
        );



        let response = await this.senForm(data, method);
        //console.log('respuesta');
        //console.log(response);

        if (response == 200 || response == 201 || response == 204) {
          Swal.close();
          this.dialogs.formCompleted = true;

        } else {
          Swal.close();
          Swal.alertError();
        }

      } else {
        for (const key in this.$refs) {
          const ref = this.$refs[key];
          this.validateField(ref);
        }
      }
    },
    async senForm(data, method = "post") {
      try {
        const url = "crearEditarClientes";
        const response = await this.$axiosApi[method](url, data,this.db);
        return response.data.code;
      } catch (error) {
        console.error("Error fetching client:", error);
        return error;
      }
    },

    validateField(refs) {

      console.log("probando validate");
      const field = this.$refs[refs];

      if (field) {
        this.errors[refs] = !field.validate(); // Cambia el estado según la validación
      }
      if (refs == 'numeroDocumento') {

      this.form.numeroDocumento = event.target.value.toUpperCase();
      }
    },
    permitirSoloNumeros(event) {
      const key = event.key;

      // Permitir solo números y teclas funcionales
      if (!/^\d$/.test(key) && !["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"].includes(key)) {
        event.preventDefault();
      }
    },

    resetAllValidations() {

      for (const key in this.$refs) {
        const ref = this.$refs[key];
        // Verifica si el ref tiene el método resetValidation
        if (ref && typeof ref.resetValidation === 'function') {
          ref.resetValidation();
        }
      }
    }

  },
};
</script>
