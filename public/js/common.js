// 共通JavaScript - ShiftBoard

// APIベースURLとユーザーIDは各ビューファイルで設定済み

// jQuery AJAX設定: X-HTTP-Method-OverrideをGETリクエストに付与しない
$(document).ready(function() {
    // jQuery AJAXのグローバル設定
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            // GETリクエストの場合は上書きしない
            if ((settings.type || settings.method || 'GET').toUpperCase() === 'GET') {
                return;
            }
            // ここから下は POST/PUT/PATCH/DELETE のみ
            // xhr.setRequestHeader('X-HTTP-Method-Override', 'PUT' など)
        }
    });
});

// グローバル設定
window.ShiftBoard = {
    config: {
        apiBaseUrl: window.API_BASE || '/api',
        animationDuration: 300,
        alertTimeout: 5000
    },

    // DOMユーティリティ
    dom: {
        ready: function (fn) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn, { once: true });
            } else {
                fn();
            }
        },
        ensureBody: function (fn) {
            if (document.body) return fn(document.body);
            document.addEventListener('DOMContentLoaded', function () {
                if (document.body) fn(document.body);
            }, { once: true });
        }
    },
    
    // ユーティリティ関数
    utils: {
        // 日付フォーマット
        formatDate: function(date, format = 'YYYY-MM-DD') {
            if (!date) return '';
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            
            return format
                .replace('YYYY', year)
                .replace('MM', month)
                .replace('DD', day);
        },
        
        // 時間フォーマット
        formatTime: function(time) {
            if (!time) return '';
            return time.substring(0, 5); // HH:MM形式
        },
        
        // 色の生成
        generateColor: function(seed) {
            const colors = [
                '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
                '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'
            ];
            return colors[seed % colors.length];
        },
        
        // デバウンス関数
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // スロットル関数
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    },
    
    // アラート管理
    alert: {
        show: function(message, type = 'info', duration = null) {
            const alertContainer = document.getElementById('alert-container') || this.createContainer();
            const alertId = 'alert-' + Date.now();
            
            const alertElement = document.createElement('div');
            alertElement.id = alertId;
            alertElement.className = `alert alert-${type} fade-in`;
            alertElement.innerHTML = `
                <span class="alert-message">${message}</span>
                <button class="alert-close" onclick="ShiftBoard.alert.hide('${alertId}')">&times;</button>
            `;
            
            alertContainer.appendChild(alertElement);
            
            // 自動非表示
            if (duration !== 0) {
                setTimeout(() => {
                    this.hide(alertId);
                }, duration || ShiftBoard.config.alertTimeout);
            }
            
            return alertId;
        },
        
        hide: function(alertId) {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                alertElement.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (alertElement.parentNode) {
                        alertElement.parentNode.removeChild(alertElement);
                    }
                }, 300);
            }
        },
        
        createContainer: function() {
            const container = document.createElement('div');
            container.id = 'alert-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            
            // DOM が準備できていないタイミングでも安全に body に追加
            ShiftBoard.dom.ensureBody(function (body) {
                body.appendChild(container);
            });
            return container;
        }
    },
    
    // ローディング管理
    loading: {
        show: function(element = null) {
            const target = element || document.body || document.documentElement;
            const loadingId = 'loading-' + Date.now();
            
            const loadingElement = document.createElement('div');
            loadingElement.id = loadingId;
            loadingElement.className = 'loading-overlay';
            loadingElement.innerHTML = `
                <div class="loading-spinner"></div>
                <div class="loading-text">読み込み中...</div>
            `;
            
            const append = function(tgt){
                if (getComputedStyle(tgt).position === 'static') {
                    tgt.style.position = 'relative';
                }
                tgt.appendChild(loadingElement);
            };
            if (target && target.appendChild) {
                append(target);
            } else {
                ShiftBoard.dom.ensureBody(function (body) { append(body); });
            }
    
            return loadingId;
        },
        
        hide: function(loadingId) {
            const loadingElement = document.getElementById(loadingId);
            if (loadingElement) {
                loadingElement.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    if (loadingElement.parentNode) {
                        loadingElement.parentNode.removeChild(loadingElement);
                    }
                }, 300);
            }
        }
    },
    
    // モーダル管理
    modal: {
        show: function(content, options = {}) {
            const modalId = 'modal-' + Date.now();
            const modalElement = document.createElement('div');
            modalElement.id = modalId;
            modalElement.className = 'modal-overlay';
            modalElement.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>${options.title || '確認'}</h3>
                        <button class="modal-close" onclick="ShiftBoard.modal.hide('${modalId}')">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        ${options.buttons || '<button class="btn btn-primary" onclick="ShiftBoard.modal.hide(\'' + modalId + '\')">閉じる</button>'}
                    </div>
                </div>
            `;
            
            ShiftBoard.dom.ensureBody(function (body) {
                body.appendChild(modalElement);
            });
            
            // アニメーション
            setTimeout(() => {
                modalElement.classList.add('show');
            }, 10);
            
            return modalId;
        },
        
        hide: function(modalId) {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                modalElement.classList.remove('show');
                setTimeout(() => {
                    if (modalElement.parentNode) {
                        modalElement.parentNode.removeChild(modalElement);
                    }
                }, 300);
            }
        }
    },
    
    // API呼び出し
    api: {
        url: function (path) {
            if (!path) return ShiftBoard.config.apiBaseUrl;
            // absolute URL
            if (/^https?:\/\//i.test(path)) return path;
            // already starts with /api
            if (path.startsWith('/api')) return path;
            // join with base
            const base = window.API_BASE || '/api';
            const left = base.replace(/\/+$/, '');
            const right = String(path).replace(/^\/+/, '');
            return left + '/' + right;
        },
        request: function(url, options = {}) {
            const defaultOptions = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            const finalOptions = { ...defaultOptions, ...options };
            // Avoid forcing Content-Type for GET without body
            if ((finalOptions.method || 'GET').toUpperCase() === 'GET') {
                // Some servers dislike Content-Type on GET; remove it
                if (finalOptions.headers && finalOptions.headers['Content-Type']) {
                    try { delete finalOptions.headers['Content-Type']; } catch (e) {}
                }
                // X-HTTP-Method-Override should never be sent with GET requests (causes 405 error)
                if (finalOptions.headers && finalOptions.headers['X-HTTP-Method-Override']) {
                    try { delete finalOptions.headers['X-HTTP-Method-Override']; } catch (e) {}
                }
            }
            const fullUrl = (/^https?:\/\//i.test(url) || url.startsWith('/api')) ? url : ShiftBoard.api.url(url);
            return fetch(fullUrl, finalOptions)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {

                    ShiftBoard.alert.show('通信エラーが発生しました', 'danger');
                    throw error;
                });
        },
        get: function(url) {
            return this.request(url);
        },
        post: function(url, data) {
            return this.request(url, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        put: function(url, data) {
            return this.request(url, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },
        delete: function(url) {
            return this.request(url, {
                method: 'DELETE'
            });
        }
    }
};

// ページ読み込み時の初期化
document.addEventListener('DOMContentLoaded', function() {
    // アニメーション用CSS追加
    const style = document.createElement('style');
    style.textContent = `
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        .loading-spinner {
            width: 32px;
            height: 32px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top-color: #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        .loading-text {
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal-content {
            transform: scale(1);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .alert-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            padding: 0;
            margin-left: 10px;
        }
        
        .alert-close:hover {
            opacity: 1;
        }
        
        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    try {
        const path = (location.pathname || '').toLowerCase();
        const isShiftsPage = /\/shifts(\/|$)/.test(path) || /\/myshifts(\/|$)/.test(path);
    
        if (isShiftsPage && ShiftBoard.dom.ensureElement) {
          ShiftBoard.dom.ensureElement('#available-shifts-container', {
            tag: 'div',
            id: 'available-shifts-container',
            className: 'available-shifts-container',
            parentSelector: '.month-recruitment-section'
          });
          ShiftBoard.dom.ensureElement('#available-shifts-container-week', {
            tag: 'div',
            id: 'available-shifts-container-week',
            className: 'available-shifts-container week',
            parentSelector: '.week-recruitment-section'
          });
          ShiftBoard.dom.ensureElement('#available-shifts-container-day', {
            tag: 'div',
            id: 'available-shifts-container-day',
            className: 'available-shifts-container day',
            parentSelector: '.day-recruitment-section'
          });
        }
      } catch(e) { }
    document.head.appendChild(style);
    
    // スムーススクロール
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // フォームバリデーション
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                ShiftBoard.alert.show('必須項目を入力してください', 'warning');
            }
        });
    });
    
    // 入力フィールドのリアルタイムバリデーション
    document.querySelectorAll('input, textarea, select').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });
});

