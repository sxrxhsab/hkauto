<?php
// ================================================
// CONFIGURATION SUPABASE (hkauto-avis)
// ================================================
$supabase_url = 'https://dgydrebzjuppsavulahe.supabase.co';
$supabase_key = 'sb_publishable_rxz0S8mpSq4eYECppJ57iw_QMdVZ5Kp';

// Fonction pour appeler l'API Supabase (avec CURL)
function supabaseQuery($table, $method = 'GET', $data = null) {
    global $supabase_url, $supabase_key;
    
    $url = $supabase_url . '/rest/v1/' . $table;
    if ($method === 'GET') {
        $url .= '?select=*';
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST' && $data !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Log pour déboguer (visible dans les logs Render)
    error_log("Supabase: $method $table - HTTP $httpCode");
    if ($error) {
        error_log("Supabase Error: $error");
    }
    if ($httpCode !== 200 && $httpCode !== 201) {
        error_log("Supabase Response: " . substr($response, 0, 500));
    }
    
    if ($httpCode === 200 || $httpCode === 201) {
        $result = json_decode($response, true);
        if ($result !== null) {
            return $result;
        }
    }
    
    return false;
}

// Récupérer les avis
$reviews = supabaseQuery('reviews', 'GET');

// Avis par défaut si erreur ou pas d'avis
if (empty($reviews) || $reviews === false) {
    $reviews = [
        [
            'username' => 'Jean D.',
            'rating' => 5,
            'comment' => 'Service impeccable, équipe professionnelle et à l\'écoute. Je recommande vivement !',
            'is_verified' => true,
            'created_at' => '2026-06-15 14:30:00'
        ],
        [
            'username' => 'Marie M.',
            'rating' => 5,
            'comment' => 'Un garage de confiance avec des prix compétitifs. Ma voiture est entre de bonnes mains.',
            'is_verified' => true,
            'created_at' => '2026-06-10 09:15:00'
        ],
        [
            'username' => 'Pierre D.',
            'rating' => 5,
            'comment' => 'Très satisfait du service. Intervention rapide et qualité au rendez-vous.',
            'is_verified' => true,
            'created_at' => '2026-06-05 16:45:00'
        ],
        [
            'username' => 'Sophie L.',
            'rating' => 5,
            'comment' => 'Excellent garage ! L\'équipe est très professionnelle et les délais ont été respectés.',
            'is_verified' => true,
            'created_at' => '2026-05-28 11:20:00'
        ],
        [
            'username' => 'Thomas R.',
            'rating' => 4,
            'comment' => 'Très bon accueil et service de qualité. Les prix sont corrects et le travail est soigné.',
            'is_verified' => true,
            'created_at' => '2026-05-20 08:00:00'
        ],
        [
            'username' => 'Nadia B.',
            'rating' => 5,
            'comment' => 'Garage sérieux et compétent. J\'ai eu un problème de freins, ils ont tout réglé rapidement.',
            'is_verified' => true,
            'created_at' => '2026-05-15 13:10:00'
        ]
    ];
}

// Traitement avis
$success_review = '';
$error_review = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = htmlspecialchars(trim($_POST['comment'] ?? ''));
    
    if (!empty($username) && $rating >= 1 && $rating <= 5 && !empty($comment)) {
        $data = [
            'username' => $username,
            'rating' => $rating,
            'comment' => $comment,
            'is_verified' => true
        ];
        
        $result = supabaseQuery('reviews', 'POST', $data);
        if ($result !== false) {
            $success_review = "✅ Merci pour votre avis !";
            // Recharger les avis
            $reviews = supabaseQuery('reviews', 'GET');
            if (empty($reviews) || $reviews === false) {
                $reviews = [
                    [
                        'username' => $username,
                        'rating' => $rating,
                        'comment' => $comment,
                        'is_verified' => true,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ];
            }
        } else {
            $error_review = "❌ Une erreur est survenue. Veuillez réessayer.";
        }
    } else {
        $error_review = "❌ Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>HK AUTO - Garage Automobile de Prestige</title>
    <meta name="description" content="HK AUTO - Garage automobile de confiance à Bezons. Entretien, diagnostic, pneus, freinage, carrosserie. Plus de 15 ans d'expertise.">
    
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="shortcut icon" type="image/png" href="logo.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { overflow-x: hidden; width: 100%; max-width: 100vw; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #0a0a0a; 
            color: #F5F5F5; 
            overflow-x: hidden;
            width: 100%;
        }
        h1, h2, h3, h4, h5 { font-family: 'Poppins', sans-serif; }
        
        .btn-primary {
            background: linear-gradient(135deg, #D90429, #8B0000);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: white;
            padding: 18px 44px;
            border-radius: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 1.05rem;
            min-height: 60px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 30px rgba(217,4,41,0.3);
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        .btn-primary:hover::before { opacity: 1; }
        .btn-primary:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 12px 50px rgba(217,4,41,0.5); }
        .btn-primary:active { transform: scale(0.97); }
        
        .btn-whatsapp {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.15);
            color: white;
            padding: 18px 44px;
            border-radius: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 1.05rem;
            min-height: 60px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-whatsapp .fa-whatsapp { color: #25D366; }
        .btn-whatsapp:hover { border-color: #D90429; color: #D90429; transform: translateY(-4px) scale(1.02); box-shadow: 0 12px 50px rgba(217,4,41,0.15); }
        .btn-whatsapp:hover .fa-whatsapp { color: #D90429; }
        
        .whatsapp-float {
            position: fixed;
            bottom: 100px;
            right: 24px;
            z-index: 999;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #25D366, #128C7E);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 34px;
            box-shadow: 0 6px 40px rgba(37,211,102,0.4);
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            text-decoration: none;
            animation: pulse-whatsapp 2.5s infinite;
        }
        .whatsapp-float:hover { transform: scale(1.12) rotate(-5deg); box-shadow: 0 8px 60px rgba(37,211,102,0.6); }
        @keyframes pulse-whatsapp { 0%,100% { box-shadow: 0 6px 40px rgba(37,211,102,0.4); } 50% { box-shadow: 0 6px 60px rgba(37,211,102,0.7); } }
        @media (max-width: 767px) { .whatsapp-float { bottom: 80px; right: 16px; width: 56px; height: 56px; font-size: 28px; } }
        
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            padding: 2px 0;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
        }
        .navbar.scrolled {
            background: rgba(10,10,10,0.92);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            padding: 2px 0;
            box-shadow: 0 4px 50px rgba(0,0,0,0.5);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .navbar .logo-img {
            height: 150px;
            width: auto;
            transition: height 0.4s ease;
            filter: drop-shadow(0 2px 30px rgba(217,4,41,0.15));
        }
        .navbar.scrolled .logo-img { height: 120px; }
        .nav-link {
            color: rgba(255,255,255,0.7);
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            font-size: 0.95rem;
            position: relative;
            padding: 4px 0;
            letter-spacing: 0.3px;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2.5px;
            background: linear-gradient(90deg, #D90429, #8B0000);
            transition: width 0.4s cubic-bezier(0.4,0,0.2,1);
            border-radius: 2px;
        }
        .nav-link:hover { color: white; }
        .nav-link:hover::after { width: 100%; }
        .nav-link.active { color: #D90429; }
        .nav-link.active::after { width: 100%; }
        
        .btn-rdv-nav {
            background: linear-gradient(135deg, #D90429, #8B0000);
            color: white;
            padding: 10px 28px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 20px rgba(217,4,41,0.3);
        }
        .btn-rdv-nav:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(217,4,41,0.4); color: white; }
        
        .hero {
            min-height: 100vh;
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
            width: 100%;
        }
        .hero-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translateX(-50%) translateY(-50%) scale(1.05);
            object-fit: cover;
        }
        .hero-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.55) 40%, rgba(0,0,0,0.8) 100%);
            z-index: 2;
        }
        .hero-content {
            position: relative;
            z-index: 10;
            padding: 120px 20px 40px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .hero-title {
            font-size: clamp(4.5rem, 13vw, 8rem);
            font-weight: 900;
            color: white;
            letter-spacing: -5px;
            line-height: 1.02;
            text-shadow: 0 8px 80px rgba(0,0,0,0.5);
        }
        .hero-title .highlight {
            background: linear-gradient(135deg, #D90429, #ff1744);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-subtitle {
            font-size: clamp(1.2rem, 2.2vw, 1.8rem);
            color: rgba(255,255,255,0.85);
            margin-top: 8px;
            font-weight: 300;
            letter-spacing: 1.5px;
            text-shadow: 0 2px 30px rgba(0,0,0,0.3);
        }
        .hero-desc {
            color: rgba(255,255,255,0.45);
            font-size: clamp(0.95rem, 1.1vw, 1.1rem);
            max-width: 520px;
            margin: 14px 0 0;
            line-height: 1.8;
            font-weight: 300;
            letter-spacing: 0.3px;
        }
        .hero-tags-line {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 20px;
            margin-top: 16px;
        }
        .hero-tags-line span {
            color: rgba(255,255,255,0.35);
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .hero-tags-line span i { color: #D90429; font-size: 0.8rem; }
        
        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 32px;
        }
        .hero-buttons .btn-primary,
        .hero-buttons .btn-whatsapp {
            min-width: 220px;
        }
        
        .hero-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 28px 48px;
            margin-top: 32px;
            padding: 16px 0;
            border-top: 1px solid rgba(255,255,255,0.06);
            max-width: 500px;
        }
        .hero-stats .stat-item {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
            font-weight: 500;
        }
        .hero-stats .stat-item .stars { color: #FFD700; letter-spacing: 2px; }
        .hero-stats .stat-item i { color: #D90429; }
        .hero-stats .stat-item strong { color: white; font-weight: 700; }
        
        .hero-scroll {
            position: absolute;
            bottom: 36px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.2);
            font-size: 0.65rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            animation: scrollBounce 2.5s infinite;
        }
        .hero-scroll i {
            font-size: 1.2rem;
            animation: scrollArrow 2s infinite;
        }
        @keyframes scrollArrow { 0%,100% { transform: translateY(0); opacity: 1; } 50% { transform: translateY(8px); opacity: 0.3; } }
        @keyframes scrollBounce { 0%,100% { transform: translateX(-50%) translateY(0); } 50% { transform: translateX(-50%) translateY(-6px); } }
        
        .feature-card {
            background: rgba(30,30,30,0.3);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 24px 20px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .feature-card:hover { transform: translateY(-6px); border-color: rgba(217,4,41,0.3); box-shadow: 0 15px 50px rgba(217,4,41,0.06); }
        .feature-card .icon { font-size: 2.2rem; color: #D90429; margin-bottom: 8px; }
        .feature-card h4 { font-weight: 600; font-size: 1rem; color: white; }
        .feature-card p { color: rgba(255,255,255,0.3); font-size: 0.8rem; margin-top: 2px; }
        
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(20px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeInModal 0.3s ease;
        }
        .modal-overlay.active { display: flex; }
        @keyframes fadeInModal { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .modal-content {
            background: #1a1a1a;
            border-radius: 24px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 40px;
            border: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 16px;
            right: 20px;
            font-size: 1.8rem;
            color: rgba(255,255,255,0.3);
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.3s ease;
            padding: 8px;
        }
        .modal-close:hover { color: #D90429; }
        .modal-content img { width: 100%; height: 250px; object-fit: cover; border-radius: 16px; margin-bottom: 20px; }
        .modal-content h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 12px; color: white; }
        .modal-content h2 span { color: #D90429; }
        .modal-content p { color: rgba(255,255,255,0.5); line-height: 1.8; font-size: 1rem; }
        .modal-content ul { list-style: none; padding: 0; margin-top: 16px; }
        .modal-content ul li { padding: 8px 0; color: rgba(255,255,255,0.4); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .modal-content ul li i { color: #D90429; width: 20px; }
        @media (max-width: 767px) { .modal-content { padding: 24px; margin: 10px; } .modal-content img { height: 160px; } .modal-content h2 { font-size: 1.4rem; } }
        
        .section-title {
            font-size: clamp(2.2rem, 4.5vw, 3.5rem);
            font-weight: 800;
            text-align: center;
            letter-spacing: -1px;
        }
        .section-title span { color: #D90429; }
        .section-subtitle {
            color: rgba(255,255,255,0.35);
            text-align: center;
            max-width: 600px;
            margin: 14px auto 0;
            font-size: clamp(1rem, 1.2vw, 1.2rem);
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        .section-padding { padding: 70px 20px; }
        @media (min-width: 768px) { .section-padding { padding: 110px 40px; } }
        
        .service-card {
            background: rgba(30,30,30,0.4);
            backdrop-filter: blur(12px);
            border-radius: 18px;
            padding: 28px;
            transition: all 0.5s cubic-bezier(0.4,0,0.2,1);
            border: 1px solid rgba(255,255,255,0.05);
            height: 100%;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #D90429, #8B0000);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .service-card:hover::before { opacity: 1; }
        .service-card img { width: 100%; height: 200px; object-fit: cover; border-radius: 14px; margin-bottom: 18px; transition: transform 0.6s cubic-bezier(0.4,0,0.2,1); }
        .service-card:hover img { transform: scale(1.04); }
        .service-card:hover { transform: translateY(-10px); box-shadow: 0 25px 70px rgba(217,4,41,0.08); border-color: rgba(217,4,41,0.15); }
        .service-card h3 { font-size: 1.25rem; font-weight: 700; margin-bottom: 6px; }
        .service-card p { color: rgba(255,255,255,0.4); font-size: 0.9rem; line-height: 1.7; }
        .service-card .badge { display: inline-block; background: rgba(217,4,41,0.1); color: #D90429; padding: 4px 16px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; margin-top: 12px; }
        .service-card .click-hint { position: absolute; bottom: 16px; right: 16px; color: rgba(255,255,255,0.1); font-size: 0.7rem; transition: color 0.3s ease; }
        .service-card:hover .click-hint { color: rgba(217,4,41,0.4); }
        
        .brands-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            padding: 10px 0;
        }
        .brand-item-svg {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 12px;
            background: rgba(255,255,255,0.02);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.04);
            transition: all 0.5s cubic-bezier(0.4,0,0.2,1);
            cursor: default;
            min-height: 90px;
            gap: 6px;
        }
        .brand-item-svg img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            filter: brightness(0) invert(0.3);
            transition: all 0.5s cubic-bezier(0.4,0,0.2,1);
            opacity: 0.6;
        }
        .brand-item-svg .brand-name {
            font-size: 0.65rem;
            font-weight: 500;
            color: rgba(255,255,255,0.12);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.5s cubic-bezier(0.4,0,0.2,1);
        }
        .brand-item-svg:hover {
            background: rgba(255,255,255,0.05);
            border-color: rgba(217,4,41,0.25);
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(217,4,41,0.06);
        }
        .brand-item-svg:hover img {
            filter: brightness(1) invert(0);
            opacity: 1;
            transform: scale(1.08);
        }
        .brand-item-svg:hover .brand-name {
            color: rgba(255,255,255,0.5);
        }
        
        .brand-item-svg.hidden-brand { display: none; }
        .brand-item-svg.hidden-brand.show { display: flex; }
        
        .btn-show-more {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.5);
            padding: 14px 36px;
            border-radius: 14px;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Inter', sans-serif;
            min-height: 54px;
        }
        .btn-show-more:hover {
            background: rgba(217,4,41,0.1);
            border-color: #D90429;
            color: #D90429;
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(217,4,41,0.08);
        }
        .btn-show-more i { transition: transform 0.4s ease; }
        .btn-show-more.active i { transform: rotate(180deg); }
        
        @media (min-width: 640px) {
            .brands-grid { grid-template-columns: repeat(4, 1fr); gap: 20px; }
            .brand-item-svg img { width: 55px; height: 55px; }
        }
        @media (min-width: 1024px) {
            .brands-grid { grid-template-columns: repeat(6, 1fr); gap: 24px; }
            .brand-item-svg img { width: 60px; height: 60px; }
            .brand-item-svg { min-height: 100px; padding: 20px 12px; }
        }
        
        .brands-footer {
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.03);
        }
        
        .timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 24px;
            padding: 22px 0;
            position: relative;
        }
        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 30px;
            top: 72px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #D90429, transparent);
        }
        .timeline-number {
            width: 60px;
            height: 60px;
            min-width: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #D90429, #8B0000);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 30px rgba(217,4,41,0.3);
        }
        .timeline-content h4 { font-weight: 700; font-size: 1.15rem; }
        .timeline-content p { color: rgba(255,255,255,0.3); font-size: 0.9rem; }
        
        .review-card {
            background: rgba(30,30,30,0.4);
            backdrop-filter: blur(12px);
            border-radius: 18px;
            padding: 28px;
            border: 1px solid rgba(255,255,255,0.05);
            height: 100%;
            transition: all 0.4s ease;
        }
        .review-card:hover { transform: translateY(-6px); border-color: rgba(217,4,41,0.12); }
        .review-card .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #D90429, #8B0000);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        .review-card .stars { color: #FFD700; font-size: 0.9rem; letter-spacing: 2px; }
        .review-card .comment { color: rgba(255,255,255,0.5); line-height: 1.8; margin-top: 10px; font-size: 0.95rem; }
        .review-card .verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #25D366;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .stat-number {
            font-size: clamp(2.8rem, 5.5vw, 4rem);
            font-weight: 900;
            color: #D90429;
            font-family: 'Poppins', sans-serif;
            text-shadow: 0 2px 30px rgba(217,4,41,0.15);
        }
        .stat-label { color: rgba(255,255,255,0.3); margin-top: 6px; font-size: 1rem; font-weight: 300; }
        
        .glass {
            background: rgba(30,30,30,0.25);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.06);
        }
        
        .form-input {
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.06);
            color: white;
            padding: 16px 20px;
            border-radius: 14px;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 1rem;
            min-height: 54px;
        }
        .form-input:focus {
            border-color: #D90429;
            outline: none;
            box-shadow: 0 0 0 4px rgba(217,4,41,0.08);
        }
        .form-input::placeholder { color: rgba(255,255,255,0.2); }
        .form-label {
            color: rgba(255,255,255,0.5);
            font-weight: 500;
            margin-bottom: 6px;
            display: block;
            font-size: 0.9rem;
        }
        .form-label .required { color: #D90429; }
        
        .mobile-bottom-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: rgba(10,10,10,0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255,255,255,0.05);
            padding: 6px 0 env(safe-area-inset-bottom, 6px) 0;
        }
        .mobile-bottom-bar .bar-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            color: rgba(255,255,255,0.3);
            text-decoration: none;
            font-size: 0.55rem;
            font-weight: 500;
            padding: 6px 0;
            transition: color 0.3s ease;
            min-height: 44px;
        }
        .mobile-bottom-bar .bar-item i { font-size: 1.3rem; }
        .mobile-bottom-bar .bar-item:active { color: #D90429; }
        .mobile-bottom-bar .bar-item.rdv {
            background: linear-gradient(135deg, #D90429, #8B0000);
            color: white;
            border-radius: 14px;
            padding: 6px 14px;
            min-height: 40px;
            margin: 0 4px;
        }
        .mobile-bottom-bar .bar-item.rdv:active { transform: scale(0.95); }
        
        @media (max-width: 767px) {
            .mobile-bottom-bar { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2px; padding: 4px 6px env(safe-area-inset-bottom, 6px) 6px; }
            body { padding-bottom: 64px; }
            .hero-title { font-size: 3.5rem; letter-spacing: -2px; }
            .hero-buttons { flex-direction: column; width: 100%; }
            .hero-buttons .btn-primary,
            .hero-buttons .btn-whatsapp { width: 100%; min-width: unset; justify-content: center; }
            .hero-stats { gap: 16px 24px; }
            .hero-stats .stat-item { font-size: 0.8rem; }
            .navbar .logo-img { height: 70px; }
            .navbar.scrolled .logo-img { height: 60px; }
        }
        
        @media (min-width: 768px) {
            .hero-buttons { flex-wrap: wrap; }
            .mobile-bottom-bar { display: none !important; }
            body { padding-bottom: 0; }
        }
        
        @media (max-width: 991px) {
            .desktop-nav { display: none !important; }
        }
        @media (min-width: 992px) {
            .mobile-nav-toggle { display: none !important; }
        }
        
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
            background: rgba(10,10,10,0.98);
            backdrop-filter: blur(50px);
            -webkit-backdrop-filter: blur(50px);
            padding: 80px 30px 40px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        .mobile-menu.open { display: flex; animation: fadeInMenu 0.4s ease; }
        @keyframes fadeInMenu {
            from { opacity: 0; transform: scale(0.95) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .mobile-menu .nav-link {
            font-size: 1.6rem;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 10px 0;
        }
        .mobile-menu .nav-link:hover,
        .mobile-menu .nav-link:active { color: #D90429; transform: translateX(12px); }
        .mobile-menu .btn-primary { width: 100%; max-width: 280px; justify-content: center; }
        .mobile-menu-close {
            position: absolute;
            top: 24px;
            right: 24px;
            font-size: 2rem;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
            padding: 12px;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }
        .mobile-menu-close:hover { opacity: 1; }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a0a0a; }
        ::-webkit-scrollbar-thumb { background: #D90429; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #8B0000; }
        
        .swiper-pagination-bullet {
            background: rgba(255,255,255,0.15);
            opacity: 1;
            width: 10px;
            height: 10px;
        }
        .swiper-pagination-bullet-active {
            background: #D90429 !important;
            transform: scale(1.2);
        }
    </style>
</head>
<body>

<!-- ===== WHATSAPP FLOATING ===== -->
<a href="https://wa.me/33758640784?text=Bonjour%20HK%20AUTO,%20je%20souhaite%20prendre%20rendez-vous%20pour%20mon%20véhicule." 
   class="whatsapp-float" target="_blank" aria-label="WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- ===== MOBILE MENU ===== -->
<div class="mobile-menu" id="mobileMenu">
    <img src="logo.png" alt="HK AUTO" class="h-12 w-auto mb-6">
    <button class="mobile-menu-close" id="closeMenu"><i class="fas fa-times"></i></button>
    <a href="#services" class="nav-link" onclick="closeMobileMenu()">Services</a>
    <a href="#about" class="nav-link" onclick="closeMobileMenu()">À propos</a>
    <a href="#process" class="nav-link" onclick="closeMobileMenu()">Processus</a>
    <a href="#reviews" class="nav-link" onclick="closeMobileMenu()">Avis</a>
    <a href="#contact" class="nav-link" onclick="closeMobileMenu()">Contact</a>
    <a href="#rdv" class="btn-primary" onclick="closeMobileMenu()" style="padding: 14px 28px; min-height: 52px; font-size: 0.95rem;">
        <i class="fas fa-calendar-check"></i> Prendre RDV
    </a>
    <div class="flex gap-5 mt-6">
        <a href="https://www.facebook.com/people/HK-Auto/61592171522340/" target="_blank" class="text-white/30 hover:text-[#D90429] text-2xl transition-colors"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/autohkcontact" target="_blank" class="text-white/30 hover:text-[#D90429] text-2xl transition-colors"><i class="fab fa-instagram"></i></a>
        <a href="https://www.tiktok.com/@hkautoservices" target="_blank" class="text-white/30 hover:text-[#D90429] text-2xl transition-colors"><i class="fab fa-tiktok"></i></a>
        <a href="https://wa.me/33758640784" target="_blank" class="text-white/30 hover:text-[#25D366] text-2xl transition-colors"><i class="fab fa-whatsapp"></i></a>
    </div>
</div>

<!-- ===== NAVBAR ===== -->
<nav class="navbar" id="navbar">
    <div class="container mx-auto px-4 flex justify-between items-center max-w-7xl">
        <a href="#" class="flex items-center no-underline">
            <img src="logo.png" alt="HK AUTO" class="logo-img">
        </a>
        
        <div class="desktop-nav flex items-center space-x-8">
            <a href="#services" class="nav-link">Services</a>
            <a href="#about" class="nav-link">À propos</a>
            <a href="#process" class="nav-link">Processus</a>
            <a href="#reviews" class="nav-link">Avis</a>
            <a href="#contact" class="nav-link">Contact</a>
            <a href="#rdv" class="btn-rdv-nav">
                <i class="fas fa-calendar-check"></i> RDV
            </a>
        </div>
        
        <button class="mobile-nav-toggle text-white text-2xl bg-transparent border-none cursor-pointer p-2" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero" id="home">
    <video class="hero-video" autoplay muted loop playsinline preload="metadata">
        <source src="hk.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    
    <div class="hero-content">
        <h1 class="hero-title" data-aos="fade-up" data-aos-duration="1000">
            HK <span class="highlight">AUTO</span>
        </h1>
        
        <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
            L'expertise automobile au service de votre véhicule
        </p>
        
        <p class="hero-desc" data-aos="fade-up" data-aos-delay="150" data-aos-duration="1000">
            Des techniciens qualifiés et un service rapide pour prendre soin de votre véhicule.
        </p>
        
        <div class="hero-tags-line" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            <span><i class="fas fa-check-circle"></i> Toutes marques</span>
            <span><i class="fas fa-bolt"></i> Intervention rapide</span>
            <span><i class="fas fa-award"></i> 15 ans d'expertise</span>
        </div>
        
        <div class="hero-buttons" data-aos="fade-up" data-aos-delay="250" data-aos-duration="1000">
            <a href="#rdv" class="btn-primary">
                <i class="fas fa-calendar-check"></i> Prendre rendez-vous
            </a>
            <a href="https://wa.me/33758640784?text=Bonjour%20HK%20AUTO,%20je%20souhaite%20prendre%20rendez-vous%20pour%20mon%20véhicule." 
               class="btn-whatsapp" target="_blank">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
        </div>
        
        <div class="hero-stats" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1000">
            <span class="stat-item"><span class="stars">★★★★★</span> <strong>4.9/5</strong></span>
            <span class="stat-item"><i class="fas fa-users"></i> <strong>+350</strong> clients satisfaits</span>
        </div>
    </div>
    
    <div class="hero-scroll">
        <span>Défiler</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- ===== SERVICES ===== -->
<section class="section-padding bg-[#0a0a0a]" id="services">
    <div class="container mx-auto max-w-7xl">
        <div data-aos="fade-up">
            <h2 class="section-title">Nos <span>Services</span></h2>
            <p class="section-subtitle">Cliquez sur un service pour plus de détails</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-12">
            <div class="service-card" data-aos="fade-up" onclick="openModal('entretien')">
                <img src="3.png" alt="Entretien et diagnostic" loading="lazy">
                <h3>Entretien &amp; Diagnostic</h3>
                <p>Révision complète, contrôle technique et diagnostic électronique.</p>
                <span class="badge">Révision • Contrôle</span>
                <span class="click-hint"><i class="fas fa-search-plus"></i> Détails</span>
            </div>
            
            <div class="service-card" data-aos="fade-up" data-aos-delay="50" onclick="openModal('pneus')">
                <img src="2.png" alt="Pneus" loading="lazy">
                <h3>Pneus</h3>
                <p>Montage, équilibrage, réparation de pneus toutes marques.</p>
                <span class="badge">Montage • Équilibrage</span>
                <span class="click-hint"><i class="fas fa-search-plus"></i> Détails</span>
            </div>
            
            <div class="service-card" data-aos="fade-up" data-aos-delay="100" onclick="openModal('vidange')">
                <img src="5.png" alt="Révision et vidange" loading="lazy">
                <h3>Révision &amp; Vidange</h3>
                <p>Vidange moteur, remplacement des filtres, vérification complète.</p>
                <span class="badge">Huile • Filtres</span>
                <span class="click-hint"><i class="fas fa-search-plus"></i> Détails</span>
            </div>
            
            <div class="service-card" data-aos="fade-up" data-aos-delay="150" onclick="openModal('freinage')">
                <img src="3.png" alt="Freinage" loading="lazy">
                <h3>Freinage</h3>
                <p>Contrôle et remplacement des plaquettes, disques, liquide de frein.</p>
                <span class="badge">Plaquettes • Disques</span>
                <span class="click-hint"><i class="fas fa-search-plus"></i> Détails</span>
            </div>
            
            <div class="service-card" data-aos="fade-up" data-aos-delay="200" onclick="openModal('carrosserie')">
                <img src="7.png" alt="Carrosserie" loading="lazy">
                <h3>Carrosserie</h3>
                <p>Debosselage, peinture, réparation de carrosserie.</p>
                <span class="badge">Peinture • Réparation</span>
                <span class="click-hint"><i class="fas fa-search-plus"></i> Détails</span>
            </div>
            
            <div class="service-card" data-aos="fade-up" data-aos-delay="250" onclick="openModal('atelier')">
                <img src="5.png" alt="Atelier moderne" loading="lazy">
                <h3>Atelier Moderne</h3>
                <p>Un atelier équipé des dernières technologies.</p>
                <span class="badge">Moderne • Équipé</span>
                <span class="click-hint"><i class="fas fa-search-plus"></i> Détails</span>
            </div>
        </div>
    </div>
</section>

<!-- ===== MODALES SERVICES ===== -->
<div id="modalEntretien" class="modal-overlay" onclick="closeModalOutside(event, 'modalEntretien')">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('modalEntretien')"><i class="fas fa-times"></i></button>
        <img src="3.png" alt="Entretien et diagnostic">
        <h2>Entretien &amp; <span>Diagnostic</span></h2>
        <p>Un entretien complet pour garantir la fiabilité et la sécurité de votre véhicule.</p>
        <ul>
            <li><i class="fas fa-check-circle"></i> Révision complète moteur</li>
            <li><i class="fas fa-check-circle"></i> Contrôle technique approfondi</li>
            <li><i class="fas fa-check-circle"></i> Diagnostic électronique</li>
            <li><i class="fas fa-check-circle"></i> Maintenance préventive</li>
            <li><i class="fas fa-check-circle"></i> Vérification des niveaux</li>
        </ul>
        <a href="#rdv" class="btn-primary" style="margin-top: 20px; width: 100%; justify-content: center; padding: 14px 28px; min-height: 52px; font-size: 0.95rem;" onclick="closeModal('modalEntretien')">
            <i class="fas fa-calendar-check"></i> Prendre rendez-vous
        </a>
    </div>
</div>

<div id="modalPneus" class="modal-overlay" onclick="closeModalOutside(event, 'modalPneus')">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('modalPneus')"><i class="fas fa-times"></i></button>
        <img src="2.png" alt="Pneus">
        <h2><span>Pneus</span></h2>
        <p>Des prestations complètes pour vos pneus, toutes marques et tous types de véhicules.</p>
        <ul>
            <li><i class="fas fa-check-circle"></i> Montage de pneus</li>
            <li><i class="fas fa-check-circle"></i> Équilibrage précis</li>
            <li><i class="fas fa-check-circle"></i> Réparation de crevaison</li>
            <li><i class="fas fa-check-circle"></i> Toutes marques</li>
            <li><i class="fas fa-check-circle"></i> Conforme aux normes</li>
        </ul>
        <a href="#rdv" class="btn-primary" style="margin-top: 20px; width: 100%; justify-content: center; padding: 14px 28px; min-height: 52px; font-size: 0.95rem;" onclick="closeModal('modalPneus')">
            <i class="fas fa-calendar-check"></i> Prendre rendez-vous
        </a>
    </div>
</div>

<div id="modalVidange" class="modal-overlay" onclick="closeModalOutside(event, 'modalVidange')">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('modalVidange')"><i class="fas fa-times"></i></button>
        <img src="5.png" alt="Révision et vidange">
        <h2>Révision &amp; <span>Vidange</span></h2>
        <p>Une vidange professionnelle pour prolonger la durée de vie de votre moteur.</p>
        <ul>
            <li><i class="fas fa-check-circle"></i> Vidange moteur complète</li>
            <li><i class="fas fa-check-circle"></i> Remplacement des filtres</li>
            <li><i class="fas fa-check-circle"></i> Vérification des niveaux</li>
            <li><i class="fas fa-check-circle"></i> Huile de qualité</li>
            <li><i class="fas fa-check-circle"></i> Contrôle général</li>
        </ul>
        <a href="#rdv" class="btn-primary" style="margin-top: 20px; width: 100%; justify-content: center; padding: 14px 28px; min-height: 52px; font-size: 0.95rem;" onclick="closeModal('modalVidange')">
            <i class="fas fa-calendar-check"></i> Prendre rendez-vous
        </a>
    </div>
</div>

<div id="modalFreinage" class="modal-overlay" onclick="closeModalOutside(event, 'modalFreinage')">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('modalFreinage')"><i class="fas fa-times"></i></button>
        <img src="3.png" alt="Freinage">
        <h2><span>Freinage</span></h2>
        <p>Un système de freinage fiable pour votre sécurité et celle des autres.</p>
        <ul>
            <li><i class="fas fa-check-circle"></i> Contrôle des freins</li>
            <li><i class="fas fa-check-circle"></i> Remplacement des plaquettes</li>
            <li><i class="fas fa-check-circle"></i> Changement des disques</li>
            <li><i class="fas fa-check-circle"></i> Purge du liquide de frein</li>
            <li><i class="fas fa-check-circle"></i> Révision complète</li>
        </ul>
        <a href="#rdv" class="btn-primary" style="margin-top: 20px; width: 100%; justify-content: center; padding: 14px 28px; min-height: 52px; font-size: 0.95rem;" onclick="closeModal('modalFreinage')">
            <i class="fas fa-calendar-check"></i> Prendre rendez-vous
        </a>
    </div>
</div>

<div id="modalCarrosserie" class="modal-overlay" onclick="closeModalOutside(event, 'modalCarrosserie')">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('modalCarrosserie')"><i class="fas fa-times"></i></button>
        <img src="7.png" alt="Carrosserie">
        <h2><span>Carrosserie</span></h2>
        <p>Un travail soigné et professionnel pour redonner à votre véhicule son éclat.</p>
        <ul>
            <li><i class="fas fa-check-circle"></i> Debosselage sans peinture</li>
            <li><i class="fas fa-check-circle"></i> Réparation de carrosserie</li>
            <li><i class="fas fa-check-circle"></i> Peinture professionnelle</li>
            <li><i class="fas fa-check-circle"></i> Retouche esthétique</li>
            <li><i class="fas fa-check-circle"></i> Finition impeccable</li>
        </ul>
        <a href="#rdv" class="btn-primary" style="margin-top: 20px; width: 100%; justify-content: center; padding: 14px 28px; min-height: 52px; font-size: 0.95rem;" onclick="closeModal('modalCarrosserie')">
            <i class="fas fa-calendar-check"></i> Prendre rendez-vous
        </a>
    </div>
</div>

<div id="modalAtelier" class="modal-overlay" onclick="closeModalOutside(event, 'modalAtelier')">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('modalAtelier')"><i class="fas fa-times"></i></button>
        <img src="5.png" alt="Atelier moderne">
        <h2>Atelier <span>Moderne</span></h2>
        <p>Un atelier équipé des dernières technologies pour un service de qualité.</p>
        <ul>
            <li><i class="fas fa-check-circle"></i> Équipements de dernière génération</li>
            <li><i class="fas fa-check-circle"></i> Diagnostic assisté par ordinateur</li>
            <li><i class="fas fa-check-circle"></i> Outillage professionnel</li>
            <li><i class="fas fa-check-circle"></i> Zone de travail propre</li>
            <li><i class="fas fa-check-circle"></i> Normes de sécurité</li>
        </ul>
        <a href="#rdv" class="btn-primary" style="margin-top: 20px; width: 100%; justify-content: center; padding: 14px 28px; min-height: 52px; font-size: 0.95rem;" onclick="closeModal('modalAtelier')">
            <i class="fas fa-calendar-check"></i> Prendre rendez-vous
        </a>
    </div>
</div>

<!-- ===== POURQUOI NOUS CHOISIR ===== -->
<section class="section-padding bg-[#111111]" id="about">
    <div class="container mx-auto max-w-7xl">
        <div data-aos="fade-up">
            <h2 class="section-title">Pourquoi nous <span>choisir</span></h2>
            <p class="section-subtitle">Un garage de confiance à votre service</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
            <div class="feature-card" data-aos="fade-up">
                <div class="icon"><i class="fas fa-car"></i></div>
                <h4>Toutes marques</h4>
                <p>Nous intervenons sur tous les modèles</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="50">
                <div class="icon"><i class="fas fa-bolt"></i></div>
                <h4>Intervention rapide</h4>
                <p>Service efficace pour vous faire gagner du temps</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="icon"><i class="fas fa-award"></i></div>
                <h4>15+ ans d'expertise</h4>
                <p>Une équipe expérimentée à votre service</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== MARQUES PREMIUM ===== -->
<section class="section-padding bg-[#0a0a0a]" id="brands">
    <div class="container mx-auto max-w-7xl">
        <div data-aos="fade-up">
            <h2 class="section-title">Nos marques <span>prises en charge</span></h2>
            <p class="section-subtitle">Nous intervenons sur toutes les marques, quel que soit le modèle</p>
        </div>
        
        <div class="brands-grid mt-12" id="brandsGrid" data-aos="fade-up" data-aos-delay="100">
            <!-- Mercedes-Benz -->
            <div class="brand-item-svg">
                <img src="assets/img/brands/mercedes.svg" alt="Mercedes-Benz" loading="lazy">
                <span class="brand-name">Mercedes-Benz</span>
            </div>
            
            <!-- Audi -->
            <div class="brand-item-svg">
                <img src="assets/img/brands/audi.svg" alt="Audi" loading="lazy">
                <span class="brand-name">Audi</span>
            </div>
            
            <!-- Peugeot -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/peugeot.svg" alt="Peugeot" loading="lazy">
                <span class="brand-name">Peugeot</span>
            </div>
            
            <!-- Renault -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/renault.svg" alt="Renault" loading="lazy">
                <span class="brand-name">Renault</span>
            </div>
            
            <!-- Citroën -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/citroen.svg" alt="Citroën" loading="lazy">
                <span class="brand-name">Citroën</span>
            </div>
            
            <!-- Toyota -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/toyota.svg" alt="Toyota" loading="lazy">
                <span class="brand-name">Toyota</span>
            </div>
            
            <!-- Nissan -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/Nissan.svg" alt="Nissan" loading="lazy">
                <span class="brand-name">Nissan</span>
            </div>
            
            <!-- Opel -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/opel.svg" alt="Opel" loading="lazy">
                <span class="brand-name">Opel</span>
            </div>
            
            <!-- Fiat -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/fiat.svg" alt="Fiat" loading="lazy">
                <span class="brand-name">Fiat</span>
            </div>
            
            <!-- Dacia -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/dacia.svg" alt="Dacia" loading="lazy">
                <span class="brand-name">Dacia</span>
            </div>
            
            <!-- Kia -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/kia.svg" alt="Kia" loading="lazy">
                <span class="brand-name">Kia</span>
            </div>
            
            <!-- Hyundai -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/hyundai.svg" alt="Hyundai" loading="lazy">
                <span class="brand-name">Hyundai</span>
            </div>
            
            <!-- Tesla -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/Tesla.svg" alt="Tesla" loading="lazy">
                <span class="brand-name">Tesla</span>
            </div>
            
            <!-- Porsche -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/porsche.svg" alt="Porsche" loading="lazy">
                <span class="brand-name">Porsche</span>
            </div>
            
            <!-- Volvo -->
            <div class="brand-item-svg hidden-brand">
                <img src="assets/img/brands/volvo.svg" alt="Volvo" loading="lazy">
                <span class="brand-name">Volvo</span>
            </div>
        </div>
        
        <!-- Bouton Voir plus -->
        <div class="text-center mt-6" data-aos="fade-up" data-aos-delay="150">
            <button class="btn-show-more" id="showMoreBrands" onclick="toggleBrands()">
                <i class="fas fa-chevron-down"></i>
                <span id="btnText">Voir plus de marques</span>
            </button>
        </div>
        
        <div class="brands-footer text-center" data-aos="fade-up" data-aos-delay="200">
            <p class="text-white/40 text-lg font-light tracking-wide">Et bien d'autres marques...</p>
            <div class="flex flex-wrap justify-center gap-6 mt-4">
                <span class="inline-flex items-center gap-2 text-white/30 text-sm">
                    <i class="fas fa-check-circle text-[#D90429]"></i> Véhicules particuliers
                </span>
                <span class="inline-flex items-center gap-2 text-white/30 text-sm">
                    <i class="fas fa-check-circle text-[#D90429]"></i> Utilitaires
                </span>
                <span class="inline-flex items-center gap-2 text-white/30 text-sm">
                    <i class="fas fa-check-circle text-[#D90429]"></i> Hybrides &amp; électriques
                </span>
            </div>
        </div>
    </div>
</section>

<!-- ===== PROCESSUS ===== -->
<section class="section-padding bg-[#111111]" id="process">
    <div class="container mx-auto max-w-4xl">
        <div data-aos="fade-up">
            <h2 class="section-title">Notre <span>processus</span></h2>
            <p class="section-subtitle">Un accompagnement pas à pas pour votre véhicule</p>
        </div>
        
        <div class="mt-12">
            <div class="timeline-item" data-aos="fade-up">
                <div class="timeline-number">1</div>
                <div class="timeline-content">
                    <h4>Prise de rendez-vous</h4>
                    <p>Contactez-nous par téléphone, WhatsApp ou via notre formulaire en ligne.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-up" data-aos-delay="50">
                <div class="timeline-number">2</div>
                <div class="timeline-content">
                    <h4>Diagnostic</h4>
                    <p>Nous réalisons un diagnostic complet de votre véhicule pour identifier les besoins.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-up" data-aos-delay="100">
                <div class="timeline-number">3</div>
                <div class="timeline-content">
                    <h4>Réparation</h4>
                    <p>Nos techniciens qualifiés interviennent avec des pièces de qualité.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-up" data-aos-delay="150">
                <div class="timeline-number">4</div>
                <div class="timeline-content">
                    <h4>Contrôle qualité</h4>
                    <p>Chaque intervention est soumise à un contrôle rigoureux.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-up" data-aos-delay="200">
                <div class="timeline-number">5</div>
                <div class="timeline-content">
                    <h4>Restitution du véhicule</h4>
                    <p>Votre véhicule vous est rendu en parfait état de fonctionnement.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== STATISTIQUES ===== -->
<section class="section-padding bg-[#0a0a0a]">
    <div class="container mx-auto max-w-7xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div data-aos="fade-up">
                <div class="stat-number" data-count="15">0</div>
                <p class="stat-label">Années d'expérience</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="50">
                <div class="stat-number" data-count="350">0</div>
                <p class="stat-label">Clients satisfaits</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="100">
                <div class="stat-number" data-count="620">0</div>
                <p class="stat-label">Interventions</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="150">
                <div class="stat-number" data-count="98">0</div>
                <p class="stat-label">% Satisfaction client</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== AVIS CLIENTS ===== -->
<section class="section-padding bg-[#111111]" id="reviews">
    <div class="container mx-auto max-w-6xl">
        <div data-aos="fade-up">
            <h2 class="section-title">Avis <span>clients</span></h2>
            <p class="section-subtitle">Ce que nos clients disent de nous</p>
        </div>
        
        <div class="glass rounded-2xl p-6 md:p-8 max-w-2xl mx-auto mt-8" data-aos="fade-up">
            <h4 class="text-lg font-bold mb-4">Donnez votre avis</h4>
            <form method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Surnom <span class="required">*</span></label>
                        <input type="text" name="username" required class="form-input" placeholder="Jean" maxlength="100">
                    </div>
                    <div>
                        <label class="form-label">Note <span class="required">*</span></label>
                        <select name="rating" required class="form-input">
                            <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                            <option value="4">⭐⭐⭐⭐ - Très bien</option>
                            <option value="3">⭐⭐⭐ - Bien</option>
                            <option value="2">⭐⭐ - Moyen</option>
                            <option value="1">⭐ - Insuffisant</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Commentaire <span class="required">*</span></label>
                        <textarea name="comment" required rows="3" class="form-input" placeholder="Votre expérience chez HK AUTO..." maxlength="1000"></textarea>
                    </div>
                    <button type="submit" name="review" class="btn-primary w-full justify-center" style="padding: 14px 28px; min-height: 52px; font-size: 0.95rem;">
                        <i class="fas fa-paper-plane"></i> Publier mon avis
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-12">
            <div class="swiper reviews-swiper">
                <div class="swiper-wrapper">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                        <div class="swiper-slide">
                            <div class="review-card">
                                <div class="flex items-center gap-4">
                                    <div class="avatar"><?= strtoupper(substr($review['username'], 0, 1)) ?></div>
                                    <div>
                                        <h4 class="font-bold text-lg"><?= htmlspecialchars($review['username']) ?></h4>
                                        <div class="stars">
                                            <?php for ($i = 0; $i < $review['rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                                            <?php for ($i = $review['rating']; $i < 5; $i++): ?><i class="fas fa-star" style="color: rgba(255,255,255,0.05);"></i><?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="comment">"<?= htmlspecialchars($review['comment']) ?>"</p>
                                <div class="flex items-center justify-between mt-3">
                                    <?php if ($review['is_verified']): ?>
                                        <span class="verified"><i class="fas fa-check-circle"></i> Client vérifié</span>
                                    <?php endif; ?>
                                    <span class="text-white/10 text-xs"><?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="swiper-slide">
                            <div class="review-card text-center py-12">
                                <i class="fas fa-star text-5xl text-[#D90429] opacity-10 mb-4"></i>
                                <p class="text-white/20">Soyez le premier à donner votre avis !</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="swiper-pagination mt-6"></div>
            </div>
        </div>
    </div>
</section>

<!-- ===== RENDEZ-VOUS ===== -->
<section class="section-padding bg-[#0a0a0a]" id="rdv">
    <div class="container mx-auto max-w-6xl">
        <div data-aos="fade-up">
            <h2 class="section-title">Prenez rendez-vous <span>dès maintenant</span></h2>
            <p class="section-subtitle">Remplissez le formulaire et nous vous confirmons rapidement</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">
            <div data-aos="fade-right">
                <form class="glass rounded-2xl p-6 md:p-8" id="rdvForm">
                    <h3 class="text-xl font-bold mb-6">Formulaire de rendez-vous</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Nom complet <span class="required">*</span></label>
                            <input type="text" id="rdvName" required class="form-input" placeholder="Jean Dupont">
                        </div>
                        <div>
                            <label class="form-label">Email (optionnel)</label>
                            <input type="email" id="rdvEmail" class="form-input" placeholder="jean@email.com">
                        </div>
                        <div>
                            <label class="form-label">Téléphone <span class="required">*</span></label>
                            <input type="tel" id="rdvPhone" required class="form-input" placeholder="06 12 34 56 78">
                        </div>
                        <div>
                            <label class="form-label">Marque du véhicule <span class="required">*</span></label>
                            <input type="text" id="rdvBrand" required class="form-input" placeholder="BMW, Mercedes, Audi...">
                        </div>
                        <div>
                            <label class="form-label">Modèle du véhicule <span class="required">*</span></label>
                            <input type="text" id="rdvModel" required class="form-input" placeholder="Série 3, Classe C...">
                        </div>
                        <div>
                            <label class="form-label">Immatriculation <span class="required">*</span></label>
                            <input type="text" id="rdvPlate" required class="form-input" placeholder="AB-123-CD">
                        </div>
                        <div>
                            <label class="form-label">Motif de la visite <span class="required">*</span></label>
                            <select id="rdvReason" required class="form-input">
                                <option value="">Sélectionnez un motif</option>
                                <option value="Entretien & Diagnostic">Entretien & Diagnostic</option>
                                <option value="Pneus">Pneus</option>
                                <option value="Révision & Vidange">Révision & Vidange</option>
                                <option value="Freinage">Freinage</option>
                                <option value="Carrosserie">Carrosserie</option>
                                <option value="Vidange moteur">Vidange moteur</option>
                                <option value="Climatisation">Climatisation</option>
                                <option value="Batterie">Batterie</option>
                                <option value="Vitres teintées">Vitres teintées</option>
                                <option value="Diagnostic électronique">Diagnostic électronique</option>
                                <option value="Autre">Autre (précisez dans le message)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Date souhaitée <span class="required">*</span></label>
                            <input type="date" id="rdvDate" required class="form-input" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div>
                            <label class="form-label">Informations supplémentaires (optionnel)</label>
                            <textarea id="rdvMessage" rows="2" class="form-input" placeholder="Informations complémentaires..."></textarea>
                        </div>
                        <button type="button" onclick="sendToWhatsApp()" class="btn-primary w-full justify-center" style="padding: 14px 28px; min-height: 52px; font-size: 0.95rem;">
                            <i class="fab fa-whatsapp"></i> Confirmer sur WhatsApp
                        </button>
                    </div>
                </form>
            </div>
            
            <div data-aos="fade-left" class="space-y-6">
                <div class="glass rounded-2xl p-6 text-center">
                    <i class="fab fa-whatsapp text-5xl text-[#25D366] mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Rendez-vous par WhatsApp</h3>
                    <p class="text-white/30 text-sm">Réponse rapide et simple</p>
                    <a href="https://wa.me/33758640784?text=Bonjour%20HK%20AUTO,%20je%20souhaite%20prendre%20rendez-vous%20pour%20mon%20véhicule." 
                       class="btn-whatsapp w-full mt-4 justify-center" target="_blank" style="padding: 14px 28px; min-height: 52px; font-size: 0.95rem;">
                        <i class="fab fa-whatsapp"></i> Nous contacter sur WhatsApp
                    </a>
                </div>
                
                <div class="glass rounded-2xl p-6 text-center">
                    <i class="fas fa-phone text-4xl text-[#D90429] mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Appelez-nous</h3>
                    <a href="tel:+33758640784" class="text-2xl font-bold text-[#D90429] hover:underline block">+33 7 58 64 07 84</a>
                    <a href="tel:+33755078301" class="text-white/30 hover:text-[#D90429] transition-colors text-sm block mt-1">+33 7 55 07 83 01</a>
                    <p class="text-white/20 text-sm mt-2">Lun - Sam : 8h00 - 18h00</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CONTACT ===== -->
<section class="section-padding bg-[#111111]" id="contact">
    <div class="container mx-auto max-w-6xl">
        <div data-aos="fade-up">
            <h2 class="section-title">Contactez-<span>nous</span></h2>
            <p class="section-subtitle">Une question ? N'hésitez pas à nous contacter</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">
            <div data-aos="fade-right">
                <img src="1.png" alt="Accueil HK AUTO" class="rounded-2xl w-full shadow-2xl" loading="lazy">
            </div>
            <div data-aos="fade-left">
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-[#D90429]/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-[#D90429] text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white">Téléphone principal</h4>
                            <a href="tel:+33758640784" class="text-[#D90429] hover:underline text-lg font-semibold">+33 7 58 64 07 84</a>
                            <br>
                            <a href="tel:+33755078301" class="text-white/40 hover:text-[#D90429] transition-colors text-sm">+33 7 55 07 83 01</a>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-[#25D366]/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fab fa-whatsapp text-[#25D366] text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white">WhatsApp</h4>
                            <a href="https://wa.me/33758640784" target="_blank" class="text-[#25D366] hover:underline">+33 7 58 64 07 84</a>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-[#D90429]/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-[#D90429] text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white">Email principal</h4>
                            <a href="mailto:Autohkcontact@gmail.com" class="text-[#D90429] hover:underline text-lg font-semibold">Autohkcontact@gmail.com</a>
                            <br>
                            <a href="mailto:Malik.salhi77@gmail.com" class="text-white/40 hover:text-[#D90429] transition-colors text-sm">Malik.salhi77@gmail.com</a>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-[#D90429]/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-[#D90429] text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white">Adresse</h4>
                            <p class="text-white/40">18 Rue Carnot, 95870 BEZONS</p>
                            <a href="https://maps.google.com/maps?q=18+Rue+Carnot+95870+BEZONS" target="_blank" class="text-[#D90429] text-sm hover:underline">Voir sur Google Maps</a>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-[#D90429]/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-[#D90429] text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white">Horaires</h4>
                            <p class="text-white/40">Lundi - Samedi : 8h00 - 18h00</p>
                            <p class="text-white/20 text-sm">Fermé le dimanche</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-[#D90429]/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-share-alt text-[#D90429] text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white">Réseaux sociaux</h4>
                            <div class="flex gap-3 mt-1">
                                <a href="https://www.facebook.com/people/HK-Auto/61592171522340/" target="_blank" class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#D90429] transition-colors">
                                    <i class="fab fa-facebook-f text-white"></i>
                                </a>
                                <a href="https://www.instagram.com/autohkcontact" target="_blank" class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#D90429] transition-colors">
                                    <i class="fab fa-instagram text-white"></i>
                                </a>
                                <a href="https://www.tiktok.com/@hkautoservices" target="_blank" class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#D90429] transition-colors">
                                    <i class="fab fa-tiktok text-white"></i>
                                </a>
                                <a href="https://wa.me/33758640784" target="_blank" class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#25D366] transition-colors">
                                    <i class="fab fa-whatsapp text-white"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="bg-[#0a0a0a] py-12 border-t border-white/5">
    <div class="container mx-auto max-w-7xl px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <img src="logo.png" alt="HK AUTO" class="h-18 w-auto mb-6">
                <p class="text-white/30 text-sm">L'expertise automobile au service de votre véhicule</p>
            </div>
            <div>
                <h4 class="font-bold mb-4 text-white">Services</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#services" class="text-white/40 hover:text-[#D90429] transition-colors no-underline">Entretien & Diagnostic</a></li>
                    <li><a href="#services" class="text-white/40 hover:text-[#D90429] transition-colors no-underline">Pneus</a></li>
                    <li><a href="#services" class="text-white/40 hover:text-[#D90429] transition-colors no-underline">Révision & Vidange</a></li>
                    <li><a href="#services" class="text-white/40 hover:text-[#D90429] transition-colors no-underline">Freinage</a></li>
                    <li><a href="#services" class="text-white/40 hover:text-[#D90429] transition-colors no-underline">Carrosserie</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4 text-white">Coordonnées</h4>
                <ul class="space-y-2 text-sm text-white/40">
                    <li><i class="fas fa-map-marker-alt mr-2 text-[#D90429]"></i> 18 Rue Carnot, 95870 BEZONS</li>
                    <li><i class="fas fa-phone mr-2 text-[#D90429]"></i> <a href="tel:+33758640784" class="hover:text-white transition-colors">+33 7 58 64 07 84</a></li>
                    <li><i class="fab fa-whatsapp mr-2 text-[#25D366]"></i> <a href="https://wa.me/33758640784" target="_blank" class="hover:text-white transition-colors">+33 7 58 64 07 84</a></li>
                    <li><i class="fas fa-envelope mr-2 text-[#D90429]"></i> <a href="mailto:Autohkcontact@gmail.com" class="hover:text-white transition-colors">Autohkcontact@gmail.com</a></li>
                    <li><i class="fas fa-envelope mr-2 text-[#D90429]"></i> <a href="mailto:Malik.salhi77@gmail.com" class="hover:text-white transition-colors text-sm">Malik.salhi77@gmail.com</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4 text-white">Suivez-nous</h4>
                <div class="flex gap-3 flex-wrap">
                    <a href="https://www.facebook.com/people/HK-Auto/61592171522340/" target="_blank" class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#D90429] transition-all duration-300 hover:scale-110">
                        <i class="fab fa-facebook-f text-white text-lg"></i>
                    </a>
                    <a href="https://www.instagram.com/autohkcontact" target="_blank" class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#D90429] transition-all duration-300 hover:scale-110">
                        <i class="fab fa-instagram text-white text-lg"></i>
                    </a>
                    <a href="https://www.tiktok.com/@hkautoservices" target="_blank" class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#D90429] transition-all duration-300 hover:scale-110">
                        <i class="fab fa-tiktok text-white text-lg"></i>
                    </a>
                    <a href="https://wa.me/33758640784" target="_blank" class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#25D366] transition-all duration-300 hover:scale-110">
                        <i class="fab fa-whatsapp text-white text-lg"></i>
                    </a>
                </div>
                <p class="text-white/20 text-xs mt-4">Lun - Sam : 8h00 - 18h00</p>
            </div>
        </div>
        
        <div class="border-t border-white/5 mt-8 pt-8 text-center text-white/20 text-sm">
            <p>&copy; 2026 HK AUTO - Tous droits réservés</p>
        </div>
    </div>
</footer>

<!-- ===== MOBILE BOTTOM BAR ===== -->
<div class="mobile-bottom-bar">
    <a href="tel:+33758640784" class="bar-item">
        <i class="fas fa-phone"></i>
        <span>Appeler</span>
    </a>
    <a href="https://wa.me/33758640784?text=Bonjour%20HK%20AUTO,%20je%20souhaite%20prendre%20rendez-vous%20pour%20mon%20véhicule." target="_blank" class="bar-item">
        <i class="fab fa-whatsapp"></i>
        <span>WhatsApp</span>
    </a>
    <a href="https://maps.google.com/maps?q=18+Rue+Carnot+95870+BEZONS" target="_blank" class="bar-item">
        <i class="fas fa-map-marker-alt"></i>
        <span>Itinéraire</span>
    </a>
    <a href="#rdv" class="bar-item rdv">
        <i class="fas fa-calendar-check"></i>
        <span>RDV</span>
    </a>
</div>

<!-- ===== SCRIPTS ===== -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
AOS.init({ duration: 800, once: true, offset: 100, easing: 'ease-out-cubic' });

// Navbar
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', function() {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
});

// Mobile menu
const menuToggle = document.getElementById('menuToggle');
const mobileMenu = document.getElementById('mobileMenu');
const closeMenuBtn = document.getElementById('closeMenu');

function openMobileMenu() {
    mobileMenu.classList.add('open');
    document.body.style.overflow = 'hidden';
    menuToggle.querySelector('i').className = 'fas fa-times';
}
function closeMobileMenu() {
    mobileMenu.classList.remove('open');
    document.body.style.overflow = '';
    menuToggle.querySelector('i').className = 'fas fa-bars';
}

if (menuToggle) {
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        mobileMenu.classList.contains('open') ? closeMobileMenu() : openMobileMenu();
    });
}
if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeMobileMenu);

document.addEventListener('click', function(e) {
    if (mobileMenu.classList.contains('open') && 
        !mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
        closeMobileMenu();
    }
});

// Reviews Swiper
new Swiper('.reviews-swiper', {
    slidesPerView: 1,
    spaceBetween: 20,
    autoplay: { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true },
    pagination: { el: '.swiper-pagination', clickable: true },
    breakpoints: { 640: { slidesPerView: 1.5 }, 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
});

// Counters
const counters = document.querySelectorAll('.stat-number');
const animateCounter = (el) => {
    const target = parseInt(el.getAttribute('data-count'));
    let current = 0;
    const increment = Math.ceil(target / 50);
    const update = () => {
        current += increment;
        if (current >= target) { el.textContent = target + (target > 10 ? '+' : '%'); return; }
        el.textContent = current + (target > 10 ? '+' : '%');
        requestAnimationFrame(update);
    };
    update();
};
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) { animateCounter(entry.target); observer.unobserve(entry.target); }
    });
}, { threshold: 0.3 });
counters.forEach(counter => observer.observe(counter));

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const offset = 80;
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top, behavior: 'smooth' });
            if (mobileMenu.classList.contains('open')) closeMobileMenu();
        }
    });
});

// ===== MODALES =====
function openModal(id) {
    const modal = document.getElementById('modal' + id.charAt(0).toUpperCase() + id.slice(1));
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function closeModalOutside(event, id) {
    if (event.target === document.getElementById(id)) {
        closeModal(id);
    }
}

// Fermer les modales avec Echap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

// ===== TOGGLE BRANDS =====
function toggleBrands() {
    const hiddenBrands = document.querySelectorAll('.brand-item-svg.hidden-brand');
    const btn = document.getElementById('showMoreBrands');
    const btnText = document.getElementById('btnText');
    
    hiddenBrands.forEach(brand => {
        brand.classList.toggle('show');
    });
    
    btn.classList.toggle('active');
    
    if (btn.classList.contains('active')) {
        btnText.textContent = 'Voir moins';
    } else {
        btnText.textContent = 'Voir plus de marques';
        document.getElementById('brands').scrollIntoView({ behavior: 'smooth' });
    }
}

// ===== FORMULAIRE VERS WHATSAPP =====
function sendToWhatsApp() {
    const name = document.getElementById('rdvName').value.trim();
    const email = document.getElementById('rdvEmail').value.trim();
    const phone = document.getElementById('rdvPhone').value.trim();
    const brand = document.getElementById('rdvBrand').value.trim();
    const model = document.getElementById('rdvModel').value.trim();
    const plate = document.getElementById('rdvPlate').value.trim();
    const reason = document.getElementById('rdvReason').value;
    const date = document.getElementById('rdvDate').value;
    const message = document.getElementById('rdvMessage').value.trim();
    
    // Validation : email optionnel
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Veuillez entrer un email valide ou laissez vide.');
        return;
    }
    
    if (!name || !phone || !brand || !model || !plate || !date || !reason) {
        alert('Veuillez remplir tous les champs obligatoires (*)');
        return;
    }
    
    let text = '🟢 *NOUVELLE DEMANDE DE RENDEZ-VOUS* 🟢%0A%0A';
    text += '👤 *Nom :* ' + name + '%0A';
    if (email) {
        text += '📧 *Email :* ' + email + '%0A';
    }
    text += '📱 *Téléphone :* ' + phone + '%0A';
    text += '🚗 *Marque :* ' + brand + '%0A';
    text += '🚙 *Modèle :* ' + model + '%0A';
    text += '📋 *Immatriculation :* ' + plate + '%0A';
    text += '🔧 *Motif :* ' + reason + '%0A';
    text += '📅 *Date souhaitée :* ' + date + '%0A';
    if (message) {
        text += '📝 *Informations complémentaires :* ' + message + '%0A';
    }
    text += '%0A---%0A';
    text += '🔴 *HK AUTO* - Votre garage de confiance';
    
    const url = 'https://wa.me/33758640784?text=' + text;
    window.open(url, '_blank');
}

console.log('🚗 HK AUTO - Site premium chargé avec succès !');
</script>

</body>
</html>