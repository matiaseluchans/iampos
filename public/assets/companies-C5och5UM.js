import{_ as $}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{a as n,V as m}from"./VCard-CqL6BpWF.js";import{a as v,V as I}from"./VDialog-C3fGDHOL.js";import{V as D,a as B}from"./VDataTable-CI5BAWbA.js";import{V as b,a as i}from"./VRow-CckguHJp.js";import{V as k}from"./VTextField-DYX6eAz5.js";import{V as T}from"./VAutocomplete-HsoC3pcZ.js";import{e as C,w as a,o as u,b as t,a as f,t as p,a3 as g,c as N,W as o,U as w,V as c,B as y,l as A}from"./main-B-bnVr7i.js";import{V as H,a as S,b as U,c as z}from"./VToolbarItems-CuXhdGtL.js";import{V as R}from"./VSpacer-BmftHtVG.js";import{V as L}from"./VForm-DMnCxvLI.js";import"./VAvatar-Bs8XwlKy.js";import"./VImg-DONpqZE8.js";/* empty css              */import"./VOverlay-DX_uWgIC.js";import"./easing-DY7PVvcf.js";import"./lazy-CVvRdrmx.js";import"./forwardRefs-C-GTDzx5.js";import"./VList-dE3g2XVB.js";import"./index-BNTuomhI.js";import"./ssrBoot-txvmje94.js";import"./VDivider-sx5WAEol.js";import"./VTable-D6sNb8VH.js";function M(){return"Companias"}const E={data:e=>({dessertName:"",valid:!0,nowDate:new Date().toISOString().slice(0,10),_method:"PUT",autoGrow:!0,rows:1,title:M(),dessertActivo:"",route:"companies",menu:!1,modal:!1,menu2:!1,dialog:!1,isActive:{value:!1},snackbar:!1,visible:!0,text:"Registro Insertado",color:"success",timeout:4e3,rules:[s=>s.length<=500||"Max 500 caracteres"],search:"",vista:!1,users:!1,headers:[{title:"Id",align:"start",sortable:!1,key:"id"},{title:"Nombre",filterable:!0,key:"name"},{title:"Direccion",key:"address"},{title:"Creado",key:"created_at"},{title:"Actualizado",key:"updated_at"},{title:"Acciones",key:"actions",value:"actions",sortable:!1}],desserts:[],editedIndex:-1,editedItem:{name:"",id:""},defaultItem:{nombre:"",id:""},filters:{id:"",name:"",address:"",created_at:"",updated_at:""},filterKey:[],selectedHeaders:[]}),computed:{formTitle(){return this.editedIndex===-1?"Registrar "+this.title:"Editar "+this.title},filteredData(){return this.$filteredData().data!==void 0?this.$filteredData().data:this.$filteredData()},showHeaders(){return this.headers.filter(e=>this.selectedHeaders.includes(e))},filteredDesserts(){let e=[];return this.dessertName&&e.push(this.filterDessertName),e.length>0?this.desserts.filter(s=>e.every(V=>V(s))):this.desserts}},watch:{dialog(e){e||this.$close()}},created(){this.$initialize(),this.selectedHeaders=this.headers},methods:{openSweetAlert(){Swal.mixin({customClass:{container:"my-swal-container"}}).fire({title:"SweetAlert2",text:"Este es un mensaje de SweetAlert2",icon:"info"})},filterDessertName(e){return e.name.toLowerCase().includes(this.dessertName.toLowerCase())},filterByNombre(e){return this.$filterBy(e,"nombre")},filterByActivo(e){return this.$filterBy(e,"activo")}},mounted(){console.log("Componente "+this.title+" creado")}},j={key:1,class:"grey--text caption"},q={class:"d-flex gap-1"},F=["innerHTML"];function G(e,s,V,P,K,d){const h=A("IconBtn");return u(),C(n,{title:"Administración de "+e.title},{default:a(()=>[t(m,{class:"d-flex"},{default:a(()=>[t(v,{id:"crud",fluid:"",tag:"section"},{default:a(()=>[t(D,{headers:d.showHeaders,items:d.filteredDesserts,search:e.search,class:"text-no-wrap"},{top:a(()=>[t(n,{flat:"",color:"white"},{default:a(()=>[t(m,null,{default:a(()=>[t(b,null,{default:a(()=>[t(i,{sm:"6",class:"pl-0 pt-20 py-2"},{default:a(()=>[t(k,{modelValue:e.search,"onUpdate:modelValue":s[0]||(s[0]=l=>e.search=l),"append-icon":"ri-search-line",label:"Busqueda de "+e.title},null,8,["modelValue","label"])]),_:1}),t(i,{sm:"2"}),t(i,{class:"pt-20 py-2",sm:"3"},{default:a(()=>[t(T,{modelValue:e.selectedHeaders,"onUpdate:modelValue":s[1]||(s[1]=l=>e.selectedHeaders=l),items:e.headers,label:"Columnas Visibles",multiple:"","return-object":""},{selection:a(({item:l,index:r})=>[r<2?(u(),C(B,{key:0},{default:a(()=>[f("span",null,p(l.title),1)]),_:2},1024)):g("",!0),r===2?(u(),N("span",j,"(otras "+p(e.selectedHeaders.length-2)+"+)",1)):g("",!0)]),_:1},8,["modelValue","items"])]),_:1}),t(i,{sm:"1",class:"pt-20 py-2"},{default:a(()=>[t(I,{modelValue:e.dialog,"onUpdate:modelValue":s[6]||(s[6]=l=>e.dialog=l),"max-width":"50%"},{activator:a(({props:l})=>[t(o,w(l,{color:e.$cv("principal"),size:"x-large",title:"Registrar nueva "+e.title}),{default:a(()=>[t(c,{size:"large",icon:"ri-add-circle-line"})]),_:2},1040,["color","title"])]),default:a(()=>[t(n,null,{default:a(()=>[t(H,{color:e.$cv("principal")},{default:a(()=>[t(o,{icon:"ri-close-line",color:"white",onClick:s[2]||(s[2]=l=>e.dialog=!1)}),t(S,null,{default:a(()=>[y(p(d.formTitle),1)]),_:1}),t(R),t(U,null,{default:a(()=>[t(o,{text:"Guardar",onClick:s[3]||(s[3]=l=>e.$save()),variant:"text"})]),_:1})]),_:1},8,["color"]),t(L,{ref:"form",modelValue:e.valid,"onUpdate:modelValue":s[5]||(s[5]=l=>e.valid=l),"lazy-validation":""},{default:a(()=>[t(m,null,{default:a(()=>[t(v,null,{default:a(()=>[t(b,null,{default:a(()=>[t(i,{cols:"12",md:"12",sm:"12"},{default:a(()=>[t(k,{modelValue:e.editedItem.name,"onUpdate:modelValue":s[4]||(s[4]=l=>e.editedItem.name=l),label:"Compañia seguro",disabled:e.vista,rules:[e.$rulesRequerido,e.$rulesAlfaNum,e.$rulesMax500]},null,8,["modelValue","disabled","rules"])]),_:1})]),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue"])]),_:1})]),_:1},8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1})]),"item.actions":a(({item:l})=>[f("div",q,[t(h,{size:"small",onClick:r=>{e.vista=!1,e.$editItem(l.id)}},{default:a(()=>[t(c,{icon:"ri-pencil-line"})]),_:2},1032,["onClick"]),t(h,{size:"small",onClick:r=>{e.vista=!1,e.$deleteItem(l.id,l.name)}},{default:a(()=>[t(c,{icon:"ri-delete-bin-line"})]),_:2},1032,["onClick"])])]),_:1},8,["headers","items","search"]),t(z,{modelValue:e.snackbar,"onUpdate:modelValue":s[8]||(s[8]=l=>e.snackbar=l),bottom:!0,color:e.color,timeout:e.timeout},{action:a(({attrs:l})=>[t(o,w({dark:"",text:""},l,{onClick:s[7]||(s[7]=r=>e.snackbar=!1)}),{default:a(()=>[y(" Cerrar ")]),_:2},1040)]),default:a(()=>[f("div",{innerHTML:e.text},null,8,F)]),_:1},8,["modelValue","color","timeout"])]),_:1})]),_:1})]),_:1},8,["title"])}const ce=$(E,[["render",G]]);export{ce as default};
