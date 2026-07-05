
(function(){
  'use strict';
  const $=(s,ctx=document)=>ctx.querySelector(s);
  const $$=(s,ctx=document)=>Array.from(ctx.querySelectorAll(s));
  const KEY_PROPERTIES='captacion_properties_v4';
  const KEY_NEEDS='captacion_needs_v4';
  const KEY_COOKIE='captacion_cookie_choice_v4';
  const MAX_IMAGE_BYTES=1200*1024;
  const GEMINI_PROXY_URL=''; // Ejemplo: '/api/gemini'. Configúralo en backend; nunca expongas una clave API en este HTML.
  const geoDb={
      "Andalucía": {
        "Almería": ["Almería", "Roquetas de Mar", "El Ejido", "Níjar"],
        "Cádiz": ["Cádiz", "Jerez de la Frontera", "Algeciras", "Chiclana de la Frontera"],
        "Córdoba": ["Córdoba", "Lucena", "Puente Genil"],
        "Granada": ["Granada", "Motril", "Almuñécar", "Armilla"],
        "Huelva": ["Huelva", "Lepe", "Almonte"],
        "Jaén": ["Jaén", "Linares", "Úbeda", "Andújar"],
        "Málaga": ["Málaga", "Marbella", "Mijas", "Fuengirola", "Estepona", "Benalmádena"],
        "Sevilla": ["Sevilla", "Dos Hermanas", "Alcalá de Guadaíra", "Utrera"]
      },
      "Aragón": {
        "Huesca": ["Huesca", "Monzón", "Barbastro"],
        "Teruel": ["Teruel", "Alcañiz"],
        "Zaragoza": ["Zaragoza", "Calatayud", "Utebo", "Ejea de los Caballeros"]
      },
      "Asturias": {
        "Asturias": ["Gijón", "Oviedo", "Avilés", "Siero", "Langreo"]
      },
      "Baleares": {
        "Illes Balears": ["Palma de Mallorca", "Ibiza", "Calvià", "Manacor", "Maó", "Ciutadella"]
      },
      "Canarias": {
        "Las Palmas": ["Las Palmas de Gran Canaria", "Telde", "Santa Lucía de Tirajana", "Arrecife", "Puerto del Rosario"],
        "Santa Cruz de Tenerife": ["Santa Cruz de Tenerife", "San Cristóbal de La Laguna", "Arona", "Adeje"]
      },
      "Cantabria": {
        "Cantabria": ["Santander", "Torrelavega", "Castro-Urdiales", "Camargo"]
      },
      "Castilla y León": {
        "Ávila": ["Ávila", "Arévalo"],
        "Burgos": ["Burgos", "Miranda de Ebro", "Aranda de Duero"],
        "León": ["León", "Ponferrada", "San Andrés del Rabanedo"],
        "Palencia": ["Palencia", "Aguilar de Campoo"],
        "Salamanca": ["Salamanca", "Béjar", "Ciudad Rodrigo"],
        "Segovia": ["Segovia", "Cuéllar"],
        "Soria": ["Soria", "Almazán"],
        "Valladolid": ["Valladolid", "Laguna de Duero", "Medina del Campo"],
        "Zamora": ["Zamora", "Benavente"]
      },
      "Castilla-La Mancha": {
        "Albacete": ["Albacete", "Hellín", "Villarrobledo"],
        "Ciudad Real": ["Ciudad Real", "Puertollano", "Tomelloso", "Alcázar de San Juan"],
        "Cuenca": ["Cuenca", "Tarancón"],
        "Guadalajara": ["Guadalajara", "Azuqueca de Henares"],
        "Toledo": ["Toledo", "Talavera de la Reina", "Illescas", "Seseña"]
      },
      "Cataluña": {
        "Barcelona": ["Barcelona", "L'Hospitalet de Llobregat", "Badalona", "Terrassa", "Sabadell", "Mataró", "Sant Cugat del Vallès"],
        "Girona": ["Girona", "Figueres", "Blanes", "Lloret de Mar"],
        "Lleida": ["Lleida", "Balaguer", "Tàrrega"],
        "Tarragona": ["Tarragona", "Reus", "Tortosa", "Cambrils", "Salou"]
      },
      "Comunidad Valenciana": {
        "Alicante": ["Alicante", "Elche", "Torrevieja", "Orihuela", "Benidorm", "Alcoy", "Elda"],
        "Castellón": ["Castellón de la Plana", "Vila-real", "Burriana", "Vinaròs"],
        "Valencia": ["Valencia", "Torrent", "Gandia", "Paterna", "Sagunto", "Alzira", "Mislata"]
      },
      "Extremadura": {
        "Badajoz": ["Badajoz", "Mérida", "Don Benito", "Almendralejo", "Villanueva de la Serena"],
        "Cáceres": ["Cáceres", "Plasencia", "Navalmoral de la Mata"]
      },
      "Galicia": {
        "A Coruña": ["A Coruña", "Santiago de Compostela", "Ferrol", "Narón", "Oleiros", "Carballo"],
        "Lugo": ["Lugo", "Monforte de Lemos", "Viveiro", "Vilalba"],
        "Ourense": ["Ourense", "Verín", "O Carballiño", "Barbadás"],
        "Pontevedra": ["Vigo", "Pontevedra", "Vilagarcía de Arousa", "Redondela", "Cangas", "Marín"]
      },
      "La Rioja": {
        "La Rioja": ["Logroño", "Calahorra", "Arnedo", "Haro"]
      },
      "Madrid": {
        "Madrid": ["Madrid", "Móstoles", "Alcalá de Henares", "Fuenlabrada", "Leganés", "Getafe", "Alcorcón", "Pozuelo de Alarcón", "Las Rozas"]
      },
      "Murcia": {
        "Murcia": ["Murcia", "Cartagena", "Lorca", "Molina de Segura", "Alcantarilla", "Mazarrón"]
      },
      "Navarra": {
        "Navarra": ["Pamplona", "Tudela", "Barañáin", "Burlada", "Estella-Lizarra"]
      },
      "País Vasco": {
        "Araba": ["Vitoria-Gasteiz", "Laudio/Llodio", "Amurrio"],
        "Bizkaia": ["Bilbao", "Barakaldo", "Getxo", "Portugalete", "Santurtzi", "Basauri"],
        "Gipuzkoa": ["Donostia-San Sebastián", "Irun", "Errenteria", "Eibar", "Zarautz"]
      },
      "Ceuta": {
        "Ceuta": ["Ceuta"]
      },
      "Melilla": {
        "Melilla": ["Melilla"]
      }
    };
  const initialProperties=[
      {
        id: "prop-1",
        title: "Piso para reforma en Galicia",
        type: "Piso",
        location: "Galicia",
        neighborhood: "Ourense · O Couto",
        price: 120000,
        fee: "4.5%",
        score: 86,
        rehab: true,
        exclusive: false,
        urgency: "Media",
        docs: "Básico",
        description: "Excelente oportunidad para inversores en Ourense. Edificio residencial tranquilo con baja cuota de comunidad. El propietario está dispuesto a escuchar ofertas para agilizar la venta.",
        badgeColor: "blue",
        badgeText: "Exclusiva compartida",
        fundingConditions: "Sujeto a aportación de fondos propios mínimos (20%) y simulación hipotecaria pre-aprobada.",
        image: ""
      },
      {
        id: "prop-2",
        title: "Edificio de Oficinas con parking subterráneo",
        type: "Edificio",
        location: "Madrid",
        neighborhood: "Madrid · Chamartín",
        price: 3450000,
        fee: "4.0%",
        score: 94,
        rehab: false,
        exclusive: true,
        urgency: "Baja",
        docs: "Completo",
        description: "Edificio corporativo con ocupación del 85%. Excelente rentabilidad recurrente garantizada. Toda la documentación técnica validada en nuestro expediente.",
        badgeColor: "blue",
        badgeText: "Exclusiva compartida",
        fundingConditions: "Financiación corporativa disponible bajo estudio de viabilidad con Banco Sabadell.",
        image: ""
      },
      {
        id: "prop-3",
        title: "Local comercial prime en rentabilidad",
        type: "Local Comercial",
        location: "Barcelona",
        neighborhood: "Barcelona · Eixample",
        price: 520000,
        fee: "5.0%",
        score: 91,
        rehab: false,
        exclusive: true,
        urgency: "Alta",
        docs: "Auditoría Completa",
        description: "Local en esquina comercial de alto tránsito peatonal. Ideal para franquicias o marcas de retail. Certificaciones estructurales correctas.",
        badgeColor: "amber",
        badgeText: "Alta motivación",
        fundingConditions: "Requiere fondos propios acreditados mediante certificado bancario antes de la firma del pre-contrato.",
        image: ""
      }
    ];
  const initialNeeds=[
      {
        id: "need-1",
        title: "Inversor busca piso para reformar en O Couto",
        type: "Piso",
        operation: "Venta",
        buyerType: "Inversor",
        urgency: "Media (1-3 meses)",
        funding: "Fondos propios / Al contado",
        ccaa: "Galicia",
        province: "Ourense",
        municipality: "Ourense",
        locality: "O Couto",
        budget: 90000,
        feeSplit: "50/50",
        description: "Inversor local solvente con capital disponible busca piso de 2 o 3 dormitorios en zona centro o El Couto. Indispensable ascensor, no importa estado de conservación.",
        date: Date.now() - 3600000 * 48,
        agency: "Real Galicia Investments"
      },
      {
        id: "need-2",
        title: "Particular busca Chalet de lujo en Pozuelo",
        type: "Casa/Chalet",
        operation: "Venta",
        buyerType: "Particular",
        urgency: "Alta (Menos de 1 mes)",
        funding: "Financiación preaprobada",
        ccaa: "Madrid",
        province: "Madrid",
        municipality: "Pozuelo de Alarcón",
        locality: "Somosaguas",
        budget: 950000,
        feeSplit: "A consultar",
        description: "Buscamos chalet independiente o pareado con jardín amplio, mínimo 4 habitaciones y piscina. Cliente premium de alta solvencia con hipoteca preaprobada.",
        date: Date.now() - 3600000 * 6,
        agency: "Capital & Luxury Homes"
      },
      {
        id: "need-3",
        title: "Profesional busca Nave Industrial para logística",
        type: "Nave",
        operation: "Alquiler con Opción a Compra",
        buyerType: "Profesional",
        urgency: "Alta (Menos de 1 mes)",
        funding: "No requiere",
        ccaa: "Comunidad Valenciana",
        province: "Alicante",
        municipality: "Elche",
        locality: "Torrellano",
        budget: 450000,
        feeSplit: "50/50",
        description: "Buscamos nave industrial de mínimo 1200m² con muelle de carga activo y fácil acceso a autovía principal. Preferible alquiler con opción a compra.",
        date: Date.now() - 3600000 * 24,
        agency: "Levante Logística"
      }
    ];
  let properties=loadLocal(KEY_PROPERTIES, initialProperties);
  let needs=loadLocal(KEY_NEEDS, initialNeeds);
  let currentNeedsLayout='bloque';
  let uploadedFileBase64='';
  let tempPropertyToPublish=null;
  let selectedPropertyId=null;

  function escapeHtml(value){return String(value??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]));}
  function normalizeText(value){return String(value??'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();}
  function safeNumber(value,fallback=0){const n=Number(value);return Number.isFinite(n)?n:fallback;}
  function money(value){return safeNumber(value).toLocaleString('es-ES',{maximumFractionDigits:0})+' €';}
  function loadLocal(key,fallback){try{const parsed=JSON.parse(localStorage.getItem(key)||'null');return Array.isArray(parsed)?parsed:[...fallback];}catch(e){return [...fallback];}}
  function persist(key,data){try{localStorage.setItem(key,JSON.stringify(data));return true;}catch(e){toast('No se pudo guardar el cambio. Reduce el tamaño de la imagen o libera almacenamiento local.','info');return false;}}
  function toast(message,type='success'){const t=$('#toast');if(!t)return;t.textContent=message;t.className='toast show'+(type==='info'?' toast-info':'');clearTimeout(window.__toastTimer);window.__toastTimer=setTimeout(()=>t.classList.remove('show'),4200);}
  window.toast=toast;

  function initMenu(){const menu=$('#menu-btn'),mobile=$('#mobile-nav');if(menu&&mobile){menu.addEventListener('click',()=>{const open=mobile.classList.toggle('open');menu.setAttribute('aria-expanded',String(open));menu.textContent=open?'✕ Cerrar':'☰ Menú';});$$('a',mobile).forEach(a=>a.addEventListener('click',()=>mobile.classList.remove('open')));}}
  function initCookie(){const cookie=$('#cookie-banner');if(!cookie)return;if(localStorage.getItem(KEY_COOKIE))cookie.classList.add('hide');$$('.cookie-choice',cookie).forEach(b=>b.addEventListener('click',()=>{localStorage.setItem(KEY_COOKIE,b.dataset.choice||'necessary');cookie.classList.add('hide');}));}
  function openModal(id){const m=$(id);if(m)m.classList.add('open');}
  function closeModal(id){const m=$(id);if(m)m.classList.remove('open');}
  function initModals(){$$('[data-close-modal]').forEach(b=>b.addEventListener('click',()=>b.closest('.modal')?.classList.remove('open')));$$('.modal').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open');}));}
  function addOptions(select,items,placeholder,all=false){if(!select)return;select.innerHTML=`<option value="${all?'all':''}">${escapeHtml(placeholder)}</option>`+items.map(i=>`<option value="${escapeHtml(i)}">${escapeHtml(i)}</option>`).join('');}
  function initGeoSelectors(){
    const ccaas=Object.keys(geoDb).sort((a,b)=>a.localeCompare(b,'es'));
    const pairs=[['need-pub','form'],['offer','form'],['need-filter','filter']];
    pairs.forEach(([prefix,type])=>{const c=$('#'+prefix+'-ccaa'),p=$('#'+prefix+'-province'),m=$('#'+prefix+'-municipality');if(!c)return;addOptions(c,ccaas,type==='filter'?'Todas las CCAA':'Seleccione comunidad autónoma',type==='filter');c.addEventListener('change',()=>{updateGeo(prefix,type,false);if(type==='filter')filterNeeds();});p?.addEventListener('change',()=>{updateGeo(prefix,type,true);if(type==='filter')filterNeeds();});m?.addEventListener('change',()=>type==='filter'&&filterNeeds());});
  }
  function updateGeo(prefix,type,provinceChangedOnly=false){const c=$('#'+prefix+'-ccaa'),p=$('#'+prefix+'-province'),m=$('#'+prefix+'-municipality');if(!c||!p||!m)return;const ccaa=c.value;const all=type==='filter';if(!provinceChangedOnly){addOptions(p,ccaa&&ccaa!=='all'&&geoDb[ccaa]?Object.keys(geoDb[ccaa]).sort((a,b)=>a.localeCompare(b,'es')):[],all?'Todas las provincias':'Seleccione provincia',all);addOptions(m,[],all?'Todos los municipios':'Seleccione municipio',all);}else{const prov=p.value;addOptions(m,ccaa&&ccaa!=='all'&&prov&&prov!=='all'&&geoDb[ccaa]?.[prov]?[...geoDb[ccaa][prov]].sort((a,b)=>a.localeCompare(b,'es')):[],all?'Todos los municipios':'Seleccione municipio',all);}}

  function renderNeedsDashboard(){const wrap=$('#needs-dashboard');if(!wrap)return;const count=(field)=>needs.reduce((a,n)=>{if(n[field])a[n[field]]=(a[n[field]]||0)+1;return a;},{});const chips=(field,obj)=>Object.keys(obj).sort((a,b)=>a.localeCompare(b,'es')).map(v=>`<button class="chip" type="button" data-dashboard-filter="${field}" data-dashboard-value="${escapeHtml(v)}">${escapeHtml(v)} <b>${obj[v]}</b></button>`).join('')||'<span class="small">Sin datos</span>';wrap.innerHTML=`<article class="dashboard-card primary"><span class="small">Demanda total activa</span><div class="kpi">${needs.length}</div><p class="small">Necesidades de compra activas en la red profesional.</p></article><article class="dashboard-card"><span class="small">Por comunidad autónoma</span><div class="chip-row">${chips('ccaa',count('ccaa'))}</div></article><article class="dashboard-card"><span class="small">Por provincia</span><div class="chip-row">${chips('province',count('province'))}</div></article><article class="dashboard-card"><span class="small">Por municipio</span><div class="chip-row">${chips('municipality',count('municipality'))}</div></article>`;$$('[data-dashboard-filter]',wrap).forEach(b=>b.addEventListener('click',()=>filterByDashboard(b.dataset.dashboardFilter,b.dataset.dashboardValue)));}
  function setNeedsLayout(layout){currentNeedsLayout=layout==='lista'?'lista':'bloque';$('#layout-block')?.classList.toggle('active',currentNeedsLayout==='bloque');$('#layout-list')?.classList.toggle('active',currentNeedsLayout==='lista');filterNeeds();}
  function getNeedFilters(){return{time:$('#need-filter-time')?.value||'newest',ccaa:$('#need-filter-ccaa')?.value||'all',province:$('#need-filter-province')?.value||'all',municipality:$('#need-filter-municipality')?.value||'all',locality:normalizeText($('#need-filter-locality')?.value||''),price:$('#need-filter-price')?.value||'all'};}
  function filterNeeds(){const f=getNeedFilters();let list=needs.filter(n=>{const budget=safeNumber(n.budget);const priceOk=f.price==='all'||(f.price==='low'&&budget<150000)||(f.price==='mid'&&budget>=150000&&budget<=500000)||(f.price==='high'&&budget>500000);return(f.ccaa==='all'||n.ccaa===f.ccaa)&&(f.province==='all'||n.province===f.province)&&(f.municipality==='all'||n.municipality===f.municipality)&&(!f.locality||normalizeText(n.locality).includes(f.locality)||normalizeText(n.title).includes(f.locality))&&priceOk;});list.sort((a,b)=>f.time==='oldest'?safeNumber(a.date)-safeNumber(b.date):safeNumber(b.date)-safeNumber(a.date));renderNeedsUI(list);}
  function clearNeedFilters(){const c=$('#need-filter-ccaa'),p=$('#need-filter-province'),m=$('#need-filter-municipality'),l=$('#need-filter-locality'),pr=$('#need-filter-price'),t=$('#need-filter-time');if(c)c.value='all';updateGeo('need-filter','filter',false);if(p)p.value='all';if(m)m.value='all';if(l)l.value='';if(pr)pr.value='all';if(t)t.value='newest';filterNeeds();}
  function filterByDashboard(level,value){const cc=$('#need-filter-ccaa'),pr=$('#need-filter-province'),mu=$('#need-filter-municipality');if(!cc)return;let found=needs.find(n=>n[level]===value);if(!found)return;cc.value=found.ccaa||'all';updateGeo('need-filter','filter',false);if(level==='province'||level==='municipality'){pr.value=found.province||'all';updateGeo('need-filter','filter',true);}if(level==='municipality')mu.value=found.municipality||'all';filterNeeds();document.querySelector('#needs-results')?.scrollIntoView({behavior:'smooth',block:'start'});}
  function ago(ts){const h=Math.max(0,Math.round((Date.now()-safeNumber(ts,Date.now()))/3600000));return h<1?'Hace unos minutos':`Hace ${h} h`;}
  function renderNeedsUI(list){const wrap=$('#needs-list-container');if(!wrap)return;if(!list.length){wrap.innerHTML='<div class="empty"><h3>No hay necesidades con estos criterios</h3><p class="copy">Prueba a modificar los filtros o publica una nueva necesidad de búsqueda.</p></div>';return;}wrap.className=currentNeedsLayout==='lista'?'demand-list':'demand-grid';wrap.innerHTML=list.map(n=>{const id=escapeHtml(n.id),loc=n.locality?`${n.municipality} (${n.locality})`:n.municipality;const detail=`<div id="details-${id}" class="details"><p><strong>Comentarios:</strong> ${escapeHtml(n.description)}</p><p><strong>Ubicación:</strong> ${escapeHtml(n.ccaa)} · ${escapeHtml(n.province)} · ${escapeHtml(loc)}</p><p><strong>Financiación:</strong> ${escapeHtml(n.funding)}</p><p><strong>Urgencia:</strong> ${escapeHtml(n.urgency)}</p></div>`;if(currentNeedsLayout==='lista')return `<article class="demand-card list-card"><div class="spread"><div><div class="tags"><span class="pill pill-blue">${escapeHtml(n.type)}</span><span class="small">${ago(n.date)} · ${escapeHtml(n.agency)}</span></div><h3>${escapeHtml(n.title)}</h3><p class="copy">${escapeHtml(n.description)}</p><p class="muted">📍 ${escapeHtml(n.ccaa)} · ${escapeHtml(n.province)} · ${escapeHtml(loc)} · ${escapeHtml(n.operation)}</p></div><div><strong>${money(n.budget)}</strong><br><span class="pill pill-green">${escapeHtml(n.feeSplit)}</span></div></div>${detail}<div class="card-actions"><button class="btn btn-secondary btn-small" data-toggle-details="${id}">Ver más detalles</button><button class="btn btn-ai btn-small" data-ai-match="${id}">✨ Match con IA</button><button class="btn btn-primary btn-small" data-collaborate="${id}">Ofrecer / colaborar</button></div></article>`;return `<article class="demand-card"><div class="property-cover"><h3>${escapeHtml(n.title)}</h3></div><div class="card-body"><div class="spread"><span class="pill pill-green">Verificada</span><span class="score">91</span></div><p class="muted">Zona aproximada visible. Datos sensibles mediante solicitud validada.</p><div class="tags"><span class="pill pill-blue">${escapeHtml(n.operation)}</span><span class="pill pill-amber">${escapeHtml(n.buyerType)}</span></div><div class="metrics"><div class="metric"><strong>${money(n.budget)}</strong><span>Presupuesto</span></div><div class="metric"><strong>${escapeHtml(n.feeSplit)}</strong><span>Reparto</span></div><div class="metric"><strong>${escapeHtml(n.province)}</strong><span>Zona</span></div></div>${detail}</div><div class="card-actions"><button class="btn btn-secondary btn-small" data-toggle-details="${id}">Ver más detalles</button><button class="btn btn-ai btn-small" data-ai-match="${id}">✨ Match inteligente con IA</button><button class="btn btn-primary btn-small" data-collaborate="${id}">Ofrecer mi cartera / colaborar</button></div></article>`;}).join('');$$('[data-toggle-details]',wrap).forEach(b=>b.addEventListener('click',()=>toggleDetails(b.dataset.toggleDetails,b)));$$('[data-ai-match]',wrap).forEach(b=>b.addEventListener('click',()=>runAIMatchmaker(b.dataset.aiMatch)));$$('[data-collaborate]',wrap).forEach(b=>b.addEventListener('click',()=>toast('Solicitud para colaborar enviada de manera segura.')));}
  function toggleDetails(id,btn){const d=$('#details-'+CSS.escape(id));if(!d)return;const open=d.classList.toggle('open');if(btn)btn.textContent=open?'Ocultar detalles':'Ver más detalles';}
  function handleNewNeed(e){e.preventDefault();const val=id=>$('#'+id)?.value?.trim()||'';const record={id:'need-'+Date.now(),title:val('need-pub-title'),type:val('need-pub-type'),operation:val('need-pub-operation'),ccaa:val('need-pub-ccaa'),province:val('need-pub-province'),municipality:val('need-pub-municipality'),locality:val('need-pub-locality'),budget:safeNumber(val('need-pub-budget')),buyerType:val('need-pub-buyer-type'),urgency:val('need-pub-urgency'),funding:val('need-pub-funding'),feeSplit:val('need-pub-fee'),description:val('need-pub-desc'),date:Date.now(),agency:'Agencia profesional registrada'};needs.unshift(record);if(persist(KEY_NEEDS,needs)){e.target.reset();addOptions($('#need-pub-province'),[],'Seleccione provincia');addOptions($('#need-pub-municipality'),[],'Seleccione municipio');renderNeedsDashboard();filterNeeds();toast('La necesidad de búsqueda se ha publicado correctamente.');}}
  function initNeedsPage(){if(!$('#needs-list-container'))return;renderNeedsDashboard();filterNeeds();$('#need-form')?.addEventListener('submit',handleNewNeed);$('#layout-block')?.addEventListener('click',()=>setNeedsLayout('bloque'));$('#layout-list')?.addEventListener('click',()=>setNeedsLayout('lista'));$('#need-clear-filters')?.addEventListener('click',clearNeedFilters);['need-filter-time','need-filter-price','need-filter-locality'].forEach(id=>$('#'+id)?.addEventListener(id==='need-filter-locality'?'input':'change',filterNeeds));}

  function sortMarketplace(){const mode=$('#market-sort')?.value||'newest';const list=[...properties];if(mode==='price-low')list.sort((a,b)=>safeNumber(a.price)-safeNumber(b.price));else if(mode==='price-high')list.sort((a,b)=>safeNumber(b.price)-safeNumber(a.price));else if(mode==='score')list.sort((a,b)=>safeNumber(b.score)-safeNumber(a.score));renderMarketplace(list);}
  function renderMarketplace(list=properties){const grid=$('#marketplace-grid');if(!grid)return;grid.innerHTML=list.map(p=>{const id=escapeHtml(p.id),img=p.image?`<img src="${escapeHtml(p.image)}" alt="Imagen de ${escapeHtml(p.title)}">`:'';return `<article class="op-card"><div class="property-cover">${img}<h3>${escapeHtml(p.title)}</h3></div><div class="card-body"><div class="spread"><span class="pill pill-green">Verificada</span><span class="score">${escapeHtml(p.score)}</span></div><p class="muted">📍 ${escapeHtml(p.neighborhood||p.location)}</p><div class="tags"><span class="pill pill-blue">${escapeHtml(p.type)}</span><span class="pill pill-amber">${escapeHtml(p.badgeText||'Colaboración B2B')}</span></div><div class="metrics"><div class="metric"><strong>${money(p.price)}</strong><span>Precio</span></div><div class="metric"><strong>${escapeHtml(p.fee)}</strong><span>Honorarios</span></div><div class="metric"><strong>${escapeHtml(p.location)}</strong><span>Zona</span></div></div><div id="market-details-${id}" class="details"><p><strong>Comentarios técnicos:</strong> ${escapeHtml(p.description)}</p><p><strong>Financiación:</strong> ${escapeHtml(p.fundingConditions||'Sujeto a estudio de solvencia.')}</p><p><strong>Documentación:</strong> ${escapeHtml(p.docs||'Información inicial')}</p></div><button class="btn btn-secondary btn-small full" data-market-details="${id}">Ver más detalles</button><button class="btn btn-primary btn-small full" data-property-access="${id}" style="margin-top:7px">Solicitar acceso</button></div></article>`;}).join('');$$('[data-market-details]',grid).forEach(b=>b.addEventListener('click',()=>{const d=$('#market-details-'+CSS.escape(b.dataset.marketDetails));const o=d?.classList.toggle('open');b.textContent=o?'Ocultar detalles':'Ver más detalles';}));$$('[data-property-access]',grid).forEach(b=>b.addEventListener('click',()=>openAccessModal(b.dataset.propertyAccess)));}
  function openAccessModal(id){selectedPropertyId=id;const p=properties.find(x=>x.id===id);if($('#access-property-title'))$('#access-property-title').textContent=p?.title||'Oportunidad seleccionada';openModal('#access-modal');}
  function requestPropertyAccess(){if(!selectedPropertyId)return;closeModal('#access-modal');toast('Solicitud de acceso enviada. El profesional publicador podrá validarla.');}
  function initMarketplacePage(){if(!$('#marketplace-grid'))return;renderMarketplace();$('#market-sort')?.addEventListener('change',sortMarketplace);$('#request-access')?.addEventListener('click',requestPropertyAccess);}

  function handleFileSelection(e){const file=e.target.files?.[0];if(!file)return;const name=$('#file-name'),preview=$('#file-preview'),status=$('#file-status');if(file.size>MAX_IMAGE_BYTES&&file.type.startsWith('image/')){e.target.value='';uploadedFileBase64='';toast('La imagen supera 1,2 MB. Redúcela antes de adjuntarla.','info');return;}if(name)name.textContent=file.name;if(preview)preview.classList.add('open');if(status)status.textContent=file.type.startsWith('image/')?'Imagen lista para previsualizar.':'Documento PDF adjuntado como referencia.';if(file.type.startsWith('image/')){const r=new FileReader();r.onload=ev=>{uploadedFileBase64=String(ev.target?.result||'');toast('Imagen cargada correctamente.');};r.readAsDataURL(file);}else{uploadedFileBase64='';toast('Documento PDF adjuntado correctamente.');}}
  function handleNewOffer(e){e.preventDefault();const val=id=>$('#'+id)?.value?.trim()||'';tempPropertyToPublish={id:'user-prop-'+Date.now(),title:val('offer-title'),type:val('offer-type'),location:val('offer-province'),neighborhood:`${val('offer-province')} · ${val('offer-municipality')}${val('offer-locality')?' ('+val('offer-locality')+')':''}`,price:safeNumber(val('offer-price')),fee:val('offer-fee'),rehab:val('offer-rehab')==='yes',exclusive:val('offer-exclusive')==='yes',urgency:val('offer-urgency'),docs:val('offer-docs'),score:Math.floor(Math.random()*21)+78,description:val('offer-description'),badgeText:val('offer-exclusive')==='yes'?'Exclusiva compartida':'Abierta a colaboración',fundingConditions:'Sujeto a viabilidad y estudio de solvencia del perfil interesado.',image:uploadedFileBase64};renderOfferPreview();openModal('#preview-modal');}
  function renderOfferPreview(){const w=$('#preview-area');if(!w||!tempPropertyToPublish)return;const p=tempPropertyToPublish;w.innerHTML=`<article class="preview-card">${p.image?`<img src="${escapeHtml(p.image)}" alt="Vista previa de la captación">`:''}<div class="property-cover"><h3>${escapeHtml(p.title)}</h3></div><div class="card-body"><div class="spread"><span class="pill pill-green">Verificada</span><span class="score">${escapeHtml(p.score)}</span></div><p class="muted">Zona aproximada visible. Datos sensibles mediante solicitud validada.</p><div class="tags"><span class="pill pill-blue">${escapeHtml(p.badgeText)}</span><span class="pill pill-amber">${p.urgency==='Alta'?'Alta motivación':'Plazo ordinario'}</span></div><div class="metrics"><div class="metric"><strong>${money(p.price)}</strong><span>Precio</span></div><div class="metric"><strong>${escapeHtml(p.fee)}</strong><span>Honorarios</span></div><div class="metric"><strong>${escapeHtml(p.location)}</strong><span>Zona</span></div></div></div></article>`;}
  function confirmAndPublish(){if(!tempPropertyToPublish)return;properties.unshift(tempPropertyToPublish);if(persist(KEY_PROPERTIES,properties)){tempPropertyToPublish=null;uploadedFileBase64='';$('#offer-form')?.reset();$('#file-preview')?.classList.remove('open');if($('#file-status'))$('#file-status').textContent='Arrastra tus archivos o haz clic para subir JPG, PNG o PDF.';closeModal('#preview-modal');toast('La captación se ha publicado con éxito en el marketplace.');setTimeout(()=>location.href='/marketplace/',500);}}
  async function callGemini(prompt,systemInstruction=''){if(!GEMINI_PROXY_URL)throw new Error('La función de IA está preparada, pero requiere conectar un endpoint seguro del servidor.');const response=await fetch(GEMINI_PROXY_URL,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({prompt,systemInstruction})});if(!response.ok)throw new Error('No se pudo completar la consulta de IA.');const data=await response.json();return data.text||data.result||'';}
  async function generateAIDescription(){const btn=$('#ai-gen-btn');if(!btn)return;const val=id=>$('#'+id)?.value?.trim()||'';if(!val('offer-type')||!val('offer-ccaa')||!val('offer-province')||!val('offer-municipality')||!val('offer-price')){toast('Completa tipo, ubicación y precio antes de redactar con IA.','info');return;}const old=btn.textContent;btn.disabled=true;btn.innerHTML='<span class="spinner">◌</span> Redactando...';try{const result=await callGemini(`Redacta una ficha B2B inmobiliaria con título precedido por [TITULO] y una descripción de máximo 150 palabras. Tipo: ${val('offer-type')}. Ubicación: ${val('offer-ccaa')}, ${val('offer-province')}, ${val('offer-municipality')}. Precio: ${val('offer-price')} €. Comisión: ${val('offer-fee')}. Evita datos sensibles.`,`Eres un redactor inmobiliario senior especializado en marketing B2B.`);const after=result.includes('[TITULO]')?result.split('[TITULO]')[1].trim():result;const pos=after.indexOf('\n');if(pos>-1){$('#offer-title').value=after.slice(0,pos).trim();$('#offer-description').value=after.slice(pos).trim();}else $('#offer-description').value=after;toast('Texto generado con IA.');}catch(err){toast(err.message,'info');}finally{btn.disabled=false;btn.textContent=old;}}
  function renderMarkdown(text){let safe=escapeHtml(text);safe=safe.replace(/^### (.*)$/gm,'<h4>$1</h4>').replace(/^## (.*)$/gm,'<h3>$1</h3>').replace(/^# (.*)$/gm,'<h2>$1</h2>').replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/\*(.*?)\*/g,'<em>$1</em>').replace(/\n/g,'<br>');return safe;}
  async function runAIMatchmaker(id){const n=needs.find(x=>x.id===id);if(!n)return;openModal('#ai-match-modal');const content=$('#ai-report-content');if(content)content.innerHTML='<p><span class="spinner">◌</span> Analizando coincidencias...</p>';try{const result=await callGemini(`Analiza esta demanda inmobiliaria y contrástala con la cartera. Demanda: ${JSON.stringify(n)}. Cartera: ${JSON.stringify(properties)}. Indica coincidencias, porcentaje estimado de encaje, reparto de honorarios y redacta una invitación B2B.`,`Eres un consultor PropTech experto en colaboraciones inmobiliarias B2B.`);if(content)content.innerHTML=renderMarkdown(result);}catch(err){if(content)content.innerHTML=`<div class="notice">${escapeHtml(err.message)}</div>`;}}
  async function copyAiReport(){const text=$('#ai-report-content')?.innerText||'';if(!text)return;try{await navigator.clipboard.writeText(text);toast('Informe copiado al portapapeles.');}catch(e){const ta=document.createElement('textarea');ta.value=text;document.body.appendChild(ta);ta.select();document.execCommand('copy');ta.remove();toast('Informe copiado al portapapeles.');}}
  function initOfferPage(){if(!$('#offer-form'))return;$('#offer-form').addEventListener('submit',handleNewOffer);$('#offer-file')?.addEventListener('change',handleFileSelection);$('#upload-box')?.addEventListener('click',e=>{if(e.target?.id!=='offer-file')$('#offer-file')?.click();});$('#ai-gen-btn')?.addEventListener('click',generateAIDescription);$('#preview-publish')?.addEventListener('click',confirmAndPublish);}

  function renderDashboard(){const body=$('#dash-table-body');if(!body)return;const total=properties.reduce((a,p)=>a+safeNumber(p.price)*(parseFloat(p.fee)||3.5)/100,0);if($('#dash-active-count'))$('#dash-active-count').textContent=properties.length;if($('#dash-total-fees'))$('#dash-total-fees').textContent=money(total);body.innerHTML=properties.map(p=>`<tr><td><strong>${escapeHtml(p.title)}</strong></td><td>${escapeHtml(p.location)}</td><td>${money(p.price)}</td><td>${escapeHtml(p.fee)}</td><td><button type="button" class="btn btn-small danger" data-delete-listing="${escapeHtml(p.id)}">Dar de baja</button></td></tr>`).join('');$$('[data-delete-listing]',body).forEach(b=>b.addEventListener('click',()=>{properties=properties.filter(p=>p.id!==b.dataset.deleteListing);persist(KEY_PROPERTIES,properties);renderDashboard();toast('La captación se ha dado de baja.','info');}));}
  function initDashboard(){renderDashboard();}
  function initCalculator(){const calc=()=>{const price=safeNumber($('#calc-price')?.value),pct=safeNumber($('#calc-pct')?.value),split=safeNumber($('#calc-split')?.value);if($('#calc-total'))$('#calc-total').textContent=money(price*pct/100);if($('#calc-share'))$('#calc-share').textContent=money(price*pct/100*split/100);};['calc-price','calc-pct','calc-split'].forEach(id=>$('#'+id)?.addEventListener('input',calc));calc();}
  function initDemoForms(){$$('form[data-demo-form]').forEach(f=>f.addEventListener('submit',e=>{e.preventDefault();toast(f.dataset.success||'Solicitud registrada correctamente.');f.reset();}));}
  function initDownloads(){$$('[data-download-name]').forEach(a=>a.addEventListener('click',()=>toast('Descarga iniciada: '+a.dataset.downloadName)));}

  function init(){initMenu();initCookie();initModals();initGeoSelectors();initNeedsPage();initMarketplacePage();initOfferPage();initDashboard();initCalculator();initDemoForms();initDownloads();$('#copy-ai-report')?.addEventListener('click',copyAiReport);}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
