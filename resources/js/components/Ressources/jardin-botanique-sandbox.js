export const AI_TURN_TIMEOUT_MS = 1500;

const SRCDOC = `<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Security-Policy" content="default-src 'none'; script-src 'unsafe-inline' 'unsafe-eval';">
</head>
<body>
<script>
const send = parent.postMessage.bind(parent);
const maxLogs = 50;
function clip(value, depth, seen) {
  if (typeof value === 'string') return value.length > 500 ? value.slice(0, 500) + '\\u2026' : value;
  if (value === null || typeof value === 'undefined') return value;
  if (typeof value === 'number' || typeof value === 'boolean') return value;
  if (typeof value === 'function') return '[Function]';
  if (typeof value !== 'object') return String(value);
  if (seen.has(value)) return '[Circular]';
  if (depth >= 4) return Array.isArray(value) ? '[Array]' : '[Object]';
  seen.add(value);
  if (Array.isArray(value)) return value.slice(0, 20).map((item) => clip(item, depth + 1, seen));
  const out = {};
  for (const key of Object.keys(value).slice(0, 20)) out[key] = clip(value[key], depth + 1, seen);
  return out;
}
function format(args) {
  return args.map((arg) => {
    if (typeof arg === 'string') return arg.length > 500 ? arg.slice(0, 500) + '\\u2026' : arg;
    try { return JSON.stringify(clip(arg, 0, new WeakSet())); }
    catch (error) { return String(arg); }
  }).join(' ');
}
window.addEventListener('message', (event) => {
  const data = event.data;
  if (!data || data.type !== 'run') return;
  const logs = [];
  const consoleProxy = {};
  for (const level of ['log', 'info', 'warn', 'error', 'debug']) {
    consoleProxy[level] = (...args) => {
      if (logs.length >= maxLogs) return;
      logs.push({ level, text: format(args) });
    };
  }
  try {
    const fn = new Function('state', 'console', '"use strict";\\n' + data.code + '\\n; if (typeof chooseMove !== "function") throw new Error("Define function chooseMove(state)");\\n return chooseMove(state);');
    const move = fn(data.state, consoleProxy);
    if (move && typeof move.then === 'function') {
      send({ type: 'result', id: data.id, ok: false, error: 'return a move, not a Promise', logs }, '*');
      return;
    }
    send({ type: 'result', id: data.id, ok: true, move, logs }, '*');
  } catch (error) {
    send({ type: 'result', id: data.id, ok: false, error: error && error.message ? String(error.message) : String(error), logs }, '*');
  }
});
</script>
</body>
</html>`;

let activeCancel = null;

export function runInSandbox(code, state) {
    activeCancel?.();
    let cancel = () => {};
    const promise = new Promise((resolve) => {
        const id = crypto.randomUUID();
        const iframe = document.createElement('iframe');
        iframe.setAttribute('sandbox', 'allow-scripts');
        iframe.setAttribute('aria-hidden', 'true');
        iframe.style.cssText = 'position:absolute;width:0;height:0;border:0;left:-9999px;top:0;';
        let settled = false;
        let sent = false;
        let timer = 0;

        const finish = (result) => {
            if (settled) return;
            settled = true;
            window.removeEventListener('message', onMessage);
            clearTimeout(timer);
            iframe.remove();
            if (activeCancel === cancel) activeCancel = null;
            resolve(result);
        };

        const onMessage = (event) => {
            if (event.source !== iframe.contentWindow) return;
            if (event.origin !== 'null') return;
            const data = event.data;
            if (!data || data.type !== 'result' || data.id !== id) return;
            if (data.ok) finish({ ok: true, move: data.move, logs: data.logs || [] });
            else finish({ ok: false, error: data.error || 'Error', logs: data.logs || [] });
        };

        cancel = () => finish({ ok: false, error: 'cancelled', cancelled: true });
        activeCancel = cancel;
        window.addEventListener('message', onMessage);
        timer = setTimeout(() => finish({ ok: false, error: 'timeout', logs: [] }), AI_TURN_TIMEOUT_MS);
        iframe.onload = () => {
            if (settled || sent || !iframe.contentWindow) return;
            sent = true;
            iframe.contentWindow.postMessage({ type: 'run', id, code, state }, '*');
        };
        iframe.srcdoc = SRCDOC;
        document.body.appendChild(iframe);
    });
    return { promise, cancel };
}
