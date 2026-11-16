export function normalizeIds(values, limit = 200) {
    if(!Array.isArray(values)) return [];
    return [...new Set(values.filter(value=>typeof value==='number' || (typeof value==='string' && /^\d+$/.test(value))).map(Number).filter(id=>Number.isSafeInteger(id) && id>0))].slice(0,limit);
}

export function createSelectionStore({key,limit=200,limitMessage='Compare up to 3 properties. Remove one to add another.'},storage) {
    const read=()=>{try{return normalizeIds(JSON.parse(storage?.getItem(key) || '[]'),limit);}catch{return [];}};
    return {
        ids:read(),notice:'',
        has(id){return this.ids.includes(Number(id));},
        toggle(id){
            id=Number(id); if(!Number.isSafeInteger(id) || id<1) return false;
            this.notice='';
            if(this.has(id)) this.ids=this.ids.filter(value=>value!==id);
            else if(this.ids.length>=limit){this.notice=limitMessage;return false;}
            else this.ids=[...this.ids,id];
            this.save();return true;
        },
        remove(id){this.ids=this.ids.filter(value=>value!==Number(id));this.notice='';this.save();},
        clear(){this.ids=[];this.notice='';this.save();},
        retain(ids){const keep=normalizeIds(ids,limit);this.ids=this.ids.filter(id=>keep.includes(id));this.save();},
        sync(){this.ids=read();},
        save(){try{storage?.setItem(key,JSON.stringify(this.ids));}catch{/* Keep selection usable without persistent storage. */}},
    };
}

export function createPropertyCollection(config,fetcher=globalThis.fetch) {
    return {
        items:[],loading:false,error:false,unavailable:0,requested:null,requestVersion:0,
        refresh(values){
            const ids=normalizeIds(values,config.mode==='compare'?3:200);
            const key=ids.join(',');if(key===this.requested)return;
            this.requested=key;return this.load(ids);
        },
        async load(ids){
            const version=++this.requestVersion;this.loading=true;this.error=false;
            if(!ids.length){this.items=[];this.unavailable=0;this.loading=false;return;}
            try{
                const url=new URL(config.endpoint);url.searchParams.set('ids',ids.join(','));url.searchParams.set('mode',config.mode);
                const response=await fetcher(url.toString(),{headers:{Accept:'application/json'},credentials:'same-origin'});
                if(!response.ok)throw Error('Unavailable');const data=await response.json();
                if(version!==this.requestVersion)return;
                this.items=Array.isArray(data.properties)?data.properties:[];this.unavailable=Number(data.unavailable)||0;
            }catch{if(version===this.requestVersion){this.error=true;this.items=[];}}
            finally{if(version===this.requestVersion)this.loading=false;}
        },
        retry(values){this.requested=null;return this.refresh(values);},
    };
}
