import{r as p,q as g,o as s,s as f,e as m,d as _,u as x,Z as v,f as e,m as c,b as o,t as a,j as d,h as y,v as w,i as b,n as k,F as C}from"./app-de19c82f.js";import{_ as N}from"./AdminAuthenticatedLayout-58f70262.js";import"./ApplicationLogo-32c770cc.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./ResponsiveNavLink-b247280f.js";const L={class:"py-12"},M={class:"max-w-7xl mx-auto sm:px-6 lg:px-8"},V={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},B={class:"p-6"},U={class:"flex justify-between items-center mb-6"},j={class:"text-2xl font-semibold"},S={key:0,class:"ml-2 bg-red-500 text-white text-sm px-2 py-1 rounded-full"},D={class:"w-1/3"},q={class:"space-y-4"},F={key:0,class:"text-center py-8"},T=e("div",{class:"text-gray-400 mb-4"},[e("svg",{class:"mx-auto h-12 w-12",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"})])],-1),z=e("h3",{class:"text-lg font-medium text-gray-900 mb-2"},"No conversations found",-1),A={class:"text-gray-500"},E={class:"flex-1"},I={class:"flex items-center gap-2"},Q={class:"font-medium"},W={key:0,class:"bg-red-500 text-white text-xs px-2 py-1 rounded-full"},Z={class:"text-sm text-gray-600"},$={class:"text-sm text-gray-500 mt-1"},G={key:0},H=e("br",null,null,-1),J={key:1,class:"text-gray-400 italic"},Y={__name:"Index",props:{users:Array,totalUnread:Number},setup(i){const h=i,n=p(""),u=g(()=>{if(!n.value)return h.users;const l=n.value.toLowerCase();return h.users.filter(r=>r.name.toLowerCase().includes(l)||r.email.toLowerCase().includes(l))});return(l,r)=>(s(),f(N,null,{default:m(()=>[_(x(v),{title:"Chat Management"}),e("div",L,[e("div",M,[e("div",V,[e("div",B,[e("div",U,[e("h2",j,[c(" Chat Management "),i.totalUnread>0?(s(),o("span",S,a(i.totalUnread)+" new ",1)):d("",!0)]),e("div",D,[y(e("input",{"onUpdate:modelValue":r[0]||(r[0]=t=>n.value=t),type:"text",placeholder:"Search users...",class:"w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"},null,512),[[w,n.value]])])]),e("div",q,[u.value.length===0?(s(),o("div",F,[T,z,e("p",A,a(n.value?"No users match your search criteria.":"Wait for users to start conversations."),1)])):d("",!0),(s(!0),o(C,null,b(u.value,t=>(s(),o("div",{key:t.id,class:"flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"},[e("div",E,[e("div",I,[e("h3",Q,a(t.name),1),t.unread_count>0?(s(),o("span",W,a(t.unread_count)+" new ",1)):d("",!0)]),e("p",Z,a(t.email),1),e("div",$,[t.chats&&t.chats.length>0?(s(),o("span",G,[c(" Last message: "+a(t.chats[0].message)+" ",1),H,c(" "+a(new Date(t.chats[0].created_at).toLocaleString()),1)])):(s(),o("span",J," No messages yet "))])]),_(x(k),{href:l.route("admin.chat.show",t.id),class:"px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"},{default:m(()=>[c(a(t.chats&&t.chats.length>0?"View Chat":"Start Chat"),1)]),_:2},1032,["href"])]))),128))])])])])])]),_:1}))}};export{Y as default};