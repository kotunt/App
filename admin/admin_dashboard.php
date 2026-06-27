<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
$conn = Database::getInstance()->getConnection();

require_admin();

$page_title = __('admin_dashboard_title');

// Fetch initial data
$today_date = date('Y-m-d');

// Total Users
$total_users_res = $conn->query("SELECT COUNT(id) as count FROM users");
$total_users = $total_users_res->fetch_assoc()['count'] ?? 0;

// Today's Revenue (Deposits)
$today_revenue_res = $conn->query("SELECT SUM(amount) as total FROM deposits WHERE status = 'approved' AND DATE(created_at) = '{$today_date}'");
$today_revenue = $today_revenue_res->fetch_assoc()['total'] ?? 0;

// Today's Payout (Withdrawals)
$today_payout_res = $conn->query("SELECT SUM(amount) as total FROM withdrawals WHERE status = 'approved' AND DATE(created_at) = '{$today_date}'");
$today_payout = $today_payout_res->fetch_assoc()['total'] ?? 0;

// Today's Bets
$today_bets_res = $conn->query("SELECT SUM(amount) as total FROM bets WHERE DATE(created_at) = '{$today_date}'");
$today_bets = $today_bets_res->fetch_assoc()['total'] ?? 0;

// Latest Activities
$latest_activities_res = $conn->query("
    (SELECT id, user_id, amount, 'deposit' as type, created_at FROM deposits ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT id, user_id, amount, 'withdrawal' as type, created_at FROM withdrawals ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT id, id as user_id, 0 as amount, 'register' as type, created_at FROM users ORDER BY created_at DESC LIMIT 3)
    ORDER BY created_at DESC LIMIT 5
");

require_once 'admin_header.php';
?>

<div class="p-4 md:p-8">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl"><i class="fas fa-users"></i></div>
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase"><?= __('total_users') ?></p>
                <p id="stat_total_users" class="text-3xl font-black text-gray-800 transition-colors"><?= number_format($total_users) ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="w-14 h-14 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-2xl"><i class="fas fa-arrow-down"></i></div>
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase"><?= __('today_revenue') ?></p>
                <p id="stat_today_revenue" class="text-3xl font-black text-gray-800 transition-colors"><?= number_format($today_revenue, 2) ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-2xl"><i class="fas fa-arrow-up"></i></div>
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase"><?= __('today_payout') ?></p>
                <p id="stat_today_payout" class="text-3xl font-black text-gray-800 transition-colors"><?= number_format($today_payout, 2) ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-2xl"><i class="fas fa-dice"></i></div>
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase"><?= __('today_bets') ?></p>
                <p id="stat_today_bets" class="text-3xl font-black text-gray-800 transition-colors"><?= number_format($today_bets, 2) ?></p>
            </div>
        </div>
    </div>

    <!-- Latest Activities -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-history mr-2 text-primary"></i> <?= __('latest_activities') ?>
            <span id="realtime_status" class="ml-auto w-3 h-3 bg-gray-300 rounded-full" title="Real-time Connection Status"></span>
        </h3>
        <div class="space-y-3 max-h-96 overflow-y-auto pr-2" id="activity_list">
            <?php while($activity = $latest_activities_res->fetch_assoc()): 
                $user_info_res = $conn->query("SELECT username FROM users WHERE id = " . intval($activity['user_id']));
                $username = $user_info_res->fetch_assoc()['username'] ?? 'Unknown';
                
                $icon = 'fa-question-circle';
                $color = 'gray';
                $text = 'An unknown activity occurred.';

                switch($activity['type']) {
                    case 'deposit':
                        $icon = 'fa-arrow-down'; $color = 'green';
                        $text = sprintf(__('%s requested a deposit of %s Ks.'), "<strong>{$username}</strong>", "<strong>" . number_format($activity['amount']) . "</strong>");
                        break;
                    case 'withdrawal':
                        $icon = 'fa-arrow-up'; $color = 'red';
                        $text = sprintf(__('%s requested a withdrawal of %s Ks.'), "<strong>{$username}</strong>", "<strong>" . number_format($activity['amount']) . "</strong>");
                        break;
                    case 'register':
                        $icon = 'fa-user-plus'; $color = 'blue';
                        $text = sprintf(__('%s has registered a new account.'), "<strong>{$username}</strong>");
                        break;
                }
            ?>
            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                <div class="w-10 h-10 bg-<?= $color ?>-100 text-<?= $color ?>-600 rounded-full flex items-center justify-center text-lg"><i class="fas <?= $icon ?>"></i></div>
                <div class="flex-1">
                    <p class="text-sm text-gray-700"><?= $text ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= date('h:i:s A', strtotime($activity['created_at'])) ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<audio id="notificationSound" src="../assets/sounds/notification.mp3" preload="auto"></audio>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusIndicator = document.getElementById('realtime_status');
    const activityList = document.getElementById('activity_list');
    const notificationSound = document.getElementById('notificationSound');

    function connectWebSocket() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.host}/ws/`;
        const ws = new WebSocket(wsUrl);

        ws.onopen = function() {
            console.log('Admin Dashboard: WebSocket connection established.');
            statusIndicator.classList.remove('bg-gray-300', 'bg-red-500');
            statusIndicator.classList.add('bg-green-500', 'animate-pulse');
            statusIndicator.title = 'Real-time connection active';
        };

        ws.onmessage = function(event) {
            try {
                const message = JSON.parse(event.data);
                console.log('Real-time update received:', message);
                
                // Play sound
                notificationSound.currentTime = 0;
                notificationSound.play().catch(e => console.warn("Audio autoplay prevented."));

                // Update UI
                updateDashboard(message.event, message.data);

            } catch (e) {
                console.error('Error parsing WebSocket message:', e);
            }
        };

        ws.onclose = function() {
            console.log('Admin Dashboard: WebSocket connection closed. Reconnecting in 5 seconds...');
            statusIndicator.classList.remove('bg-green-500', 'animate-pulse');
            statusIndicator.classList.add('bg-red-500');
            statusIndicator.title = 'Real-time connection lost. Reconnecting...';
            setTimeout(connectWebSocket, 5000);
        };

        ws.onerror = function(error) {
            console.error('WebSocket error:', error);
            statusIndicator.classList.remove('bg-green-500', 'animate-pulse');
            statusIndicator.classList.add('bg-red-500');
            ws.close();
        };
    }

    function updateDashboard(event, data) {
        let icon = 'fa-question-circle';
        let color = 'gray';
        let text = 'An unknown activity occurred.';
        let amount = parseFloat(data.amount || 0);

        switch(event) {
            case 'new_user':
                icon = 'fa-user-plus'; color = 'blue';
                text = `<strong>${data.username}</strong> has registered a new account.`;
                updateStat('total_users', 1);
                break;
            case 'new_deposit':
                icon = 'fa-arrow-down'; color = 'green';
                text = `<strong>${data.username}</strong> requested a deposit of <strong>${amount.toLocaleString()}</strong> Ks.`;
                // Note: We only update revenue on 'approved' status, so this is just for activity feed.
                break;
            case 'new_withdrawal':
                icon = 'fa-arrow-up'; color = 'red';
                text = `<strong>${data.username}</strong> requested a withdrawal of <strong>${amount.toLocaleString()}</strong> Ks.`;
                break;
            case 'new_bet':
                icon = 'fa-dice'; color = 'purple';
                text = `<strong>${data.username}</strong> placed a ${data.type} bet of <strong>${amount.toLocaleString()}</strong> Ks.`;
                updateStat('today_bets', amount);
                break;
        }

        addActivityToList(icon, color, text);
    }

    function updateStat(elementId, valueToAdd) {
        const el = document.getElementById(`stat_${elementId}`);
        if (el) {
            let currentValue = parseFloat(el.innerText.replace(/,/g, '')) || 0;
            let newValue = currentValue + valueToAdd;
            el.innerText = newValue.toLocaleString(undefined, { minimumFractionDigits: elementId.includes('revenue') || elementId.includes('payout') || elementId.includes('bets') ? 2 : 0 });
            
            // Highlight effect
            el.classList.add('text-yellow-500');
            setTimeout(() => {
                el.classList.remove('text-yellow-500');
            }, 1500);
        }
    }

    function addActivityToList(icon, color, text) {
        const newActivity = document.createElement('div');
        newActivity.className = 'flex items-center gap-4 p-3 bg-blue-50 rounded-xl border border-blue-200 animate__animated animate__fadeInDown';
        
        const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

        newActivity.innerHTML = `
            <div class="w-10 h-10 bg-${color}-100 text-${color}-600 rounded-full flex items-center justify-center text-lg"><i class="fas ${icon}"></i></div>
            <div class="flex-1">
                <p class="text-sm text-gray-700">${text}</p>
                <p class="text-xs text-gray-400 mt-1">${time}</p>
            </div>
        `;

        activityList.prepend(newActivity);

        // Keep the list to a max of 10 items
        if (activityList.children.length > 10) {
            activityList.lastChild.remove();
        }

        // Remove highlight after a few seconds
        setTimeout(() => {
            newActivity.classList.remove('bg-blue-50', 'border-blue-200', 'animate__fadeInDown');
            newActivity.classList.add('bg-gray-50', 'border-gray-100');
        }, 3000);
    }

    connectWebSocket();
});
</script>

<?php require_once 'admin_footer.php'; ?>

</body>
</html>