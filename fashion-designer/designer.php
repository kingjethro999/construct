<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id     = $_SESSION['user_id'];
$design_id   = intval($_GET['id'] ?? 0);
$design_data = '';
$design_name = 'Untitled Design';

if ($design_id) {
    $stmt = $conn->prepare("SELECT name, design_data FROM designs WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $design_id, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $design_data = $row['design_data'];
        $design_name = $row['name'];
    } else {
        $design_id = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Designer — FashionForge</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0f0f0f;color:#fff;height:100vh;display:flex;flex-direction:column;overflow:hidden}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:10px 20px;background:#141414;border-bottom:1px solid #222;flex-shrink:0;gap:12px}
.topbar-left{display:flex;align-items:center;gap:12px}
.logo{font-size:1.1rem;font-weight:700;white-space:nowrap}
.logo span{color:#c084fc}
.design-name-input{background:transparent;border:1px solid transparent;color:#fff;font-size:.9rem;padding:5px 10px;border-radius:6px;outline:none;min-width:160px}
.design-name-input:hover{border-color:#333}
.design-name-input:focus{border-color:#c084fc;background:#1a1a1a}
.topbar-right{display:flex;align-items:center;gap:8px}
.btn{padding:7px 16px;border-radius:7px;font-size:.82rem;font-weight:600;cursor:pointer;border:none;transition:all .2s;text-decoration:none}
.btn-primary{background:#c084fc;color:#000}
.btn-primary:hover{background:#a855f7}
.btn-ghost{background:transparent;color:#888;border:1px solid #2a2a2a}
.btn-ghost:hover{color:#fff;border-color:#555}
.btn-icon{background:#1e1e1e;border:1px solid #2a2a2a;color:#ccc;padding:7px 10px;border-radius:7px;cursor:pointer;font-size:.85rem;transition:all .2s}
.btn-icon:hover{background:#2a2a2a;color:#fff}
.btn-icon.active{background:#c084fc22;border-color:#c084fc;color:#c084fc}
.workspace{display:flex;flex:1;overflow:hidden}
.left-panel{width:220px;background:#141414;border-right:1px solid #222;display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0}
.panel-section{padding:14px;border-bottom:1px solid #1e1e1e}
.panel-section h4{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#555;margin-bottom:10px}
.template-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.template-btn{background:#1a1a1a;border:1px solid #2a2a2a;border-radius:8px;padding:10px 6px;cursor:pointer;text-align:center;transition:all .2s;color:#ccc;font-size:.75rem}
.template-btn:hover{border-color:#c084fc;color:#c084fc}
.template-btn .icon{font-size:1.6rem;display:block;margin-bottom:4px}
.color-grid{display:flex;flex-wrap:wrap;gap:6px}
.color-swatch{width:28px;height:28px;border-radius:50%;cursor:pointer;border:2px solid transparent;transition:border-color .15s,transform .15s}
.color-swatch:hover{transform:scale(1.15);border-color:#fff}
.color-swatch.active{border-color:#c084fc}
.tool-btn{display:flex;align-items:center;gap:8px;padding:8px 10px;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:7px;cursor:pointer;color:#ccc;font-size:.82rem;transition:all .2s;width:100%;text-align:left;margin-bottom:6px}
.tool-btn:hover{border-color:#c084fc;color:#c084fc}
.tool-btn .icon{font-size:1rem}
.canvas-area{flex:1;display:flex;align-items:center;justify-content:center;background:#1a1a1a;background-image:radial-gradient(#2a2a2a 1px,transparent 1px);background-size:20px 20px;overflow:hidden;position:relative}
#canvas-container{position:relative;box-shadow:0 8px 40px rgba(0,0,0,.6)}
#mainCanvas{display:block}
.right-panel{width:220px;background:#141414;border-left:1px solid #222;display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0}
.prop-row{display:flex;flex-direction:column;gap:4px;margin-bottom:12px}
.prop-row label{font-size:.72rem;color:#666}
.prop-row input[type=text],.prop-row input[type=number],.prop-row select{background:#111;border:1px solid #2a2a2a;color:#fff;padding:6px 10px;border-radius:6px;font-size:.82rem;outline:none;width:100%}
.prop-row input:focus,.prop-row select:focus{border-color:#c084fc}
.prop-row input[type=color]{width:100%;height:34px;border-radius:6px;border:1px solid #2a2a2a;background:#111;cursor:pointer;padding:2px}
.prop-row input[type=range]{width:100%;accent-color:#c084fc}
.delete-btn{width:100%;padding:8px;background:#2a1a1a;border:1px solid #5a2a2a;color:#f87171;border-radius:7px;cursor:pointer;font-size:.82rem;font-weight:600;transition:all .2s;margin-top:4px}
.delete-btn:hover{background:#3a1a1a}
.no-selection{color:#444;font-size:.82rem;text-align:center;padding:20px 0}
.layer-item{display:flex;align-items:center;gap:6px;padding:6px 8px;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:6px;cursor:pointer;font-size:.78rem;color:#ccc;margin-bottom:4px}
.layer-item:hover{border-color:#c084fc;color:#c084fc}
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);background:#1e1e1e;border:1px solid #333;color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;opacity:0;transition:all .3s;z-index:999;pointer-events:none}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast.success{border-color:#4ade80;color:#4ade80}
.toast.error{border-color:#f87171;color:#f87171}

/* ── 3D Preview Panel ───────────────────────────────────────────────────── */
.preview-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:200;display:none;align-items:center;justify-content:center}
.preview-overlay.open{display:flex}
.preview-modal{background:#141414;border:1px solid #2a2a2a;border-radius:14px;overflow:hidden;display:flex;flex-direction:column;width:min(680px,95vw);height:min(620px,90vh)}
.preview-header{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid #222;flex-shrink:0}
.preview-header h3{font-size:.95rem;font-weight:600;color:#fff}
.preview-header-right{display:flex;align-items:center;gap:8px}
.preview-hint{font-size:.72rem;color:#555}
#threeCanvas{width:100%!important;height:100%!important;display:block}
.preview-body{flex:1;position:relative;overflow:hidden;background:#111}
.preview-status{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:#555;font-size:.85rem;pointer-events:none}
.preview-spinner{width:36px;height:36px;border:3px solid #2a2a2a;border-top-color:#c084fc;border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.no-model-msg{text-align:center;padding:20px;line-height:1.6}
.no-model-msg a{color:#c084fc}
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-left">
    <div class="logo">Fashion<span>Forge</span></div>
    <input type="text" class="design-name-input" id="designName"
           value="<?= htmlspecialchars($design_name) ?>" placeholder="Design name...">
  </div>
  <div style="display:flex;gap:6px">
    <button class="btn-icon" onclick="zoomOut()" title="Zoom Out">−</button>
    <button class="btn-icon" id="zoomLabel" style="min-width:52px;text-align:center;cursor:default">100%</button>
    <button class="btn-icon" onclick="zoomIn()" title="Zoom In">+</button>
    <button class="btn-icon" onclick="resetZoom()" title="Reset Zoom">⊙</button>
    <button class="btn-icon" onclick="undoAction()" title="Undo (Ctrl+Z)">↩</button>
    <button class="btn-icon" onclick="redoAction()" title="Redo (Ctrl+Y)">↪</button>
    <button class="btn-icon" onclick="duplicateSelected()" title="Duplicate">⧉</button>
    <button class="btn-icon" onclick="exportDesign()" title="Export PNG">⬇</button>
    <button class="btn-icon" onclick="canvas.discardActiveObject();canvas.renderAll()" title="Deselect">✕</button>
  </div>
  <div class="topbar-right">
    <a href="dashboard.php" class="btn btn-ghost">← Dashboard</a>
    <button class="btn btn-ghost" onclick="open3DPreview()" title="3D Preview">🧊 3D Preview</button>
    <button class="btn btn-primary" onclick="saveDesign()">Save Design</button>
  </div>
</div>

<div class="workspace">

  <!-- Left Panel -->
  <div class="left-panel">
    <div class="panel-section">
      <h4>Clothing</h4>
      <div class="template-grid">
        <button class="template-btn" onclick="loadTemplate('tshirt')"><span class="icon">👕</span>T-Shirt</button>
        <button class="template-btn" onclick="loadTemplate('hoodie')"><span class="icon">🧥</span>Hoodie</button>
        <button class="template-btn" onclick="loadTemplate('dress')"><span class="icon">👗</span>Dress</button>
        <button class="template-btn" onclick="loadTemplate('pants')"><span class="icon">👖</span>Pants</button>
        <button class="template-btn" onclick="loadTemplate('jacket')"><span class="icon">🥼</span>Jacket</button>
        <button class="template-btn" onclick="loadTemplate('cap')"><span class="icon">🧢</span>Cap</button>
      </div>
    </div>

    <div class="panel-section">
      <h4>Garment Color</h4>
      <div class="color-grid">
        <?php
        $colors = ['#ffffff','#f5f5f5','#1a1a1a','#111827','#374151','#6b7280',
                   '#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#8b5cf6',
                   '#ec4899','#14b8a6','#f59e0b','#84cc16'];
        foreach ($colors as $c): ?>
        <div class="color-swatch" style="background:<?= $c ?>"
             onclick="setGarmentColor('<?= $c ?>', this)" title="<?= $c ?>"></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="panel-section">
      <h4>Fabric Texture</h4>
      <div class="template-grid">
        <button class="template-btn" onclick="applyTexture('none')"><span class="icon">⬜</span>Plain</button>
        <button class="template-btn" onclick="applyTexture('stripes')"><span class="icon">〰</span>Stripes</button>
        <button class="template-btn" onclick="applyTexture('grid')"><span class="icon">⊞</span>Grid</button>
        <button class="template-btn" onclick="applyTexture('dots')"><span class="icon">⁙</span>Dots</button>
        <button class="template-btn" onclick="applyTexture('camo')"><span class="icon">🟢</span>Camo</button>
        <button class="template-btn" onclick="applyTexture('tiedye')"><span class="icon">🌀</span>Tie-Dye</button>
        <button class="template-btn" onclick="applyTexture('zigzag')"><span class="icon">⚡</span>Zigzag</button>
        <button class="template-btn" onclick="applyTexture('denim')"><span class="icon">🔷</span>Denim</button>
      </div>
    </div>

    <div class="panel-section">
      <h4>Add Elements</h4>
      <button class="tool-btn" onclick="addText()"><span class="icon">T</span> Add Text</button>
      <button class="tool-btn" onclick="addRect()"><span class="icon">▭</span> Rectangle</button>
      <button class="tool-btn" onclick="addCircle()"><span class="icon">○</span> Circle</button>
      <button class="tool-btn" onclick="triggerImageUpload()"><span class="icon">🖼</span> Upload Image</button>
      <input type="file" id="imageUpload" accept="image/*" style="display:none" onchange="addImage(event)">
    </div>

    <div class="panel-section">
      <h4>Draw</h4>
      <button class="tool-btn" id="drawBtn" onclick="toggleDraw()"><span class="icon">✏️</span> Free Draw</button>
      <div class="prop-row" style="margin-top:8px">
        <label>Brush Size</label>
        <input type="range" id="brushSize" min="1" max="40" value="6" oninput="updateBrush()">
      </div>
      <div class="prop-row">
        <label>Brush Color</label>
        <input type="color" id="brushColor" value="#c084fc" oninput="updateBrush()">
      </div>
    </div>

    <div class="panel-section">
      <h4>Design Presets</h4>
      <button class="tool-btn" onclick="applyPreset('streetwear')"><span class="icon">🔥</span> Streetwear</button>
      <button class="tool-btn" onclick="applyPreset('minimal')"><span class="icon">◻</span> Minimal</button>
      <button class="tool-btn" onclick="applyPreset('vintage')"><span class="icon">⭐</span> Vintage</button>
      <button class="tool-btn" onclick="applyPreset('neon')"><span class="icon">⚡</span> Neon Glow</button>
      <button class="tool-btn" onclick="applyPreset('sport')"><span class="icon">🏆</span> Sport</button>
    </div>
  </div>

  <!-- Canvas -->
  <div class="canvas-area">
    <div id="canvas-container">
      <canvas id="mainCanvas"></canvas>
    </div>
  </div>

  <!-- Right Panel -->
  <div class="right-panel">
    <div class="panel-section">
      <h4>Properties</h4>
      <div id="noSelection" class="no-selection">Select an object to edit</div>
      <div id="objProps" style="display:none">
        <div class="prop-row" id="textProps" style="display:none">
          <label>Text</label>
          <input type="text" id="propText" oninput="updateProp('text')">
        </div>
        <div class="prop-row" id="fontSizeRow" style="display:none">
          <label>Font Size</label>
          <input type="number" id="propFontSize" min="6" max="200" oninput="updateProp('fontSize')">
        </div>
        <div class="prop-row" id="fontFamilyRow" style="display:none">
          <label>Font Family</label>
          <select id="propFontFamily" onchange="updateProp('fontFamily')">
            <option>Segoe UI</option><option>Arial</option><option>Georgia</option>
            <option>Times New Roman</option><option>Courier New</option><option>Verdana</option>
            <option>Impact</option><option>Comic Sans MS</option>
          </select>
        </div>
        <div class="prop-row" id="fontStyleRow" style="display:none">
          <label>Style</label>
          <div style="display:flex;gap:6px">
            <button class="btn-icon" id="boldBtn" onclick="toggleBold()" style="flex:1;font-weight:700">B</button>
            <button class="btn-icon" id="italicBtn" onclick="toggleItalic()" style="flex:1;font-style:italic">I</button>
            <button class="btn-icon" id="underlineBtn" onclick="toggleUnderline()" style="flex:1;text-decoration:underline">U</button>
          </div>
        </div>
        <div class="prop-row">
          <label>Fill Color</label>
          <input type="color" id="propFill" oninput="updateProp('fill')">
        </div>
        <div class="prop-row">
          <label>Stroke Color</label>
          <input type="color" id="propStroke" oninput="updateProp('stroke')">
        </div>
        <div class="prop-row">
          <label>Stroke Width</label>
          <input type="number" id="propStrokeWidth" min="0" max="20" value="0" oninput="updateProp('strokeWidth')">
        </div>
        <div class="prop-row">
          <label>Opacity</label>
          <input type="range" id="propOpacity" min="0" max="100" value="100" oninput="updateProp('opacity')">
        </div>
        <div class="prop-row">
          <label>X</label>
          <input type="number" id="propX" oninput="updateProp('left')">
        </div>
        <div class="prop-row">
          <label>Y</label>
          <input type="number" id="propY" oninput="updateProp('top')">
        </div>
        <button class="delete-btn" onclick="deleteSelected()">🗑 Delete</button>
      </div>
    </div>
    <div class="panel-section">
      <h4>Layers</h4>
      <div style="display:flex;gap:4px;margin-bottom:8px">
        <button class="btn-icon" style="flex:1;font-size:.75rem" onclick="bringForward()" title="Bring Forward">↑ Forward</button>
        <button class="btn-icon" style="flex:1;font-size:.75rem" onclick="sendBackward()" title="Send Backward">↓ Back</button>
      </div>
      <div id="layersList"></div>
    </div>
  </div>

</div><!-- /workspace -->

<div class="toast" id="toast"></div>

<!-- ── 3D Preview Modal ──────────────────────────────────────────────────── -->
<div class="preview-overlay" id="previewOverlay" onclick="handleOverlayClick(event)">
  <div class="preview-modal">
    <div class="preview-header">
      <h3>🧊 3D Preview</h3>
      <div class="preview-header-right">
        <span class="preview-hint">Drag to rotate · Scroll to zoom · Right-drag to pan</span>
        <button class="btn-icon" onclick="close3DPreview()" title="Close">✕</button>
      </div>
    </div>
    <div class="preview-body" id="previewBody">
      <div class="preview-status" id="previewStatus">
        <div class="preview-spinner"></div>
        <span>Loading 3D model…</span>
      </div>
      <!-- Three.js renders here -->
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
let DESIGN_ID   = <?= $design_id ?>;
const DESIGN_DATA = <?= $design_data ? json_encode($design_data) : 'null' ?>;

// ── Canvas ──────────────────────────────────────────────────────────────────
const canvas = new fabric.Canvas('mainCanvas', {
  width: 600, height: 700,
  backgroundColor: '#ffffff',
  preserveObjectStacking: true
});

// ── Zoom ────────────────────────────────────────────────────────────────────
let zoom = 1;
function zoomIn()    { zoom = Math.min(zoom + 0.1, 3);   canvas.setZoom(zoom); updateZoomLabel(); }
function zoomOut()   { zoom = Math.max(zoom - 0.1, 0.2); canvas.setZoom(zoom); updateZoomLabel(); }
function resetZoom() { zoom = 1; canvas.setZoom(1); updateZoomLabel(); }
function updateZoomLabel() { document.getElementById('zoomLabel').textContent = Math.round(zoom * 100) + '%'; }

// Mouse wheel zoom
document.getElementById('mainCanvas').addEventListener('wheel', e => {
  e.preventDefault();
  e.deltaY < 0 ? zoomIn() : zoomOut();
}, { passive: false });

// ── Undo / Redo ─────────────────────────────────────────────────────────────
let history = [], redoStack = [], historyLock = false;
function snapshot() {
  if (historyLock) return;
  history.push(JSON.stringify(canvas.toJSON(['id'])));
  redoStack = [];
  if (history.length > 40) history.shift();
}
function undoAction() {
  if (history.length < 2) return;
  redoStack.push(history.pop());
  historyLock = true;
  canvas.loadFromJSON(history[history.length - 1], () => {
    canvas.renderAll(); historyLock = false; updateLayers();
  });
}
function redoAction() {
  if (!redoStack.length) return;
  const state = redoStack.pop();
  history.push(state);
  historyLock = true;
  canvas.loadFromJSON(state, () => {
    canvas.renderAll(); historyLock = false; updateLayers();
  });
}
canvas.on('object:added',    snapshot);
canvas.on('object:modified', snapshot);
canvas.on('object:removed',  snapshot);
snapshot();

// ── Garment templates ───────────────────────────────────────────────────────
let garmentObj = null;
let garmentColor = '#ffffff';

function loadTemplate(type) {
  if (garmentObj) { canvas.remove(garmentObj); garmentObj = null; }
  window._activeGarment = type; // expose for 3D preview

  const cx = canvas.width  / 2;  // 300
  const cy = canvas.height / 2;  // 350
  let group;

  const base = { fill: garmentColor, stroke: '#aaaaaa', strokeWidth: 2 };

  if (type === 'tshirt') {
    group = new fabric.Group([
      // body
      new fabric.Rect({ left: -130, top: -200, width: 260, height: 380, rx: 8, ...base }),
      // left sleeve
      new fabric.Polygon([{x:-130,y:-200},{x:-220,y:-160},{x:-200,y:-80},{x:-130,y:-100}], base),
      // right sleeve
      new fabric.Polygon([{x:130,y:-200},{x:220,y:-160},{x:200,y:-80},{x:130,y:-100}], base),
      // collar
      new fabric.Ellipse({ left: -50, top: -215, rx: 50, ry: 22, fill: garmentColor, stroke: '#aaaaaa', strokeWidth: 2 }),
    ], { left: cx, top: cy, originX: 'center', originY: 'center', selectable: false, evented: false, id: '__garment__' });

  } else if (type === 'hoodie') {
    group = new fabric.Group([
      new fabric.Rect({ left: -135, top: -200, width: 270, height: 390, rx: 10, ...base }),
      new fabric.Polygon([{x:-135,y:-200},{x:-230,y:-155},{x:-210,y:-70},{x:-135,y:-95}], base),
      new fabric.Polygon([{x:135,y:-200},{x:230,y:-155},{x:210,y:-70},{x:135,y:-95}], base),
      // hood
      new fabric.Ellipse({ left: -70, top: -240, rx: 70, ry: 55, ...base }),
      // pocket
      new fabric.Rect({ left: -55, top: 80, width: 110, height: 70, rx: 6, fill: garmentColor, stroke: '#aaaaaa', strokeWidth: 2 }),
    ], { left: cx, top: cy, originX: 'center', originY: 'center', selectable: false, evented: false, id: '__garment__' });

  } else if (type === 'dress') {
    group = new fabric.Group([
      // bodice
      new fabric.Polygon([{x:-80,y:-240},{x:80,y:-240},{x:110,y:-60},{x:-110,y:-60}], base),
      // skirt
      new fabric.Polygon([{x:-110,y:-60},{x:110,y:-60},{x:180,y:240},{x:-180,y:240}], base),
      // straps
      new fabric.Rect({ left: -70, top: -270, width: 28, height: 35, rx: 4, ...base }),
      new fabric.Rect({ left: 42,  top: -270, width: 28, height: 35, rx: 4, ...base }),
    ], { left: cx, top: cy, originX: 'center', originY: 'center', selectable: false, evented: false, id: '__garment__' });

  } else if (type === 'pants') {
    group = new fabric.Group([
      // waistband
      new fabric.Rect({ left: -130, top: -240, width: 260, height: 50, rx: 6, ...base }),
      // left leg
      new fabric.Polygon([{x:-130,y:-190},{x:0,y:-190},{x:-10,y:240},{x:-140,y:240}], base),
      // right leg
      new fabric.Polygon([{x:0,y:-190},{x:130,y:-190},{x:140,y:240},{x:10,y:240}], base),
    ], { left: cx, top: cy, originX: 'center', originY: 'center', selectable: false, evented: false, id: '__garment__' });

  } else if (type === 'jacket') {
    group = new fabric.Group([
      // body left
      new fabric.Polygon([{x:-140,y:-210},{x:0,y:-180},{x:0,y:200},{x:-140,y:200}], base),
      // body right
      new fabric.Polygon([{x:0,y:-180},{x:140,y:-210},{x:140,y:200},{x:0,y:200}], base),
      // left sleeve
      new fabric.Polygon([{x:-140,y:-210},{x:-230,y:-160},{x:-215,y:-50},{x:-140,y:-80}], base),
      // right sleeve
      new fabric.Polygon([{x:140,y:-210},{x:230,y:-160},{x:215,y:-50},{x:140,y:-80}], base),
      // collar left
      new fabric.Polygon([{x:-40,y:-210},{x:0,y:-180},{x:0,y:-100},{x:-50,y:-130}], { fill: '#dddddd', stroke: '#aaaaaa', strokeWidth: 1.5 }),
      // collar right
      new fabric.Polygon([{x:40,y:-210},{x:0,y:-180},{x:0,y:-100},{x:50,y:-130}], { fill: '#dddddd', stroke: '#aaaaaa', strokeWidth: 1.5 }),
    ], { left: cx, top: cy, originX: 'center', originY: 'center', selectable: false, evented: false, id: '__garment__' });

  } else if (type === 'cap') {
    group = new fabric.Group([
      // dome
      new fabric.Ellipse({ left: -120, top: -160, rx: 120, ry: 100, ...base }),
      // brim
      new fabric.Ellipse({ left: -150, top: -30, rx: 150, ry: 28, ...base }),
      // button on top
      new fabric.Circle({ left: -12, top: -175, radius: 12, fill: '#cccccc', stroke: '#aaaaaa', strokeWidth: 1.5 }),
    ], { left: cx, top: cy, originX: 'center', originY: 'center', selectable: false, evented: false, id: '__garment__' });
  }

  if (group) {
    canvas.add(group);
    canvas.sendToBack(group);
    garmentObj = group;
    canvas.renderAll();
    updateLayers();
  }
}

function setGarmentColor(color, el) {
  document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
  el.classList.add('active');
  garmentColor = color;
  if (garmentObj) {
    garmentObj.getObjects().forEach(o => {
      if (o.fill !== '#dddddd' && o.fill !== '#cccccc') o.set('fill', color);
    });
    canvas.renderAll();
  }
}

// ── Fabric Textures ──────────────────────────────────────────────────────────
let textureOverlay = null;
let currentTextureType = 'none';

function applyTexture(type) {
  // Remove previous texture overlay
  if (textureOverlay) { canvas.remove(textureOverlay); textureOverlay = null; }
  currentTextureType = type;
  if (type === 'none') { canvas.renderAll(); showToast('Texture removed'); return; }

  // Build a garment-sized tile (same bounding box as garment)
  const bounds = getGarmentBounds();
  const tw = bounds.width  || 260;
  const th = bounds.height || 380;

  const offscreen = document.createElement('canvas');
  offscreen.width  = Math.round(tw);
  offscreen.height = Math.round(th);
  const ctx = offscreen.getContext('2d');

  const base = garmentColor || '#ffffff';
  drawTexturePattern(ctx, type, Math.round(tw), Math.round(th), base);

  const dataUrl = offscreen.toDataURL();
  fabric.Image.fromURL(dataUrl, img => {
    // Place it centred over the garment
    img.set({
      left:        bounds.left + bounds.width  / 2,
      top:         bounds.top  + bounds.height / 2,
      originX:     'center',
      originY:     'center',
      selectable:  true,
      evented:     true,
      opacity:     0.88,
      id:          '__texture__',
      hasControls: true,
      hasBorders:  true,
    });

    // Clip texture to garment shape so it never bleeds outside
    if (garmentObj) {
      // Clone garment as clipPath (must be un-grouped clone)
      garmentObj.clone(cloneGroup => {
        // Convert group to a path-like clip using the group directly
        cloneGroup.set({
          left:    garmentObj.left,
          top:     garmentObj.top,
          originX: garmentObj.originX || 'center',
          originY: garmentObj.originY || 'center',
          absolutePositioned: true,
        });
        img.clipPath = cloneGroup;
        canvas.add(img);
        // Keep texture above garment but below design elements
        canvas.sendToBack(img);
        if (garmentObj) canvas.sendToBack(garmentObj);
        textureOverlay = img;
        canvas.renderAll();
        updateLayers();
        showToast('Texture applied — drag & resize to position it', 'success');
      });
    } else {
      canvas.add(img);
      canvas.sendToBack(img);
      textureOverlay = img;
      canvas.renderAll();
      updateLayers();
    }
  });
}

// Draw the actual pattern onto a given ctx
function drawTexturePattern(ctx, type, w, h, base) {
  if (type === 'stripes') {
    ctx.fillStyle = shadeColor(base, -15);
    ctx.fillRect(0, 0, w, h);
    const s = 22;
    ctx.fillStyle = shadeColor(base, 20);
    for (let i = -h; i < w + h; i += s * 2) {
      ctx.save();
      ctx.translate(i, 0);
      ctx.rotate(Math.PI / 4);
      ctx.fillRect(0, -h * 2, s, h * 4);
      ctx.restore();
    }
  } else if (type === 'grid') {
    ctx.fillStyle = shadeColor(base, 10);
    ctx.fillRect(0, 0, w, h);
    ctx.strokeStyle = shadeColor(base, -25);
    ctx.lineWidth = 1;
    const gs = 24;
    for (let x = 0; x < w; x += gs) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, h); ctx.stroke(); }
    for (let y = 0; y < h; y += gs) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke(); }
  } else if (type === 'dots') {
    ctx.fillStyle = shadeColor(base, 8);
    ctx.fillRect(0, 0, w, h);
    const dr = 3, ds = 18;
    for (let y = ds / 2; y < h; y += ds) {
      for (let x = ds / 2; x < w; x += ds) {
        ctx.beginPath();
        ctx.arc(x + (Math.floor(y / ds) % 2 === 0 ? 0 : ds / 2), y, dr, 0, Math.PI * 2);
        ctx.fillStyle = shadeColor(base, -28);
        ctx.fill();
      }
    }
  } else if (type === 'camo') {
    const camoColors = [shadeColor(base, 0), '#4a7c59', '#2d4a1e', '#6b5a2a'];
    ctx.fillStyle = camoColors[0];
    ctx.fillRect(0, 0, w, h);
    const rand = (min, max) => min + Math.random() * (max - min);
    for (let i = 0; i < 80; i++) {
      ctx.beginPath();
      ctx.ellipse(rand(0,w), rand(0,h), rand(20,80), rand(15,55), rand(0,Math.PI), 0, Math.PI*2);
      ctx.fillStyle = camoColors[Math.floor(rand(1, camoColors.length))];
      ctx.globalAlpha = rand(0.5, 0.9);
      ctx.fill();
    }
    ctx.globalAlpha = 1;
  } else if (type === 'tiedye') {
    const cx2 = w / 2, cy2 = h / 2;
    const tieColors = ['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#8b5cf6','#ec4899'];
    const maxR = Math.sqrt(cx2*cx2 + cy2*cy2);
    const ringW = maxR / tieColors.length;
    for (let i = tieColors.length - 1; i >= 0; i--) {
      const grad = ctx.createRadialGradient(cx2, cy2, 0, cx2, cy2, (i+1)*ringW);
      grad.addColorStop(0, tieColors[i]);
      grad.addColorStop(1, tieColors[(i+1) % tieColors.length]);
      ctx.fillStyle = grad;
      ctx.beginPath(); ctx.arc(cx2, cy2, (i+1)*ringW, 0, Math.PI*2); ctx.fill();
    }
    ctx.globalAlpha = 0.3;
    for (let a = 0; a < Math.PI*2; a += 0.3) {
      ctx.beginPath(); ctx.moveTo(cx2, cy2);
      for (let r = 0; r < maxR; r += 2) {
        ctx.lineTo(cx2 + r*Math.cos(a + r*0.04), cy2 + r*Math.sin(a + r*0.04));
      }
      ctx.strokeStyle = '#ffffff'; ctx.lineWidth = 1.5; ctx.stroke();
    }
    ctx.globalAlpha = 1;
  } else if (type === 'zigzag') {
    ctx.fillStyle = shadeColor(base, 10);
    ctx.fillRect(0, 0, w, h);
    const zs = 20;
    ctx.strokeStyle = shadeColor(base, -30); ctx.lineWidth = 2;
    for (let row = 0; row < h/zs + 1; row++) {
      ctx.beginPath();
      for (let col = 0; col < w/zs + 1; col++) {
        const x2 = col*zs, y2 = row*zs + (col%2===0 ? 0 : zs/2);
        col===0 ? ctx.moveTo(x2,y2) : ctx.lineTo(x2,y2);
      }
      ctx.stroke();
    }
  } else if (type === 'denim') {
    ctx.fillStyle = '#2563eb'; ctx.fillRect(0, 0, w, h);
    const ds2 = 6;
    ctx.globalAlpha = 0.15;
    for (let y = 0; y < h; y += ds2) {
      ctx.strokeStyle = y%(ds2*2)===0 ? '#ffffff' : '#1d4ed8';
      ctx.lineWidth = ds2/2;
      ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(w,y); ctx.stroke();
    }
    ctx.globalAlpha = 0.08;
    for (let x = 0; x < w; x += ds2*3) {
      ctx.strokeStyle = '#ffffff'; ctx.lineWidth = 1;
      ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,h); ctx.stroke();
    }
    ctx.globalAlpha = 1;
  }
}

// Get garment bounding box in canvas coordinates
function getGarmentBounds() {
  if (!garmentObj) return { left: 100, top: 100, width: 260, height: 380 };
  const br = garmentObj.getBoundingRect();
  return { left: br.left, top: br.top, width: br.width, height: br.height };
}

// Lighten/darken a hex colour
function shadeColor(hex, amount) {
  hex = hex.replace('#','');
  if (hex.length === 3) hex = hex.split('').map(c => c+c).join('');
  let r = parseInt(hex.substring(0,2),16);
  let g = parseInt(hex.substring(2,4),16);
  let b = parseInt(hex.substring(4,6),16);
  r = Math.min(255, Math.max(0, r + amount));
  g = Math.min(255, Math.max(0, g + amount));
  b = Math.min(255, Math.max(0, b + amount));
  return '#' + [r,g,b].map(v => v.toString(16).padStart(2,'0')).join('');
}

// ── Add elements ────────────────────────────────────────────────────────────
function addText() {
  const t = new fabric.IText('Edit me', {
    left: 200, top: 250, fontSize: 28, fill: '#000000',
    fontFamily: 'Segoe UI', id: 'text_' + Date.now()
  });
  canvas.add(t); canvas.setActiveObject(t); canvas.renderAll();
}
function addRect() {
  const r = new fabric.Rect({
    left: 180, top: 220, width: 120, height: 80,
    fill: '#c084fc', stroke: '#a855f7', strokeWidth: 1, rx: 4, ry: 4,
    id: 'rect_' + Date.now()
  });
  canvas.add(r); canvas.setActiveObject(r); canvas.renderAll();
}
function addCircle() {
  const c = new fabric.Circle({
    left: 200, top: 230, radius: 50,
    fill: '#c084fc', stroke: '#a855f7', strokeWidth: 1,
    id: 'circle_' + Date.now()
  });
  canvas.add(c); canvas.setActiveObject(c); canvas.renderAll();
}
function triggerImageUpload() { document.getElementById('imageUpload').click(); }
function addImage(e) {
  const file = e.target.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    fabric.Image.fromURL(ev.target.result, img => {
      img.scaleToWidth(180);
      img.set({ left: 160, top: 200, id: 'img_' + Date.now() });
      canvas.add(img); canvas.setActiveObject(img); canvas.renderAll();
    });
  };
  reader.readAsDataURL(file);
  e.target.value = '';
}

// ── Duplicate ────────────────────────────────────────────────────────────────
function duplicateSelected() {
  const obj = canvas.getActiveObject(); if (!obj) return;
  obj.clone(clone => {
    clone.set({ left: obj.left + 20, top: obj.top + 20, id: obj.type + '_' + Date.now() });
    canvas.add(clone); canvas.setActiveObject(clone); canvas.renderAll();
  });
}

// ── Export ───────────────────────────────────────────────────────────────────
function exportDesign() {
  const url = canvas.toDataURL({ format: 'png', multiplier: 2 });
  const a   = document.createElement('a');
  a.href    = url;
  a.download = (document.getElementById('designName').value.trim() || 'design') + '.png';
  a.click();
}

// ── Layer reorder ────────────────────────────────────────────────────────────
function bringForward() {
  const obj = canvas.getActiveObject(); if (!obj) return;
  canvas.bringForward(obj); canvas.renderAll(); updateLayers();
}
function sendBackward() {
  const obj = canvas.getActiveObject(); if (!obj) return;
  if (obj.id === '__garment__') return; // can't move garment
  canvas.sendBackwards(obj);
  // Always keep garment at back, texture just above it
  if (textureOverlay) canvas.sendToBack(textureOverlay);
  if (garmentObj)     canvas.sendToBack(garmentObj);
  canvas.renderAll(); updateLayers();
}
let drawing = false;
function toggleDraw() {
  drawing = !drawing;
  canvas.isDrawingMode = drawing;
  document.getElementById('drawBtn').classList.toggle('active', drawing);
  updateBrush();
}
function updateBrush() {
  canvas.freeDrawingBrush.width = parseInt(document.getElementById('brushSize').value);
  canvas.freeDrawingBrush.color = document.getElementById('brushColor').value;
}

// ── Properties panel ────────────────────────────────────────────────────────
canvas.on('selection:created',  updateProps);
canvas.on('selection:updated',  updateProps);
canvas.on('selection:cleared',  clearProps);
canvas.on('object:modified',    updateProps);

function updateProps() {
  const obj = canvas.getActiveObject();
  if (!obj) { clearProps(); return; }
  document.getElementById('noSelection').style.display = 'none';
  document.getElementById('objProps').style.display    = 'block';
  const isText = obj.type === 'i-text' || obj.type === 'text';
  document.getElementById('textProps').style.display     = isText ? 'block' : 'none';
  document.getElementById('fontSizeRow').style.display   = isText ? 'block' : 'none';
  document.getElementById('fontFamilyRow').style.display = isText ? 'block' : 'none';
  document.getElementById('fontStyleRow').style.display  = isText ? 'block' : 'none';
  if (isText) {
    document.getElementById('propText').value       = obj.text || '';
    document.getElementById('propFontSize').value   = obj.fontSize || 24;
    document.getElementById('propFontFamily').value = obj.fontFamily || 'Segoe UI';
    document.getElementById('boldBtn').classList.toggle('active',      obj.fontWeight === 'bold');
    document.getElementById('italicBtn').classList.toggle('active',    obj.fontStyle  === 'italic');
    document.getElementById('underlineBtn').classList.toggle('active', !!obj.underline);
  }
  document.getElementById('propFill').value        = toHex(obj.fill    || '#000000');
  document.getElementById('propStroke').value      = toHex(obj.stroke  || '#000000');
  document.getElementById('propStrokeWidth').value = obj.strokeWidth || 0;
  document.getElementById('propOpacity').value     = Math.round((obj.opacity ?? 1) * 100);
  document.getElementById('propX').value           = Math.round(obj.left || 0);
  document.getElementById('propY').value           = Math.round(obj.top  || 0);
}
function clearProps() {
  document.getElementById('noSelection').style.display = 'block';
  document.getElementById('objProps').style.display    = 'none';
}
function toHex(color) {
  if (!color || color === 'transparent') return '#000000';
  if (/^#[0-9a-f]{6}$/i.test(color)) return color;
  const ctx = document.createElement('canvas').getContext('2d');
  ctx.fillStyle = color;
  return ctx.fillStyle;
}
function updateProp(prop) {
  const obj = canvas.getActiveObject(); if (!obj) return;
  const map = {
    text:        () => obj.set('text',        document.getElementById('propText').value),
    fontSize:    () => obj.set('fontSize',    parseInt(document.getElementById('propFontSize').value)),
    fontFamily:  () => obj.set('fontFamily',  document.getElementById('propFontFamily').value),
    fill:        () => obj.set('fill',        document.getElementById('propFill').value),
    stroke:      () => obj.set('stroke',      document.getElementById('propStroke').value),
    strokeWidth: () => obj.set('strokeWidth', parseInt(document.getElementById('propStrokeWidth').value)),
    opacity:     () => obj.set('opacity',     parseInt(document.getElementById('propOpacity').value) / 100),
    left:        () => obj.set('left',        parseInt(document.getElementById('propX').value)),
    top:         () => obj.set('top',         parseInt(document.getElementById('propY').value)),
  };
  if (map[prop]) map[prop]();
  obj.setCoords(); canvas.renderAll();
}
function toggleBold() {
  const obj = canvas.getActiveObject(); if (!obj) return;
  obj.set('fontWeight', obj.fontWeight === 'bold' ? 'normal' : 'bold');
  canvas.renderAll(); updateProps();
}
function toggleItalic() {
  const obj = canvas.getActiveObject(); if (!obj) return;
  obj.set('fontStyle', obj.fontStyle === 'italic' ? 'normal' : 'italic');
  canvas.renderAll(); updateProps();
}
function toggleUnderline() {
  const obj = canvas.getActiveObject(); if (!obj) return;
  obj.set('underline', !obj.underline);
  canvas.renderAll(); updateProps();
}
function deleteSelected() {
  const obj = canvas.getActiveObject(); if (!obj) return;
  canvas.remove(obj); canvas.discardActiveObject(); canvas.renderAll(); updateLayers();
}

// ── Layers ───────────────────────────────────────────────────────────────────
function updateLayers() {
  const list = document.getElementById('layersList');
  list.innerHTML = '';
  canvas.getObjects().slice().reverse().forEach(obj => {
    if (obj.id === '__garment__') return;
    const isTexture = obj.id === '__texture__';
    const label = isTexture                  ? '🎨 Fabric Texture'
                : obj.type === 'i-text'      ? '✏ ' + (obj.text || '').slice(0, 14)
                : obj.type === 'image'       ? '🖼 Image'
                : '▭ ' + obj.type;
    const div = document.createElement('div');
    div.className   = 'layer-item';
    div.textContent = label;
    if (isTexture) div.style.color = '#c084fc';
    div.onclick = () => { canvas.setActiveObject(obj); canvas.renderAll(); updateProps(); };
    list.appendChild(div);
  });
}
canvas.on('object:added',   updateLayers);
canvas.on('object:removed', updateLayers);

// ── Keyboard shortcuts ───────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (document.activeElement.tagName === 'INPUT') return;
  if (e.key === 'Delete' || e.key === 'Backspace') deleteSelected();
  if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undoAction(); }
  if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); redoAction(); }
  if ((e.ctrlKey || e.metaKey) && e.key === 'd') { e.preventDefault(); duplicateSelected(); }
});

// ── Save ─────────────────────────────────────────────────────────────────────
async function saveDesign() {
  const name  = document.getElementById('designName').value.trim() || 'Untitled Design';
  const json  = JSON.stringify(canvas.toJSON(['id']));
  const thumb = canvas.toDataURL({ format: 'jpeg', quality: 0.5, multiplier: 0.4 });
  const data  = new FormData();
  data.append('design',    json);
  data.append('name',      name);
  data.append('thumbnail', thumb);
  if (DESIGN_ID) data.append('id', DESIGN_ID);
  try {
    const res  = await fetch('save_design.php', { method: 'POST', body: data });
    const text = await res.text();
    if (res.ok) {
      showToast('Design saved!', 'success');
      const match = text.match(/id:(\d+)/);
      if (!DESIGN_ID && match) {
          DESIGN_ID = parseInt(match[1], 10);
          history.replaceState(null, '', '?id=' + match[1]);
      }
    } else {
      showToast('Save failed', 'error');
    }
  } catch { showToast('Network error', 'error'); }
}

// ── Toast ────────────────────────────────────────────────────────────────────
function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast show' + (type ? ' ' + type : '');
  setTimeout(() => { t.className = 'toast'; }, 2500);
}

// ── Design Presets ────────────────────────────────────────────────────────────
function applyPreset(name) {
  // Remove all non-garment, non-texture objects
  canvas.getObjects().filter(o => o.id !== '__garment__' && o.id !== '__texture__').forEach(o => canvas.remove(o));

  const cx = canvas.width / 2;

  const presets = {
    streetwear: () => {
      setGarmentColorByHex('#1a1a1a');
      const brand = new fabric.IText('FORGE', {
        left: cx - 100, top: 180, fontSize: 72, fontWeight: '800',
        fill: '#ffffff', fontFamily: 'Impact', charSpacing: 150
      });
      const sub = new fabric.IText('EST. 2024 — ORIGINAL', {
        left: cx - 95, top: 268, fontSize: 14, fontWeight: '600',
        fill: '#c084fc', fontFamily: 'Arial', charSpacing: 200
      });
      const line1 = new fabric.Line([cx - 120, 310, cx + 120, 310], { stroke: '#c084fc', strokeWidth: 1.5 });
      const line2 = new fabric.Line([cx - 120, 262, cx + 120, 262], { stroke: '#c084fc', strokeWidth: 1.5 });
      const star  = new fabric.IText('★', { left: cx - 18, top: 140, fontSize: 36, fill: '#c084fc', fontFamily: 'Arial' });
      [star, brand, line1, line2, sub].forEach(o => canvas.add(o));
    },

    minimal: () => {
      setGarmentColorByHex('#f5f5f5');
      const dot  = new fabric.Circle({ left: cx - 8, top: 195, radius: 8, fill: '#1a1a1a' });
      const name2 = new fabric.IText('fashion forge', {
        left: cx - 80, top: 220, fontSize: 22, fontFamily: 'Georgia',
        fill: '#1a1a1a', fontStyle: 'italic', charSpacing: 80
      });
      const rule    = new fabric.Line([cx - 60, 268, cx + 60, 268], { stroke: '#1a1a1a', strokeWidth: 0.8 });
      const tagline = new fabric.IText('WEAR YOUR STORY', {
        left: cx - 62, top: 280, fontSize: 9, fontFamily: 'Arial',
        fill: '#888888', charSpacing: 300
      });
      [dot, name2, rule, tagline].forEach(o => canvas.add(o));
    },

    vintage: () => {
      setGarmentColorByHex('#8b5cf6');
      const outer = new fabric.Circle({ left: cx - 80, top: 175, radius: 80, fill: 'transparent', stroke: '#fef08a', strokeWidth: 2 });
      const inner = new fabric.Circle({ left: cx - 68, top: 187, radius: 68, fill: 'transparent', stroke: '#fef08a', strokeWidth: 1 });
      const title = new fabric.IText('FASHION', {
        left: cx - 72, top: 210, fontSize: 28, fontWeight: '800',
        fill: '#fef08a', fontFamily: 'Impact', charSpacing: 120
      });
      const year  = new fabric.IText('· SINCE 1994 ·', {
        left: cx - 55, top: 248, fontSize: 11,
        fill: '#fef08a', fontFamily: 'Georgia', fontStyle: 'italic', charSpacing: 150
      });
      const forge = new fabric.IText('FORGE', {
        left: cx - 28, top: 190, fontSize: 13,
        fill: '#fef08a', fontFamily: 'Arial', fontWeight: '600', charSpacing: 250
      });
      [outer, inner, forge, title, year].forEach(o => canvas.add(o));
    },

    neon: () => {
      setGarmentColorByHex('#0f0f0f');
      const glow1 = new fabric.IText('NEON', {
        left: cx - 105, top: 170, fontSize: 80, fontWeight: '800',
        fill: '#000000', stroke: '#00f5ff', strokeWidth: 2,
        fontFamily: 'Impact', charSpacing: 100
      });
      const glow2 = new fabric.IText('NIGHTS', {
        left: cx - 90, top: 255, fontSize: 40, fontWeight: '800',
        fill: '#000000', stroke: '#f0abfc', strokeWidth: 1.5,
        fontFamily: 'Impact', charSpacing: 80
      });
      const line  = new fabric.Line([cx - 100, 308, cx + 100, 308], { stroke: '#00f5ff', strokeWidth: 1 });
      const sub2  = new fabric.IText('FASHION FORGE 2024', {
        left: cx - 80, top: 320, fontSize: 10,
        fill: '#f0abfc', fontFamily: 'Arial', charSpacing: 200
      });
      [glow1, glow2, line, sub2].forEach(o => canvas.add(o));
    },

    sport: () => {
      setGarmentColorByHex('#1d4ed8');
      const num = new fabric.IText('23', {
        left: cx - 80, top: 190, fontSize: 130, fontWeight: '800',
        fill: 'rgba(255,255,255,0.12)', fontFamily: 'Impact'
      });
      const teamName = new fabric.IText('FASHION FORGE', {
        left: cx - 95, top: 190, fontSize: 24, fontWeight: '800',
        fill: '#ffffff', fontFamily: 'Impact', charSpacing: 150
      });
      const pos = new fabric.IText('DESIGN SQUAD', {
        left: cx - 65, top: 224, fontSize: 12,
        fill: '#93c5fd', fontFamily: 'Arial', fontWeight: '600', charSpacing: 300
      });
      const stripe1 = new fabric.Rect({ left: cx - 160, top: 216, width: 320, height: 6, fill: '#ffffff', opacity: 0.15 });
      const stripe2 = new fabric.Rect({ left: cx - 160, top: 226, width: 320, height: 3, fill: '#ffffff', opacity: 0.08 });
      [num, stripe1, stripe2, teamName, pos].forEach(o => canvas.add(o));
    }
  };

  if (presets[name]) {
    presets[name]();
    canvas.renderAll();
    updateLayers();
    showToast('Preset applied: ' + name.charAt(0).toUpperCase() + name.slice(1), 'success');
  }
}

// Helper — set garment color by hex without needing a swatch click
function setGarmentColorByHex(color) {
  garmentColor = color;
  document.querySelectorAll('.color-swatch').forEach(s => {
    s.classList.toggle('active', s.style.background === color ||
      s.style.backgroundColor === color);
  });
  if (garmentObj) {
    garmentObj.getObjects().forEach(o => {
      if (o.fill !== '#dddddd' && o.fill !== '#cccccc') o.set('fill', color);
    });
    canvas.renderAll();
  }
}

// ── Boot ─────────────────────────────────────────────────────────────────────
window._activeGarment = 'tshirt'; // default
if (DESIGN_DATA) {
  canvas.loadFromJSON(DESIGN_DATA, () => {
    canvas.getObjects().forEach(o => {
      if (o.id === '__garment__') { o.selectable = false; o.evented = false; garmentObj = o; }
      if (o.id === '__texture__') { o.selectable = true;  o.evented = true;  textureOverlay = o; }
    });
    canvas.renderAll(); updateLayers(); snapshot();
  });
} else {
  loadTemplate('tshirt');
}</script>

<!-- ── Three.js 3D Preview ──────────────────────────────────────────────── -->
<script type="importmap">
{
  "imports": {
    "three": "https://cdn.jsdelivr.net/npm/three@0.165.0/build/three.module.js",
    "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.165.0/examples/jsm/"
  }
}
</script>
<script type="module">
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader }    from 'three/addons/loaders/GLTFLoader.js';

// ── Module-level state ───────────────────────────────────────────────────────
let renderer, scene, camera, controls, animId;
let currentMesh = null;
let fabricTexture = null;

// Map garment type → glb path
const MODEL_MAP = {
  tshirt:  '../models/tshirt.glb',
  hoodie:  '../models/hoodie.glb',
  dress:   '../models/dress.glb',
  pants:   '../models/pants.glb',
  jacket:  '../models/jacket.glb',
  cap:     '../models/cap.glb',
};

// Expose open/close to the global scope so inline onclick handlers work
window.open3DPreview  = open3DPreview;
window.close3DPreview = close3DPreview;
window.handleOverlayClick = handleOverlayClick;

function open3DPreview() {
  const overlay = document.getElementById('previewOverlay');
  overlay.classList.add('open');
  initThree();
  loadGarment();
}

function close3DPreview() {
  document.getElementById('previewOverlay').classList.remove('open');
  destroyThree();
}

function handleOverlayClick(e) {
  if (e.target === document.getElementById('previewOverlay')) close3DPreview();
}

// ── Three.js lifecycle ───────────────────────────────────────────────────────
function initThree() {
  if (renderer) return; // already initialised

  const body = document.getElementById('previewBody');

  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setPixelRatio(window.devicePixelRatio);
  renderer.setSize(body.clientWidth, body.clientHeight);
  renderer.shadowMap.enabled = true;
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.2;
  body.appendChild(renderer.domElement);

  scene = new THREE.Scene();
  scene.background = new THREE.Color(0x111111);

  camera = new THREE.PerspectiveCamera(45, body.clientWidth / body.clientHeight, 0.01, 100);
  camera.position.set(0, 0.5, 2.5);

  // Lights
  const ambient = new THREE.AmbientLight(0xffffff, 0.6);
  scene.add(ambient);

  const key = new THREE.DirectionalLight(0xffffff, 1.4);
  key.position.set(2, 4, 3);
  key.castShadow = true;
  scene.add(key);

  const fill = new THREE.DirectionalLight(0xc084fc, 0.4);
  fill.position.set(-3, 1, -2);
  scene.add(fill);

  const rim = new THREE.DirectionalLight(0xffffff, 0.3);
  rim.position.set(0, -2, -3);
  scene.add(rim);

  controls = new OrbitControls(camera, renderer.domElement);
  controls.enableDamping = true;
  controls.dampingFactor = 0.07;
  controls.minDistance   = 0.5;
  controls.maxDistance   = 8;
  controls.target.set(0, 0, 0);

  // Resize observer
  const ro = new ResizeObserver(() => {
    if (!renderer) return;
    renderer.setSize(body.clientWidth, body.clientHeight);
    camera.aspect = body.clientWidth / body.clientHeight;
    camera.updateProjectionMatrix();
  });
  ro.observe(body);

  animate();
}

function animate() {
  animId = requestAnimationFrame(animate);
  controls.update();
  // Keep fabric texture in sync if design changed
  if (fabricTexture) {
    fabricTexture.needsUpdate = true;
  }
  renderer.render(scene, camera);
}

function destroyThree() {
  if (animId) cancelAnimationFrame(animId);
  if (renderer) {
    renderer.dispose();
    renderer.domElement.remove();
    renderer = null;
  }
  scene = camera = controls = currentMesh = fabricTexture = null;
  animId = null;
}

// ── Load garment ─────────────────────────────────────────────────────────────
function loadGarment() {
  // Detect active garment type from garmentObj (set in main script)
  const garmentType = detectGarmentType();
  const glbPath     = MODEL_MAP[garmentType];

  setStatus(true, 'Loading 3D model…');

  // Remove previous mesh
  if (currentMesh) { scene.remove(currentMesh); currentMesh = null; }

  if (!glbPath) {
    setStatus(false, '');
    showFallbackMesh(garmentType);
    return;
  }

  const loader = new GLTFLoader();
  loader.load(
    glbPath,
    (gltf) => {
      const model = gltf.scene;

      // Centre & normalise scale
      const box    = new THREE.Box3().setFromObject(model);
      const size   = box.getSize(new THREE.Vector3());
      const centre = box.getCenter(new THREE.Vector3());
      const maxDim = Math.max(size.x, size.y, size.z);
      model.scale.setScalar(1.6 / maxDim);
      model.position.sub(centre.multiplyScalar(1.6 / maxDim));

      // Build Fabric canvas texture
      fabricTexture = buildFabricTexture();

      // Apply texture to all mesh materials
      model.traverse(node => {
        if (node.isMesh) {
          node.castShadow    = true;
          node.receiveShadow = true;
          node.material = new THREE.MeshStandardMaterial({
            map:         fabricTexture,
            roughness:   0.75,
            metalness:   0.05,
          });
        }
      });

      scene.add(model);
      currentMesh = model;
      setStatus(false, '');
    },
    undefined,
    () => {
      // GLB not found — show procedural fallback with texture
      setStatus(false, '');
      showFallbackMesh(garmentType);
    }
  );
}

// ── Build a THREE.CanvasTexture from the Fabric.js canvas ───────────────────
function buildFabricTexture() {
  // Access the main Fabric canvas element from the global scope
  const fabricCanvas = document.getElementById('mainCanvas');
  const tex = new THREE.CanvasTexture(fabricCanvas);
  tex.colorSpace  = THREE.SRGBColorSpace;
  tex.needsUpdate = true;
  return tex;
}

// ── Detect which garment is currently loaded ─────────────────────────────────
function detectGarmentType() {
  // The main script stores the garment object; we read its type from the button
  // We look at which template was last loaded via window._activeGarment
  return window._activeGarment || 'tshirt';
}

// ── Procedural fallback when .glb is missing ─────────────────────────────────
function showFallbackMesh(type) {
  fabricTexture = buildFabricTexture();

  const mat = new THREE.MeshStandardMaterial({
    map:       fabricTexture,
    roughness: 0.8,
    metalness: 0.0,
    side:      THREE.DoubleSide,
  });

  let geo;
  if (type === 'pants') {
    // Two cylinders side by side
    const g1 = new THREE.CylinderGeometry(0.18, 0.22, 1.1, 24);
    const g2 = new THREE.CylinderGeometry(0.18, 0.22, 1.1, 24);
    g2.translate(0.42, 0, 0);
    const merged = mergeGeos([g1, g2]);
    geo = merged;
  } else if (type === 'dress') {
    geo = new THREE.ConeGeometry(0.55, 1.6, 32);
  } else if (type === 'cap') {
    geo = new THREE.SphereGeometry(0.55, 32, 16, 0, Math.PI * 2, 0, Math.PI / 2);
  } else {
    // T-shirt / hoodie / jacket — use a rounded box
    geo = new THREE.BoxGeometry(0.9, 1.1, 0.25, 4, 6, 2);
  }

  const mesh = new THREE.Mesh(geo, mat);
  mesh.castShadow = mesh.receiveShadow = true;
  scene.add(mesh);
  currentMesh = mesh;

  // Show a friendly note
  const body = document.getElementById('previewBody');
  const note = document.createElement('div');
  note.style.cssText = 'position:absolute;bottom:12px;left:0;right:0;text-align:center;font-size:.72rem;color:#666;pointer-events:none';
  note.innerHTML = `No <code style="color:#888">${type}.glb</code> found — showing preview shape. 
    Add models from <a href="https://poly.pizza" target="_blank" style="color:#c084fc">poly.pizza</a> or 
    <a href="https://sketchfab.com" target="_blank" style="color:#c084fc">Sketchfab</a>.`;
  body.appendChild(note);
}

// Minimal geometry merge (no BufferGeometryUtils dependency)
function mergeGeos(geos) {
  const positions = [];
  const normals   = [];
  const uvs       = [];
  const indices   = [];
  let   offset    = 0;
  for (const g of geos) {
    const pos = g.attributes.position.array;
    const nor = g.attributes.normal.array;
    const uv  = g.attributes.uv.array;
    const idx = g.index ? g.index.array : null;
    for (let i = 0; i < pos.length; i++) positions.push(pos[i]);
    for (let i = 0; i < nor.length; i++) normals.push(nor[i]);
    for (let i = 0; i < uv.length;  i++) uvs.push(uv[i]);
    const count = pos.length / 3;
    if (idx) { for (let i = 0; i < idx.length; i++) indices.push(idx[i] + offset); }
    else      { for (let i = 0; i < count; i++) indices.push(i + offset); }
    offset += count;
  }
  const out = new THREE.BufferGeometry();
  out.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
  out.setAttribute('normal',   new THREE.Float32BufferAttribute(normals,   3));
  out.setAttribute('uv',       new THREE.Float32BufferAttribute(uvs,       2));
  out.setIndex(indices);
  return out;
}

// ── Status overlay ────────────────────────────────────────────────────────────
function setStatus(show, msg) {
  const el = document.getElementById('previewStatus');
  el.style.display = show ? 'flex' : 'none';
  if (msg) el.querySelector('span').textContent = msg;
}
</script>
</body>
</html>
