 


export default {
  install(Vue, options) {
    
    //Swal.alertGetInfo("Obteniendo información");
    Vue.config.globalProperties.$initialize = function() {
        let vm = this;
        Swal.alertGetInfo("Obteniendo información");
        let arrayRoutes = Vue.config.globalProperties.$routes;
        //console.log(arrayRoutes);
        this.$axios.
            get(arrayRoutes[vm.route])
            .then((r) => {
              Swal.close();
              console.log(r.data);
                vm.desserts = r.data.data;
            })
            .catch(function (error) {
              Swal.close();
                vm.snackbar = true;
                vm.text = "Error al obtener datos. Error: " + error;
                vm.color = "error";
            });
    },

    Vue.config.globalProperties.$enabledItem= function(item) {
        let vm = this;
        let id = item.id;
        let titulo = vm.title;
        if (titulo.substring( titulo.length - 1)== 's')
        titulo = titulo.substring(0, titulo.length - 1);
        let registro = (item.nombre)?item.nombre:'';
        let activar = item.activo == 1 ? 0 : 1;
        let form = {'activo':activar};
        let msj =
            item.activo == 1
                ? "Desea desactivar el " + titulo + " " + registro + " ?"
                : "Desea activar el " + titulo + " " + registro + " ?";

                vm.$confirm({
            message: msj,
            button: {
                no: "Cancelar",
                yes: "Confirmar",
            },

            callback: (confirm) => {
                let respuesta = item.activo == 1 ? 4 : 5;
                if (confirm) {
                    this.$axios
                        //.put(url)
                        .put(vm.route+'_enable', id, form)
                        .then((r) => {
                            vm.$respuesta(vm, r, respuesta);
                        })
                        .catch((e) => {
                            vm.$respuesta(vm, e, respuesta, true);
                        });
                }
            },
        });
    }


    Vue.config.globalProperties.$deleteItem = async function (id, nombre) {
        let vm = this;

          let titulo = vm.title;
        if (titulo.substring( titulo.length - 1)== 's')
        titulo = titulo.substring(0, titulo.length - 1);


        Swal.fire({
          title: "Eliminar " + titulo,
          text: "¿Desea ELIMINAR el " + titulo + " " + nombre + "?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Confirmar",
          cancelButtonText: "Cancelar"
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.alertGetInfo("Eliminando Registro");
                    let formData = new FormData();
                    let arrayRoutes = Vue.config.globalProperties.$routes;
                    let url = arrayRoutes[vm.route] + "/" + id;
                    formData.append("_method", "DELETE");
                    this.$axios.post(url, {_method: "DELETE", data: id,})
                    /*this.$axios
                        .delete(vm.route, id)*/
                        .then((r) => {
                            vm.$respuesta(vm, r, 0);
                        })
                        .catch((e) => {
                            vm.$respuesta(vm, e, 0, true);
                        });
                        Swal.close();
          }
        });


  /*
        vm.$confirm({
            message: "¿Desea ELIMINAR el " + titulo + " " + nombre + "?",
            button: {
                no: "Cancelar",
                yes: "Eliminar",
            },

            callback: async function (confirm)  {
  */

                //if (confirm) {
                    
                //}
            /*},
        });*/

    }



    Vue.config.globalProperties.$save = async function() {
      let vm = this;
      
      if (vm.$refs.form.validate()) {
        let arrayRoutes = Vue.config.globalProperties.$routes;
        if (vm.editedIndex != -1) {
          Swal.alertGetInfo("Actualizando información");
          let formData = new FormData();
          let url = arrayRoutes[vm.route] + "/" + vm.editedIndex;
          formData.append("_method", "PUT");
          await this.$axios.post(url, {_method: "PUT", data: vm.editedItem,})
            //await this.$axios.put(arrayRoutes[vm.route], vm.editedIndex, vm.editedItem)
            .then((r) => {
              vm.$respuesta(vm, r, 2);
            }).catch((e) => {
              vm.$respuesta(vm, e, 2, true);

            });

        } else {
          Swal.alertGetInfo("Registrando información");
          
          await this.$axios.post(arrayRoutes[vm.route], vm.editedItem)
            .then((r) => {
              vm.$respuesta(vm, r, 1);
            }).catch((e) => {
              vm.$respuesta(vm, e, 1, true);
              Swal.close();
            });
        }
        Swal.close();
        vm.$close();
        }
    }


    Vue.config.globalProperties.$saveUser = async function() {
      let vm = this;
      
      if (vm.$refs.form.validate()) {
        let formData = new FormData();
        
        // Agregar todos los campos del formulario
        /*Object.keys(vm.editedItem).forEach(key => {
          if (key === 'roles') {
            // Manejar roles como array de IDs
            const roleIds = vm.editedItem.roles.map(role => role.id || role);
            formData.append('roles', JSON.stringify(roleIds));
          } else if (key !== 'imageFile' && key !== 'image') {
            formData.append(key, vm.editedItem[key]);
          }
        });*/

        Object.keys(this.editedItem).forEach(key => {
          if (key !== 'imageFiles' && key !== 'image') {
            formData.append(key, this.editedItem[key]);
          }
        });

        
        // Agregar la imagen si existe
        if (vm.editedItem.imageFile) {
          formData.append('image', vm.editedItem.imageFile);
        }
        
        try {
          Swal.alertGetInfo(vm.editedIndex != -1 ? "Actualizando información" : "Registrando información");
          
          let response;
          if (vm.editedIndex != -1) {
            // Para edición
            formData.append('_method', 'PUT');
            response = await vm.$axios.post(
              `${vm.$routes[vm.route]}/${vm.editedItem.id}`,
              formData,
              {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              }
            );
          } else {
            // Para creación
            response = await vm.$axios.post(
              vm.$routes[vm.route],
              formData,
              {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              }
            );
          }
          
          vm.$respuesta(vm, response, vm.editedIndex != -1 ? 2 : 1);
          vm.$close();
        } catch (error) {
          vm.$respuesta(vm, error, vm.editedIndex != -1 ? 2 : 1, true);
        } finally {
          Swal.close();
        }
      }
    };

    Vue.config.globalProperties.$saveWithFile = async function() {
      let vm = this;
      
      if (vm.$refs.form.validate()) {
        let formData = new FormData();
        
        // Agregar todos los campos al FormData
        // Agregar todos los campos del formulario
        Object.keys(this.editedItem).forEach(key => {
          if (key !== 'imageFiles') {
            formData.append(key, this.editedItem[key]);
          }
        });
        
        // Agregar la imagen si existe
        if (this.editedItem.imageFiles?.length > 0) {
          formData.append('image', this.editedItem.imageFiles[0]);
          // Guardar el nombre original del archivo
          formData.append('original_filename', this.editedItem.imageFiles[0].name); 
        }
        
        try {
          Swal.alertGetInfo(vm.editedIndex != -1 ? "Actualizando información" : "Registrando información");
          
          let response;
          if (vm.editedIndex != -1) {
            // Para edición
            formData.append('_method', 'PUT');
            response = await vm.$axios.post(
              `${vm.$routes[vm.route]}/${vm.editedIndex}`,
              formData,
              {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              }
            );
          } else {
            // Para creación
            response = await vm.$axios.post(
              vm.$routes[vm.route],
              formData,
              {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              }
            );
          }
          
          vm.$respuesta(vm, response, vm.editedIndex != -1 ? 2 : 1);
          vm.$close();
        } catch (error) {
          vm.$respuesta(vm, error, vm.editedIndex != -1 ? 2 : 1, true);
        } finally {
          Swal.close();
        }
      }
    };

    Vue.config.globalProperties.$saveExternal = async function() {
      let vm = this;

      if (vm.$refs.form.validate()) {
        let arrayRoutes = Vue.config.globalProperties.$routes;
          if (vm.editedIndex > -1) {
            Swal.alertGetInfo("Actualizando información");

            await vm.$this.$axiosApi.putExternal(arrayRoutes[vm.route], vm.editedIndex, vm.editedItem)
              .then((r) => {
                  vm.$respuesta(vm, r, 2);

              }).catch((e) => {
                  vm.$respuesta(vm, e, 2, true);

              });

          } else {
            Swal.alertGetInfo("Registrando información");

            await vm.$this.$axiosApi.postExternal(vm.route, vm.editedItem)
              .then((r) => {
                  vm.$respuesta(vm, r, 1);
              }).catch((e) => {
                  vm.$respuesta(e, 1, true);
              });
          }
          Swal.close();
          //vm.$close();
      }
    }

    Vue.config.globalProperties.$editItem = async function(id){
        let vm = this;
        //aca toque
        Swal.alertGetInfo("Obteniendo información <br><b></b>");
        let arrayRoutes = Vue.config.globalProperties.$routes;
        /*vm.snackbar = true;
        vm.text = "Obteniendo datos ";
        vm.color = "primary";
        */
        const url = arrayRoutes[vm.route]+"/"+id;
        await this.$axios
        .get(url)
            .then((r) => {
              
              
                Vue.config.globalProperties.$respuesta(vm, r, 3);

                Swal.close();
                //vm.snackbar = false;

            })
            .catch(function (error) {
              Swal.close();
                Vue.config.globalProperties.$respuesta(vm, error, 3, true);
            });


    }
    Vue.config.globalProperties.$sleep = async function (ms) {
      return new Promise((resolve) => setTimeout(resolve, ms));
    }

    Vue.config.globalProperties.$formatDate = function (date) {
        if (!date) return null;
        let hora =date.slice(10);
        date =date.slice(0, 10);

        const [year, month, day] = date.split("-");
        return `${day}/${month}/${year} ${hora}`;
    }

    Vue.config.globalProperties.getDateTimeNow = function() {
      const ahora = new Date();
    
      // Obtener los componentes de fecha y hora
      const dia = ahora.getDate().toString().padStart(2, '0');
      const mes = (ahora.getMonth() + 1).toString().padStart(2, '0');
      const año = ahora.getFullYear();
      const horas = ahora.getHours().toString().padStart(2, '0');
      const minutos = ahora.getMinutes().toString().padStart(2, '0');
    
      // Formatear la fecha y hora en el formato deseado
      const fechaHoraFormateada = `${dia}/${mes}/${año} ${horas}:${minutos}`;
    
      return fechaHoraFormateada;
    }

    Vue.config.globalProperties.getDateTimeTomorrow = function() {
      const tomorrow = new Date();
      
      tomorrow.setDate(tomorrow.getDate() + 1);
    
      // Obtener los componentes de fecha y hora
      const dia = tomorrow.getDate().toString().padStart(2, '0');
      const mes = (tomorrow.getMonth() + 1).toString().padStart(2, '0');
      const año = tomorrow.getFullYear();
      const horas = tomorrow.getHours().toString().padStart(2, '0');
      const minutos = tomorrow.getMinutes().toString().padStart(2, '0');
    
      // Formatear la fecha y hora en el formato deseado
      const fechaHoraFormateada = `${dia}/${mes}/${año} ${horas}:${minutos}`;
    
      return fechaHoraFormateada;
    }

    Vue.config.globalProperties.$formatDateDb= function (date) {
      if (!date) return null;

      const [day, month, year] = date.split("/");
      return `${year}-${month}-${day}`;
    }

    Vue.config.globalProperties.$formatDateFromTimestamp= function (date) {
      if (!date) return null;
      const formattedDate = Vue.config.globalProperties.moment(date).format('DD/MM/YYYY HH:mm:ss');
      return (formattedDate);
      //const [day, month, year] = date.split("/");
      //return `${year}-${month}-${day}`;
    }


    Vue.config.globalProperties.$close= function() {

        let vm = this;

        vm.dialog = false;
        vm.valid = true;

        vm.$resetValidation();
        vm.$nextTick(() => {
            vm.editedItem = Object.assign({}, vm.defaultItem);
            vm.editedIndex = -1;
        });
    }

    Vue.config.globalProperties.$resetValidation= function() {
        let vm = this;
        vm.$refs.form.resetValidation();
    },


    Vue.config.globalProperties.$capitalize =function(s) {
        if (typeof s !== "string") return "";
        return s.charAt(0).toUpperCase() + s.slice(1);
    },

    Vue.config.globalProperties.$getQuery = async function(url) {
        let vm = this;

        let records =  await this.$axios
            .get(url)
            .then((r) => {

                return r.data;
            })
            .catch(function (error) {
                vm.snackbar = true;
                vm.text = "Error al obtener datos. Error: " + error;
                vm.color = "error";
            });
            //console.log('records');
            //console.log(records);
        return records;
    },

    Vue.config.globalProperties.$resolveStatusVariant = function(status) {
 
      if (status == 1)
        return {
          color: 'success',
          text: 'Activo',
        }
      else if (status == 0)
        return {
          color: 'error',
          text: 'Inactivo',
        }          
      else
        return {
          color: 'success',
          text: 'Activo',
        }
    },
    Vue.config.globalProperties.$getRecord = async function(id){
        let vm = this;
        //aca toque
        Swal.alertGetInfo("Obteniendo información <br><b></b>");
        let arrayRoutes = Vue.config.globalProperties.$routes;
        
      const url = arrayRoutes[vm.route]+"/"+id;
      let record;
      await this.$axios.get(url).then((r) => {                  
        Swal.close();
        record = r.data;
      }).catch(function (error) {
        Swal.close();
        Vue.config.globalProperties.$respuesta(vm, error, 3, true);
      });

      return record;
    },
    Vue.config.globalProperties.$respuesta = function(vm,r,tipo,excepcion  = false,recargarGrilla=true){

      let text1 = "Creado";
      let text2 = "crear";
      switch (tipo) {
        case 0:
          text1 = "Eliminado";
          text2 = "eliminar";
          break;
        case 2:
          text1 = "Actualizado";
          text2 = "actualizar";
          break;
        case 3:
          text1 = "Obtener";
          text2 = "actualizar";
          break;
        case 4:
          text1 = "Desactivado";
          text2 = "desactivar";
          break;
        case 5:
          text1 = "Activo";
          text2 = "activar";
          break;
      }

      if (excepcion == false) {

        if (tipo == 3) {

          //if (r.data && r.data.code == 200) {
            console.log(r.data);
          if (r.data) {
            vm.editedIndex = (vm.route!='permissions' )? r.data.data.id:r.data.data.name;
            //vm.editedIndex = (vm.route!='permissions' )? r.data.id:r.data.name;
            delete r.data.id;
            //vm.editedItem = Object.assign({}, r.data);
            vm.editedItem = Object.assign({}, r.data.data);

            if (vm.editedItem.image) {
              vm.imagePreview = `/storage/${vm.editedItem.image}`;
            }

            

            vm.dialog = true;
          } else {

            vm.snackbar = true;
            vm.text =
              "<p>Ocurrió un problema al recuperar el registro.<br><hr><b>Codigo:</b>" +
              r.data.code +
              " - <b>Error:</b> " +
              r.data.message +
              "</p>";
            vm.color = "error";
          }
        } else {
          //console.log(r.data);
          if (r.data && r.data.code == 200 || r.data.code == 201) {
          //if (r.data && r.data.status == 200 || r.data.status == 201) {
            vm.color = "success";
            vm.snackbar = true;
            vm.text = "Registro " + text1;


            if (tipo == 1 || tipo == 2) {
              vm.$close();
            }
            if(recargarGrilla)
            vm.$initialize();
          } else {
            vm.snackbar = true;
            vm.text =
              "<p>Ocurrió un problema al " +
              text2 +
              " el registro.<br><hr><b>Codigo:</b>" +
              r.data.code +
              " - <b>Error:</b> " +
              r.data.message +
              "</p>";
            vm.color = "error";
            Swal.close();
          }
        }
      } else {
        vm.snackbar = true;
        vm.text = "Error al " + text2 + " los datos. Error: " + r.response.data.message;
        vm.color = "error";
        Swal.close();
        return r;
      }
    }


    Vue.config.globalProperties.$getCss= function (value, item)
    {
      let verde = "grid-green rounded-pill text-center mr-9";
      let amarillo = "grid-yellow rounded-pill text-center mr-9";
      let rojo = "grid-red rounded-pill text-center p-1 mr-9";

      switch (item) {
        case "activo":
          if (value== "1") return verde;
          if (value  === "") return "white" ;
          else return rojo;
          break;
          case "estado":
          if (value  == "enviado") return amarillo;
          if (value  == "leido") return verde ;
          else return rojo;
          break;

          case "prioridad":
          if (value  == "ALTA") return rojo;
          if (value  == "MEDIA") return amarillo ;
          else return verde;
          break;
      }
    }


    Vue.config.globalProperties.$getDate = function () {
        return Math.floor(Date.now() / 1000);
    }

    Vue.config.globalProperties.$getValue= function (value, item)
    {


      switch (item) {
        case "activo":

          if (value== 1) return "Si";
          if (value == 0) return "No";
          if (value == null) return "";
        break;
      }
    }

    window.Swal.alertGetInfo= function (msgHtml)
    {

      Swal.mixin({
        customClass: {
          container: 'my-swal-container'
        }
      }).
      fire({
        title: "Aguarde un instante!",
        icon: "info",
        html: msgHtml,
        allowOutsideClick: false,
        showConfirmButton: false,            
        willOpen: () => {
          Swal.showLoading();
        },
      });
    }

    window.Swal.alertError= function (title = "Atención",msgHtml = "Se ha producido un error")
    {

      Swal.mixin({
        customClass: {
          container: 'my-swal-container'
        }
      }).
      fire({
        title: title,
        html: msgHtml,
        icon: "error",
        showCancelButton: false,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Aceptar",
        hideClass: {  popup: 'animate__animated animate__fadeOutUp' },
      });
    }



    window.Swal.objSuccess = function (title, msgHtml){

      let r = { title: title,
        icon: "success",
        html: msgHtml,
        showCloseButton: false,
        showCancelButton: false,
        focusConfirm: true,
        confirmButtonColor: "#3085d6",
        confirmButtonText: "Aceptar",
        hideClass: {  popup: 'animate__animated animate__fadeOutUp' },
        };
        return r;

    };

    window.Swal.objQuestion = function (title, msgHtml){

      let q = {
        title: title,
        html: msgHtml,
        icon: "question",
        showCloseButton: false,
        showCancelButton: true,
        focusConfirm: true,
        confirmButtonColor: "green",
        cancelButtonColor: "#d33",
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
        hideClass: {  popup: 'animate__animated animate__fadeOutUp' },

      };
        return q;

    };

    Vue.config.globalProperties.$verifyIdentifiers = async function (tipo, documento) {
      let vm = this;
      if (tipo && documento) {
        const forLoopIdentifiers = async (tipo, documento) => {
          let dni = vm.editedItem.cuit.substr(3, 8);
          let idTipoDni = vm.identifiertypes.filter(
            (item) => item.name.toUpperCase() == "DNI"
          );
          let arrayDocs = [{'tipo': Number(tipo), 'documento':documento}]
          arrayDocs.push({'tipo': Number(idTipoDni[0].id), 'documento':dni});
          //console.log("Consultando identificadores...");
          //arrayDocs.forEach(async (element) => {

            for (let i = 0; i < arrayDocs.length; i++) {
                console.log(arrayDocs[i]);
                let query = "IdentifierTypeId=" + Number(arrayDocs[i].tipo);
                query = query + "&Identifier=" + arrayDocs[i].documento;

                const resp = await vm.$this.$axiosApi
                  .getByQuery(vm.routePersons, query)
                  .then((r) => {
                    console.log(r);
                    if (r.data.data) {

                      if( r.data.data.length>0){
                        return r.data.data;
                      }
                    }
                  })
                  .catch(function (error) {
                    console.log(error);
                    vm.snackbar = true;
                    vm.text = "Error al consultar personas. Error: " + error;
                    vm.color = "error";
                  });

                  if(resp){
                    i = arrayDocs.length;
                    return resp;
                  }

                //return resp;
            }
            /*}

          );*/

          //console.log("End consultando identificadores...");
          //console.log(resp);
          return resp;
        };
        if (vm.editedItem.id) {
          if (vm.tDocAnt == tipo.id && vm.docAnt == documento) {
            return;
          } else if (vm.tDocAnt == "" && vm.docAnt == "") {
            return;
          }
        } else if (vm.tDocAnt == tipo.id && vm.docAnt == documento) {
          return;
        }

        let resp = await forLoopIdentifiers(tipo.id, documento);
        //console.log(resp);
        this.tDocAnt = Number(tipo.id);
        this.docAnt = documento;

        if (resp && resp.length > 0) {
          //console.log("respuesta recibida del loop");
          //console.log(resp);
          let tituloSwal =
            "El " +
            tipo.name +
            ": " +
            documento +
            " ya esta registrado. Desea utilizar la información?";
          //title: "<b>" + tituloSwal + " Datos del " + this.title + "?</b>",
          //text: "Presione Aceptar para confirmar los datos del " + this.title,
          Swal.fire({
            title: tituloSwal,
            icon: "question",
            html: "Presione Aceptar para completar el formulario con los datos existentes",
            showCloseButton: false,
            showCancelButton: true,
            focusConfirm: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: "Aceptar",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
          }).then((result) => {
            if (result.isConfirmed) {
              Swal.fire({
                title: "Aguarde un instante!",
                icon: "info",
                html: "Cargando datos de la persona",
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                  Swal.showLoading();
                },
              });
              let id = resp[0].id;
              vm.$this.$axiosApi
                .getById(vm.route, id)
                .then((r) => {
                  //console.log(r.data.data);
                  if (r.data.data) {
                    Swal.close();
                    vm.editedItem.id = Number(r.data.data.id);
                    vm.editedItem.businessName = r.data.data.businessName;
                    vm.editedItem.lastName = r.data.data.lastName;
                    vm.editedItem.firstName = r.data.data.firstName;
                    vm.editedItem.birthDate = r.data.data.birthDate
                      ? vm.$formatDate(r.data.data.birthDate.substring(0, 10))
                      : "";
                      vm.editedItem.birthPlace = r.data.data.birthPlace;
                      vm.editedItem.genderId = r.data.data.gender;
                      vm.$set(vm.editedItem, 'maritalStatusId', r.data.data.maritalStatus);
                      vm.editedItem.pais_dni = {
                      id: r.data.data.nationality,
                    };
                    vm.editedItem.nacionalidad = {
                      id: r.data.data.nationality,
                    };
                    vm.editedItem.activity = r.data.data.activity;
                    vm.editedItem.mail = r.data.data.emails[0].email;
                    vm.editedItem.telefono =
                      r.data.data.phones[0].phoneNumber;
                      vm.editedItem.personType = {
                      id: Number(r.data.data.personType),
                    };
                    //falta cargar los domicilios
                    let domicilios = r.data.data.addresses;
                    for (let i = 0; i < domicilios.length; i++) {
                      //console.log(Number(domicilios[i].addressTypeId));
                      if (Number(domicilios[i].addressTypeId) == 1) {
                        //this.editedItem.domicilioReal = domicilios[i].address;
                        vm.editedItem.domicilioReal.calle =
                          domicilios[i].address.street;
                          vm.editedItem.domicilioReal.numero =
                          domicilios[i].address.number;
                          vm.editedItem.domicilioReal.cp =
                          domicilios[i].address.postCode;
                          vm.editedItem.domicilioReal.localidad =
                          domicilios[i].address.location;
                          vm.editedItem.domicilioReal.partido =
                          domicilios[i].address.partido;
                          vm.editedItem.domicilioReal.provincia = {
                          name: domicilios[i].address.province,
                        };
                        vm.editedItem.domicilioReal.barrio = {
                          name: domicilios[i].address.neighborhood,
                        };
                        //console.log(this.editedItem.domicilioReal);
                        vm.keyDomicilioReal = vm.keyDomicilioReal + 1;
                        //console.log("keyDomicilioReal");
                        //console.log(vm.keyDomicilioReal);
                      } else {
                        vm.editedItem.domicilioLegal.calle =
                          domicilios[i].address.street;
                          vm.editedItem.domicilioLegal.numero =
                          domicilios[i].address.number;
                          vm.editedItem.domicilioLegal.cp =
                          domicilios[i].address.postCode;
                          vm.editedItem.domicilioLegal.localidad =
                          domicilios[i].address.location;
                          vm.editedItem.domicilioLegal.partido =
                          domicilios[i].address.partido;
                          vm.editedItem.domicilioLegal.provincia = {
                          name: domicilios[i].address.province,
                        };
                        vm.editedItem.domicilioLegal.barrio = {
                          name: domicilios[i].address.neighborhood,
                        };
                        vm.keyDomicilioLegal = this.keyDomicilioLegal + 1;
                        //console.log("keyDomicilioLegal");
                        //console.log(this.keyDomicilioLegal);
                      }
                    }
                  }
                })
                .catch(function (error) {
                  console.log(error);
                  //this.snackbar = true;
                  //this.text = "Error al consultar personas. Error: " + error;
                  //this.color = "error";
                });
            }
            else{
              //no desea utilizar los datos, pero igual debe actualizarse
              if(typeof resp[0] !== "undefined"){
                vm.editedItem.id = Number(resp[0].id);
              }
            }
          });
          //return resp;
        } else {
          vm.editedItem.id = null;
          return false;
        }
      } else {
        return false;
      }
    },


    Vue.config.globalProperties.$filteredData=  function (item)
    {
      let vm = this;
      let conditions = [];
      for (var k in vm.filters) {
          if (vm.filters[k].length >0) {
          vm.filterKey =k;

          let a ="vm.filterBy"+k.charAt(0).toUpperCase() + k.slice(1);

          conditions.push( eval(a));
        }
      }

      if (conditions.length > 0) {
        return vm.desserts.data.filter((dessert) => {
          return conditions.every((condition) => {
            return condition(dessert);
          });
        });
      }

      return vm.desserts;
    }


    //////////////////////////////////////////////////////////////////////
    Vue.config.globalProperties.$filteredData2=  function (item)
        {
          let vm = this;
          let conditions = [];




          for (var k in vm.filters) {
					   if (vm.filters[k].length >0) {
							vm.filterKey =k;


              //conditions.push( vm.filterData);
              vm.filterData(k);



              //conditions.push( eval(a));
          	}
					}

/*

          if (conditions.length > 0) {
            return vm.desserts.filter((dessert) => {
              return conditions.every((condition) => {
                return condition(dessert);
              });
            });
          }*/

          return vm.desserts;
    }


    Vue.config.globalProperties.$filterBy= function (item,filterKey)
    {
        let k = filterKey;

        if (k == "activo") {
          if (this.filters[k].toLowerCase() == "si" || this.filters[k].toLowerCase() == "s")
            return item[k]==1;
          if (this.filters[k].toLowerCase() == "no" || this.filters[k].toLowerCase() == "n")
            return item[k]==0;
        }

        return String(item[k])
          .toLowerCase()
          .includes(this.filters[k].toLowerCase());

    }



    Vue.config.globalProperties.$getListForSelect =  function ($route) {
      return new Promise((resolve, reject) => {
        let vm = this;
        //Swal.alertGetInfo("Obteniendo información <br><b></b>");
        let arrayRoutes = Vue.config.globalProperties.$routes;
          //console.log($route);              
          this.$axios.get(arrayRoutes[$route]).then((response) => {
            let options = response.data.data;
            //console.log(options);
            if(typeof options[0] !== 'undefined'){
                switch($route){
                  case 'usuarios':
                    this[$route] = options.filter(o => o.enabled == true)
                  break;
                  case 'roles':
                  case 'clients':
                  case 'permissions':
                    this[$route] = options;
                  break;
                  default:
                    this[$route] = options.filter(o => o.activo == "1")
                  break;
                }
                resolve(response.data);
                /*
                    if($route === 'usuarios')
                        this[$route] = options.filter(o => o.enabled == true)
                    else if (($route !== 'roles') && ($route !== 'clients'))
                        this[$route] = options.filter(o => o.activo == "1")
                    else
                    {
                      this[$route] = options
                    }
                    //this.$toastr.removeByName("toast-"+$route);
                    resolve(response.data);
                */
              }
              //reject(new Error('Error interno del servidor'));

          })
          .catch((error) => {
            reject(error);
          });

        });
      }



      Vue.config.globalProperties.$getData = async function(arrayUrl) {

        let style="width: 51px;    height: 19px;  font-size: 10px; border-radius: 30px;  padding-top: 3px;";
        let estadoPendiente =
          '<div class="swal2-loader" style="display: flex;zoom: 60%;"></div>';
        let estadoError =
          "<i class='mdi mdi-close color-red' style='font-size: 1.7rem;'></i>";
        let estadoOk =
          "<i class='mdi mdi-check color-green' style='font-size: 1.7rem;'></i>";


        var stringAlert = "<div id='contenido'><table class='table' style='width:200px;margin-left:27%'><tbody>";

        arrayUrl.map(function(cadena) {

          let nombre =cadena.replace(/_/g, " ")
          stringAlert = stringAlert +

          "<tr style='height:30px'>" +

          "<td style='width:25%;text-align: center;''>" +
          "<div id='div_"+cadena+"'>" +
          estadoPendiente+"</div>" +
          "</td>" +
          "<td style='width:80%;text-align: left;'><b>"+cadena.replace(/_/g, " ")+"</b></td>" +
          "</tr>";

        });
        stringAlert = stringAlert +"</tbody></table></div>";

        //Swal.alertGetInfo();



        const swalGetData = Swal.mixin({
          title: "Aguarde un instante!",
          icon: "info",
          html: "Obteniendo información: <br>"+stringAlert+"",
          allowOutsideClick: false,
          showConfirmButton: false,
          showCancelButton:false,
          //showClass: {  popup: 'animate__animated animate__fadeInDown' },
          hideClass: {  popup: 'animate__animated animate__fadeOutUp' },

          willOpen: () => {
            Swal.showLoading();
          },
        });
        swalGetData.fire();

        let hayError =false;
        try {
          const responses = [];

          for (let i = 0; i < arrayUrl.length; i++) {
            var div = document.getElementById("div_"+arrayUrl[i]);

            try {
                let  response = '';

              switch(arrayUrl[i])
              {

                case "paises":

                  response = await this.$getListPaisesForSelect(arrayUrl[i], i);
                  responses.push(response);
                  if (response.status === 200) {
                    //console.log(`La solicitud en el índice ${i} fue exitosa.`);
                    div.innerHTML = estadoOk;
                    // Aquí puedes tomar decisiones basadas en una solicitud exitosa si es necesario.
                  } else {
                    console.log(`La solicitud en el índice ${i} falló con el código ${response.status}.`);
                  }
                break;
                case "identifiertypes":
                case  "genders":
                case  "maritalstatuses":


                    response = await this.$getExternalListForSelect(arrayUrl[i], i);

                    responses.push(response);

                    if (response.succeeded ) {
                      //console.log(`La solicitud en el índice ${i} fue exitosa.`);
                      div.innerHTML = estadoOk;
                      // Aquí puedes tomar decisiones basadas en una solicitud exitosa si es necesario.
                    } else {
                      console.log(`La solicitud en el índice ${i} falló con el código ${response.errors}.`);
                    }
                break;
                default:
                    response = await this.$getListForSelect(arrayUrl[i], i);

                    responses.push(response);

                    if (response.code === 200) {
                      console.log(`La solicitud en el índice ${i} fue exitosa.`);
                      div.innerHTML = estadoOk;
                      // Aquí puedes tomar decisiones basadas en una solicitud exitosa si es necesario.
                    } else {
                      console.log(`La solicitud en el índice ${i} falló con el código ${response.status}.`);
                    }

              }


            } catch (error) {
              //console.error(`Error en la solicitud en el índice ${i}:`, error);
              hayError =true;
              div.innerHTML = estadoError;

              //console.log(response);

              const contenido = document.querySelector('#contenido');
                  contenido.innerHTML +="<br>La solicitud a: <b>"+arrayUrl[i].replace(/_/g, " ")+"</b> ha fallado. Por favor, inténtelo de nuevo o comuníquese con el administrador.";



            }

          }

          if(responses.length == arrayUrl.length)
          {
            setTimeout(() => {
            //console.log(`Todas las ${responses.length} peticiones GET se han completado con éxito.`);

            swalGetData.close();},500);
          }



        } catch (error) {
          console.error('Error general:', error);
        }

        if(hayError)
            {
              contenido.innerHTML += '<br><button id="confirmButton" class="swal2-deny swal2-styled">Cancelar</button>';

              const confirmButton = document.querySelector('#confirmButton');
              confirmButton.addEventListener('click', () => {

                swalGetData.close();
              })
            }



      }





      Vue.config.globalProperties.$exit = async function  (id) {
        let vm = this;

        Swal.fire({
          title: "<b>Desea salir del trámite?</b>",
              icon: "question",
              html:
                "Para <b>continuar con la carga en otro momento</b> presione <b>Guardar y Salir</b>. <p>" +
                "Para <b>salir sin guardar</b> los datos presione <b>Salir</b>. <p>" +
                "Presione <b>Cancelar</b> para seguir cargando el trámite.<p>",
          showDenyButton: true,
          showCancelButton: true,
          confirmButtonText: "Guardar y Salir",
          denyButtonText: "Salir",
          cancelButtonText: "Cancelar",
          confirmButtonColor: "green",
          denyButtonColor: "blue",
          cancelButtonColor: "red",
          hideClass: {  popup: 'animate__animated animate__fadeOutUp' },
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {

            if(!id){
              Swal.fire({
                icon: "error",
                title: "Registro de Trámite",
                confirmButtonText: "Aceptar",
                html:"Para guardar los datos temporalmente, debe avanzar de sección",
                hideClass: {  popup: 'animate__animated animate__fadeOutUp' },

              })
              return;
            }
            //console.log(id)
            Swal.alertGetInfo("Guardando datos del trámite");
            vm.$saveInProgress();
            /*this.$router.push({
              name: "f01_autos"
            });*/
          } else if (result.isDenied) {
            Swal.alertGetInfo("Saliendo del trámite");
            vm.$deleteTramite(id);
          }
        });
      },

      Vue.config.globalProperties.$saveInProgress = async function () {
        await this.$sleep(1000);
            Swal.close();
            Swal.fire({
              icon: "success",
              title: "Registro de Trámite",
              confirmButtonText: "Aceptar",
              html:"El trámite ha quedado en progreso, disponible para ser editado",
              hideClass: {  popup: 'animate__animated animate__fadeOutUp' },

            }).then((r) => {
              if (r.isConfirmed) {
                this.$router.push({
                  name: "f01_autos"
                });
              }
            });
      },

           

        



      Vue.config.globalProperties.$pdfExport= async function (id) {
        let vm = this;
        vm.$this.$axiosApi.pdfExport(vm.route+"_pdf",id);

      }



      Vue.config.globalProperties.$hideMenu = function(){

        if ( this.$root.$children[0].$children[0].$children[0].$children[0].$vnode.componentOptions.tag == "dashboard-core-app-bar")
              this.$root.$children[0].$children[0].$children[0].$children[0].setDrawer( false );
        if (this.$root.$children[0].$children[0].$children[0].$children[1].$vnode.componentOptions.tag == "dashboard-core-app-bar")
              this.$root.$children[0].$children[0].$children[0].$children[1].setDrawer(false );


      }

      Vue.config.globalProperties.$goBack = async function  () {

        let msj = "Desea volver a la pagina anterior ?";
        let vm = this;
        Swal.fire(
          Swal.objQuestion(
            msj,
            ""
          )
        ).then((result) => {
          if (result.isConfirmed) {
            vm.$router.go(-1);
          }
        });

      }

      Vue.config.globalProperties.$toggleActive = async function(item)  {
        Swal.alertGetInfo("Actualizando información");
        const originalState = item.active; // Guarda el estado original

        console.log("item.active");
        console.log(item.active);
        try {
          let arrayRoutes = Vue.config.globalProperties.$routes;

          let url = arrayRoutes[this.route];
          const response = await this.$axios.put(url+'/'+item.id, {
            data: {
              active: item.active === 1 ? 0 : 1
            }
          });

          if (response.data.code == 200) {
            this.snackbar = true;
            this.text = "Se modificó el estado con exito.";
            this.color = "success";
          } else {
            item.active = originalState;
            this.snackbar = true;
            this.text = "Error al cambiar el estado";
            this.color = "error";
          }
          this.$initialize();
        } catch (error) {
          item.active = originalState;
          console.error("Error al cambiar el estado:", error);
          this.snackbar = true;
          this.text = "Error al cambiar el estado:" + error;
          this.color = "error";
          this.$initialize();
        } finally {
          Swal.close();
        }
      }

    }


}
