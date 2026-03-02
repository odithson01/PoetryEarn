<?php
session_start();

// Initialize User Data if not exists
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 0.00;
    $_SESSION['uid'] = "USER" . rand(1000, 9999);
}

// --- DATA: Shayari List ---
$shayaris = [
    ["text" => "Success is not final, failure is not fatal: it is the courage to continue that counts.", "cat" => "Success"],
    ["text" => "Your time is limited, so don't waste it living someone else's life.", "cat" => "Life"],
    ["text" => "The best way to predict your future is to create it.", "cat" => "Motivation"],
    ["text" => "Do what you can, with what you have, where you are.", "cat" => "Wisdom"]
];

// --- LOGIC: Handle AJAX Actions ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    // Action 1: Get New Shayari
    if ($_GET['action'] == 'next_shayari') {
        echo json_encode($shayaris[array_rand($shayaris)]);
    }
    
    // Action 2: Claim Reward (Watch Ad Simulation)
    if ($_GET['action'] == 'claim_reward') {
        $reward = 0.05; // $0.05 per "ad"
        $_SESSION['balance'] += $reward;
        echo json_encode(['status' => 'success', 'new_balance' => number_format($_SESSION['balance'], 2)]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PoetryEarn - Shayari & Rewards</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:ital@1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --accent: #10b981;
            --bg: #f3f4f6;
            --white: #ffffff;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; padding-bottom: 80px; }

        /* Header & Wallet */
        .header {
            background: var(--primary);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .wallet-card {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 600;
        }

        /* Main Container */
        .container { max-width: 500px; margin: 20px auto; padding: 0 15px; }

        /* Shayari Card */
        .card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
        }

        .quote { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin: 20px 0; min-height: 80px; }

        /* Earning Zone */
        .earning-section { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; }

        .task-card {
            background: var(--white);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            border: 2px solid transparent;
        }

        .task-card:hover { border-color: var(--primary); transform: translateY(-3px); }
        .task-card i { font-size: 2rem; color: var(--primary); margin-bottom: 10px; }
        .task-card h4 { margin: 5px 0; font-size: 0.9rem; }
        .task-card span { font-size: 0.8rem; color: #6b7280; }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: var(--white);
            display: flex;
            justify-content: space-around;
            padding: 15px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }

        .nav-item { color: #9ca3af; text-decoration: none; text-align: center; font-size: 0.75rem; }
        .nav-item.active { color: var(--primary); }

        /* Modal / Overlay */
        #ad-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            color: white;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .btn {
            background: var(--primary); color: white; border: none; padding: 12px 25px;
            border-radius: 10px; cursor: pointer; font-weight: 600; width: 100%;
        }

        .reward-popup {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            background: var(--accent); color: white; padding: 10px 20px;
            border-radius: 50px; display: none; z-index: 2000;
        }
    </style>
</head>
<body>

    <!-- Reward Notification -->
    <div id="reward-popup" class="reward-popup">Claimed $0.05 Reward!</div>

    <!-- Header -->
    <div class="header">
        <div style="font-weight: 600;">PoetryEarn</div>
        <div class="wallet-card">
            <i class="fa-solid fa-wallet"></i> $<span id="balance"><?php echo number_format($_SESSION['balance'], 2); ?></span>
        </div>
    </div>

    <div class="container">
        <!-- Shayari Section -->
        <div class="card">
            <span style="color: var(--primary); font-weight: 600; font-size: 0.8rem; text-transform: uppercase;">Daily Inspiration</span>
            <div class="quote" id="shayari-text">"Click the button below to start your day with poetry."</div>
            <button class="btn" onclick="nextShayari()">Read Next Shayari</button>
        </div>

        <!-- Earning Zone -->
        <h3 style="margin-left: 5px;">Earning Zone</h3>
        <div class="earning-section">
            <div class="task-card" onclick="startVideoAd()">
                <i class="fa-solid fa-circle-play"></i>
                <h4>Watch & Earn</h4>
                <span>Earn $0.05 per ad</span>
            </div>
            <div class="task-card" onclick="copyReferral()">
                <i class="fa-solid fa-users"></i>
                <h4>Refer Friends</h4>
                <span>Earn $1.00 per user</span>
            </div>
            <div class="task-card" onclick="alert('Coming Soon: High paying surveys')">
                <i class="fa-solid fa-clipboard-list"></i>
                <h4>Take Surveys</h4>
                <span>Earn up to $5.00</span>
            </div>
            <div class="task-card" onclick="alert('Minimal Payout: $10.00')">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <h4>Withdraw</h4>
                <span>Instant Payout</span>
            </div>
        </div>
    </div>

    <!-- Ad Simulation Overlay -->
    <div id="ad-overlay">
        <h2>Loading Reward...</h2>
        <p>Your reward will be ready in <span id="timer">5</span>s</p>
        <div style="font-size: 0.8rem; color: #888;">(Replace this with your Adsterra/Google Script)</div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="#" class="nav-item active"><i class="fa-solid fa-house"></i><div>Home</div></a>
        <a href="#" class="nav-item"><i class="fa-solid fa-gift"></i><div>Rewards</div></a>
        <a href="#" class="nav-item" onclick="alert('User ID: <?php echo $_SESSION['uid']; ?>')"><i class="fa-solid fa-user"></i><div>Profile</div></a>
    </nav>

    <script>
        // 1. Get Next Shayari (AJAX)
        async function nextShayari() {
            const res = await fetch('?action=next_shayari');
            const data = await res.json();
            document.getElementById('shayari-text').innerText = `"${data.text}"`;
        }

        // 2. Simulated Rewarded Video Ad
        function startVideoAd() {
            const overlay = document.getElementById('ad-overlay');
            const timerSpan = document.getElementById('timer');
            let timeLeft = 5;

            overlay.style.display = 'flex';
            
            const countdown = setInterval(async () => {
                timeLeft--;
                timerSpan.innerText = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    overlay.style.display = 'none';
                    
                    // Call backend to add money
                    const res = await fetch('?action=claim_reward');
                    const data = await res.json();
                    
                    document.getElementById('balance').innerText = data.new_balance;
                    showPopup();
                }
            }, 1000);
        }

        function showPopup() {
            const p = document.getElementById('reward-popup');
            p.style.display = 'block';
            setTimeout(() => p.style.display = 'none', 3000);
        }

        function copyReferral() {
            const link = window.location.href + "?ref=<?php echo $_SESSION['uid']; ?>";
            navigator.clipboard.writeText(link);
            alert("Referral Link Copied: " + link);
        }
    </script>
</body>
</html>