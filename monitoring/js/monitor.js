// Khởi tạo biểu đồ với responsive options
const ctx = document.getElementById('requests-chart').getContext('2d');
const requestsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Requests/giây',
            data: [],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 1
            }
        },
        scales: {
            x: {
                display: true,
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: '#666',
                    maxTicksLimit: 10
                }
            },
            y: {
                display: true,
                beginAtZero: true,
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: '#666'
                }
            }
        },
        elements: {
            point: {
                radius: 3,
                hoverRadius: 5
            }
        }
    }
});

// Lưu chart vào window để có thể truy cập từ HTML
window.requestsChart = requestsChart;

// Hàm cập nhật log với responsive design
function updateLog(log) {
    const logContainer = document.getElementById('log-container');
    const logEntry = document.createElement('div');
    logEntry.className = 'log-entry';
    logEntry.textContent = log;
    
    // Thêm timestamp nếu cần
    const timestamp = new Date().toLocaleTimeString('vi-VN');
    logEntry.setAttribute('data-timestamp', timestamp);
    
    logContainer.appendChild(logEntry);
    
    // Auto-scroll chỉ khi được bật
    if (window.autoScroll !== false) {
        logContainer.scrollTop = logContainer.scrollHeight;
    }
    
    // Giới hạn số lượng log entries để tránh memory leak
    const maxLogs = 1000;
    while (logContainer.children.length > maxLogs) {
        logContainer.removeChild(logContainer.firstChild);
    }
}

// Hàm cập nhật thống kê với animation
function updateStats(stats) {
    const totalElement = document.getElementById('total-requests');
    const rpsElement = document.getElementById('requests-per-second');
    
    // Animate số thay đổi
    animateNumber(totalElement, parseInt(totalElement.textContent), stats.totalRequests);
    animateNumber(rpsElement, parseInt(rpsElement.textContent), stats.requestsPerSecond);
}

