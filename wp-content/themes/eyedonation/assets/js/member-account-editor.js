(()=>{
 const el=wp.element.createElement;
 wp.blocks.registerBlockType('eyedonation/member-account',{
  apiVersion:3,title:'Member welcome and logout',category:'theme',icon:'admin-users',
  description:'Displays the signed-in user’s welcome, initial and logout link.',
  supports:{html:false,multiple:false},
  edit:()=>el('div',wp.blockEditor.useBlockProps(),el(wp.serverSideRender,{block:'eyedonation/member-account'})),save:()=>null
 });
})();
