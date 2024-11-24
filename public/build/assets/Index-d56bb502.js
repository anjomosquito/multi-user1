import{r as y,O as _,s as g,o,c as r,a as d,u as b,w as h,F as x,Z as f,b as t,e as i,v as w,g as v,j as k,t as a,n as N}from"./app-b78d6429.js";import{_ as m}from"./AdminAuthenticatedLayout-aea9e875.js";import{l as S}from"./lodash-838827cb.js";import"./sweetalert2.esm.all-509645c6.js";import"./ApplicationLogo-0aef615f.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./ResponsiveNavLink-c2d65a8c.js";const M=t("h2",{class:"font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"},"Medicine Index",-1),A={class:"py-12"},B={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},C={class:"flex justify-between items-center mb-6"},D={class:"w-1/2"},V={class:"relative"},j=t("div",{class:"absolute inset-y-0 right-0 flex items-center pr-3"},[t("svg",{class:"w-5 h-5 text-gray-400",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"})])],-1),E={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},P={class:"relative overflow-x-auto shadow-md sm:rounded-lg"},z={class:"w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"},F=t("thead",{class:"text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"},[t("tr",null,[t("th",{scope:"col",class:"px-6 py-3"},"Name"),t("th",{scope:"col",class:"px-6 py-3"},"Low Price"),t("th",{scope:"col",class:"px-6 py-3"},"Median Price"),t("th",{scope:"col",class:"px-6 py-3"},"Highest Price"),t("th",{scope:"col",class:"px-6 py-3"},"Quantity"),t("th",{scope:"col",class:"px-6 py-3"},"Dosage"),t("th",{scope:"col",class:"px-6 py-3"},"Exp Date"),t("th",{scope:"col",class:"px-6 py-3"},"Status")])],-1),I={key:0},L=t("td",{colspan:"8",class:"px-6 py-4 text-center text-gray-500"}," No medicines found ",-1),O=[L],Q={scope:"row",class:"px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white"},q={class:"px-6 py-4"},H={class:"px-6 py-4"},T={class:"px-6 py-4"},U={class:"px-6 py-4"},Z={class:"px-6 py-4"},$={class:"px-6 py-4"},G={class:"px-6 py-4"},st={__name:"Index",props:{medicines:{type:Array,default:()=>[]}},setup(l){const p=y(""),n=S.debounce(e=>{_.get(route("admin.medicines.index"),{search:e},{preserveState:!0,preserveScroll:!0})},300);return g(p,e=>{n(e)}),(e,c)=>(o(),r(x,null,[d(b(f),{title:"Medicines"}),d(m,null,{header:h(()=>[M]),default:h(()=>[t("div",A,[t("div",B,[t("div",C,[t("div",D,[t("div",V,[i(t("input",{"onUpdate:modelValue":c[0]||(c[0]=s=>p.value=s),type:"text",placeholder:"Search medicines by name or dosage...",class:"w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"},null,512),[[w,p.value]]),j])])]),t("div",E,[t("div",P,[t("table",z,[F,t("tbody",null,[!l.medicines||l.medicines.length===0?(o(),r("tr",I,O)):v("",!0),(o(!0),r(x,null,k(l.medicines,(s,u)=>(o(),r("tr",{key:(s==null?void 0:s.id)||u,class:"odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700"},[t("th",Q,a((s==null?void 0:s.name)||"N/A"),1),t("td",q,"₱"+a((s==null?void 0:s.lprice)||"0"),1),t("td",H,"₱"+a((s==null?void 0:s.mprice)||"0"),1),t("td",T,"₱"+a((s==null?void 0:s.hprice)||"0"),1),t("td",U,a((s==null?void 0:s.quantity)||"0"),1),t("td",Z,a((s==null?void 0:s.dosage)||"N/A"),1),t("td",$,a((s==null?void 0:s.expdate)||"N/A"),1),t("td",G,[t("span",{class:N(s.status==="disabled"?"text-red-600":"text-green-600")},a(s.status),3)])]))),128))])])])])])])]),_:1})],64))}};export{st as default};