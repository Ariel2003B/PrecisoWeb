(function () {
  "use strict";

  // ------------- Utils -------------
  const $id = (x) => document.getElementById(x);
  const safeJson = (s) => { try { return JSON.parse(s || "null"); } catch { return null; } };
  const num2 = (n) => (isNaN(n) ? 0 : Number(n)).toFixed(2);
  const esc = (s) => (s || "").replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  const neutralizeScriptClosers = (s) => String(s || "").replace(/<\/script/gi, "<\\/script");

  const getStopId = (stop) => {
    const cand = stop?.id ?? stop?.ID ?? stop?.nid ?? stop?.nimbusId ?? stop?.gsId ?? stop?.gs_id;
    const n = parseInt(cand, 10);
    return isNaN(n) ? String(cand ?? "") : n;
  };

  const buildTarifaLookup = (raw) => {
    const out = {};
    if (!raw) return out;
    if (typeof raw === "object" && !Array.isArray(raw)) {
      for (const k of Object.keys(raw)) out[String(k)] = parseFloat(raw[k]) || 0;
      return out;
    }
    if (Array.isArray(raw)) {
      for (const x of raw) {
        const id = getStopId(x);
        if (id !== "" && id !== undefined && id !== null) {
          out[String(id)] = parseFloat(x.t ?? x.valor ?? x.rate ?? x.tarifa ?? 0) || 0;
        }
      }
    }
    return out;
  };

  // ------------- Cambiar pestaña (rutas) -------------
  document.addEventListener("click", (e) => {
    const b = e.target.closest(".route-btn");
    if (!b) return;
    const tgt = b.getAttribute("data-target");
    document.querySelectorAll(".route-btn").forEach(x => x.classList.remove("active"));
    document.querySelectorAll(".route-table").forEach(x => x.classList.add("d-none"));
    b.classList.add("active");
    const pane = document.querySelector(tgt);
    if (pane) pane.classList.remove("d-none");
  });

  // ------------- Calcular totales de Sanción -------------
  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("table.table-compact").forEach((table) => {
      const stops = safeJson(table.dataset.stops) || [];
      const tarifas = buildTarifaLookup(safeJson(table.dataset.tarifas));

      table.querySelectorAll("tbody tr").forEach((tr) => {
        let total = 0;
        tr.querySelectorAll("td.col-dif").forEach((td, idx) => {
          const diff = parseInt((td.textContent || "").replace(/[^\-0-9]/g, ""), 10);
          const stop = stops[idx] || {};
          const nid = getStopId(stop);
          const t = parseFloat(tarifas[String(nid)] ?? tarifas[nid] ?? 0) || 0;
          if (!isNaN(diff) && diff < 0 && t > 0) total += Math.abs(diff) * t;
        });

        const amountEl = tr.querySelector(".sancion-amount");
        if (amountEl) {
          amountEl.dataset.total = total.toFixed(2);
          amountEl.textContent = "$" + num2(total);
        }
        tr._sancionContext = { stops, tarifas };
      });
    });
  });

  // ------------- Ver detalle (delegación) -------------
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".ver-sancion");
    if (!btn) return;

    const tr = btn.closest("tr");
    const table = btn.closest("table");
    const contRT = btn.closest(".route-table");

    const stops = (tr?._sancionContext?.stops) || safeJson(table.dataset.stops) || [];
    const tarifas = (tr?._sancionContext?.tarifas) || buildTarifaLookup(safeJson(table.dataset.tarifas)) || {};

    const placa = tr.querySelector(".sticky-placa")?.innerText.replace(/\s+/g, " ").trim() || "";

    const ruta = contRT?.querySelector("h5")?.textContent?.trim() || "";
    const difTds = tr.querySelectorAll("td.col-dif");

    let body = "";
    let total = 0, caidas = 0;

    difTds.forEach((td, idx) => {
      const raw = (td.textContent || "").trim();
      const diff = parseInt(raw.replace(/[^\-0-9]/g, ""), 10);
      const stop = stops[idx] || {};
      const nid = getStopId(stop);
      const name = stop.n || ("Parada " + (idx + 1));
      const t = parseFloat(tarifas[String(nid)] ?? tarifas[nid] ?? 0) || 0;
      const cargo = (!isNaN(diff) && diff < 0 && t > 0) ? Math.abs(diff) * t : 0;

      if (cargo > 0) { total += cargo; caidas++; }

      const cls = isNaN(diff) ? "text-muted" : (diff < 0 ? "text-danger fw-semibold" : (diff > 0 ? "text-success" : "text-secondary"));
      body += `
        <tr>
          <td class="text-center">${idx + 1}</td>
          <td>${esc(name)}</td>
          <td class="text-center ${cls}">${isNaN(diff) ? "—" : (diff > 0 ? "+" + diff : diff)}</td>
          <td class="text-end">${t ? "$" + num2(t) : "$0.00"}</td>
          <td class="text-end">${cargo ? "$" + num2(cargo) : "$0.00"}</td>
        </tr>`;
    });

    // Pinta modal
    const bodyEl = $id("detSancionBody");
    if (bodyEl) {
      bodyEl.innerHTML = body || `<tr><td colspan="5" class="text-center text-muted">Sin datos</td></tr>`;
    }
    // Metadata para imprimir
    const modalEl = $id("modalSancion");
    modalEl.querySelector(".modal-title").textContent = `Detalle de sanción – ${placa}`;

    const meta = $id("printMeta");
    modalEl.dataset.empresa = meta?.dataset.empresa || "";
    modalEl.dataset.fecha = meta?.dataset.fecha || "";
    modalEl.dataset.ruta = ruta;
    modalEl.dataset.placa = placa;
    modalEl.dataset.total = num2(total);
    modalEl.dataset.caidas = String(caidas);
    modalEl.dataset.rowsAll = neutralizeScriptClosers(body);

    showModal(modalEl);
  });

  // ------------- Imprimir (delegación) -------------
  document.addEventListener("click", (e) => {
    if (!e.target.closest("#btnPrintSancion")) return;

    const modalEl = $id("modalSancion");
    if (!modalEl) return;

    // Si hay Bootstrap, espera a que se esconda para evitar backdrop en impresión
    if (window.bootstrap && window.bootstrap.Modal) {
      const inst = bootstrap.Modal.getInstance(modalEl);
      modalEl.addEventListener("hidden.bs.modal", () => {
        cleanupBackdrops();
        doPrint(modalEl);
      }, { once: true });
      inst?.hide();
    } else {
      cleanupBackdrops();
      doPrint(modalEl);
    }
  });

  function cleanupBackdrops() {
    document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("overflow");
    document.body.style.removeProperty("padding-right");
  }

  function doPrint(modalEl) {
    const empresa = modalEl.dataset.empresa || "";
    const fecha = modalEl.dataset.fecha || "";
    const ruta = modalEl.dataset.ruta || "";
    const placa = modalEl.dataset.placa || "";
    const total = modalEl.dataset.total || "0.00";
    const caidas = parseInt(modalEl.dataset.caidas || "0", 10);
    const rowsAll = modalEl.dataset.rowsAll || "";

    const html = `
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sanción ${esc(placa)} - ${esc(fecha)}</title>
  <style>
    @page { size: A4; margin: 18mm 14mm; }
    body { font-family: system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif; color:#0f172a; }
    h1 { font-size:18px; margin:0 0 8px; }
    h2 { font-size:15px; margin:14px 0 6px; }
    .muted{ color:#64748b; } .badge{ display:inline-block; padding:2px 8px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:12px; }
    .resume{ margin:12px 0 16px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px; } .resume strong{ font-size:18px; }
    table{ width:100%; border-collapse:collapse; } th,td{ border:1px solid #e2e8f0; padding:6px 8px; font-size:12px; }
    thead th{ background:#f8fafc; text-align:left; } .text-end{ text-align:right; } .text-center{ text-align:center; }
    .small{ font-size:11px; } .foot{ margin-top:18px; font-size:11px; color:#64748b; }
  </style>
</head>
<body>
  <h1>Reporte de sanción <span class="badge">${esc(empresa)}</span></h1>
  <div class="small muted">Fecha: ${esc(fecha)} · Ruta: ${esc(ruta)} · Unidad: ${esc(placa)}</div>
  <div class="resume">
    <div><span class="muted">Geocercas con caída:</span> <strong>${caidas}</strong></div>
    <div><span class="muted">Total a pagar:</span> <strong>$${total}</strong></div>
  </div>
  <h2>Detalle (todas las geocercas)</h2>
  <table>
    <thead>
      <tr>
        <th style="width:40px;" class="text-center">#</th>
        <th>Geocerca</th>
        <th style="width:70px;" class="text-center">Dif</th>
        <th style="width:110px;" class="text-end">Tarifa</th>
        <th style="width:110px;" class="text-end">Cargo</th>
      </tr>
    </thead>
    <tbody id="rows"></tbody>
    <tfoot><tr><th colspan="4" class="text-end">Total</th><th class="text-end">$${total}</th></tr></tfoot>
  </table>
  <div class="foot">Generado automáticamente · ${esc(new Date().toLocaleString())}</div>
</body>
</html>`;

    const w = window.open("", "_blank");
    if (!w) { alert("Permite las ventanas emergentes para imprimir."); return; }

    // 1) escribir skeleton
    w.document.open(); w.document.write(html); w.document.close();

    // 2) inyectar filas y mandar a imprimir
    const doFillAndPrint = () => {
      try {
        const tbody = w.document.getElementById("rows");
        if (tbody) tbody.innerHTML = rowsAll; // ya viene escapado para </script>
        try { w.focus(); } catch { }
        try { w.print(); } catch { }
        w.onafterprint = () => { try { w.close(); } catch { } };
      } catch {
        try { w.close(); } catch { }
      }
    };

    if (w.document.readyState === "complete") {
      doFillAndPrint();
    } else {
      w.addEventListener("load", doFillAndPrint, { once: true });
    }
  }

  // ------------- Modal helpers (fallback si no hay BS) -------------
  function showModal(modalEl) {
    try {
      if (window.bootstrap && window.bootstrap.Modal) { new bootstrap.Modal(modalEl).show(); return; }
    } catch { }
    modalEl.style.display = "block";
    modalEl.classList.add("show");
    const bd = document.createElement("div");
    bd.className = "modal-backdrop fade show";
    document.body.appendChild(bd);
    document.body.classList.add("modal-open");
    document.body.style.overflow = "hidden";
  }
})();
