import{m as g,j as A,u as F,b as t,h as l}from"./VTextField-CDhod4HM.js";import{D as I,a8 as U,K as B,a9 as D,aa as R,z as j,Q as z,aL as K,b as u,U as r}from"./main-C_Te8aZH.js";const L=I({...g(),...U(A(),["inline"])},"VCheckbox"),Q=B()({name:"VCheckbox",inheritAttrs:!1,props:L(),emits:{"update:modelValue":e=>!0,"update:focused":e=>!0},setup(e,c){let{attrs:d,slots:o}=c;const s=D(e,"modelValue"),{isFocused:n,focus:i,blur:m}=F(e),b=R(),V=j(()=>e.id||`checkbox-${b}`);return z(()=>{const[p,k]=K(d),v=t.filterProps(e),f=l.filterProps(e);return u(t,r({class:["v-checkbox",e.class]},p,v,{modelValue:s.value,"onUpdate:modelValue":a=>s.value=a,id:V.value,focused:n.value,style:e.style}),{...o,default:a=>{let{id:h,messagesId:x,isDisabled:P,isReadonly:C}=a;return u(l,r(f,{id:h.value,"aria-describedby":x.value,disabled:P.value,readonly:C.value},k,{modelValue:s.value,"onUpdate:modelValue":y=>s.value=y,onFocus:i,onBlur:m}),o)}})}),{}}});export{Q as V};
