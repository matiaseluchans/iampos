import{V as M,a as P,b as $}from"./VDataTable-_vW1TeBv.js";import{V as D,a as F}from"./VDialog-DbL5-Iim.js";import{V as G}from"./VAvatar-3pXkIHTf.js";import{V as J}from"./VImg-CcznBU0J.js";import{r as i,Y as K,c as w,b as e,w as l,u as o,q as S,a1 as Y,o as _,a as c,n as q,e as H,t as V,B as d,V as A,W as x,l as W}from"./main-C_Te8aZH.js";import{a as I,c as h,V as Q,e as E}from"./VCard-BP6mQR4Q.js";import{V as X,a as m}from"./VRow-BZgZDGuK.js";import{V as v}from"./VTextField-CDhod4HM.js";import{V as g}from"./VSpacer-DtQJVvy9.js";import"./forwardRefs-C-GTDzx5.js";import"./VList-9vjn5KEf.js";import"./VOverlay-CNcpFCPJ.js";import"./easing-DY7PVvcf.js";import"./lazy-Se7h04Ci.js";import"./index-D7wZTw18.js";import"./ssrBoot-BccHZXhj.js";import"./VDivider-ByZzaXTt.js";import"./VTable-BTPIonOZ.js";/* empty css              */const Z={class:"d-flex align-center"},ee={key:1,class:"text-sm"},ae={class:"d-flex flex-column ms-3"},le={class:"d-block font-weight-medium text-high-emphasis text-truncate"},te={class:"d-flex gap-1"},se=c("span",{class:"headline"},"Edit Item",-1),De={__name:"datatables",setup(oe){const O=[{fullName:"Edwina Ebsworth",post:"Human Resources Assistant",email:"eebsworth2m@sbwire.com",startDate:"09/27/2018",salary:"19586.23",age:"27",status:1},{fullName:"Korrie OCrevy",post:"Nuclear Power Engineer",email:"kocrevy0@thetimes.co.uk	",startDate:"09/23/2016",salary:"23896.35",age:"61",status:2},{fullName:"Eileen Diehn",post:"Environmental Specialist",email:"ediehn6@163.com",startDate:"10/15/2017",salary:"18991.67",age:"59",status:3},{fullName:"Eileen Diehn",post:"Sales Representative",email:"ediehn6@163.com",startDate:"10/15/2017",salary:"18991.67",age:"59",status:4},{fullName:"Stella Ganderton",post:"Operator",email:"eebsworth2m@sbwire.com",startDate:"09/27/2018",salary:"19586.23",age:"27",status:5}],f=i(!1),p=i(!1),y=i({responsiveId:"",id:-1,avatar:"",fullName:"",post:"",email:"",city:"",startDate:"",salary:-1,age:"",experience:"",status:-1}),t=i(y.value),n=i(-1),u=i([]),U=[{text:"Current",value:1},{text:"Professional",value:2},{text:"Rejected",value:3},{text:"Resigned",value:4},{text:"Applied",value:5}],R=[{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"SALARY",key:"salary"},{title:"AGE",key:"age"},{title:"STATUS",key:"status"},{title:"ACTIONS",key:"actions"}],C=r=>r===1?{color:"primary",text:"Current"}:r===2?{color:"success",text:"Professional"}:r===3?{color:"error",text:"Rejected"}:r===4?{color:"warning",text:"Resigned"}:{color:"info",text:"Applied"},T=r=>{n.value=u.value.indexOf(r),t.value={...r},f.value=!0},B=r=>{n.value=u.value.indexOf(r),t.value={...r},p.value=!0},k=()=>{f.value=!1,n.value=-1,t.value={...y.value}},b=()=>{p.value=!1,n.value=-1,t.value={...y.value}},z=()=>{n.value>-1?Object.assign(u.value[n.value],t.value):u.value.push(t.value),k()},j=()=>{u.value.splice(n.value,1),b()};return K(()=>{u.value=JSON.parse(JSON.stringify(O))}),(r,s)=>{const N=W("IconBtn");return _(),w(Y,null,[e(M,{headers:R,items:o(u),"items-per-page":5,class:"text-no-wrap"},{"item.fullName":l(({item:a})=>[c("div",Z,[e(G,{size:"32",color:a.avatar?"":"primary",class:q(a.avatar?"":"v-avatar-light-bg primary--text"),variant:a.avatar?void 0:"tonal"},{default:l(()=>[a.avatar?(_(),H(J,{key:0,src:a.avatar},null,8,["src"])):(_(),w("span",ee,V(a.fullName),1))]),_:2},1032,["color","class","variant"]),c("div",ae,[c("span",le,V(a.fullName),1),c("small",null,V(a.post),1)])])]),"item.status":l(({item:a})=>[e(P,{color:C(a.status).color,density:"comfortable"},{default:l(()=>[d(V(C(a.status).text),1)]),_:2},1032,["color"])]),"item.actions":l(({item:a})=>[c("div",te,[e(N,{size:"small",onClick:L=>T(a)},{default:l(()=>[e(A,{icon:"ri-pencil-line"})]),_:2},1032,["onClick"]),e(N,{size:"small",onClick:L=>B(a)},{default:l(()=>[e(A,{icon:"ri-delete-bin-line"})]),_:2},1032,["onClick"])])]),_:1},8,["items"]),e(D,{modelValue:o(f),"onUpdate:modelValue":s[6]||(s[6]=a=>S(f)?f.value=a:null),"max-width":"600px"},{default:l(()=>[e(I,null,{default:l(()=>[e(h,null,{default:l(()=>[se]),_:1}),e(Q,null,{default:l(()=>[e(F,null,{default:l(()=>[e(X,null,{default:l(()=>[e(m,{cols:"12",sm:"6",md:"4"},{default:l(()=>[e(v,{modelValue:o(t).fullName,"onUpdate:modelValue":s[0]||(s[0]=a=>o(t).fullName=a),label:"User name"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12",sm:"6",md:"4"},{default:l(()=>[e(v,{modelValue:o(t).email,"onUpdate:modelValue":s[1]||(s[1]=a=>o(t).email=a),label:"Email"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12",sm:"6",md:"4"},{default:l(()=>[e(v,{modelValue:o(t).salary,"onUpdate:modelValue":s[2]||(s[2]=a=>o(t).salary=a),label:"Salary",prefix:"$",type:"number"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12",sm:"6",md:"4"},{default:l(()=>[e(v,{modelValue:o(t).age,"onUpdate:modelValue":s[3]||(s[3]=a=>o(t).age=a),label:"Age",type:"number"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12",sm:"6",md:"4"},{default:l(()=>[e(v,{modelValue:o(t).startDate,"onUpdate:modelValue":s[4]||(s[4]=a=>o(t).startDate=a),label:"Date"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12",sm:"6",md:"4"},{default:l(()=>[e($,{modelValue:o(t).status,"onUpdate:modelValue":s[5]||(s[5]=a=>o(t).status=a),items:U,"item-title":"text","item-value":"value",label:"Status",variant:"outlined"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1}),e(E,null,{default:l(()=>[e(g),e(x,{color:"error",variant:"outlined",onClick:k},{default:l(()=>[d(" Cancel ")]),_:1}),e(x,{color:"success",variant:"elevated",onClick:z},{default:l(()=>[d(" Save ")]),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue"]),e(D,{modelValue:o(p),"onUpdate:modelValue":s[7]||(s[7]=a=>S(p)?p.value=a:null),"max-width":"500px"},{default:l(()=>[e(I,null,{default:l(()=>[e(h,null,{default:l(()=>[d(" Are you sure you want to delete this item? ")]),_:1}),e(E,null,{default:l(()=>[e(g),e(x,{color:"error",variant:"outlined",onClick:b},{default:l(()=>[d(" Cancel ")]),_:1}),e(x,{color:"success",variant:"elevated",onClick:j},{default:l(()=>[d(" OK ")]),_:1}),e(g)]),_:1})]),_:1})]),_:1},8,["modelValue"])],64)}}};export{De as default};
