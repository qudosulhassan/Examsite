/* ExamTopicsBase practice-test engine.
 *
 * Behaviour matches the PL-900 practice file (one question at a time, Check / Reveal,
 * Yes-No rows, drop-down and drag & drop answer areas, per-row feedback, self-grading,
 * locked navigator), with results saved to the website.
 *
 * Config comes from window.ETB_ENGINE (see resources/views/test-engine/engine.blade.php):
 *   mode       practice | review | exam   (exam: no answers shipped, free navigation, graded on the server)
 *   questions  [{qid, number, topic, type, question[], questionHtml?, options[], images[], boxCount, yesNo, ia}]
 *   answers    base64 JSON {qid: answer} — null in exam mode
 *   state      {qid: {sel, boxes, rows, status, self, flagged}}
 *   urls       {save, flag, submit, restart, back}
 */
(function(){
"use strict";
const C = window.ETB_ENGINE;
const QUESTIONS = C.questions;
const TOTAL = QUESTIONS.length;
const EXAM = C.mode === "exam";

let _answers = null;
function answerFor(q){
  if(EXAM || !C.answers) return {};
  if(!_answers){
    const bin = atob(C.answers); const bytes = new Uint8Array(bin.length);
    for(let i=0;i<bin.length;i++) bytes[i]=bin.charCodeAt(i);
    _answers = JSON.parse(new TextDecoder().decode(bytes));
  }
  return _answers[q.qid] || {};
}

/* ---------- state ---------- */
const STORE_KEY = C.storeKey;
const S = {cur:0, furthest:0, q:{}};
QUESTIONS.forEach(q=>{
  const st = (C.state && C.state[q.qid]) || {};
  S.q[q.qid] = {sel: st.sel||[], boxes: st.boxes||{}, rows: st.rows||{}, status: st.status||null, self: !!st.self, flagged: !!st.flagged};
});
// Unlock up to the first question not yet finished (practice); everything in exam mode.
if(EXAM){ S.furthest = TOTAL-1; }
else { let f = 0; while(f < TOTAL-1 && S.q[QUESTIONS[f].qid].status) f++; S.furthest = f; }
try{ const raw = localStorage.getItem(STORE_KEY); if(raw){ const p = JSON.parse(raw); if(p && typeof p.cur==="number" && p.cur>=0 && p.cur<TOTAL) S.cur = Math.min(p.cur, S.furthest); } }catch(e){}
function saveLocal(){ try{ localStorage.setItem(STORE_KEY, JSON.stringify({cur:S.cur})); }catch(e){} }
function qs(i){ return S.q[QUESTIONS[i].qid]; }
// status: null | 'correct' | 'incorrect' | 'revealed' | 'reviewed' | 'answered' (exam mode)

/* ---------- server sync ---------- */
function post(url, body){
  return fetch(url, {method:"POST", credentials:"same-origin",
    headers:{"Content-Type":"application/json","Accept":"application/json","X-CSRF-TOKEN":C.csrf,"X-Requested-With":"XMLHttpRequest"},
    body: JSON.stringify(Object.assign({attempt_id: C.attemptId}, body))
  }).then(r=>{ if(!r.ok) throw new Error("HTTP "+r.status); return r.json(); });
}
function note(msg, err){ const n = $("#saveNote"); if(n){ n.textContent = msg; n.classList.toggle("err", !!err); } }
function sync(i){
  const q = QUESTIONS[i], st = qs(i);
  const response = {sel:st.sel, boxes:st.boxes, rows:st.rows, status:st.status, self:st.self};
  post(C.urls.save, {question_id: q.qid, response}).then(()=>{ if(S.cur===i) note(EXAM ? "✓ Answer saved" : "✓ Progress saved"); })
    .catch(()=>{ if(S.cur===i) note("⚠ Could not save to the server — check your connection. Your answer is kept on this page.", true); });
}

/* ---------- helpers ---------- */
const $ = s => document.querySelector(s);
function esc(s){ return String(s).replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c])); }
function paras(arr){ return (arr||[]).map(p=>`<p>${esc(p)}</p>`).join(""); }
function linkify(u){ const e = esc(u); return /^https?:\/\//.test(u) ? `<a href="${e}" target="_blank" rel="noopener noreferrer">${e}</a>` : e; }
const TYPE_LABEL = {"multiple-choice":"Multiple Choice","multiple-select":"Multiple Select","hotspot":"Hotspot","drag-drop":"Drag & Drop"};
function isDone(st){ return st && st.status !== null; }
function gradableQ(q){ return q.options.length || (q.boxCount && q.yesNo) || q.ia; }
function hasResponse(q, st){
  if(q.options.length) return st.sel.length>0;
  if(q.ia) return q.ia.rows.every((r,n)=>st.rows[n]);
  if(q.boxCount && q.yesNo){ for(let b=1;b<=q.boxCount;b++){ if(!st.boxes[b]) return false; } return true; }
  return false;
}

/* ---------- render question ---------- */
function render(){
  const i = S.cur, q = QUESTIONS[i], st = qs(i);
  $("#doneCard").classList.add("hidden"); $("#qCard").classList.remove("hidden");
  const done = isDone(st) && !EXAM;
  let h = `<div class="q-top"><span class="q-num">Question ${i+1}</span><span class="tag">${TYPE_LABEL[q.type]||"Question"}</span>${q.topic?`<span class="tag topic">${esc(q.topic)}</span>`:""}
    <button type="button" class="q-flag ${st.flagged?"on":""}" id="flagBtn" aria-pressed="${st.flagged}">⚑ ${st.flagged?"Flagged":"Flag"}</button></div>`;
  h += q.questionHtml ? `<div class="q-text q-html">${q.questionHtml}</div>` : `<div class="q-text">${paras(q.question)}</div>`;
  (q.images||[]).forEach((src,n)=>{ h += `<figure class="exhibit"><img src="${esc(src)}" alt="Question ${i+1} exhibit ${n+1}" loading="lazy"><figcaption>Exhibit${q.images.length>1?" "+(n+1):""} · tap to enlarge</figcaption></figure>`; });

  if(q.options.length){
    const multi = q.type === "multiple-select";
    h += `<ul class="options" role="${multi?"group":"radiogroup"}">`;
    q.options.forEach(o=>{
      const checked = st.sel.includes(o.label);
      h += `<li><label class="opt ${checked?"selected":""} ${done?"locked":""}" data-l="${esc(o.label)}">
        <input type="${multi?"checkbox":"radio"}" name="q${q.qid}" value="${esc(o.label)}" ${checked?"checked":""} ${done?"disabled":""}>
        <span class="lab">${esc(o.label)}.</span><span class="txt">${esc(o.text)}</span></label></li>`;
    });
    h += `</ul>`;
    if(multi) h += `<div class="select-hint">Select all answers that apply.</div>`;
  } else {
    h += `<div class="answer-area"><h3>Your Answer</h3>`;
    if(q.ia){
      h += renderIA(q, st, done);
    } else if(q.boxCount && q.yesNo){
      h += `<p class="notice">Select Yes or No for each statement in the exhibit above (Box 1 = first statement, Box 2 = second, …), then press <b>${EXAM?"Save Answer":"Check Answer"}</b>.</p>`;
      for(let b=1;b<=q.boxCount;b++){
        const v = st.boxes[b];
        h += `<div class="box-row"><span class="bl">Box ${b}</span><span class="seg" data-box="${b}">
          <button type="button" data-v="Yes" class="${v==="Yes"?"on":""}" ${done?"disabled":""}>Yes</button>
          <button type="button" data-v="No" class="${v==="No"?"on":""}" ${done?"disabled":""}>No</button></span></div>`;
      }
    } else {
      h += EXAM
        ? `<p class="notice">This question is answered using the exhibit above. It is not scored in exam mode — review it in practice mode.</p>`
        : `<p class="notice">Work out your selections using the answer area in the exhibit above, then press <b>Reveal Answer</b> to compare with the correct answer and mark yourself.</p>`;
    }
    h += `</div>`;
  }

  const gradable = gradableQ(q);
  if(EXAM){
    h += `<div class="actions">
      <button class="btn" id="prevBtn" ${i===0?"disabled":""}>← Previous</button>
      ${gradable?`<button class="btn primary" id="checkBtn">Save Answer</button>`:""}
      <button class="btn" id="resetBtn">Clear</button>
      <button class="btn next" id="nextBtn">${i===TOTAL-1?"Finish Exam":"Next Question →"}</button>
    </div>`;
  } else {
    h += `<div class="actions">
      ${gradable?`<button class="btn primary" id="checkBtn" ${done?"disabled":""}>Check Answer</button>`:""}
      <button class="btn warn" id="revealBtn" ${done?"disabled":""}>Reveal Answer</button>
      <button class="btn" id="resetBtn">Reset</button>
      <button class="btn next ${done?"":"hidden"}" id="nextBtn">${i===TOTAL-1?"🎉 Test Completed":"Next Question →"}</button>
    </div>`;
  }
  h += `<div id="fb"></div><div id="ap"></div><div class="save-note" id="saveNote"></div>`;
  $("#qCard").innerHTML = h;

  if(EXAM){ if(st.status==="answered") $("#fb").innerHTML = `<div class="feedback info">✓ Answer saved. You can change it until you submit the exam.</div>`; }
  else if(done){ showFeedback(); showAnswerPanel(); applyMarks(); }
  bindQuestion();
  updateChrome();
  window.scrollTo({top:0,behavior:"instant" in window ? "instant" : "auto"});
}

function renderIA(q, st, done){
  const ia = q.ia, R = st.rows || {};
  let h = "";
  const dis = done ? "disabled" : "";
  const act = EXAM ? "Save Answer" : "Check Answer";
  if(ia.kind === "yesno"){
    h += `<p class="notice">Select Yes or No for each statement, then press <b>${act}</b>.</p>`;
    ia.rows.forEach((r,n)=>{
      const v = R[n];
      h += `<div class="ia-row" data-row="${n}"><div class="ia-label">${esc(r.label)}</div><span class="seg" data-row="${n}">
        <button type="button" data-v="Yes" class="${v==="Yes"?"on":""}" ${dis}>Yes</button>
        <button type="button" data-v="No" class="${v==="No"?"on":""}" ${dis}>No</button></span><div class="ia-fix"></div></div>`;
    });
    return h;
  }
  if(ia.kind === "select"){
    h += `<p class="notice">Choose the correct option from each drop-down list, then press <b>${act}</b>.</p>`;
  } else {
    h += `<p class="notice">${ia.kind==="order" ? "Put the actions in the correct order." : "Match an item to each target."} Drag an item onto a box, tap an item then tap a box, or pick from the drop-down. Items can be used more than once or not at all. Then press <b>${act}</b>.</p>`;
    h += `<div class="pool">${ia.pool.map(p=>`<button type="button" class="chip-item" draggable="${done?"false":"true"}" data-v="${esc(p)}" ${dis}>${esc(p)}</button>`).join("")}</div>`;
  }
  ia.rows.forEach((r,n)=>{
    const choices = r.choices || ia.pool, v = R[n] || "";
    h += `<div class="ia-row ${ia.kind!=="select"?"droppable":""}" data-row="${n}"><div class="ia-label">${ia.kind==="order"?`<b>${esc(r.label)}</b>`:esc(r.label)}</div>
      <select class="ia-select" data-row="${n}" ${dis}><option value="">— Select —</option>${choices.map(c=>`<option value="${esc(c)}" ${c===v?"selected":""}>${esc(c)}</option>`).join("")}</select><div class="ia-fix"></div></div>`;
  });
  return h;
}

function bindQuestion(){
  const i = S.cur, q = QUESTIONS[i], st = qs(i);
  const locked = ()=> isDone(st) && !EXAM;
  const touched = ()=>{ if(EXAM && st.status==="answered"){ st.status=null; $("#fb").innerHTML=""; note("Unsaved change — press Save Answer."); } };
  document.querySelectorAll(".opt input").forEach(inp=>{
    inp.addEventListener("change", ()=>{
      if(locked()) return;
      if(q.type === "multiple-select"){
        st.sel = [...document.querySelectorAll(".opt input:checked")].map(x=>x.value);
      } else st.sel = [inp.value];
      document.querySelectorAll(".opt").forEach(l=>l.classList.toggle("selected", st.sel.includes(l.dataset.l)));
      touched();
    });
  });
  document.querySelectorAll(".seg button").forEach(b=>{
    b.addEventListener("click", ()=>{
      if(locked()) return;
      if(b.parentElement.dataset.row !== undefined){ st.rows = st.rows||{}; st.rows[b.parentElement.dataset.row] = b.dataset.v; }
      else { const box = b.parentElement.dataset.box; st.boxes[box] = b.dataset.v; }
      b.parentElement.querySelectorAll("button").forEach(x=>x.classList.toggle("on", x===b));
      touched();
    });
  });
  const setRow = (n, v)=>{ if(locked()) return; st.rows = st.rows||{}; st.rows[n] = v; const s = document.querySelector(`.ia-select[data-row="${n}"]`); if(s) s.value = v; touched(); };
  document.querySelectorAll(".ia-select").forEach(s=> s.addEventListener("change", ()=> setRow(s.dataset.row, s.value)));
  let picked = null;
  document.querySelectorAll(".chip-item").forEach(c=>{
    c.addEventListener("dragstart", e=>{ if(locked()){ e.preventDefault(); return; } e.dataTransfer.effectAllowed = "copy"; e.dataTransfer.setData("text/plain", c.dataset.v); });
    c.addEventListener("click", ()=>{ if(locked()) return; picked = (picked===c.dataset.v) ? null : c.dataset.v;
      document.querySelectorAll(".chip-item").forEach(x=>x.classList.toggle("picked", x.dataset.v===picked)); });
  });
  window.__iaDrop = (n, v)=> setRow(n, v);
  window.__iaDone = ()=> locked();
  document.querySelectorAll(".ia-row.droppable").forEach(row=>{
    row.addEventListener("click", e=>{ if(!picked || e.target.closest("select")) return; setRow(row.dataset.row, picked); picked=null;
      document.querySelectorAll(".chip-item").forEach(x=>x.classList.remove("picked")); });
  });
  const cb = $("#checkBtn"); if(cb) cb.addEventListener("click", EXAM ? saveExamAnswer : check);
  const rb = $("#revealBtn"); if(rb) rb.addEventListener("click", reveal);
  const pb = $("#prevBtn"); if(pb) pb.addEventListener("click", ()=>go(S.cur-1));
  $("#resetBtn").addEventListener("click", resetQ);
  $("#nextBtn").addEventListener("click", next);
  $("#flagBtn").addEventListener("click", toggleFlag);
}

function sameSet(a,b){ if(a.length!==b.length) return false; const s=new Set(a); return b.every(x=>s.has(x)); }

function check(){
  const i = S.cur, q = QUESTIONS[i], st = qs(i), a = answerFor(q);
  if(q.options.length){
    if(!st.sel.length){ flash("Select an answer before checking."); return; }
    st.status = sameSet(st.sel, a.correctAnswer||[]) ? "correct" : "incorrect";
  } else if(q.ia){
    const R = st.rows || {};
    if(q.ia.rows.some((r,n)=>!R[n])){ flash("Make a selection for every row before checking."); return; }
    st.status = (a.rowAnswers||[]).every((ans,n)=> R[n]===ans) ? "correct" : "incorrect";
  } else {
    for(let b=1;b<=q.boxCount;b++){ if(!st.boxes[b]){ flash("Make a Yes/No selection for every box before checking."); return; } }
    const ok = (a.boxes||[]).every(bx => (st.boxes[bx.box]||"") === bx.value.replace(/\.$/,"").trim());
    st.status = ok ? "correct" : "incorrect";
  }
  finish();
}
function saveExamAnswer(){
  const q = QUESTIONS[S.cur], st = qs(S.cur);
  if(!hasResponse(q, st)){ flash(q.options.length ? "Select an answer before saving." : "Make a selection for every row before saving."); return; }
  st.status = "answered";
  sync(S.cur); render();
}
function reveal(){
  const q = QUESTIONS[S.cur], st = qs(S.cur), a = answerFor(q);
  st.status = "revealed"; st.hasKey = !!(a.correctAnswer || (a.boxes && a.boxes.length) || (a.rowAnswers && a.rowAnswers.length) || (a.explanation && a.explanation.length) || a.explanationHtml || (a.answerImages && a.answerImages.length));
  if(!st.hasKey) st.status = "reviewed";
  finish();
}
function finish(){
  if(S.cur === S.furthest && S.furthest < TOTAL-1) S.furthest = S.cur + 1;
  sync(S.cur); saveLocal(); render();
}
function flash(msg){ $("#fb").innerHTML = `<div class="feedback info">${esc(msg)}</div>`; }

function toggleFlag(){
  const q = QUESTIONS[S.cur], st = qs(S.cur);
  st.flagged = !st.flagged;
  const b = $("#flagBtn"); b.classList.toggle("on", st.flagged); b.textContent = "⚑ " + (st.flagged?"Flagged":"Flag"); b.setAttribute("aria-pressed", st.flagged);
  renderNav();
  post(C.urls.flag, {question_id: q.qid}).then(r=>{ if(typeof r.is_flagged === "boolean" && r.is_flagged !== st.flagged){ st.flagged = r.is_flagged; renderNav(); } }).catch(()=>{});
}

function showFeedback(){
  const q = QUESTIONS[S.cur], st = qs(S.cur); let cls, txt;
  if(st.status==="correct"){ cls="ok"; txt = st.self ? "✓ Marked as correct (self-assessed)." : "✓ Correct!"; }
  else if(st.status==="incorrect"){ cls="bad"; txt = st.self ? "✗ Marked as incorrect (self-assessed)." : "✗ Incorrect. Review the correct answer below."; }
  else if(st.status==="revealed"){
    cls="warn"; txt="👁 Answer revealed — not scored.";
    const a = answerFor(q);
    let tot=0, right=0;
    if(q.ia && a.rowAnswers){ const R=st.rows||{}; a.rowAnswers.forEach((ans,n)=>{ if(R[n]){ tot++; if(R[n]===ans) right++; } }); }
    else if(q.boxCount && q.yesNo && a.boxes){ a.boxes.forEach(bx=>{ const m=st.boxes[bx.box]; if(m){ tot++; if(m===bx.value.replace(/\.$/,"").trim()) right++; } }); }
    else if(q.options.length && st.sel.length){ tot=1; right = sameSet(st.sel, a.correctAnswer||[])?1:0; }
    if(tot){ txt += q.options.length ? (right ? " Your selection was correct." : " Your selection was wrong.") : ` ${right} of ${tot} of your selections were correct — wrong ones are marked in red.`; }
    else txt += " You did not answer this question.";
  }
  else { cls="warn"; txt="👁 Reviewed — no answer key in the source for this question."; }
  $("#fb").innerHTML = `<div class="feedback ${cls}">${txt}</div>`;
}

function showAnswerPanel(){
  const q = QUESTIONS[S.cur], st = qs(S.cur), a = answerFor(q);
  let h = `<div class="answer-panel">`;
  h += `<h4>Correct Answer</h4>`;
  if(a.correctAnswer && a.correctAnswer.length){ h += `<div class="ca">${esc(a.correctAnswer.join(", "))}</div>`; }
  else if(q.ia && a.rowAnswers){ h += `<ul>${q.ia.rows.map((r,n)=>`<li>${esc(r.label)} → <b>${esc(a.rowAnswers[n])}</b></li>`).join("")}</ul>`; }
  else if(a.boxes && a.boxes.length){ h += `<ul>${a.boxes.map(b=>`<li><b>Box ${b.box}:</b> ${esc(b.value)}</li>`).join("")}</ul>`; }
  else if((a.answerImages||[]).length){ /* answer shown as image below */ }
  else if((a.explanation && a.explanation.length) || a.explanationHtml){ h += `<p>See explanation below.</p>`; }
  else { h += `<p class="na">No answer is given in the source document for this question.</p>`; }
  (a.answerImages||[]).forEach(src=>{ h += `<figure class="exhibit"><img src="${esc(src)}" alt="Question ${S.cur+1} answer" loading="lazy"><figcaption>Answer · tap to enlarge</figcaption></figure>`; });
  if(a.explanation && a.explanation.length){
    h += `<h4>Explanation</h4>` + a.explanation.map(e=> typeof e === "string" ? `<p>${esc(e)}</p>` : `<figure class="exhibit"><img src="${esc(e.img)}" alt="Explanation image" loading="lazy"></figure>`).join("");
  } else if(a.explanationHtml){
    h += `<h4>Explanation</h4><div class="q-html">${a.explanationHtml}</div>`;
  }
  if(a.reference && a.reference.length){ h += `<h4>Reference</h4>${a.reference.map(r=>`<p>${linkify(r)}</p>`).join("")}`; }
  // self-assessment for keyed questions that cannot be auto-graded
  if(!gradableQ(q) && st.status==="revealed" && st.hasKey !== false && !st.self){
    h += `<div class="self-grade"><span>Did you get it right?</span><button class="btn" id="sgOk">✓ I got it right</button><button class="btn" id="sgBad">✗ I got it wrong</button></div>`;
  }
  h += `</div>`;
  $("#ap").innerHTML = h;
  const ok = $("#sgOk"), bad = $("#sgBad");
  if(ok){ ok.onclick = ()=>{ st.status="correct"; st.self=true; sync(S.cur); render(); };
          bad.onclick = ()=>{ st.status="incorrect"; st.self=true; sync(S.cur); render(); }; }
}

function applyMarks(){
  const q = QUESTIONS[S.cur], st = qs(S.cur), a = answerFor(q);
  if(q.options.length && a.correctAnswer){
    document.querySelectorAll(".opt").forEach(l=>{
      const L = l.dataset.l;
      if(a.correctAnswer.includes(L)) l.classList.add("is-correct");
      else if(st.sel.includes(L)) l.classList.add("is-wrong");
    });
  }
  if(q.ia && a.rowAnswers){
    const R = st.rows || {};
    a.rowAnswers.forEach((ans,n)=>{
      const row = document.querySelector(`.ia-row[data-row="${n}"]`); if(!row) return;
      const mine = R[n], fix = row.querySelector(".ia-fix");
      if(mine){
        const ok = mine===ans;
        row.classList.add(ok?"row-ok":"row-bad");
        fix.className = "ia-fix " + (ok?"ok":"bad");
        fix.innerHTML = ok ? `✓ Your answer is correct` : `✗ Your answer "${esc(mine)}" is wrong — correct answer: <b>${esc(ans)}</b>`;
      } else {
        fix.className = "ia-fix info";
        fix.innerHTML = `Correct answer: <b>${esc(ans)}</b>`;
      }
      const seg = row.querySelector(".seg");
      if(seg) seg.querySelectorAll("button").forEach(b=>{ b.classList.remove("on"); if(b.dataset.v===ans) b.classList.add("good"); else if(mine===b.dataset.v) b.classList.add("badc"); });
    });
  }
  if(q.boxCount && q.yesNo && a.boxes){
    a.boxes.forEach(bx=>{
      const seg = document.querySelector(`.seg[data-box="${bx.box}"]`); if(!seg) return;
      const right = bx.value.replace(/\.$/,"").trim();
      seg.querySelectorAll("button").forEach(b=>{
        b.classList.remove("on");
        if(b.dataset.v===right) b.classList.add("good");
        else if(st.boxes[bx.box]===b.dataset.v) b.classList.add("badc");
      });
    });
  }
}

function resetQ(){
  const q = QUESTIONS[S.cur], old = qs(S.cur);
  S.q[q.qid] = {sel:[], boxes:{}, rows:{}, status:null, self:false, flagged: old.flagged};
  sync(S.cur); render();
}
function next(){
  if(EXAM){ if(S.cur === TOTAL-1){ showComplete(); return; } go(S.cur+1); return; }
  if(!isDone(qs(S.cur))) return;
  if(S.cur === TOTAL-1){ showComplete(); return; }
  go(S.cur+1);
}
function go(i){
  if(i<0 || i>=TOTAL || i> S.furthest) return;   // future questions are locked in practice
  S.cur = i; saveLocal(); render(); closeNav();
}

/* ---------- chrome: header, navigator ---------- */
let filter = "all";
function counts(){
  let c={correct:0,incorrect:0,revealed:0,answered:0,flagged:0,done:0};
  QUESTIONS.forEach(q=>{ const st=S.q[q.qid]; if(st.flagged) c.flagged++; if(st.status){ c.done++; if(st.status==="correct")c.correct++; else if(st.status==="incorrect")c.incorrect++; else if(st.status==="answered")c.answered++; else c.revealed++; } });
  return c;
}
function updateChrome(){
  const c = counts();
  $("#progText").textContent = `Question ${S.cur+1} of ${TOTAL}`;
  const pct = Math.round(c.done/TOTAL*100);
  $("#progPct").textContent = `${c.done} ${EXAM?"answered":"attempted"} · ${pct}%`;
  $("#progBar").style.width = pct+"%";
  if(EXAM){ $("#stOk").textContent = "✓ "+c.answered+" saved"; }
  else { $("#stOk").textContent = "✓ "+c.correct; $("#stBad").textContent = "✗ "+c.incorrect; $("#stRev").textContent = "👁 "+c.revealed; }
  renderNav();
}
function renderNav(){
  const term = $("#search").value.trim().toLowerCase();
  let h = "", shown = 0;
  QUESTIONS.forEach((q,i)=>{
    const locked = i > S.furthest;
    const st = S.q[q.qid]; const status = st.status;
    // search & filters only ever look at unlocked questions, so they never expose future content
    if(term || filter!=="all"){
      if(locked) return;
      if(term){
        const hay = (String(i+1)+" "+(q.question||[]).join(" ")+" "+(q.searchText||"")+" "+q.options.map(o=>o.text).join(" ")).toLowerCase();
        if(!(String(i+1)===term || hay.includes(term))) return;
      }
      if(filter==="correct" && status!=="correct") return;
      if(filter==="incorrect" && status!=="incorrect") return;
      if(filter==="revealed" && !(status==="revealed"||status==="reviewed")) return;
      if(filter==="answered" && status!=="answered") return;
      if(filter==="unanswered" && status) return;
      if(filter==="flagged" && !st.flagged) return;
    }
    const cls = [ "nav-btn", i===S.cur?"current":"", locked?"locked":"", st.flagged?"flagged":"", status==="correct"?"correct":"", status==="incorrect"?"incorrect":"", (status==="revealed"||status==="reviewed")?"revealed":"", status==="answered"?"answered":"" ].join(" ");
    h += `<button class="${cls}" data-i="${i}" ${locked?'aria-disabled="true" title="Locked — answer the current question first"':`title="Question ${i+1}"`}>${i+1}</button>`;
    shown++;
  });
  if(!shown) h = `<div class="nav-empty">No unlocked questions match.</div>`;
  $("#navGrid").innerHTML = h;
  const cur = $("#navGrid .nav-btn.current");
  if(cur){ const g = $("#navGrid"); const top = cur.offsetTop - g.offsetTop; if(top < g.scrollTop || top > g.scrollTop + g.clientHeight - 40) g.scrollTop = top - 60; }
}
$("#navGrid").addEventListener("click", e=>{
  const b = e.target.closest(".nav-btn"); if(!b) return;
  const i = +b.dataset.i; if(i > S.furthest) return; go(i);
});
$("#search").addEventListener("input", renderNav);
$("#filters").addEventListener("click", e=>{
  const c = e.target.closest(".chip"); if(!c) return;
  filter = c.dataset.f; document.querySelectorAll(".chip").forEach(x=>x.classList.toggle("active", x===c)); renderNav();
});

/* ---------- completion ---------- */
function submitForm(label, cls){
  return `<form method="POST" action="${esc(C.urls.submit)}" onsubmit="this.querySelector('[name=time_taken]').value=window.__etbElapsed()">
    <input type="hidden" name="_token" value="${esc(C.csrf)}"><input type="hidden" name="time_taken" value="0">
    <button class="btn ${cls}" type="submit">${label}</button></form>`;
}
function showComplete(){
  const c = counts();
  $("#qCard").classList.add("hidden");
  const d = $("#doneCard"); d.classList.remove("hidden");
  if(EXAM){
    const left = TOTAL - c.answered;
    d.innerHTML = `<h2>Ready to submit?</h2><p style="color:var(--muted)">You have saved answers for ${c.answered} of ${TOTAL} questions.${left?` ${left} question${left===1?" is":"s are"} not answered and will count as wrong.`:""}</p>
      <div class="res-grid">
        <div class="res"><b style="color:var(--info)">${c.answered}</b><small>Answered</small></div>
        <div class="res"><b style="color:var(--bad)">${left}</b><small>Not answered</small></div>
        <div class="res"><b style="color:var(--warn)">${c.flagged}</b><small>Flagged</small></div>
        <div class="res"><b>${TOTAL}</b><small>Total questions</small></div>
      </div>
      <div class="actions" style="justify-content:center"><button class="btn" id="reviewBtn">Review answers</button>${submitForm("Submit Exam","primary")}</div>`;
  } else {
    const graded = c.correct + c.incorrect;
    const pct = graded ? Math.round(c.correct/graded*100) : 0;
    d.innerHTML = `<h2>🎉 Test Completed</h2><p style="color:var(--muted)">You reached the end of all ${TOTAL} questions.</p>
      <div class="score-big">${pct}%</div><p style="color:var(--muted);margin:0">score on ${graded} graded question${graded===1?"":"s"}</p>
      <div class="res-grid">
        <div class="res"><b style="color:var(--ok)">${c.correct}</b><small>Correct</small></div>
        <div class="res"><b style="color:var(--bad)">${c.incorrect}</b><small>Incorrect</small></div>
        <div class="res"><b style="color:var(--warn)">${c.revealed}</b><small>Revealed / reviewed</small></div>
        <div class="res"><b>${TOTAL}</b><small>Total questions</small></div>
      </div>
      <div class="actions" style="justify-content:center"><button class="btn" id="reviewBtn">Review questions</button>${C.urls.restart?`<a class="btn" style="text-decoration:none" href="${esc(C.urls.restart)}">Restart Test</a>`:""}${submitForm("Save Results","primary")}</div>`;
  }
  $("#reviewBtn").onclick = ()=>go(0);
  updateChrome();
  window.scrollTo(0,0);
}

/* ---------- timer (elapsed time is sent on submit) ---------- */
const startedAt = Date.now() - (C.elapsedSeconds||0)*1000;
window.__etbElapsed = ()=> Math.round((Date.now()-startedAt)/1000);
const timerEl = $("#timer");
if(timerEl){
  const tick = ()=>{ const s = window.__etbElapsed(); const hh = Math.floor(s/3600), mm = Math.floor(s%3600/60), ss = s%60;
    timerEl.textContent = "⏱ " + (hh?hh+":":"") + String(mm).padStart(2,"0") + ":" + String(ss).padStart(2,"0"); };
  tick(); setInterval(tick, 1000);
}

/* ---------- mobile nav & theme ---------- */
function closeNav(){ $("#sidebar").classList.remove("open"); $("#backdrop").classList.remove("open"); }
$("#menuBtn").onclick = ()=>{ $("#sidebar").classList.add("open"); $("#backdrop").classList.add("open"); };
$("#backdrop").onclick = closeNav;
$("#themeBtn").onclick = ()=>{
  const r = document.documentElement; const dark = r.dataset.theme ? r.dataset.theme==="dark" : matchMedia("(prefers-color-scheme: dark)").matches;
  r.dataset.theme = dark ? "light" : "dark"; try{ localStorage.setItem("etb-engine-theme", r.dataset.theme); }catch(e){}
};
document.addEventListener("keydown", e=>{
  if(e.target.matches("input,select,textarea")) return;
  if(e.key==="ArrowRight"){ const nb=$("#nextBtn"); if(nb && !nb.classList.contains("hidden") && $("#doneCard").classList.contains("hidden")) next(); }
  if(e.key==="ArrowLeft" && S.cur>0) go(S.cur-1);
});

function dropRow(e){ const el = e.target && e.target.closest ? e.target : e.target.parentElement; return el ? el.closest(".ia-row.droppable") : null; }
document.addEventListener("dragover", e=>{
  const row = dropRow(e); document.querySelectorAll(".ia-row.over").forEach(r=>{ if(r!==row) r.classList.remove("over"); });
  if(!row || !window.__iaDone || window.__iaDone()) return;
  e.preventDefault(); e.dataTransfer.dropEffect = "copy"; row.classList.add("over");
}, true);
document.addEventListener("drop", e=>{
  const row = dropRow(e); document.querySelectorAll(".ia-row.over").forEach(r=>r.classList.remove("over"));
  if(!row || !window.__iaDone || window.__iaDone()) return;
  e.preventDefault(); e.stopPropagation();
  const v = e.dataTransfer.getData("text/plain"); if(v && window.__iaDrop) window.__iaDrop(row.dataset.row, v);
}, true);
document.addEventListener("dragend", ()=> document.querySelectorAll(".ia-row.over").forEach(r=>r.classList.remove("over")));
document.addEventListener("click", e=>{
  const img = e.target.closest(".exhibit img"); if(!img) return;
  const lb = document.createElement("div"); lb.className="lightbox"; lb.innerHTML = `<img src="${img.src}" alt="">`;
  lb.onclick = ()=>lb.remove(); document.body.appendChild(lb);
});
document.addEventListener("keydown", e=>{ if(e.key==="Escape"){ const lb=document.querySelector(".lightbox"); if(lb) lb.remove(); } });

if(!TOTAL){ $("#qCard").innerHTML = `<p>No questions in this test.</p>`; return; }
render();
})();
