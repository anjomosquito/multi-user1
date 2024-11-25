import{r as c,T as _,o as m,b as i,f as a,d as e,u as r,e as w,C as y,k as g,m as v,j as x}from"./app-a2ecb7c5.js";import{a as l,b as n,_ as d}from"./TextInput-e4abe8d7.js";import{_ as V}from"./PrimaryButton-7496d6f1.js";const k=a("header",null,[a("h2",{class:"text-lg font-medium text-gray-900 dark:text-gray-100"},"Update Admin Password"),a("p",{class:"mt-1 text-sm text-gray-600 dark:text-gray-400"}," Ensure your account is using a long, random password to stay secure. ")],-1),b={class:"flex items-center gap-4"},P={key:0,class:"text-sm text-gray-600 dark:text-gray-400"},$={__name:"UpdatePasswordForm",setup(S){const u=c(null),p=c(null),s=_({current_password:"",password:"",password_confirmation:""}),f=()=>{s.put(route("admin.password.update"),{preserveScroll:!0,onSuccess:()=>s.reset(),onError:()=>{s.errors.password&&(s.reset("password","password_confirmation"),u.value.focus()),s.errors.current_password&&(s.reset("current_password"),p.value.focus())}})};return(C,o)=>(m(),i("section",null,[k,a("form",{onSubmit:g(f,["prevent"]),class:"mt-6 space-y-6"},[a("div",null,[e(d,{for:"current_password",value:"Current Password"}),e(l,{id:"current_password",ref_key:"currentPasswordInput",ref:p,modelValue:r(s).current_password,"onUpdate:modelValue":o[0]||(o[0]=t=>r(s).current_password=t),type:"password",class:"mt-1 block w-full",autocomplete:"current-password"},null,8,["modelValue"]),e(n,{message:r(s).errors.current_password,class:"mt-2"},null,8,["message"])]),a("div",null,[e(d,{for:"password",value:"New Password"}),e(l,{id:"password",ref_key:"passwordInput",ref:u,modelValue:r(s).password,"onUpdate:modelValue":o[1]||(o[1]=t=>r(s).password=t),type:"password",class:"mt-1 block w-full",autocomplete:"new-password"},null,8,["modelValue"]),e(n,{message:r(s).errors.password,class:"mt-2"},null,8,["message"])]),a("div",null,[e(d,{for:"password_confirmation",value:"Confirm Password"}),e(l,{id:"password_confirmation",modelValue:r(s).password_confirmation,"onUpdate:modelValue":o[2]||(o[2]=t=>r(s).password_confirmation=t),type:"password",class:"mt-1 block w-full",autocomplete:"new-password"},null,8,["modelValue"]),e(n,{message:r(s).errors.password_confirmation,class:"mt-2"},null,8,["message"])]),a("div",b,[e(V,{disabled:r(s).processing},{default:w(()=>[v("Save")]),_:1},8,["disabled"]),e(y,{"enter-from-class":"opacity-0","leave-to-class":"opacity-0",class:"transition ease-in-out"},{default:w(()=>[r(s).recentlySuccessful?(m(),i("p",P,"Saved.")):x("",!0)]),_:1})])],32)]))}};export{$ as default};
