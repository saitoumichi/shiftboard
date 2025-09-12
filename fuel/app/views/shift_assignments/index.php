<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>シフト割り当て管理 - ShiftBoard</title>
    <link rel="stylesheet" href="/assets/css/shifts.css">
    <link rel="stylesheet" href="/assets/css/shift_assignments.css">
</head>
<body>
    <div class="container">
        <!-- ヘッダー -->
        <header class="page-header">
            <h1>シフト割り当て管理</h1>
            <nav class="header-nav">
                <a href="/shifts" class="nav-link">シフト一覧</a>
                <a href="/myshifts" class="nav-link">マイシフト</a>
                <a href="/shift_assignments" class="nav-link active">割り当て管理</a>
            </nav>
        </header>

        <!-- メインコンテンツ -->
        <main class="main-content">
            <!-- 統計情報 -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>総割り当て数</h3>
                        <div class="stat-number" id="total-assignments">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>今月の割り当て</h3>
                        <div class="stat-number" id="monthly-assignments">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>未割り当てシフト</h3>
                        <div class="stat-number" id="unassigned-shifts">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>満員シフト</h3>
                        <div class="stat-number" id="full-shifts">-</div>
                    </div>
                </div>
            </section>

            <!-- フィルターとアクション -->
            <section class="filter-section">
                <div class="filter-controls">
                    <select id="status-filter" class="filter-select">
                        <option value="">すべてのステータス</option>
                        <option value="assigned">割り当て済み</option>
                        <option value="confirmed">確定済み</option>
                        <option value="cancelled">キャンセル</option>
                    </select>
                    
                    <select id="member-filter" class="filter-select">
                        <option value="">すべてのメンバー</option>
                    </select>
                    
                    <input type="date" id="date-from" class="date-input" placeholder="開始日">
                    <input type="date" id="date-to" class="date-input" placeholder="終了日">
                    
                    <button id="apply-filters" class="btn btn-primary">フィルター適用</button>
                    <button id="clear-filters" class="btn btn-secondary">クリア</button>
                </div>
            </section>

            <!-- 割り当て一覧 -->
            <section class="assignments-section">
                <div class="section-header">
                    <h2>シフト割り当て一覧</h2>
                    <div class="section-actions">
                        <button id="export-btn" class="btn btn-outline">エクスポート</button>
                        <button id="refresh-btn" class="btn btn-primary">更新</button>
                    </div>
                </div>
                
                <div class="assignments-container">
                    <div class="loading" id="loading-indicator" style="display: none;">
                        <div class="loading-spinner"></div>
                        <p>読み込み中...</p>
                    </div>
                    
                    <div class="assignments-list" id="assignments-list">
                        <!-- 割り当て一覧がここに表示されます -->
                    </div>
                    
                    <div class="no-data" id="no-data" style="display: none;">
                        <p>割り当てデータがありません</p>
                    </div>
                </div>
            </section>

            <!-- 割り当て詳細モーダル -->
            <div id="assignment-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>割り当て詳細</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="assignment-details">
                            <!-- 詳細情報がここに表示されます -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="edit-assignment" class="btn btn-primary">編集</button>
                        <button id="cancel-assignment" class="btn btn-danger">キャンセル</button>
                        <button class="btn btn-secondary modal-close">閉じる</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script src="/assets/js/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/shift_assignments.js"></script>
</body>
</html>
