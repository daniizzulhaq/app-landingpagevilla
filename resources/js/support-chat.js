function browserStorage() {
    try { return globalThis.localStorage; } catch { return null; }
}

export function readInquiryHistory(storage = browserStorage()) {
    try {
        const records = JSON.parse(storage?.getItem('propertyme.inquiry-history.v1') || '[]');
        if (!Array.isArray(records)) return [];
        return records.filter(item => item && typeof item.id==='string' && typeof item.title==='string' && typeof item.message==='string' && typeof item.openedAt==='string')
            .slice(0,10).map(item=>({id:item.id.slice(0,80),title:item.title.slice(0,200),message:item.message.slice(0,3000),openedAt:item.openedAt.slice(0,40)}));
    } catch { return []; }
}

export function createSupportChat(config, storage = browserStorage()) {
    return {
        open:false, tab:'chat', selected:null, question:'', history:readInquiryHistory(storage),
        phone:typeof config.phone==='string' && /^[1-9][0-9]{7,14}$/.test(config.phone) ? config.phone : null,
        labels:{continue:'Continue on WhatsApp',contact:'View Contact Options',close:'Close',launcher:'Chat with us',...(config.strings || {})},
        openPanel() { this.open=true; this.$nextTick(()=>this.$refs.closeButton.focus()); },
        closePanel() { this.open=false; this.$nextTick(()=>this.$refs.launcher.focus()); },
        choose(key) { this.selected=config.topics.find(topic=>topic.key===key) || null; },
        get message() {
            const template=this.selected?.message || config.defaultMessage;
            return template+(this.question.trim() ? '\n\n'+(config.strings?.additional || 'Additional details:')+' '+this.question.trim().slice(0,1000) : '');
        },
        get chatUrl() { return this.phone ? 'https://wa.me/'+this.phone+'?text='+encodeURIComponent(this.message) : config.fallbackUrl; },
        historyUrl(item) { return this.phone ? 'https://wa.me/'+this.phone+'?text='+encodeURIComponent(item.message) : config.fallbackUrl; },
        remember() {
            if(!this.phone) return;
            const entry={id:globalThis.crypto?.randomUUID?.() || Date.now().toString(36)+Math.random().toString(36).slice(2),title:this.selected?.label || config.strings?.defaultTitle || 'Property consultation',message:this.message,openedAt:new Date().toISOString()};
            this.history=[entry,...this.history.filter(item=>item.message!==entry.message)].slice(0,10);
            this.saveHistory();
        },
        clearHistory() { this.history=[]; this.saveHistory(); },
        saveHistory() { try { storage?.setItem('propertyme.inquiry-history.v1',JSON.stringify(this.history)); } catch { /* History stays in memory when storage is unavailable. */ } },
        formatDate(value) { const date=new Date(value); return Number.isNaN(date.valueOf()) ? '' : date.toLocaleString(undefined,{dateStyle:'medium',timeStyle:'short'}); },
    };
}