// エラーハンドリング
(function () {
    function safeAlert(message, type) {
        try {
            ShiftBoard.alert.show(message, type);
        } catch (err) {
            // Ensure body exists before trying to show an alert
            ShiftBoard.dom && ShiftBoard.dom.ensureBody(function () {
                ShiftBoard.alert.show(message, type);
            });
        }
    }
    window.addEventListener('error', function(e) {

        safeAlert('予期しないエラーが発生しました', 'danger');
    });
    window.addEventListener('unhandledrejection', function(e) {

        safeAlert('通信エラーが発生しました', 'danger');
    });
})();

// 最終セーフティ：ShiftBoard 初期化で body 未準備のときでも安全に動作
ShiftBoard.dom && ShiftBoard.dom.ready(function(){ /* no-op: フック確保 */ });

ShiftBoard.dom.ensureElement = function(selector, opts = {}) {
    const found = document.querySelector(selector);
    if (found) return found;
    const tag = opts.tag || 'div';
    const el = document.createElement(tag);
    if (opts.id) el.id = opts.id;
    else if (selector.startsWith('#')) el.id = selector.slice(1);
    if (opts.className) el.className = opts.className;
  
    const parent =
      (opts.parentSelector && document.querySelector(opts.parentSelector)) ||
      document.querySelector('.month-recruitment-section') ||
      document.querySelector('.view-content.active') ||
      document.body;
  
    if (parent) {
      parent.appendChild(el);
    }
    return el;
  };