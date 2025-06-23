<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hệ thống giám sát DoS/DDoS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --navbar-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --card-bg: rgba(255, 255, 255, 0.95);
            --log-bg: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --border-radius: 15px;
            --shadow: 0 8px 32px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
        }

        body { 
            background: var(--primary-gradient);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .card { 
            box-shadow: var(--shadow); 
            border: none; 
            backdrop-filter: blur(10px);
            background: var(--card-bg);
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .stat-icon { 
            font-size: clamp(2rem, 4vw, 3rem); 
            margin-right: 15px; 
            opacity: 0.8;
        }

        .badge-danger { background: linear-gradient(45deg, #e74c3c, #c0392b); }
        .badge-warning { background: linear-gradient(45deg, #f39c12, #e67e22); }
        .badge-success { background: linear-gradient(45deg, #27ae60, #2ecc71); }
        .badge-primary { background: linear-gradient(45deg, #3498db, #2980b9); }
        
        #log-container { 
            height: clamp(300px, 50vh, 400px); 
            overflow-y: auto; 
            background: var(--log-bg);
            color: #ecf0f1; 
            padding: 15px; 
            font-family: 'Fira Code', 'Courier New', monospace; 
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            font-size: clamp(0.8rem, 2vw, 0.9rem);
        }

        .log-entry { 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
            padding: 5px 0; 
            font-size: clamp(0.75rem, 2vw, 0.9rem);
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .log-entry:hover {
            background: rgba(255,255,255,0.05);
            border-radius: 5px;
            padding-left: 5px;
        }

        .ip-badge { 
            font-size: clamp(0.8rem, 2.5vw, 1rem); 
            font-weight: bold;
        }
        
        .navbar {
            background: var(--navbar-gradient) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: clamp(1rem, 3vw, 1.25rem);
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        .status-online { background: #27ae60; }
        .status-warning { background: #f39c12; }
        .status-danger { background: #e74c3c; }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .metric-card {
            transition: var(--transition);
            min-height: 120px;
        }

        .metric-card .card-body {
            padding: clamp(1rem, 3vw, 1.5rem);
        }

        .metric-card .fs-2 {
            font-size: clamp(1.5rem, 4vw, 2rem) !important;
        }

        .metric-card .fw-bold {
            font-size: clamp(0.8rem, 2.5vw, 1rem);
        }
        
        .alert-banner {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }
        
        .top-ips-card {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .method-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: clamp(0.7rem, 2vw, 0.8rem);
            font-weight: bold;
        }
        
        .method-get { background: #3498db; color: white; }
        .method-post { background: #27ae60; color: white; }
        .method-head { background: #f39c12; color: white; }
        .method-put { background: #9b59b6; color: white; }
        .method-delete { background: #e74c3c; color: white; }
        .method-options { background: #7f8c8d; color: white; }
        .method-patch { background: #3d5afe; color: white; }
        .method-unknown { background: #ced4da; color: #212529; }

        .method-badge-block {
            padding: clamp(6px, 2vw, 8px) clamp(8px, 2.5vw, 12px);
            border-radius: 6px;
            font-size: clamp(0.7rem, 2vw, 0.9rem);
            font-weight: bold;
            color: white;
            text-align: center;
            word-wrap: break-word;
        }

        .btn-group .btn {
            font-size: clamp(0.7rem, 2vw, 0.875rem);
            padding: clamp(0.25rem, 1vw, 0.375rem) clamp(0.5rem, 2vw, 0.75rem);
        }

        .card-title {
            font-size: clamp(1rem, 3vw, 1.25rem);
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .container {
                padding: 10px;
            }
            
            .row.g-4 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }
            
            .metric-card .card-body {
                padding: 1rem;
                text-align: center;
            }
            
            .stat-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
            
            #log-container {
                height: 250px;
                font-size: 0.75rem;
            }
            
            .method-badge-block {
                font-size: 0.7rem;
                padding: 6px 8px;
            }
        }

        @media (max-width: 768px) {
            .col-md-3 {
                margin-bottom: 1rem;
            }
            
            .metric-card {
                min-height: 100px;
            }
            
            .btn-group {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-group .btn {
                border-radius: 0.375rem !important;
                margin-bottom: 0.25rem;
            }
        }

        @media (max-width: 992px) {
            .col-md-8, .col-md-4 {
                margin-bottom: 1.5rem;
            }
        }

        /* Landscape orientation */
        @media (max-height: 500px) and (orientation: landscape) {
            .navbar {
                padding: 0.5rem 1rem;
            }
            
            .container {
                margin-top: 1rem !important;
            }
            
            .metric-card {
                min-height: 80px;
            }
            
            #log-container {
                height: 200px;
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .card {
                box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            }
        }

        /* Reduced motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            
            .metric-card:hover {
                transform: none;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --card-bg: rgba(44, 62, 80, 0.95);
            }
        }

        /* Print styles */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            
            .card {
                background: white !important;
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }
            
            .btn-group {
                display: none !important;
            }
            
            #log-container {
                background: #f8f9fa !important;
                color: black !important;
                border: 1px solid #ccc !important;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-lg">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-shield-lock me-2"></i> 
                Hệ thống giám sát DoS/DDoS
                <span class="status-indicator status-online" id="ws-status"></span>
            </span>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text">
                    <i class="bi bi-clock me-1"></i>
                    <span id="current-time"></span>
                </span>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Alert Banner -->
        <div class="alert-banner" id="alert-banner">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Phát hiện tấn công DoS/DDoS!</strong> Hệ thống đang bị tấn công với tần suất cao.
        </div>

        <!-- Thống kê chính -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="card metric-card text-bg-primary">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-bar-chart stat-icon"></i>
                        <div>
                            <div class="fw-bold">Tổng request</div>
                            <div class="fs-2" id="total-requests">0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card metric-card text-bg-success">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-activity stat-icon"></i>
                        <div>
                            <div class="fw-bold">Request/giây</div>
                            <div class="fs-2" id="requests-per-second">0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card metric-card text-bg-warning">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle stat-icon"></i>
                        <div>
                            <div class="fw-bold">IP đáng ngờ</div>
                            <div class="fs-2" id="suspicious-count">0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card metric-card text-bg-info">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-clock-history stat-icon"></i>
                        <div>
                            <div class="fw-bold">Thời gian hoạt động</div>
                            <div class="fs-2" id="uptime">00:00:00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ và thống kê -->
        <div class="row g-4">
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up me-2"></i> 
                            Request theo thời gian
                        </h5>
                        <div style="position: relative; height: 300px;">
                            <canvas id="requests-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-list-ul me-2"></i> 
                            HTTP Methods
                        </h5>
                        <div id="method-stats-body" class="mt-3">
                            <!-- Dữ liệu sẽ được cập nhật bằng JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IP đáng ngờ và Top IPs -->
        <div class="row g-4 mt-4">
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-exclamation-triangle me-2"></i> 
                            IP đáng ngờ
                        </h5>
                        <div id="suspicious-ips" class="list-group">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-trophy me-2"></i> 
                            Top IPs (Request cao nhất)
                        </h5>
                        <div id="top-ips" class="top-ips-card">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Realtime -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-terminal me-2"></i> 
                                Log Realtime
                            </h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearLogs()">
                                    <i class="bi bi-trash me-1"></i>Xóa log
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAutoScroll()">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Auto-scroll
                                </button>
                            </div>
                        </div>
                        <div id="log-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/monitor.js?v=<?php echo time(); ?>"></script>
    <script>
    // Cập nhật thời gian
    function updateTime() {
        const now = new Date();
        document.getElementById('current-time').textContent = now.toLocaleTimeString('vi-VN');
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Cập nhật uptime
    let startTime = Date.now();
    function updateUptime() {
        const elapsed = Date.now() - startTime;
        const hours = Math.floor(elapsed / 3600000);
        const minutes = Math.floor((elapsed % 3600000) / 60000);
        const seconds = Math.floor((elapsed % 60000) / 1000);
        document.getElementById('uptime').textContent = 
            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    setInterval(updateUptime, 1000);

    // Gradient cho biểu đồ
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('requests-chart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.7)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0.1)');
        if(window.requestsChart) {
            window.requestsChart.data.datasets[0].backgroundColor = gradient;
            window.requestsChart.update();
        }
    });

    // Các hàm tiện ích
    let autoScroll = true;
    function toggleAutoScroll() {
        autoScroll = !autoScroll;
        const btn = event.target.closest('button');
        if (autoScroll) {
            btn.innerHTML = '<i class="bi bi-arrow-down-circle me-1"></i>Auto-scroll';
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-outline-secondary');
        } else {
            btn.innerHTML = '<i class="bi bi-arrow-down-circle-fill me-1"></i>Auto-scroll';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-secondary');
        }
    }

    function clearLogs() {
        document.getElementById('log-container').innerHTML = '';
    }

    // Responsive chart
    window.addEventListener('resize', function() {
        if(window.requestsChart) {
            window.requestsChart.resize();
        }
    });
    </script>
</body>
</html> 