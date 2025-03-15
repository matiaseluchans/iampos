import{m as Q,u as Y,V as E}from"./VOverlay-DX_uWgIC.js";import{f as $}from"./forwardRefs-C-GTDzx5.js";import{D as _,F as q,ay as W,G,az as O,J as U,a8 as X,K as w,a9 as Z,P as p,aA as ee,ad as z,aB as te,M as J,r as L,y as D,k as A,Y as ae,aC as ne,Q as B,b as a,aD as oe,a4 as le,aE as C,U as F,aF as se,a0 as ie,E as H,H as j,aG as re,aH as ue,L as ce,v as N,aI as me,aJ as ve,aj as de,z as M,ap as K,ar as S}from"./main-B-bnVr7i.js";import{V as fe}from"./index-BNTuomhI.js";import{V as be}from"./VImg-DONpqZE8.js";function ge(e){const o=D(e);let t=-1;function n(){clearInterval(t)}function l(){n(),ie(()=>o.value=e)}function v(u){const d=u?getComputedStyle(u):{transitionDuration:.2},c=parseFloat(d.transitionDuration)*1e3||200;if(n(),o.value<=0)return;const f=performance.now();t=window.setInterval(()=>{const i=performance.now()-f+c;o.value=Math.max(e-i,0),o.value<=0&&n()},c)}return se(n),{clear:n,time:o,start:v,reset:l}}const ye=_({multiLine:Boolean,text:String,timer:[Boolean,String],timeout:{type:[Number,String],default:5e3},vertical:Boolean,...q({location:"bottom"}),...W(),...G(),...O(),...U(),...X(Q({transition:"v-snackbar-transition"}),["persistent","noClickAnimation","scrim","scrollStrategy"])},"VSnackbar"),Be=w()({name:"VSnackbar",props:ye(),emits:{"update:modelValue":e=>!0},setup(e,o){let{slots:t}=o;const n=Z(e,"modelValue"),{locationStyles:l}=p(e),{positionClasses:v}=ee(e),{scopeId:u}=Y(),{themeClasses:d}=z(e),{colorClasses:c,colorStyles:f,variantClasses:i}=te(e),{roundedClasses:b}=J(e),s=ge(Number(e.timeout)),g=L(),V=L(),y=D(!1);A(n,r),A(()=>e.timeout,r),ae(()=>{n.value&&r()});let m=-1;function r(){s.reset(),window.clearTimeout(m);const k=Number(e.timeout);if(!n.value||k===-1)return;const I=ne(V.value);s.start(I),m=window.setTimeout(()=>{n.value=!1},k)}function h(){s.reset(),window.clearTimeout(m)}function x(){y.value=!0,h()}function T(){y.value=!1,r()}return B(()=>{const k=E.filterProps(e),I=!!(t.default||t.text||e.text);return a(E,F({ref:g,class:["v-snackbar",{"v-snackbar--active":n.value,"v-snackbar--multi-line":e.multiLine&&!e.vertical,"v-snackbar--timer":!!e.timer,"v-snackbar--vertical":e.vertical},v.value,e.class],style:e.style},k,{modelValue:n.value,"onUpdate:modelValue":P=>n.value=P,contentProps:F({class:["v-snackbar__wrapper",d.value,c.value,b.value,i.value],style:[l.value,f.value],onPointerenter:x,onPointerleave:T},k.contentProps),persistent:!0,noClickAnimation:!0,scrim:!1,scrollStrategy:"none",_disableGlobalStack:!0},u),{default:()=>{var P,R;return[oe(!1,"v-snackbar"),e.timer&&a("div",{key:"timer",class:"v-snackbar__timer"},[a(le,{ref:V,active:!y.value,color:typeof e.timer=="string"?e.timer:"info",max:e.timeout,"model-value":s.time.value},null)]),I&&a("div",{key:"content",class:"v-snackbar__content",role:"status","aria-live":"polite"},[((P=t.text)==null?void 0:P.call(t))??e.text,(R=t.default)==null?void 0:R.call(t)]),t.actions&&a(C,{defaults:{VBtn:{variant:"text",ripple:!1,slim:!0}}},{default:()=>[a("div",{class:"v-snackbar__actions"},[t.actions()])]})]},activator:t.activator})}),$({},g)}}),ke=_({text:String,...H(),...j()},"VToolbarTitle"),Ve=w()({name:"VToolbarTitle",props:ke(),setup(e,o){let{slots:t}=o;return B(()=>{const n=!!(t.default||t.text||e.text);return a(e.tag,{class:["v-toolbar-title",e.class],style:e.style},{default:()=>{var l;return[n&&a("div",{class:"v-toolbar-title__placeholder"},[t.text?t.text():e.text,(l=t.default)==null?void 0:l.call(t)])]}})}),{}}}),he=[null,"prominent","default","comfortable","compact"],xe=_({absolute:Boolean,collapse:Boolean,color:String,density:{type:String,default:"default",validator:e=>he.includes(e)},extended:Boolean,extensionHeight:{type:[Number,String],default:48},flat:Boolean,floating:Boolean,height:{type:[Number,String],default:64},image:String,title:String,...re(),...H(),...ue(),...G(),...j({tag:"header"}),...U()},"VToolbar"),Ie=w()({name:"VToolbar",props:xe(),setup(e,o){var g;let{slots:t}=o;const{backgroundColorClasses:n,backgroundColorStyles:l}=ce(N(e,"color")),{borderClasses:v}=me(e),{elevationClasses:u}=ve(e),{roundedClasses:d}=J(e),{themeClasses:c}=z(e),{rtlClasses:f}=de(),i=D(!!(e.extended||(g=t.extension)!=null&&g.call(t))),b=M(()=>parseInt(Number(e.height)+(e.density==="prominent"?Number(e.height):0)-(e.density==="comfortable"?8:0)-(e.density==="compact"?16:0),10)),s=M(()=>i.value?parseInt(Number(e.extensionHeight)+(e.density==="prominent"?Number(e.extensionHeight):0)-(e.density==="comfortable"?4:0)-(e.density==="compact"?8:0),10):0);return K({VBtn:{variant:"text"}}),B(()=>{var r;const V=!!(e.title||t.title),y=!!(t.image||e.image),m=(r=t.extension)==null?void 0:r.call(t);return i.value=!!(e.extended||m),a(e.tag,{class:["v-toolbar",{"v-toolbar--absolute":e.absolute,"v-toolbar--collapse":e.collapse,"v-toolbar--flat":e.flat,"v-toolbar--floating":e.floating,[`v-toolbar--density-${e.density}`]:!0},n.value,v.value,u.value,d.value,c.value,f.value,e.class],style:[l.value,e.style]},{default:()=>[y&&a("div",{key:"image",class:"v-toolbar__image"},[t.image?a(C,{key:"image-defaults",disabled:!e.image,defaults:{VImg:{cover:!0,src:e.image}}},t.image):a(be,{key:"image-img",cover:!0,src:e.image},null)]),a(C,{defaults:{VTabs:{height:S(b.value)}}},{default:()=>{var h,x,T;return[a("div",{class:"v-toolbar__content",style:{height:S(b.value)}},[t.prepend&&a("div",{class:"v-toolbar__prepend"},[(h=t.prepend)==null?void 0:h.call(t)]),V&&a(Ve,{key:"title",text:e.title},{text:t.title}),(x=t.default)==null?void 0:x.call(t),t.append&&a("div",{class:"v-toolbar__append"},[(T=t.append)==null?void 0:T.call(t)])])]}}),a(C,{defaults:{VTabs:{height:S(s.value)}}},{default:()=>[a(fe,null,{default:()=>[i.value&&a("div",{class:"v-toolbar__extension",style:{height:S(s.value)}},[m])]})]})]})}),{contentHeight:b,extensionHeight:s}}}),Te=_({...H(),...O({variant:"text"})},"VToolbarItems"),Ne=w()({name:"VToolbarItems",props:Te(),setup(e,o){let{slots:t}=o;return K({VBtn:{color:N(e,"color"),height:"inherit",variant:N(e,"variant")}}),B(()=>{var n;return a("div",{class:["v-toolbar-items",e.class],style:e.style},[(n=t.default)==null?void 0:n.call(t)])}),{}}});export{Ie as V,Ve as a,Ne as b,Be as c};
