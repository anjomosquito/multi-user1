import{o as s,b as a,f as r,t as p,p as u,j as n}from"./app-de19c82f.js";const l={class:"px-6 py-4 whitespace-nowrap"},d={key:0,class:"mt-2"},b={__name:"Index",setup(c){function o(e){window.open(route("purchase.report",e.id),"_blank")}return(e,t)=>(s(),a("td",l,[r("span",{class:u({"px-2 py-1 rounded-full text-xs font-medium":!0,"bg-yellow-100 text-yellow-800":e.purchase.status==="pending","bg-blue-100 text-blue-800":e.purchase.status==="confirmed","bg-purple-100 text-purple-800":e.purchase.status==="verified","bg-green-400 text-black-800":e.purchase.status==="completed","bg-red-100 text-red-800":e.purchase.status==="cancelled"})},p(e.formatStatus(e.purchase.status)),3),e.purchase.status==="completed"?(s(),a("div",d,[r("button",{onClick:t[0]||(t[0]=i=>o(e.purchase)),class:"px-2 py-1 bg-green-200 hover:bg-green-300 text-gray-800 rounded text-xs font-medium"}," View Receipt ")])):n("",!0)]))}};export{b as default};