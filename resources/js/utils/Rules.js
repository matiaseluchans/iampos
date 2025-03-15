export default {
  install(Vue, options) {
    Vue.config.globalProperties.$rulesAlfaNum = function (v) {
      const pattern = /^[A-ZÑa-zñáéíóúÁÉÍÓÚ´\-' 0-9]+$/;
      if (typeof v !== "undefined" && v != null && v !== "") {
        if (!pattern.test(v)) return "Se deben ingresar letras o numeros";
      }
      if (typeof v !== "undefined" && v != null && v !== "") {
        return v.length <= 500 || "Max 500 caracteres";
      }
      return true;
    };

    Vue.config.globalProperties.$rulesRequerido = function (value) {
      //console.log(value);
      if (typeof value !== "undefined") {
        if (value !== null && value !== "") {
          return true;
        } else {
          return "Este campo es requerido.";
        }
      }
      return "Este campo es requerido.";
    };

    Vue.config.globalProperties.$rulesRequeridoOtro = function (value) {
      if (typeof value !== "undefined") {
        if (value !== null && value !== "") {
          return true;
        } else {
          return "Este campo es requerido.";
        }
      }
      return "Este campo es requerido.";
    };

    Vue.config.globalProperties.$rulesDni = function (v) {
      const pattern = /^\d{7,8}$/;
      if (!pattern.test(v))
        return "Debe ser numérico y tener al menos 7 digitos";

      return true;
    };

    Vue.config.globalProperties.$rulesMail = function (value) {
      if (/.+@.+\..+/.test(value)) {
        return true;
      }

      return "Este campo debe ser un e-mail válido.";
    };

    Vue.config.globalProperties.$rulesFloatRequerido = function (v) {
      if (!!v) {
        if (parseFloat(v) > 0) {
          return true;
        } else {
          return "Ingrese un valor mayor a 0";
        }
      } else {
        return "Este campo es requerido.";
      }
    };

    Vue.config.globalProperties.$rulesNumeroCertificado = function (v) {
      const pattern = /\b[0-9]{2}-(\D)?[0-9]{8}\/(\D)?[0-9]{4}/;
      if (typeof v !== "undefined") {
        if (!pattern.test(v)) {
          return "Ingrese el numero de certificado con el formato: 99-99999999/9999";
        } else {
          return true;
        }
      } else {
        return true;
      }
    };

    Vue.config.globalProperties.$rulesEnterosPositivos = function (v) {
      const pattern = /^[1-9]\d*$/;

      if (typeof v != "undefined" && v != null) {
        if (!pattern.test(v) && v.length > 0) {
          return "Debe ser numérico";
        }
        return true;
      }
      return true;
    };

    Vue.config.globalProperties.$rulesEnterosPositivosOCero = function (v) {
      const pattern = /^[0-9]\d*$/;

      if (typeof v != "undefined" && v != null) {
        if (!pattern.test(v) && v.length > 0) {
          return "Debe ser numérico";
        }
        return true;
      }
      return true;
    };

    Vue.config.globalProperties.$rulesPorcentaje = function (v) {
      const pattern = /^[0-9]+$/;

      if (pattern.test(v)) {
        if (parseInt(v) > 0 && parseInt(v) <= 100) {
          return true;
        } else if (parseInt(v) == 0) {
          return "El porcentaje no puede ser igual a 0";
        } else {
          return "El porcentaje no puede ser mayor a 100";
        }
      }
      return "Ingreso inválido. Ingrese solo números";
    };

    Vue.config.globalProperties.$rulesNupcias = function (v) {
      const pattern = /^[0-9]+$/;

      if (pattern.test(v)) {
        if (parseInt(v) >= 0 && parseInt(v) < 100) {
          return true;
        } else if (parseInt(v) == 0) {
          return "El número de nupcias no puede ser menor a 0";
        } else {
          return "El número de nupcias no puede ser mayor a 99";
        }
      }
      return "Ingreso inválido. Ingrese solo números";
    };

    Vue.config.globalProperties.$rulesTelefono = function (v) {
      const pattern = /^\d*$/;
      if (!pattern.test(v)) return "Debe ser numérico";

      if (typeof v !== "undefined") {
        return v.length <= 20 || "Max. 20 caracteres";
      }
      return true;
    };

    Vue.config.globalProperties.$rulesNumericos = function (v) {
      const pattern = /^\d*$/;
      if (!pattern.test(v)) return "Debe ser numérico";

      if (typeof v !== "undefined") {
        return v.length <= 20 || "Max. 20 caracteres";
      }
      return true;
    };
    Vue.config.globalProperties.$rulesFormularios = function (v) {
      const pattern = /^\d*$/;
      if (!pattern.test(v)) return "Debe ser numérico";

      if (typeof v !== "undefined") {
        return v.length <= 8 || "Max. 8 caracteres";
      }
      return true;
    };

    Vue.config.globalProperties.$facturaLength = function (v) {
      if (typeof v !== "undefined") {
        return v.length === 12 || "El número de factura debe tener 12 dígitos";
      }
      return true;
    };

    Vue.config.globalProperties.$puntoVentaLength = function (v) {
      if (typeof v !== "undefined") {
        return v.length === 4 || "El punto de venta debe tener 4 dígitos";
      }
      return true;
    };

    Vue.config.globalProperties.$rulesChasis = function (v) {
      const pattern = /^[A-ZÑa-zñáéíóúÁÉÍÓÚ' 0-9]+$/;
      if (!pattern.test(v)) return "Debe ser  alfanumérico";

      if (typeof v !== "undefined") {
        return (
          v.toString().length >= 7 ||
          "Debe ingresar los últimos 7 números del chasis"
        );
      }
      return true;
    };

    Vue.config.globalProperties.$rulesCuit = function (v) {
      const pattern = /\b[0-9]{2}-(\D)?[0-9]{8}\-(\D)?[0-9]{1}/;
      if (typeof v !== "undefined") {
        if (!pattern.test(v)) {
          return "Ingrese el número de cuit con el formato: 99-99999999-9";
        } else {
          return true;
        }
      } else {
        return true;
      }

      /*
            const pattern = /^\d{11}$/;
            if (!pattern.test(v)) return "Debe ser numérico y tener 11 caracteres";

            return true;*/
    };

    Vue.config.globalProperties.$rulesImg = function (v) {
      if (!!v) {
        const pattern = /^$|^.*\.(jpg|jpeg|bmp|png)$/;
        if (!pattern.test(v.name))
          return "El archivo debe ser una imagen JPEG, PNG o BMP";
      }
      return true;
    };

    Vue.config.globalProperties.$rulesMax50 = function (v) {
      if (typeof v !== "undefined") {
        return v.length <= 50 || "Max. 50 caracteres";
      }
      return true;
    };

    Vue.config.globalProperties.$rulesMax500 = function (v) {
      if (typeof v !== "undefined") {
        return v.length <= 500 || "Max. 500 caracteres";
      }
      return true;
    };

    Vue.config.globalProperties.$rulesMax3 = function (v) {
      if (typeof v !== "undefined") {
        return v.length <= 3 || "Max. 3 caracteres";
      }
      return true;
    };

    Vue.config.globalProperties.$rulesLegajo = function (value) {
      if (typeof value !== "undefined") {
        if (String(value).length < 20) {
          return true;
        } else {
          return "Este campo no puede contener mas de 20 caracteres.";
        }
      } else {
        return "Este campo no puede contener mas de 20 caracteres.";
      }
    };

    Vue.config.globalProperties.$rulesFechaMayorAHoy = function (value) {
      try {
        const fechaIngresada = new Date(value);
        const fechaActual = new Date();

        if (fechaIngresada > fechaActual) {
          return true;
        } else {
          return "La fecha debe ser posterior a la fecha actual.";
        }
      } catch (error) {
        console.log("rulesFechaMayorAHoy: " + error);
        return false;
      }
    };

    Vue.config.globalProperties.$rulesFechaMenorAHoy = function (date) {
      try {
        if (typeof date !== "undefined") {
          //console.log(date.length);
          if (date.length == 16) {
            let fechaIngresada = Vue.config.globalProperties.moment(date, "DD-MM-YYYY");
            if (fechaIngresada.isValid()) {
              let now = new Date();
              if (fechaIngresada.diff(now) > 0) {
                return "La fecha no puede ser mayor a la fecha actual";
              } else {
                return true;
              }
            } else {
              return "Ingrese una fecha válida";
            }
          } else {
            return "Ingrese una fecha válida";
          }
        }
        return true;
      } catch (error) {
        console.log("rulesFechaMenorAHoy: " + error);
        return false;
      }
    };

    Vue.config.globalProperties.$rulesFechaValida = function (date) {
      try {
        if (typeof date !== "undefined") {
          if (date.length == 10) {
            let timestamp = Vue.config.globalProperties.moment(date, "DD-MM-YYYY");
            if (timestamp.isValid()) {
              return true;
            } else {
              return "Ingrese una fecha válida";
            }
          } else {
            return "Ingrese una fecha válida";
          }
        }
        return true;
      } catch (error) {
        console.log("rulesFechaValida: " + error);
      }
      return false;
    };

    Vue.config.globalProperties.$rulesAnioMin = function (v) {
      if (v <= 1885) return "El año debe ser mayor a 1886";

      return true;
    };
    Vue.config.globalProperties.$rulesAnioMax = function (v) {
      if (v > parseInt(new Date().getFullYear()))
        return "El año no puede ser mayor a " + new Date().getFullYear();

      return true;
    };
  },
};
