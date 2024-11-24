import{r as u,T as f,o as y,c as w,a as t,w as a,b as s,p as h,h as c,u as o,y as x,n as g}from"./app-b78d6429.js";import{a as m,_ as k}from"./DangerButton-a773f028.js";import{_ as v,a as b,b as C}from"./TextInput-9d2e7e65.js";import{_ as D}from"./Modal-6605ecd7.js";const V={class:"space-y-6"},$=s("header",null,[s("h2",{class:"text-lg font-medium text-gray-900 dark:text-gray-100"},"Delete Account"),s("p",{class:"mt-1 text-sm text-gray-600 dark:text-gray-400"}," Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain. ")],-1),U={class:"p-6"},A=s("h2",{class:"text-lg font-medium text-gray-900 dark:text-gray-100"}," Are you sure you want to delete your account? ",-1),B=s("p",{class:"mt-1 text-sm text-gray-600 dark:text-gray-400"}," Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account. ",-1),N={class:"mt-6"},P={class:"mt-6 flex justify-end"},S={__name:"DeleteUserForm",setup(T){const r=u(!1),l=u(null),e=f({password:""}),_=()=>{r.value=!0,h(()=>l.value.focus())},d=()=>{e.delete(route("profile.destroy"),{preserveScroll:!0,onSuccess:()=>n(),onError:()=>l.value.focus(),onFinish:()=>e.reset()})},n=()=>{r.value=!1,e.reset()};return(E,i)=>(y(),w("section",V,[$,t(m,{onClick:_},{default:a(()=>[c("Delete Account")]),_:1}),t(D,{show:r.value,onClose:n},{default:a(()=>[s("div",U,[A,B,s("div",N,[t(v,{for:"password",value:"Password",class:"sr-only"}),t(b,{id:"password",ref_key:"passwordInput",ref:l,modelValue:o(e).password,"onUpdate:modelValue":i[0]||(i[0]=p=>o(e).password=p),type:"password",class:"mt-1 block w-3/4",placeholder:"Password",onKeyup:x(d,["enter"])},null,8,["modelValue"]),t(C,{message:o(e).errors.password,class:"mt-2"},null,8,["message"])]),s("div",P,[t(k,{onClick:n},{default:a(()=>[c(" Cancel ")]),_:1}),t(m,{class:g(["ml-3",{"opacity-25":o(e).processing}]),disabled:o(e).processing,onClick:d},{default:a(()=>[c(" Delete Account ")]),_:1},8,["class","disabled"])])])]),_:1},8,["show"])]))}};export{S as default};