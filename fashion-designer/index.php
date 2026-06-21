<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FashionForge — Design Studio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0f0f0f;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 60px;
            border-bottom: 1px solid #222;
        }
        .logo { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.5px; }
        .logo span { color: #c084fc; }
        .nav-links a {
            color: #aaa;
            text-decoration: none;
            margin-left: 24px;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: #fff; }

        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            padding: 80px 20px;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 16px;
        }
        .hero h1 span { color: #c084fc; }
        .hero p {
            color: #888;
            font-size: 1.1rem;
            max-width: 500px;
            margin-bottom: 40px;
        }
        .cta-buttons { display: flex; gap: 12px; }
        .btn {
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary { background: #c084fc; color: #000; }
        .btn-primary:hover { background: #a855f7; }
        .btn-outline { background: transparent; color: #fff; border: 1px solid #333; }
        .btn-outline:hover { border-color: #c084fc; color: #c084fc; }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .modal h2 { font-size: 1.4rem; margin-bottom: 24px; }
        .tabs { display: flex; gap: 0; margin-bottom: 28px; border-bottom: 1px solid #2a2a2a; }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            color: #666;
            font-size: 0.9rem;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
        }
        .tab.active { color: #c084fc; border-bottom-color: #c084fc; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.8rem; color: #888; margin-bottom: 6px; }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            background: #111;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #fff;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus { border-color: #c084fc; }
        .form-submit {
            width: 100%;
            padding: 12px;
            background: #c084fc;
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .form-submit:hover { background: #a855f7; }
        .msg { font-size: 0.85rem; margin-top: 12px; padding: 10px; border-radius: 6px; display: none; }
        .msg.error { background: #2a1a1a; color: #f87171; display: block; }
        .msg.success { background: #1a2a1a; color: #4ade80; display: block; }
        .close-modal {
            float: right;
            background: none;
            border: none;
            color: #666;
            font-size: 1.2rem;
            cursor: pointer;
            margin-top: -10px;
        }
        .close-modal:hover { color: #fff; }
    </style>
</head>
<body>

<nav>
    <div class="logo">Fashion<span>Forge</span></div>
    <div class="nav-links">
        <a href="#" onclick="openModal()">Sign In</a>
        <a href="#" onclick="openModal('register')" class="btn btn-primary" style="padding:8px 18px;border-radius:6px;">Get Started</a>
    </div>
</nav>

<section class="hero">
    <h1>Design clothes.<br><span>Your way.</span></h1>
    <p>A canvas for fashion brands to create, customize, and explore clothing designs — no design degree needed.</p>
    <div class="cta-buttons">
        <a href="#" class="btn btn-primary" onclick="openModal('register')">Start Designing Free</a>
        <a href="#" class="btn btn-outline" onclick="openModal()">Sign In</a>
    </div>
</section>

<!-- Auth Modal -->
<div class="modal-overlay" id="authModal">
    <div class="modal">
        <button class="close-modal" onclick="closeModal()">✕</button>
        <div class="tabs">
            <div class="tab active" onclick="switchTab('login')">Sign In</div>
            <div class="tab" onclick="switchTab('register')">Register</div>
        </div>

        <!-- Login -->
        <div class="tab-content active" id="tab-login">
            <form id="loginForm">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="you@brand.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="form-submit">Sign In</button>
                <div class="msg" id="loginMsg"></div>
            </form>
        </div>

        <!-- Register -->
        <div class="tab-content" id="tab-register">
            <form id="registerForm">
                <div class="form-group">
                    <label>Brand / Username</label>
                    <input type="text" name="username" required placeholder="Your brand name">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="you@brand.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Min 8 characters">
                </div>
                <button type="submit" class="form-submit">Create Account</button>
                <div class="msg" id="registerMsg"></div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(tab = 'login') {
    document.getElementById('authModal').classList.add('active');
    switchTab(tab);
}
function closeModal() {
    document.getElementById('authModal').classList.remove('active');
}
function switchTab(tab) {
    document.querySelectorAll('.tab').forEach((t, i) => {
        t.classList.toggle('active', (i === 0 && tab === 'login') || (i === 1 && tab === 'register'));
    });
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
}

document.getElementById('loginForm').addEventListener('submit', async e => {
    e.preventDefault();
    const data = new FormData(e.target);
    const res = await fetch('login.php', { method: 'POST', body: data });
    const text = await res.text();
    const msg = document.getElementById('loginMsg');
    if (text.includes('success')) {
        window.location.href = 'dashboard.php';
    } else {
        msg.className = 'msg error';
        msg.textContent = text;
    }
});

document.getElementById('registerForm').addEventListener('submit', async e => {
    e.preventDefault();
    const data = new FormData(e.target);
    const res = await fetch('register.php', { method: 'POST', body: data });
    const text = await res.text();
    const msg = document.getElementById('registerMsg');
    if (text.includes('success')) {
        msg.className = 'msg success';
        msg.textContent = 'Account created! Signing you in...';
        setTimeout(() => window.location.href = 'dashboard.php', 1000);
    } else {
        msg.className = 'msg error';
        msg.textContent = text;
    }
});

document.getElementById('authModal').addEventListener('click', e => {
    if (e.target === document.getElementById('authModal')) closeModal();
});
</script>
</body>
</html>