// Hàm animate số
function animateNumber(element, start, end) {
    const duration = 500;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = Math.floor(start + (end - start) * progress);
        element.textContent = current.toLocaleString('vi-VN');
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Hàm cập nhật danh sách IP đáng ngờ với responsive design
function updateSuspiciousIPs(ips) {
    const container = document.getElementById('suspicious-ips');
    container.innerHTML = '';
    
    if (ips.length === 0) {
        container.innerHTML = '<div class="text-muted text-center py-3">Không có IP đáng ngờ</div>';
        return;
    }
    
    ips.forEach((ip, index) => {
        const item = document.createElement('div');
        item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
        item.innerHTML = `
            <div class="d-flex flex-column flex-md-row align-items-md-center w-100">
                <div class="mb-1 mb-md-0">
                    <h6 class="mb-1 text-break">${ip.address}</h6>
                    <small class="text-danger">${ip.reason}</small>
                </div>
                <div class="ms-md-auto">
                    <span class="badge bg-danger rounded-pill">${ip.requests} requests</span>
                </div>
            </div>
        `;
        container.appendChild(item);
    });
    
    // Cập nhật số lượng IP đáng ngờ
    document.getElementById('suspicious-count').textContent = ips.length;
}

// Hàm cập nhật top IPs
function updateTopIPs(ips) {
    const container = document.getElementById('top-ips');
    container.innerHTML = '';
    
    if (ips.length === 0) {
        container.innerHTML = '<div class="text-muted text-center py-3">Chưa có dữ liệu</div>';
        return;
    }
    
    ips.forEach((ip, index) => {
        const item = document.createElement('div');
        item.className = 'd-flex justify-content-between align-items-center p-2 border-bottom';
        item.innerHTML = `
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2">#${index + 1}</span>
                <span class="text-break">${ip.address}</span>
            </div>
            <span class="badge bg-secondary">${ip.requests}</span>
        `;
        container.appendChild(item);
    });
}

function getMethodStyle(method) {
    switch(method.toUpperCase()) {
        case 'GET':     return { color: '#28a745', textColor: 'white' };
        case 'POST':    return { color: '#0d6efd', textColor: 'white' };
        case 'PUT':     return { color: '#ffc107', textColor: '#212529' };
        case 'DELETE':  return { color: '#dc3545', textColor: 'white' };
        case 'HEAD':    return { color: '#6c757d', textColor: 'white' };
        case 'OPTIONS': return { color: '#0dcaf0', textColor: '#212529' };
        case 'PATCH':   return { color: '#fd7e14', textColor: 'white' };
        default:        return { color: '#6f42c1', textColor: 'white' }; // UNKNOWN / OTHER
    }
}

// Hàm cập nhật thống kê method với responsive design
function updateMethodStats(methodStats) {
    const container = document.getElementById('method-stats-body');
    container.innerHTML = '';

    const methodLayout = [
        ['GET', 'HEAD'],
        ['POST', 'OPTIONS'],
        ['PUT', 'PATCH'],
        ['DELETE', 'UNKNOWN']
    ];

    methodLayout.forEach(pair => {
        const [leftMethod, rightMethod] = pair;

        const leftCount = methodStats[leftMethod] || 0;
        const rightCount = methodStats[rightMethod] || 0;

        const leftStyle = getMethodStyle(leftMethod);
        const rightStyle = getMethodStyle(rightMethod);
        
        const row = document.createElement('div');
        row.className = 'row mb-2 align-items-center';
        row.innerHTML = `
            <div class="col-4 col-sm-4">
                <div class="method-badge-block" style="background-color: ${leftStyle.color}; color: ${leftStyle.textColor};">
                    ${leftMethod}
                </div>
            </div>
            <div class="col-2 col-sm-2 text-center fw-bold fs-5">
                ${leftCount.toLocaleString('vi-VN')}
            </div>
            <div class="col-4 col-sm-4">
                <div class="method-badge-block" style="background-color: ${rightStyle.color}; color: ${rightStyle.textColor};">
                    ${rightMethod === 'UNKNOWN' ? 'OTHER' : rightMethod}
                </div>
            </div>
            <div class="col-2 col-sm-2 text-center fw-bold fs-5">
                ${rightCount.toLocaleString('vi-VN')}
            </div>
        `;
        container.appendChild(row);
    });
}

// Kết nối WebSocket với retry logic
let ws = null;
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;

function connectWebSocket() {
    try {
        ws = new WebSocket('ws://' + window.location.hostname + ':9000');
        
        ws.onopen = function() {
            console.log('WebSocket connected');
            reconnectAttempts = 0;
            document.getElementById('ws-status').className = 'status-indicator status-online';
        };

        ws.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                
                switch(data.type) {
                    case 'log':
                        updateLog(data.content);
                        break;
                    case 'stats':
                        updateStats(data.content);
                        break;
                    case 'chart':
                        updateChart(data.content);
                        break;
                    case 'suspicious':
                        updateSuspiciousIPs(data.content);
                        break;
                    case 'methodStats':
                        updateMethodStats(data.content);
                        break;
                    case 'topIPs':
                        updateTopIPs(data.content);
                        break;
                }
            } catch (error) {
                console.error('Error parsing WebSocket message:', error);
            }
        };

        ws.onerror = function(error) {
            console.error('WebSocket error:', error);
            document.getElementById('ws-status').className = 'status-indicator status-warning';
        };

        ws.onclose = function() {
            console.log('WebSocket disconnected');
            document.getElementById('ws-status').className = 'status-indicator status-danger';
            
            if (reconnectAttempts < maxReconnectAttempts) {
                reconnectAttempts++;
                const delay = Math.min(1000 * Math.pow(2, reconnectAttempts), 10000);
                console.log(`Reconnecting in ${delay}ms (attempt ${reconnectAttempts})`);
                setTimeout(connectWebSocket, delay);
            } else {
                console.error('Max reconnection attempts reached');
                updateLog('Lỗi kết nối WebSocket - Không thể kết nối lại');
            }
        };
    } catch (error) {
        console.error('Error creating WebSocket:', error);
    }
}

// Hàm cập nhật biểu đồ
function updateChart(data) {
    if (!window.requestsChart) return;
    
    const chart = window.requestsChart;
    chart.data.labels.push(data.time);
    chart.data.datasets[0].data.push(data.value);
    
    // Giới hạn số điểm dữ liệu để tránh lag
    const maxDataPoints = window.innerWidth < 768 ? 15 : 20;
    if (chart.data.labels.length > maxDataPoints) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }
    
    chart.update('none'); // Không animate để tăng performance
}

// Khởi tạo kết nối WebSocket
connectWebSocket();

// Responsive chart resize
window.addEventListener('resize', function() {
    if (window.requestsChart) {
        window.requestsChart.resize();
    }
});

// Performance optimization: Throttle scroll events
let scrollTimeout;
function throttledScroll() {
    if (scrollTimeout) return;
    
    scrollTimeout = setTimeout(() => {
        const logContainer = document.getElementById('log-container');
        if (logContainer) {
            const isAtBottom = logContainer.scrollTop + logContainer.clientHeight >= logContainer.scrollHeight - 10;
            window.autoScroll = isAtBottom;
        }
        scrollTimeout = null;
    }, 100);
}

// Thêm event listener cho scroll
document.addEventListener('DOMContentLoaded', function() {
    const logContainer = document.getElementById('log-container');
    if (logContainer) {
        logContainer.addEventListener('scroll', throttledScroll);
    }
}); 