import {test} from 'node:test';
import assert from 'node:assert/strict';
import {createSupportChat,readInquiryHistory} from '../../resources/js/support-chat.js';

const key='propertyme.inquiry-history.v1';
const settings={phone:'6281234567890',fallbackUrl:'http://localhost/contact#contact-channels',defaultMessage:'Interested in Palm Villa. Property link: https://example.test/properties/palm-villa',topics:[{key:'rent',label:'Plan my stay',message:'Please check villa dates & rental rates.'},{key:'buy',label:'Buy a property',message:'Please help me explore properties for sale.'}]};
function memoryStorage(){ const values=new Map; return {getItem:key=>values.get(key) ?? null,setItem:(key,value)=>values.set(key,value)}; }

test('a property inquiry retains its context and encodes visitor text correctly',()=>{
    const chat=createSupportChat(settings,memoryStorage()); chat.question='June 12–15, 4 guests & a pool?';
    const url=new URL(chat.chatUrl); assert.equal(url.origin,'https://wa.me'); assert.equal(url.pathname,'/6281234567890');
    assert.equal(url.searchParams.get('text'),settings.defaultMessage+'\n\nAdditional details: June 12–15, 4 guests & a pool?');
    assert.equal(chat.history.length,0);
});
test('quick choices use their matching inquiry templates',()=>{
    const chat=createSupportChat(settings,memoryStorage()); chat.choose('rent');
    assert.equal(new URL(chat.chatUrl).searchParams.get('text'),settings.topics[0].message);
    chat.choose('buy'); assert.equal(chat.message,settings.topics[1].message);
});
test('a missing or invalid phone never invents a WhatsApp recipient or saves a false inquiry',()=>{
    for(const phone of [null,'','+62bad','javascript:invalid']){
        const chat=createSupportChat({...settings,phone},memoryStorage());
        assert.equal(chat.chatUrl,settings.fallbackUrl); chat.remember(); assert.equal(chat.history.length,0);
    }
});
test('history starts empty and only records opened inquiries, without duplicates',()=>{
    const storage=memoryStorage(); const chat=createSupportChat(settings,storage);
    chat.choose('rent'); chat.remember(); chat.remember(); assert.equal(chat.history.length,1);
    const restored=createSupportChat(settings,storage); assert.equal(restored.history[0].title,'Plan my stay');
    assert.equal(restored.history[0].message,settings.topics[0].message);
    assert.ok(!('delivered' in restored.history[0]));
    restored.clearHistory(); assert.deepEqual(readInquiryHistory(storage),[]);
});
test('history keeps at most ten items and reopens with the current company number',()=>{
    const storage=memoryStorage(); const chat=createSupportChat(settings,storage);
    for(let n=0;n<12;n++){chat.question='Question '+n;chat.remember();}
    assert.equal(chat.history.length,10);
    const updated=createSupportChat({...settings,phone:'447911123456'},storage);
    assert.equal(new URL(updated.historyUrl(updated.history[0])).pathname,'/447911123456');
});
test('blocked storage and malformed history do not break composing a message',()=>{
    const blocked={getItem(){throw Error('blocked');},setItem(){throw Error('blocked');}};
    const chat=createSupportChat(settings,blocked); chat.remember(); assert.equal(chat.history.length,1);
    const storage=memoryStorage(); storage.setItem(key,'{bad json'); assert.deepEqual(readInquiryHistory(storage),[]);
    storage.setItem(key,JSON.stringify([null,{id:'bad'}])); assert.deepEqual(readInquiryHistory(storage),[]);
    chat.question='x'.repeat(1500); assert.equal(chat.message.split('Additional details: ')[1].length,1000);
});
