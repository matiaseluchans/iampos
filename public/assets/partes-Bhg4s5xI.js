import{_ as ae}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{o as T,e as j,w as a,b as t,W as y,B as w,t as S,a as k,V as M,D as Ve,K as ye,a9 as we,z as B,r as C,y as xe,aK as be,Y as ke,k as I,_ as Ce,Q as Ie,aL as _e,U as z,a1 as E,S as N,au as le,aM as Re,a0 as O,aN as $e,ar as Fe,ae as Pe,aO as Se}from"./main-B-bnVr7i.js";import{a as v,V as p}from"./VRow-CckguHJp.js";import{V as Be}from"./VDataTable-CI5BAWbA.js";import{V as G,m as Te,a as je,u as Ne,b as X,f as Ae,c as De,d as Ue}from"./VTextField-DYX6eAz5.js";import{V as oe}from"./VAutocomplete-HsoC3pcZ.js";import{a as Z,V as ee}from"./VCard-CqL6BpWF.js";import{V as q}from"./VDivider-sx5WAEol.js";import{V as He}from"./VForm-DMnCxvLI.js";import{f as Me}from"./forwardRefs-C-GTDzx5.js";import{I as ze}from"./VImg-DONpqZE8.js";/* empty css              */import"./VList-dE3g2XVB.js";import"./VOverlay-DX_uWgIC.js";import"./easing-DY7PVvcf.js";import"./lazy-CVvRdrmx.js";import"./index-BNTuomhI.js";import"./ssrBoot-txvmje94.js";import"./VAvatar-Bs8XwlKy.js";import"./VTable-D6sNb8VH.js";const Ee={name:"OficialesRow",props:{modulo:String},data:e=>({funciones:[],personas:[],personalPsa:[],route:"personalPsa",headers:[{title:"Id",key:"index",align:" d-none"},{title:"Legajo",key:"legajo",align:"center"},{title:"Documento",key:"documento"},{title:"Apellido",key:"apellido"},{title:"Nombres",key:"nombres",align:"center"},{title:"Funcion",key:"funcion",align:"center"},{title:"Acciones",key:"actions",align:"center"}],avatar:"/images/user_primary.png",avatarCondomino:"/images/user_primary_2.png",keyTablePersonas:0}),methods:{async getOficial(e){if(e.legajo){let l=e.index,i=e.legajo,d=await this.$getRecord(i);d&&(this.personas[l].documento=d.data.documento,this.personas[l].apellido=d.data.apellido,this.personas[l].nombres=d.data.nombres)}},add(){var e=this.personas.length;this.personas.push({legajo:"",documento:"",apellido:"",nombres:"",funcion:"",index:e})},remove(e){this.personas.splice(e,1)},reset(){this.personas=[]},forceRerender(e){this.componentKey+=1},resetRow(e){this.personas[e].legajo="",this.personas[e].documento="",this.personas[e].apellido="",this.personas[e].nombres=""}},mounted(){console.log("Componente ContactosRow creado")},created(){this.$getListForSelect("funciones")}},Oe={class:"text-h6 text-left"},qe={class:"text-h6 text-center"},Ge={class:"text-h6 text-center"};function Le(e,l,i,d,c,r){return e.personas.length?(T(),j(p,{key:1,justify:"center"},{default:a(()=>[t(v,{cols:"12",md:"12",sm:"12",class:"text-center"},{default:a(()=>[(T(),j(Be,{headers:e.headers,items:e.personas,key:e.keyTablePersonas,"hide-default-footer":!0},{"item.legajo":a(({item:o})=>[t(G,{class:"text-left",modelValue:o.legajo,"onUpdate:modelValue":f=>o.legajo=f,label:o.legajo?"":"Legajo",rules:[e.$rulesRequerido,e.$rulesLegajo],required:"",onBlur:f=>r.getOficial(o)},null,8,["modelValue","onUpdate:modelValue","label","rules","onBlur"])]),"item.documento":a(({item:o})=>[k("span",Oe,S(o.documento),1)]),"item.apellido":a(({item:o})=>[k("span",qe,S(o.apellido),1)]),"item.nombres":a(({item:o})=>[k("span",Ge,S(o.nombres),1)]),"item.funcion":a(({item:o})=>[t(oe,{items:e.funciones,"item-value":"id","item-title":"detalle",label:o.funcion_id?"":"Funcion",modelValue:o.funcion_id,"onUpdate:modelValue":f=>o.funcion_id=f,rules:[e.$rulesRequerido],"return-object":""},null,8,["items","label","modelValue","onUpdate:modelValue","rules"])]),"item.actions":a(({item:o})=>[t(y,{color:"warning",fab:"","x-small":"",dark:"",title:"Reset",onClick:f=>r.resetRow(o.index),class:"x-btn-grid"},{default:a(()=>[t(M,null,{default:a(()=>[w("ri-refresh-line")]),_:1})]),_:2},1032,["onClick"]),t(y,{color:"error",fab:"","x-small":"",dark:"",title:"Eliminar Persona",onClick:l[0]||(l[0]=f=>r.remove(e.index)),class:"x-btn-grid"},{default:a(()=>[t(M,null,{default:a(()=>[w("ri-user-unfollow-fill")]),_:1})]),_:1}),t(y,{color:e.$cv("principal"),fab:"","x-small":"",dark:"",title:"Agregar Persona",onClick:r.add,class:"x-btn-grid"},{default:a(()=>[t(M,null,{default:a(()=>[w(" ri-user-add-fill ")]),_:1})]),_:1},8,["color","onClick"])]),_:2},1032,["headers","items"]))]),_:1})]),_:1})):(T(),j(p,{key:0,dense:"",align:"center",class:"text-fields-row"},{default:a(()=>[t(v,{cols:"12",md:"12",sm:"12"},{default:a(()=>[t(y,{small:"",color:"primary",onClick:r.add,dark:""},{default:a(()=>[w(" Agregar "+S(this.modulo),1)]),_:1},8,["onClick"])]),_:1})]),_:1}))}const ne=ae(Ee,[["render",Le],["__scopeId","data-v-07a4910c"]]),Ke=Ve({autoGrow:Boolean,autofocus:Boolean,counter:[Boolean,Number,String],counterValue:Function,prefix:String,placeholder:String,persistentPlaceholder:Boolean,persistentCounter:Boolean,noResize:Boolean,rows:{type:[Number,String],default:5,validator:e=>!isNaN(parseFloat(e))},maxRows:{type:[Number,String],validator:e=>!isNaN(parseFloat(e))},suffix:String,modelModifiers:Object,...Te(),...je()},"VTextarea"),te=ye()({name:"VTextarea",directives:{Intersect:ze},inheritAttrs:!1,props:Ke(),emits:{"click:control":e=>!0,"mousedown:control":e=>!0,"update:focused":e=>!0,"update:modelValue":e=>!0},setup(e,l){let{attrs:i,emit:d,slots:c}=l;const r=we(e,"modelValue"),{isFocused:o,focus:f,blur:s}=Ne(e),re=B(()=>typeof e.counterValue=="function"?e.counterValue(r.value):(r.value||"").toString().length),ie=B(()=>{if(i.maxlength)return i.maxlength;if(!(!e.counter||typeof e.counter!="number"&&typeof e.counter!="string"))return e.counter});function se(n,m){var u,h;!e.autofocus||!n||(h=(u=m[0].target)==null?void 0:u.focus)==null||h.call(u)}const L=C(),_=C(),K=xe(""),R=C(),de=B(()=>e.persistentPlaceholder||o.value||e.active);function A(){var n;R.value!==document.activeElement&&((n=R.value)==null||n.focus()),o.value||f()}function ue(n){A(),d("click:control",n)}function ce(n){d("mousedown:control",n)}function fe(n){n.stopPropagation(),A(),O(()=>{r.value="",$e(e["onClick:clear"],n)})}function me(n){var u;const m=n.target;if(r.value=m.value,(u=e.modelModifiers)!=null&&u.trim){const h=[m.selectionStart,m.selectionEnd];O(()=>{m.selectionStart=h[0],m.selectionEnd=h[1]})}}const x=C(),$=C(+e.rows),D=B(()=>["plain","underlined"].includes(e.variant));be(()=>{e.autoGrow||($.value=+e.rows)});function b(){e.autoGrow&&O(()=>{if(!x.value||!_.value)return;const n=getComputedStyle(x.value),m=getComputedStyle(_.value.$el),u=parseFloat(n.getPropertyValue("--v-field-padding-top"))+parseFloat(n.getPropertyValue("--v-input-padding-top"))+parseFloat(n.getPropertyValue("--v-field-padding-bottom")),h=x.value.scrollHeight,F=parseFloat(n.lineHeight),U=Math.max(parseFloat(e.rows)*F+u,parseFloat(m.getPropertyValue("--v-input-control-height"))),H=parseFloat(e.maxRows)*F+u||1/0,V=Pe(h??0,U,H);$.value=Math.floor((V-u)/F),K.value=Fe(V)})}ke(b),I(r,b),I(()=>e.rows,b),I(()=>e.maxRows,b),I(()=>e.density,b);let g;return I(x,n=>{n?(g=new ResizeObserver(b),g.observe(x.value)):g==null||g.disconnect()}),Ce(()=>{g==null||g.disconnect()}),Ie(()=>{const n=!!(c.counter||e.counter||e.counterValue),m=!!(n||c.details),[u,h]=_e(i),{modelValue:F,...U}=X.filterProps(e),H=Ae(e);return t(X,z({ref:L,modelValue:r.value,"onUpdate:modelValue":V=>r.value=V,class:["v-textarea v-text-field",{"v-textarea--prefixed":e.prefix,"v-textarea--suffixed":e.suffix,"v-text-field--prefixed":e.prefix,"v-text-field--suffixed":e.suffix,"v-textarea--auto-grow":e.autoGrow,"v-textarea--no-resize":e.noResize||e.autoGrow,"v-input--plain-underlined":D.value},e.class],style:e.style},u,U,{centerAffix:$.value===1&&!D.value,focused:o.value}),{...c,default:V=>{let{id:P,isDisabled:Q,isDirty:W,isReadonly:ve,isValid:he}=V;return t(De,z({ref:_,style:{"--v-textarea-control-height":K.value},onClick:ue,onMousedown:ce,"onClick:clear":fe,"onClick:prependInner":e["onClick:prependInner"],"onClick:appendInner":e["onClick:appendInner"]},H,{id:P.value,active:de.value||W.value,centerAffix:$.value===1&&!D.value,dirty:W.value||e.dirty,disabled:Q.value,focused:o.value,error:he.value===!1}),{...c,default:pe=>{let{props:{class:Y,...J}}=pe;return t(E,null,[e.prefix&&t("span",{class:"v-text-field__prefix"},[e.prefix]),N(t("textarea",z({ref:R,class:Y,value:r.value,onInput:me,autofocus:e.autofocus,readonly:ve.value,disabled:Q.value,placeholder:e.placeholder,rows:e.rows,name:e.name,onFocus:A,onBlur:s},J,h),null),[[le("intersect"),{handler:se},null,{once:!0}]]),e.autoGrow&&N(t("textarea",{class:[Y,"v-textarea__sizer"],id:`${J.id}-sizer`,"onUpdate:modelValue":ge=>r.value=ge,ref:x,readonly:!0,"aria-hidden":"true"},null),[[Re,r.value]]),e.suffix&&t("span",{class:"v-text-field__suffix"},[e.suffix])])}})},details:m?V=>{var P;return t(E,null,[(P=c.details)==null?void 0:P.call(c,V),n&&t(E,null,[t("span",null,null),t(Ue,{active:e.persistentCounter||o.value,value:re.value,max:ie.value},c.counter)])])}:void 0})}),Me({},L,_,R)}});function Qe(){return"Parte"}const We={components:{oficialesRow:ne},data:e=>({mask:"##/##/#### ##:##",valid:!1,route:"api/partes",title:Qe(),editedIndex:-1,editedItem:{},defaultItem:{},clasificaciones:[]}),watch:{dialog(e){e||this.$close()}},created(){this.$initialize(),this.$getListForSelect("clasificaciones"),this.editedItem.fecha_parte=this.editedIndex==-1?this.getDateTimeNow():this.editedItem.fecha_parte},methods:{clear(){let e=this;e.valid=!0,e.$resetValidation(),e.$nextTick(()=>{e.editedItem=Object.assign({},e.defaultItem),e.editedIndex=-1}),this.$refs.oficiales.reset()},resetValidation(){this.$refs.form.resetValidation()},async save(){if(this.$refs.oficiales.personas.length<=0){Swal.alertError("Error al Registrar Nuevo Parte","Debe incluir oficiales");return}Swal.fire({title:"Registrar nuevo parte ",text:"¿Confirmar registro de parte?",icon:"question",showCancelButton:!0,confirmButtonColor:"#3085d6",cancelButtonColor:"#d33",confirmButtonText:"Confirmar",cancelButtonText:"Cancelar"}).then(e=>{if(e.isConfirmed){Swal.alertGetInfo("Registrando información");let l={parte:this.editedItem,oficiales:this.$refs.oficiales.personas};Se.post(this.route,l).then(i=>{let d=i.data.data.parte.numero+"/"+i.data.data.parte.anio;Swal.close(),Swal.fire({title:"Se ha registrado el parte "+d,text:"Presione Imprimir para ver mas información o Aceptar para continuar",icon:"success",showCancelButton:!0,confirmButtonColor:"#3085d6",cancelButtonColor:"#d33",confirmButtonText:"Aceptar",cancelButtonText:"Imprimir"}).then(c=>{c.isConfirmed?this.clear():this.getPdf("denuncia",i.data.id)})}).catch(i=>{Swal.close(),console.log(i.response.data.message);let d="Se ha producido un error. "+i.response.data.message;Swal.alertError("Error al Registrar Nuevo Parte",d)})}})},setDate(e,l){if(e)return;let i=this.getDateTimeNow();switch(l){case"parte":this.editedItem.fecha_parte=i;break;case"hecho":this.editedItem.fecha_hecho=i;break}}},mounted(){console.log("Componente partes creado")}},Ye=k("br",null,null,-1),Je=k("br",null,null,-1),Xe=k("br",null,null,-1);function Ze(e,l,i,d,c,r){const o=ne,f=le("mask");return T(),j(p,null,{default:a(()=>[t(v,{cols:"12"},{default:a(()=>[t(Z,{title:"Nuevo Parte"},{default:a(()=>[t(q),t(ee,null,{default:a(()=>[t(He,{ref:"form",modelValue:e.valid,"onUpdate:modelValue":l[9]||(l[9]=s=>e.valid=s),"lazy-validation":""},{default:a(()=>[t(p,null,{default:a(()=>[t(v,{cols:"12",md:"12"},{default:a(()=>[t(oe,{modelValue:e.editedItem.clasificacion_id,"onUpdate:modelValue":l[0]||(l[0]=s=>e.editedItem.clasificacion_id=s),items:e.clasificaciones,"item-value":"id","item-title":"detalle",label:"Clasificacion del parte",rules:[e.$rulesRequerido],"return-object":""},null,8,["modelValue","items","rules"])]),_:1})]),_:1}),t(p,null,{default:a(()=>[t(v,{cols:"12",md:"6"},{default:a(()=>[N(t(G,{modelValue:e.editedItem.fecha_parte,"onUpdate:modelValue":l[1]||(l[1]=s=>e.editedItem.fecha_parte=s),label:"Fecha y Hora del Parte",rules:[e.$rulesRequerido,e.$rulesFechaMenorAHoy],placeholder:"dd/mm/yyyy hh:mm",maxLength:"16",onFocus:l[2]||(l[2]=s=>r.setDate(e.editedItem.fecha_parte,"parte"))},null,8,["modelValue","rules"]),[[f,e.mask]])]),_:1}),t(v,{cols:"12",md:"6"},{default:a(()=>[N(t(G,{modelValue:e.editedItem.fecha_hecho,"onUpdate:modelValue":l[3]||(l[3]=s=>e.editedItem.fecha_hecho=s),label:"Fecha y Hora del Hecho",rules:[e.$rulesRequerido,e.$rulesFechaMenorAHoy],placeholder:"dd/mm/yyyy hh:mm",maxLength:"16",onFocus:l[4]||(l[4]=s=>r.setDate(e.editedItem.fecha_hecho,"hecho"))},null,8,["modelValue","rules"]),[[f,e.mask]])]),_:1})]),_:1}),t(p,null,{default:a(()=>[t(v,{md:"12",sm:"12",cols:"12"},{default:a(()=>[t(te,{modelValue:e.editedItem.relato,"onUpdate:modelValue":l[5]||(l[5]=s=>e.editedItem.relato=s),counter:"",label:"Relato Sucinto","auto-grow":"",placeholder:"Ingrese la descripción del hecho",rules:[e.$rulesRequerido]},null,8,["modelValue","rules"])]),_:1})]),_:1}),t(p,{dense:""},{default:a(()=>[t(v,{md:"12",sm:"12",cols:"12"},{default:a(()=>[t(te,{modelValue:e.editedItem.observaciones,"onUpdate:modelValue":l[6]||(l[6]=s=>e.editedItem.observaciones=s),counter:"",label:"Observaciones","auto-grow":""},null,8,["modelValue"])]),_:1})]),_:1}),t(q),Ye,t(p,{class:"justify-center"},{default:a(()=>[t(v,{cols:"12",md:"12",sm:"12"},{default:a(()=>[t(Z,{title:"Oficiales Intervinientes"},{default:a(()=>[t(ee,null,{default:a(()=>[t(o,{ref:"oficiales",modulo:"oficiales"},null,512)]),_:1})]),_:1})]),_:1})]),_:1}),Je,t(q),Xe,t(p,null,{default:a(()=>[t(v,{cols:"12",class:"d-flex flex-wrap gap-4"},{default:a(()=>[t(y,{disabled:!e.valid,onClick:l[7]||(l[7]=s=>r.save())},{default:a(()=>[w("Guardar Cambios")]),_:1},8,["disabled"]),t(y,{color:"secondary",variant:"outlined",onClick:l[8]||(l[8]=s=>r.clear()),type:"reset"},{default:a(()=>[w(" Reset ")]),_:1}),t(y,{color:"error",class:"d-flex flex-wrap gap-4"},{default:a(()=>[w(" Cancelar ")]),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1})}const wt=ae(We,[["render",Ze]]);export{wt as default};
