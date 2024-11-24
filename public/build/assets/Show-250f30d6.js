import{o,c as r,a as i,u as c,w as n,F as m,Z as h,b as t,t as s,n as x,g as l,i as f,h as y}from"./app-914d880b.js";import{_}from"./AdminAuthenticatedLayout-0b05c9ac.js";import"./ApplicationLogo-bcb5eeac.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./ResponsiveNavLink-54b7070c.js";const p={class:"font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"},b={class:"py-12"},k={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},v={class:"bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"},w={class:"p-6 text-gray-900 dark:text-gray-100"},D={class:"mb-8"},S=t("h3",{class:"text-lg font-semibold mb-4"},"Purchase Information",-1),N={class:"grid grid-cols-1 md:grid-cols-2 gap-4"},C=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Order ID",-1),B={class:"font-medium"},I=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Status",-1),V=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Date Ordered",-1),L={class:"font-medium"},M=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Last Updated",-1),O={class:"font-medium"},P={class:"mb-8"},j=t("h3",{class:"text-lg font-semibold mb-4"},"Customer Information",-1),A={class:"grid grid-cols-1 md:grid-cols-2 gap-4"},E=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Name",-1),F={class:"font-medium"},R=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Email",-1),T={class:"font-medium"},U={class:"mb-8"},q=t("h3",{class:"text-lg font-semibold mb-4"},"Medicine Information",-1),z={class:"grid grid-cols-1 md:grid-cols-2 gap-4"},Q=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Medicine Name",-1),Z={class:"font-medium"},$=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Quantity",-1),G={class:"font-medium"},H=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Total Amount",-1),J={class:"font-medium"},K={key:0},W=t("p",{class:"text-sm text-gray-600 dark:text-gray-400"},"Dosage",-1),X={class:"font-medium"},Y={class:"flex justify-end space-x-4"},it={__name:"Show",props:{purchase:Object},setup(e){const u=e;function d(a){return new Date(a).toLocaleString("en-US",{year:"numeric",month:"long",day:"numeric",hour:"2-digit",minute:"2-digit"})}function g(){router.post(route("admin.purchase.mark-ready",u.purchase.id),{},{preserveScroll:!0,onSuccess:()=>{}})}return(a,tt)=>(o(),r(m,null,[i(c(h),{title:"Purchase Details"}),i(_,null,{header:n(()=>[t("h2",p," Purchase Details #"+s(e.purchase.id),1)]),default:n(()=>[t("div",b,[t("div",k,[t("div",v,[t("div",w,[t("div",D,[S,t("div",N,[t("div",null,[C,t("p",B,"#"+s(e.purchase.id),1)]),t("div",null,[I,t("span",{class:x(["inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium",{"bg-yellow-100 text-yellow-800":e.purchase.status==="pending","bg-blue-100 text-blue-800":e.purchase.status==="processing","bg-green-100 text-green-800":e.purchase.status==="completed"}])},s(e.purchase.status),3)]),t("div",null,[V,t("p",L,s(d(e.purchase.created_at)),1)]),t("div",null,[M,t("p",O,s(d(e.purchase.updated_at)),1)])])]),t("div",P,[j,t("div",A,[t("div",null,[E,t("p",F,s(e.purchase.user.name),1)]),t("div",null,[R,t("p",T,s(e.purchase.user.email),1)])])]),t("div",U,[q,t("div",z,[t("div",null,[Q,t("p",Z,s(e.purchase.name),1)]),t("div",null,[$,t("p",G,s(e.purchase.quantity),1)]),t("div",null,[H,t("p",J,"₱"+s(e.purchase.total_amount),1)]),e.purchase.dosage?(o(),r("div",K,[W,t("p",X,s(e.purchase.dosage),1)])):l("",!0)])]),t("div",Y,[i(c(f),{href:a.route("admin.purchase.index"),class:"inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"},{default:n(()=>[y(" Back to List ")]),_:1},8,["href"]),e.purchase.status==="pending"?(o(),r("button",{key:0,onClick:g,class:"inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"}," Mark as Ready ")):l("",!0)])])])])])]),_:1})],64))}};export{it as default};