// 自分のシフトページ用JavaScript

// Knockout.js ViewModel
function MyShiftsViewModel() {
    const vm = this;
    
    // データ
    vm.myShifts = ko.observableArray([]);
    vm.loading = ko.observable(true);
    vm.error = ko.observable('');
    
    // 現在のユーザーID
    const currentUserId = Number(window.CURRENT_USER_ID || document.querySelector('meta[name="current-user-id"]')?.content || 0);
    
    // 自分のシフトを取得
    vm.load = function() {
        vm.loading(true);
        vm.error('');
        
        const API = window.API_BASE || '/api';
        
        fetch(API + '/shifts?mine=1', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            const shifts = data.data || data.shifts || [];
            
            // 自分が参加しているシフトのみをフィルター
            const myShifts = shifts.filter(function(shift) {
                // assigned_users配列をチェック
                if (shift.assigned_users && Array.isArray(shift.assigned_users)) {
                    return shift.assigned_users.some(function(user) {
                        return user.user_id == currentUserId || user.id == currentUserId;
                    });
                }
                
                // mine フラグをチェック
                if (shift.mine === true || shift.mine === 1 || shift.mine === '1') {
                    return true;
                }
                
                // participating フラグをチェック
                if (shift.participating === true || shift.participating === 1 || shift.participating === '1') {
                    return true;
                }
                
                return false;
            });
            
            vm.myShifts(myShifts);
            vm.loading(false);
        })
        .catch(function(error) {
            vm.error('シフトの取得に失敗しました');
            vm.loading(false);
        });
    };
    
    // 初期化
    vm.load();
}

// ViewModel をグローバルに登録
window.__myShiftsVM = new MyShiftsViewModel();

// Knockout.js バインディングを適用
document.addEventListener('DOMContentLoaded', function() {
    const root = document.getElementById('my-shifts-root');
    if (root && window.ko) {
        ko.applyBindings(window.__myShiftsVM, root);
    }
});

