(() => {
  'use strict';
  const cfg = window.CDP_CONFIG;
  const roots = document.querySelectorAll('[data-cdp-portal]');
  if (!cfg || !roots.length) return;

  const icons = {
    folder: '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 6.5h6l2 2h10v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-10a2 2 0 0 1 2-2Z"/></svg>',
    file: '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 2h8l4 4v16H6Z"/><path d="M14 2v5h5"/></svg>',
    download: '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v12m-5-5 5 5 5-5M5 18v3h14v-3"/></svg>',
    up: '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 20V5m-6 6 6-6 6 6"/></svg>',
    plus: '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h6l2 2h8v12H4Z"/><path d="M12 10v6m-3-3h6"/></svg>',
    trash: '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16M9 3h6l1 4H8Zm-3 4 1 14h10l1-14M10 11v6m4-6v6"/></svg>',
    upload: '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 16V3m-5 5 5-5 5 5M4 14v7h16v-7"/></svg>',
    chevron: '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>'
  };
  const el = (tag, cls, text) => { const node = document.createElement(tag); if (cls) node.className = cls; if (text !== undefined) node.textContent = text; return node; };
  const icon = name => { const span = el('span', 'cdp-icon'); span.innerHTML = icons[name]; return span; };

  roots.forEach(start);
  function start(root) {
    const state = { libraries: [], library: '', path: '', sort: 'newest', entries: [], selected: new Set(), request: 0, treeRequest: 0, folderCache: new Map(), foldersOpen: false };
    request('/libraries').then(data => {
      if (!data.protectionReady) throw new Error('The portal is locked until an administrator verifies direct-access protection.');
      state.libraries = data.libraries || [];
      if (!state.libraries.length) throw new Error('You do not have access to a document library.');
      state.library = state.libraries[0].id;
      build(root, state); loadEntries(root, state);
    }).catch(error => showFatal(root, error));
  }

  async function request(route, options = {}) {
    const response = await fetch(cfg.restUrl + route, { credentials: 'same-origin', ...options, headers: { 'X-WP-Nonce': cfg.nonce, ...(options.headers || {}) } });
    let data = {}; try { data = await response.json(); } catch (_) {}
    if (!response.ok) { const error = new Error(data.message || cfg.strings.loadError); error.data = data.data || {}; error.status = response.status; throw error; }
    return data;
  }

  function build(root, state) {
    root.textContent = '';
    const tabs = el('div', 'cdp-tabs'); tabs.setAttribute('role', 'tablist'); root.append(tabs);
    state.libraries.forEach(library => {
      const button = el('button', 'cdp-tab', library.label); button.type = 'button'; button.dataset.library = library.id; button.setAttribute('role', 'tab');
      button.addEventListener('click', () => { if (state.library === library.id) return; state.library = library.id; state.path = ''; state.selected.clear(); updateTabs(root, state); loadEntries(root, state); }); tabs.append(button);
    });
    const shell = el('div', 'cdp-browser');
    const sidebar = el('aside', 'cdp-sidebar'); sidebar.setAttribute('aria-label', 'Folders'); sidebar.innerHTML = '<strong>FOLDERS</strong><div class="cdp-tree"></div>'; shell.append(sidebar);
    const main = el('section', 'cdp-main');
    const top = el('div', 'cdp-top');
    const folderToggle = el('button', 'cdp-folder-toggle', 'Folders'); folderToggle.type = 'button'; folderToggle.prepend(icon('folder')); folderToggle.setAttribute('aria-expanded', 'false');
    folderToggle.addEventListener('click', () => { state.foldersOpen = !state.foldersOpen; shell.classList.toggle('cdp-folders-open', state.foldersOpen); folderToggle.setAttribute('aria-expanded', String(state.foldersOpen)); }); top.append(folderToggle);
    const crumbs = el('nav', 'cdp-breadcrumb'); crumbs.setAttribute('aria-label', 'Current folder'); top.append(crumbs);
    const sort = el('select', 'cdp-sort'); sort.setAttribute('aria-label', 'Sort items'); [['newest','Newest first'],['oldest','Oldest first'],['name_asc','Name A–Z'],['name_desc','Name Z–A']].forEach(([v,t]) => { const o=el('option','',t); o.value=v; sort.append(o); });
    sort.addEventListener('change', () => { state.sort = sort.value; loadEntries(root, state); }); top.append(sort); main.append(top);
    const mobilePanel = el('div', 'cdp-mobile-panel'); mobilePanel.setAttribute('aria-label', 'Folder navigation'); main.append(mobilePanel);
    const utility = el('div', 'cdp-utility-row');
    const up = el('button', 'cdp-up', 'Up one folder'); up.type='button'; up.prepend(icon('up')); up.addEventListener('click', () => navigate(root, state, parentPath(state.path))); utility.append(up);
    const toolbar = el('div', 'cdp-toolbar'); utility.append(toolbar); main.append(utility);
    const list = el('div', 'cdp-list'); main.append(list);
    const footer = el('div', 'cdp-footer'); main.append(footer);
    const status = el('div', 'cdp-sr-status'); status.setAttribute('role','status'); status.setAttribute('aria-live','polite'); main.append(status);
    shell.append(main); root.append(shell);
    root._cdp = { tabs, shell, sidebar, tree: sidebar.querySelector('.cdp-tree'), main, crumbs, sort, utility, up, toolbar, list, footer, status, mobilePanel, folderToggle };
    updateTabs(root, state);
  }

  function updateTabs(root, state) { root.querySelectorAll('.cdp-tab').forEach(button => { const active=button.dataset.library===state.library; button.classList.toggle('is-active', active); button.setAttribute('aria-selected', String(active)); button.tabIndex=active?0:-1; }); }
  function currentLibrary(state) { return state.libraries.find(item => item.id === state.library); }

  async function loadEntries(root, state) {
    const id = ++state.request; root.classList.add('is-loading'); announce(root, 'Loading folder…');
    try {
      const params = new URLSearchParams({ library: state.library, path: state.path, sort: state.sort });
      const data = await request('/entries?' + params);
      if (id !== state.request) return;
      state.path = data.path; state.entries = data.entries || []; state.selected.clear(); state.data = data;
      render(root, state);
      root.classList.remove('is-loading');
      announce(root, `${data.count} items loaded.`);
      loadTree(root, state).catch(error => announce(root, error.message));
    } catch (error) { if (id === state.request) showError(root, error); }
    finally { if (id === state.request) root.classList.remove('is-loading'); }
  }

  function render(root, state) {
    const ui=root._cdp, data=state.data, manage=data.canManage;
    ui.crumbs.textContent=''; data.breadcrumbs.forEach((crumb,index) => { if(index) ui.crumbs.append(document.createTextNode(' / ')); if(index<data.breadcrumbs.length-1){const a=el('button','cdp-crumb',crumb.name);a.type='button';a.addEventListener('click',()=>navigate(root,state,crumb.path));ui.crumbs.append(a);}else ui.crumbs.append(el('strong','',crumb.name)); });
    ui.up.hidden = data.parent === null;
    renderToolbar(root,state,manage);ui.utility.hidden=data.parent===null&&!manage;renderList(root,state,manage); ui.footer.textContent = `${data.count} ${data.count===1?'item':'items'}`;
  }

  function renderToolbar(root,state,manage){ const bar=root._cdp.toolbar;bar.textContent='';bar.hidden=!manage;if(!manage)return;
    const upload=el('button','cdp-action cdp-action-primary','Upload');upload.type='button';upload.prepend(icon('upload'));const input=el('input','cdp-file-input');input.type='file';input.multiple=true;input.accept='.pdf,.jpg,.jpeg,.xls,.xlsx,.ppt,.pptx,.doc,.docx,.zip';input.hidden=true;upload.addEventListener('click',()=>input.click());input.addEventListener('change',()=>uploadQueue(root,state,[...input.files]));bar.append(upload,input);
    const folder=el('button','cdp-action','New Folder');folder.type='button';folder.prepend(icon('plus'));folder.addEventListener('click',()=>newFolder(root,state));bar.append(folder);
    const del=el('button','cdp-action cdp-delete','Delete');del.type='button';del.prepend(icon('trash'));del.disabled=!state.selected.size;del.addEventListener('click',()=>deleteSelected(root,state));bar.append(del);
    const count=el('span','cdp-selection-count',`${state.selected.size} selected`);bar.append(count);
  }

  function renderList(root,state,manage){const list=root._cdp.list;list.textContent='';list.classList.toggle('has-management',manage);
    if(manage){const selectRow=el('div','cdp-select-row');const all=el('input');all.type='checkbox';all.checked=state.entries.length>0&&state.selected.size===state.entries.length;all.indeterminate=state.selected.size>0&&!all.checked;all.setAttribute('aria-label','Select all items');all.addEventListener('change',()=>{state.selected.clear();if(all.checked)state.entries.forEach(i=>state.selected.add(i.path));render(root,state);});selectRow.append(all,el('span','cdp-select-label','Select all'),el('span','cdp-mobile-count',`${state.selected.size} selected`));list.append(selectRow);}
    const header=el('div','cdp-table-head');if(manage)header.append(el('span',''));header.append(el('strong','', 'Name'),el('strong','', 'Size'),el('strong','', 'Modified'),el('span',''));list.append(header);
    if(!state.entries.length){list.append(el('p','cdp-empty',cfg.strings.empty));return;}
    state.entries.forEach(item=>list.append(entryRow(root,state,item,manage)));
  }

  function entryRow(root,state,item,manage){const row=el('div','cdp-row');row.classList.toggle('is-selected',state.selected.has(item.path));row.dataset.type=item.type;
    if(manage){const check=el('input');check.type='checkbox';check.checked=state.selected.has(item.path);check.setAttribute('aria-label',`Select ${item.name}`);check.addEventListener('change',()=>{check.checked?state.selected.add(item.path):state.selected.delete(item.path);render(root,state);});row.append(check);}
    const name=el(item.type==='folder'?'button':'div','cdp-name');if(item.type==='folder'){name.type='button';name.setAttribute('aria-label',`Open ${item.name}`);name.addEventListener('click',()=>navigate(root,state,item.path));}name.append(icon(item.type==='folder'?'folder':'file'));const text=el('div','cdp-name-text');text.append(el('strong','',item.name));if(item.type==='file')text.append(el('span','cdp-mobile-meta',`${item.modifiedLabel} · ${item.sizeLabel}`));name.append(text);row.append(name);
    row.append(el('span','cdp-size',item.sizeLabel),el('span','cdp-modified',item.modifiedLabel));
    if(item.type==='file'){const download=el('button','cdp-download','Download');download.type='button';download.prepend(icon('download'));download.setAttribute('aria-label',`Download ${item.name}`);download.addEventListener('click',()=>submitDownload(state,item));row.append(download);}
    return row;
  }

  async function loadTree(root,state){
    const requestId=++state.treeRequest,library=state.library,currentPath=state.path,parts=currentPath.split('/').filter(Boolean),branches=[];
    let path='',depth=0;
    while(true){
      const folders=await folderChildren(state,library,path);
      if(requestId!==state.treeRequest||library!==state.library||currentPath!==state.path)return;
      branches.push({depth,folders});
      if(depth>=parts.length)break;
      path=path?`${path}/${parts[depth]}`:parts[depth];depth++;
    }
    const ui=root._cdp,definition=currentLibrary(state);
    ui.tree.textContent='';ui.mobilePanel.textContent='';
    [ui.tree,ui.mobilePanel].forEach(container=>{
      container.append(treeButton(root,state,definition.rootLabel,'',0,true));
      branches.forEach(branch=>branch.folders.forEach(folder=>container.append(treeButton(root,state,folder.name,folder.path,branch.depth+1,folder.hasChildren))));
    });
  }
  async function folderChildren(state,library,path){
    const key=`${library}|${path}`;
    if(state.folderCache.has(key))return state.folderCache.get(key);
    const data=await request('/folders?'+new URLSearchParams({library,path}));
    const folders=data.folders||[];
    if(state.folderCache.size>=100)state.folderCache.clear();
    state.folderCache.set(key,folders);
    return folders;
  }
  function treeButton(root,state,name,path,depth,hasChildren){const button=el('button','cdp-tree-item',name);button.type='button';button.style.setProperty('--depth',depth);button.classList.toggle('is-current',state.path===path);button.prepend(icon('folder'));if(hasChildren)button.dataset.children='true';button.addEventListener('click',()=>{state.foldersOpen=false;root._cdp.shell.classList.remove('cdp-folders-open');root._cdp.folderToggle.setAttribute('aria-expanded','false');navigate(root,state,path);});return button;}
  function navigate(root,state,path){state.path=path==='.'?'':path;state.selected.clear();loadEntries(root,state);}
  function parentPath(path){const parts=path.split('/').filter(Boolean);parts.pop();return parts.join('/');}

  function submitDownload(state,item){const form=el('form');form.method='post';form.action=state.data.downloadUrl;[['action','cdp_download'],['library',state.library],['path',item.path],['cdp_download_nonce',state.data.downloadNonce]].forEach(([name,value])=>{const input=el('input');input.type='hidden';input.name=name;input.value=value;form.append(input);});document.body.append(form);form.submit();form.remove();}

  async function newFolder(root,state){const name=window.prompt('Folder name:');if(name===null)return;if(!name.trim())return announce(root,'Enter a folder name.');try{await request('/folders',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({library:state.library,path:state.path,name})});state.folderCache.clear();announce(root,'Folder created.');loadEntries(root,state);}catch(error){showError(root,error);}}

  async function uploadQueue(root,state,files){for(const file of files){if(file.size>cfg.maxUpload){announce(root,`${file.name} is larger than 50 MB.`);continue;}let overwrite=false,revision='';while(true){const body=new FormData();body.append('library',state.library);body.append('path',state.path);body.append('file',file,file.name);body.append('overwrite',overwrite?'1':'0');if(revision)body.append('revision',revision);try{announce(root,`Uploading ${file.name}…`);await request('/uploads',{method:'POST',body});announce(root,`${file.name} uploaded.`);break;}catch(error){if(error.status===409&&error.data&&error.data.revision&&!overwrite){if(window.confirm(`${error.message}\n\n${error.data.name}\nFolder: ${error.data.folder||currentLibrary(state).rootLabel}`)){overwrite=true;revision=error.data.revision;continue;}}announce(root,`${file.name}: ${error.message}`);break;}}}loadEntries(root,state);}

  async function deleteSelected(root,state){if(!state.selected.size)return;const selected=state.entries.filter(i=>state.selected.has(i.path));const folders=selected.filter(i=>i.type==='folder');let warning=folders.length===1&&selected.length===1?cfg.strings.deleteFolder:(folders.length?cfg.strings.deleteMultiple:cfg.strings.deleteFiles);warning+='\n\n'+selected.map(i=>i.name).join('\n');if(!window.confirm(warning))return;try{await request('/delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({library:state.library,items:[...state.selected]})});state.folderCache.clear();announce(root,'Selected items deleted.');loadEntries(root,state);}catch(error){state.folderCache.clear();showError(root,error);loadEntries(root,state);}}

  function announce(root,message){root._cdp?root._cdp.status.textContent=message:null;}
  function showError(root,error){announce(root,error.status===401?cfg.strings.sessionExpired:error.message);const list=root._cdp&&root._cdp.list;if(list){list.textContent='';const p=el('p','cdp-error',error.message);p.setAttribute('role','alert');list.append(p);}}
  function showFatal(root,error){root.textContent='';const p=el('p','cdp-error',error.message);p.setAttribute('role','alert');root.append(p);}
})();
