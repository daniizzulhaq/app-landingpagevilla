import Alpine from 'alpinejs';
import { createSupportChat } from './support-chat';
import { createSelectionStore, createPropertyCollection } from './property-selections';

Alpine.data('supportChat', (config) => createSupportChat(config));

function selectionStorage(){try{return globalThis.localStorage;}catch{return null;}}
Alpine.store('savedProperties',createSelectionStore({key:'propertyme.saved-properties',limit:200},selectionStorage()));
Alpine.store('comparison',createSelectionStore({key:'propertyme.compare-properties',limit:3,limitMessage:document.documentElement.lang==='id' ? 'Bandingkan maksimal 3 properti. Hapus salah satu untuk menambahkan properti lain.' : 'Compare up to 3 properties. Remove one to add another.'},selectionStorage()));
Alpine.data('propertyCollection',(config)=>createPropertyCollection(config));
window.addEventListener('storage',(event)=>{if(event.key===null || event.key==='propertyme.saved-properties')Alpine.store('savedProperties').sync();if(event.key===null || event.key==='propertyme.compare-properties')Alpine.store('comparison').sync();});

Alpine.data('propertyCard', (photos, url, title) => ({
    photos, index: 0, shareStatus: '',
    next() { this.index = (this.index+1)%this.photos.length; },
    previous() { this.index = (this.index-1+this.photos.length)%this.photos.length; },
    async share() {
        try {
            if (navigator.share) { await navigator.share({title,url}); this.shareStatus=''; }
            else if (navigator.clipboard?.writeText) { await navigator.clipboard.writeText(url); this.shareStatus='Property link copied.'; }
            else { this.shareStatus='Copy this property link: '+url; }
        } catch(error) { if(error.name!=='AbortError') this.shareStatus='Copy this property link: '+url; }
    },
}));

Alpine.data('gallery', (items = []) => ({
    items, index: 0,
    show(index) { this.index = index; this.$refs.lightbox.showModal(); },
    next() { this.index = (this.index + 1) % this.items.length; },
    previous() { this.index = (this.index - 1 + this.items.length) % this.items.length; },
    close() { this.$refs.lightbox.close(); },
}));
Alpine.data('imagePreview', () => ({
    previews: [],
    select(event) {
        this.previews.forEach(url => URL.revokeObjectURL(url));
        this.previews = Array.from(event.target.files).filter(file => file.type.startsWith('image/')).map(file => URL.createObjectURL(file));
    },
    destroy() { this.previews.forEach(url => URL.revokeObjectURL(url)); },
}));
window.Alpine = Alpine;
Alpine.start();

// Content remains visible when JS is unavailable or motion is reduced.
if (! window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('motion-ready');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); } });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(element => observer.observe(element));
}

document.querySelectorAll('img[data-fallback]').forEach(img => {
    const fallback = () => { if (img.src !== img.dataset.fallback) img.src = img.dataset.fallback; };
    img.addEventListener('error', fallback, { once: true });
    if (img.complete && img.naturalWidth === 0) fallback();
});
