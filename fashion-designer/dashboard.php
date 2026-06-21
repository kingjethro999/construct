<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Designer';

$stmt = $conn->prepare("SELECT id, name, thumbnail, updated_at FROM designs WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$designs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — FashionForge</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: #fff; min-height: 100vh; }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 40px;
            border-bottom: 1px solid #1e1e1e;
        }
        .logo { font-size: 1.3rem; font-weight: 700; }
        .logo span { color: #c084fc; }
        .nav-right { display: flex; align-items: center; gap: 16px; }
        .nav-right span { color: #888; font-size: 0.9rem; }
        .btn {
            padding: 9px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary { background: #c084fc; color: #000; }
        .btn-primary:hover { background: #a855f7; }
        .btn-ghost { background: transparent; color: #888; border: 1px solid #2a2a2a; }
        .btn-ghost:hover { color: #fff; border-color: #444; }

        .main { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 36px;
        }
        .page-header h1 { font-size: 1.6rem; font-weight: 700; }
        .page-header p { color: #666; font-size: 0.9rem; margin-top: 4px; }

        .designs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .design-card {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.2s, transform 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .design-card:hover { border-color: #c084fc; transform: translateY(-2px); }
        .card-thumb {
            width: 100%;
            aspect-ratio: 4/3;
            background: #111;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .card-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .card-thumb .placeholder { color: #333; font-size: 2.5rem; }
        .card-info { padding: 14px; }
        .card-info h3 { font-size: 0.9rem; font-weight: 600; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-info p { font-size: 0.75rem; color: #555; }

        .new-card {
            background: transparent;
            border: 2px dashed #2a2a2a;
            border-radius: 12px;
            aspect-ratio: unset;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color 0.2s;
            text-decoration: none;
            color: #555;
            gap: 8px;
        }
        .new-card:hover { border-color: #c084fc; color: #c084fc; }
        .new-card .plus { font-size: 2rem; line-height: 1; }
        .new-card span { font-size: 0.85rem; font-weight: 500; }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
            color: #444;
        }
        .empty-state p { margin-top: 8px; font-size: 0.9rem; }

        /* Card actions */
        .design-card { position: relative; }
        .card-actions {
            position: absolute;
            top: 8px; right: 8px;
            display: flex;
            gap: 6px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .design-card:hover .card-actions { opacity: 1; }
        .card-action-btn {
            width: 30px; height: 30px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .btn-delete { background: rgba(239,68,68,0.85); color: #fff; }
        .btn-delete:hover { background: #ef4444; }
        .btn-duplicate { background: rgba(30,30,30,0.9); color: #ccc; border: 1px solid #333; }
        .btn-duplicate:hover { background: #2a2a2a; color: #fff; }

        /* Confirm modal */
        .confirm-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.7); z-index: 200;
            align-items: center; justify-content: center;
        }
        .confirm-overlay.active { display: flex; }
        .confirm-box {
            background: #1a1a1a; border: 1px solid #2a2a2a;
            border-radius: 14px; padding: 32px; max-width: 360px; width: 90%;
            text-align: center;
        }
        .confirm-box h3 { font-size: 1.1rem; margin-bottom: 10px; }
        .confirm-box p { color: #666; font-size: 0.88rem; margin-bottom: 24px; }
        .confirm-btns { display: flex; gap: 10px; justify-content: center; }
        .confirm-btns button {
            padding: 9px 22px; border-radius: 8px; border: none;
            font-size: 0.88rem; font-weight: 600; cursor: pointer;
        }
        .btn-cancel-confirm { background: #2a2a2a; color: #aaa; }
        .btn-cancel-confirm:hover { background: #333; }
        .btn-confirm-delete { background: #ef4444; color: #fff; }
        .btn-confirm-delete:hover { background: #dc2626; }
    </style>
</head>
<body>

<nav>
    <div class="logo">Fashion<span>Forge</span></div>
    <div class="nav-right">
        <span>👋 <?= htmlspecialchars($username) ?></span>
        <a href="logout.php" class="btn btn-ghost">Sign Out</a>
        <a href="designer.php" class="btn btn-primary">+ New Design</a>
    </div>
</nav>

<div class="main">
    <div class="page-header">
        <div>
            <h1>My Designs</h1>
            <p><?= count($designs) ?> design<?= count($designs) !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <div class="designs-grid">
        <a href="designer.php" class="new-card">
            <div class="plus">+</div>
            <span>New Design</span>
        </a>

        <?php if (empty($designs)): ?>
            <div class="empty-state">
                <div style="font-size:3rem">👗</div>
                <p>No designs yet. Create your first one!</p>
            </div>
        <?php else: ?>
            <?php foreach ($designs as $d): ?>
                <a href="designer.php?id=<?= $d['id'] ?>" class="design-card">
                    <div class="card-actions">
                        <button class="card-action-btn btn-duplicate" title="Duplicate"
                            onclick="event.preventDefault();duplicateDesign(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['name'])) ?>')">⧉</button>
                        <button class="card-action-btn btn-delete" title="Delete"
                            onclick="event.preventDefault();confirmDelete(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['name'])) ?>')">🗑</button>
                    </div>
                    <div class="card-thumb">
                        <?php if ($d['thumbnail']): ?>
                            <img src="<?= htmlspecialchars($d['thumbnail']) ?>" alt="<?= htmlspecialchars($d['name']) ?>">
                        <?php else: ?>
                            <div class="placeholder">👕</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-info">
                        <h3><?= htmlspecialchars($d['name']) ?></h3>
                        <p><?= date('M j, Y', strtotime($d['updated_at'])) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <h3>Delete Design?</h3>
        <p id="confirmText">This action cannot be undone.</p>
        <div class="confirm-btns">
            <button class="btn-cancel-confirm" onclick="closeConfirm()">Cancel</button>
            <button class="btn-confirm-delete" onclick="doDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
let deleteTargetId = null;

function confirmDelete(id, name) {
    deleteTargetId = id;
    document.getElementById('confirmText').textContent = `"${name}" will be permanently deleted.`;
    document.getElementById('confirmOverlay').classList.add('active');
}

function closeConfirm() {
    deleteTargetId = null;
    document.getElementById('confirmOverlay').classList.remove('active');
}

async function doDelete() {
    if (!deleteTargetId) return;
    const data = new FormData();
    data.append('id', deleteTargetId);
    const res = await fetch('delete_design.php', { method: 'POST', body: data });
    const text = await res.text();
    if (text === 'deleted') {
        closeConfirm();
        location.reload();
    } else {
        alert('Could not delete design. Please try again.');
    }
}

async function duplicateDesign(id, name) {
    // Instead of loading the full page, hit a duplicate endpoint
    const data = new FormData();
    data.append('source_id', id);
    data.append('name', 'Copy of ' + name);
    const r = await fetch('duplicate_design.php', { method: 'POST', body: data });
    if (r.ok) location.reload();
    else alert('Could not duplicate. Please try again.');
}

document.getElementById('confirmOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('confirmOverlay')) closeConfirm();
});
</script>

</body>
</html>
