import {test} from 'node:test';
import assert from 'node:assert/strict';
import {normalizeIds,createSelectionStore,createPropertyCollection} from '../../resources/js/property-selections.js';

function memoryStorage(){const values=new Map;return {getItem:key=>values.get(key)??null,setItem:(key,value)=>values.set(key,value)};}

test('saved IDs accept the previous browser format and reject malformed values',()=>{
    assert.deepEqual(normalizeIds([1,'2',2,null,true,-1,0,'x',{},3.5]),[1,2]);
    const storage=memoryStorage();storage.setItem('propertyme.saved-properties','[1,2,3]');
    const saved=createSelectionStore({key:'propertyme.saved-properties'},storage);assert.deepEqual(saved.ids,[1,2,3]);
    saved.toggle(2);saved.toggle(4);assert.deepEqual(saved.ids,[1,3,4]);
    assert.deepEqual(createSelectionStore({key:'propertyme.saved-properties'},storage).ids,[1,3,4]);
});
test('comparison permits three properties and explains an attempted fourth selection',()=>{
    const store=createSelectionStore({key:'compare',limit:3,limitMessage:'Maximum 3'},memoryStorage());
    [1,2,3].forEach(id=>store.toggle(id));assert.equal(store.toggle(4),false);assert.equal(store.notice,'Maximum 3');assert.deepEqual(store.ids,[1,2,3]);
    store.toggle(2);assert.equal(store.toggle(4),true);assert.deepEqual(store.ids,[1,3,4]);assert.equal(store.notice,'');
});
test('selection remains usable with blocked browser storage and can remove unavailable IDs',()=>{
    const storage={getItem(){throw Error('blocked');},setItem(){throw Error('blocked');}};
    const store=createSelectionStore({key:'saved'},storage);store.toggle(1);store.toggle(2);store.retain([2]);assert.deepEqual(store.ids,[2]);store.clear();assert.deepEqual(store.ids,[]);
});
test('collection loads database records in the requested mode and reports unavailable properties',async()=>{
    let requested;
    const collection=createPropertyCollection({mode:'compare',endpoint:'https://example.test/property-selection?lang=id'},async url=>{requested=new URL(url);return {ok:true,json:async()=>({properties:[{id:3,title:'Vila'}],unavailable:1})};});
    await collection.refresh([3,9]);assert.equal(requested.searchParams.get('ids'),'3,9');assert.equal(requested.searchParams.get('mode'),'compare');assert.equal(requested.searchParams.get('lang'),'id');assert.equal(collection.items[0].title,'Vila');assert.equal(collection.unavailable,1);assert.equal(collection.loading,false);
});
test('failed requests can be retried without losing saved IDs',async()=>{
    let attempts=0;const collection=createPropertyCollection({mode:'favorites',endpoint:'https://example.test/property-selection'},async()=>({ok:++attempts>1,json:async()=>({properties:[{id:1}]})}));
    await collection.refresh([1]);assert.equal(collection.error,true);await collection.retry([1]);assert.equal(collection.error,false);assert.equal(collection.items[0].id,1);
});
test('an older slow response cannot replace a newer selection',async()=>{
    let finish;const firstResponse=new Promise(resolve=>{finish=resolve;});let requests=0;
    const collection=createPropertyCollection({mode:'favorites',endpoint:'https://example.test/property-selection'},async()=>{if(++requests===1)return firstResponse;return {ok:true,json:async()=>({properties:[{id:2}]})};});
    const old=collection.refresh([1]);await collection.refresh([2]);finish({ok:true,json:async()=>({properties:[{id:1}]})});await old;assert.equal(collection.items[0].id,2);
});
