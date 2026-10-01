let csrf = '';
const el = id => document.getElementById(id);
async function api(route,options={}) {
  const response = await fetch(`/server/api.php?route=${route}`,{credentials:'same-origin',...options});
  const data = await response.json();
  if (!response.ok) throw new Error(data.error || 'Ошибка запроса');
  return data;
}
const post = data => ({method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify(data)});
const clean = value => String(value ?? '').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
async function show(logged) {
  el('guest').hidden=logged; el('customer-dashboard').hidden=!logged;
  if (!logged) return;
  try {
    const data = await api('my-orders');
    el('customer-orders').innerHTML = data.orders.map(o=>`<article class="account-order"><strong>Заказ ${clean(o.code)}</strong><p>${clean(o.status)} · ${Number(o.total)} ₽ · ${o.fulfillment==='delivery'?'Доставка':'Самовывоз'}</p></article>`).join('') || '<p>Заказов пока нет.</p>';
  } catch(error) { el('customer-orders').textContent=error.message; }
}
for (const [formId,route,fields] of [
  ['customer-login','customer-login',['phone','password']],
  ['customer-register','register',['phone','password','repeat']],
  ['customer-reset','reset-password',['phone','code','password','repeat']]
]) {
  el(formId).addEventListener('submit',async event=>{
    event.preventDefault(); const form=event.currentTarget; const output=form.querySelector('[role=status]'); const data={};
    fields.forEach(field=>data[field]=form.elements[field].value.trim()); output.textContent='Проверяем…';
    try {
      const result=await api(route,post(data));
      if(result.csrf)csrf=result.csrf;
      output.textContent=route==='reset-password'?'Пароль изменён. Теперь войдите.':'';
      form.reset(); if(route!=='reset-password')show(true);
    } catch(error) { output.textContent=error.message; }
  });
}
el('customer-logout').addEventListener('click',async()=>{try{const result=await api('customer-logout',post({}));csrf=result.csrf;show(false);}catch(error){el('customer-orders').textContent=error.message;}});
api('session').then(data=>{csrf=data.csrf;show(data.customer);}).catch(()=>{el('customer-login').querySelector('[role=status]').textContent='Сервер недоступен.';});
