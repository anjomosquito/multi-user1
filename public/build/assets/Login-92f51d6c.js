import{T as x,o as i,s as c,e as l,d as t,u as e,Z as k,b as w,t as y,j as u,f as a,n as f,m,p as h,k as v}from"./app-de19c82f.js";import{_ as V}from"./Checkbox-5cc1c655.js";import{_ as B}from"./GuestLayout-736a90f2.js";import{_ as p,a as g,b as _}from"./TextInput-6b226b5f.js";import{_ as E}from"./PrimaryButton-cb5f9ce2.js";import"./ApplicationLogo-32c770cc.js";import"./_plugin-vue_export-helper-c27b6911.js";const $={key:0,class:"mb-4 font-medium text-sm text-green-600"},C=a("div",{class:"text-center text-2xl font-bold mb-4 text-black rounded"}," USER LOGIN ",-1),L={class:"mt-4"},N={class:"block mt-4"},R={class:"flex items-center"},S=a("span",{class:"ml-2 text-sm text-gray-600 dark:text-gray-400"},"Remember me",-1),U={class:"flex justify-center mt-4"},j={class:"flex items-center justify-between mt-4"},G={__name:"Login",props:{canResetPassword:Boolean,status:String},setup(n){const s=x({email:"",password:"",remember:!1}),b=()=>{s.post(route("login"),{onFinish:()=>s.reset("password")})};return(d,o)=>(i(),c(B,null,{default:l(()=>[t(e(k),{title:"Log in"}),n.status?(i(),w("div",$,y(n.status),1)):u("",!0),a("form",{onSubmit:v(b,["prevent"])},[a("div",null,[C,t(p,{for:"email",value:"Email"}),t(g,{id:"email",type:"email",class:"mt-1 block w-full",modelValue:e(s).email,"onUpdate:modelValue":o[0]||(o[0]=r=>e(s).email=r),required:"",autofocus:"",autocomplete:"username"},null,8,["modelValue"]),t(_,{class:"mt-2",message:e(s).errors.email},null,8,["message"])]),a("div",L,[t(p,{for:"password",value:"Password"}),t(g,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:e(s).password,"onUpdate:modelValue":o[1]||(o[1]=r=>e(s).password=r),required:"",autocomplete:"current-password"},null,8,["modelValue"]),t(_,{class:"mt-2",message:e(s).errors.password},null,8,["message"])]),a("div",N,[a("label",R,[t(V,{name:"remember",checked:e(s).remember,"onUpdate:checked":o[2]||(o[2]=r=>e(s).remember=r)},null,8,["checked"]),S])]),a("div",U,[n.canResetPassword?(i(),c(e(f),{key:0,href:d.route("password.request"),class:"underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"},{default:l(()=>[m(" Forgot your password? ")]),_:1},8,["href"])):u("",!0)]),a("div",j,[t(e(f),{href:d.route("admin.login"),class:"flex-start px-4 py-2 bg-[#B5C99A] hover:bg-[#D2E0BE] text-black rounded"},{default:l(()=>[m(" Log in as Admin ")]),_:1},8,["href"]),m("     "),t(E,{class:h(["px-4 py-2 ml-4 bg-[#B5C99A] hover:bg-[#D2E0BE] text-black rounded",{"opacity-25":e(s).processing}]),disabled:e(s).processing},{default:l(()=>[m(" Log in ")]),_:1},8,["class","disabled"])])],32)]),_:1}))}};export{G as default};