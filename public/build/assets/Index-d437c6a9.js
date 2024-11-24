import{r,O as b,o as u,c as x,a as s,u as y,w as o,F as w,Z as N,b as e,e as A,v as V,f as $,i as C,h as m,j as T,t as l,n as D,g as O}from"./app-914d880b.js";import{_ as B}from"./AdminAuthenticatedLayout-0b05c9ac.js";import{_ as E}from"./Pagination-09000c78.js";import{_ as F}from"./Modal-507294d8.js";import{_ as I,a as P}from"./DangerButton-122c33f7.js";import{l as U}from"./lodash-e58483d4.js";import{f as z}from"./format-66ee9ebe.js";import"./ApplicationLogo-bcb5eeac.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./ResponsiveNavLink-54b7070c.js";const L=e("h2",{class:"font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"},"Announcements",-1),Z={class:"py-12"},q={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},G={class:"bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"},H={class:"p-6 text-gray-900 dark:text-gray-100"},J={class:"flex flex-col sm:flex-row justify-between mb-6 gap-4"},K={class:"flex flex-col sm:flex-row gap-4"},Q={class:"flex items-center"},R={class:"flex items-center"},W=e("option",{value:""},"All Status",-1),X=e("option",{value:"draft"},"Draft",-1),Y=e("option",{value:"published"},"Published",-1),ee=[W,X,Y],te=e("i",{class:"fas fa-plus mr-2"},null,-1),se={class:"overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow"},ae={class:"w-full whitespace-nowrap"},oe=e("thead",null,[e("tr",{class:"text-left font-bold bg-gray-50 dark:bg-gray-700"},[e("th",{class:"px-6 py-3 text-gray-600 dark:text-gray-200"},"Title"),e("th",{class:"px-6 py-3 text-gray-600 dark:text-gray-200"},"Status"),e("th",{class:"px-6 py-3 text-gray-600 dark:text-gray-200"},"Published At"),e("th",{class:"px-6 py-3 text-gray-600 dark:text-gray-200"},"Created At"),e("th",{class:"px-6 py-3 text-gray-600 dark:text-gray-200"},"Actions")])],-1),ne={class:"divide-y divide-gray-100 dark:divide-gray-700"},re={class:"px-6 py-4"},le={class:"font-medium text-gray-900 dark:text-gray-100"},de={class:"text-sm text-gray-500 dark:text-gray-400 truncate max-w-md"},ie={class:"px-6 py-4"},ce={class:"px-6 py-4 text-gray-500 dark:text-gray-400"},ue={class:"px-6 py-4 text-gray-500 dark:text-gray-400"},xe={class:"px-6 py-4"},_e={class:"flex items-center space-x-4"},ge=e("i",{class:"fas fa-edit"},null,-1),fe=["onClick"],he=e("i",{class:"fas fa-trash"},null,-1),ye=[he],me={key:0},pe=e("td",{class:"px-6 py-4 text-center text-gray-500 dark:text-gray-400",colspan:"5"}," No announcements found. ",-1),ve=[pe],ke={class:"p-6"},be=e("h2",{class:"text-lg font-medium text-gray-900 dark:text-gray-100"}," Delete Announcement ",-1),we=e("p",{class:"mt-1 text-sm text-gray-600 dark:text-gray-400"}," Are you sure you want to delete this announcement? This action cannot be undone. ",-1),Ae={class:"mt-6 flex justify-end space-x-4"},Ee={__name:"Index",props:{announcements:Object,filters:{type:Object,default:()=>({search:"",status:""})}},setup(d){const p=d,_=r(p.filters.search||""),g=r(p.filters.status||""),f=r(!1),i=r(null),n=r(!1),S=U.debounce(()=>{b.get(route("admin.announcements.index"),{search:_.value,status:g.value},{preserveState:!0,preserveScroll:!0,replace:!0})},300);function v(){S()}function k(a){return a?z(new Date(a),"MMM d, yyyy"):"N/A"}function j(a){i.value=a,f.value=!0}function h(){f.value=!1,i.value=null}function M(){i.value&&(n.value=!0,b.delete(route("admin.announcements.destroy",i.value.id),{onSuccess:()=>{h(),n.value=!1},onError:()=>{n.value=!1}}))}return(a,c)=>(u(),x(w,null,[s(y(N),{title:"Announcements"}),s(B,null,{header:o(()=>[L]),default:o(()=>[e("div",Z,[e("div",q,[e("div",G,[e("div",H,[e("div",J,[e("div",K,[e("div",Q,[A(e("input",{"onUpdate:modelValue":c[0]||(c[0]=t=>_.value=t),type:"text",placeholder:"Search announcements...",class:"border rounded-lg px-4 py-2 w-full sm:w-80 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300",onInput:v},null,544),[[V,_.value]])]),e("div",R,[A(e("select",{"onUpdate:modelValue":c[1]||(c[1]=t=>g.value=t),class:"border rounded-lg px-4 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300",onChange:v},ee,544),[[$,g.value]])])]),s(y(C),{href:a.route("admin.announcements.create"),class:"px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg inline-flex items-center justify-center"},{default:o(()=>[te,m(" Create Announcement ")]),_:1},8,["href"])]),e("div",se,[e("table",ae,[oe,e("tbody",ne,[(u(!0),x(w,null,T(d.announcements.data,t=>(u(),x("tr",{key:t.id,class:"hover:bg-gray-50 dark:hover:bg-gray-700"},[e("td",re,[e("div",le,l(t.title),1),e("div",de,l(t.content),1)]),e("td",ie,[e("span",{class:D([{"bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100":t.status==="published","bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100":t.status==="draft"},"px-3 py-1 rounded-full text-xs font-medium"])},l(t.status),3)]),e("td",ce,l(k(t.published_at)),1),e("td",ue,l(k(t.created_at)),1),e("td",xe,[e("div",_e,[s(y(C),{href:a.route("admin.announcements.edit",t.id),class:"text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"},{default:o(()=>[ge]),_:2},1032,["href"]),e("button",{onClick:Ce=>j(t),class:"text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"},ye,8,fe)])])]))),128)),d.announcements.data.length===0?(u(),x("tr",me,ve)):O("",!0)])])]),s(E,{links:d.announcements.links,class:"mt-6"},null,8,["links"])])])])]),s(F,{show:f.value,onClose:h},{default:o(()=>[e("div",ke,[be,we,e("div",Ae,[s(I,{onClick:h},{default:o(()=>[m("Cancel")]),_:1}),s(P,{class:D(["ml-3",{"opacity-25":n.value}]),disabled:n.value,onClick:M},{default:o(()=>[m(" Delete Announcement ")]),_:1},8,["class","disabled"])])])]),_:1},8,["show"])]),_:1})],64))}};export{Ee as default};
