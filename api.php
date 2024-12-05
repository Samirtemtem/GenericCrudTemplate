<?php
require_once 'config.php';
require_once 'app/core/Database.php';

// Enable CORS and set content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$db = \App\Core\Database::getInstance();

// Route handling
$route = $_GET['route'] ?? 'dashboard';

try {
    switch ($route) {
        case 'dashboard-stats':
            // Fetch total counts for dashboard
            $stats = [
                'activities' => fetchTotalCount('activity'),
                'courses' => fetchTotalCount('course'),
                'instructors' => fetchTotalCount('instructor'),
                'activity_sessions' => fetchTotalCount('activitysession')
            ];
            echo json_encode($stats);
            break;

        case 'activities':
            echo json_encode(fetchAllRecords('activity'));
            break;

        case 'courses':
            echo json_encode(fetchAllRecords('course'));
            break;

        case 'instructors':
            echo json_encode(fetchAllRecords('instructor'));
            break;

        case 'activity-sessions':
            echo json_encode(fetchAllRecords('activitysession'));
            break;

        case 'dashboard':
        default:
            // Return dashboard HTML
            echo generateDashboardHTML();
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function fetchTotalCount($table) {
    global $db;
    $stmt = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM $table");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] ?? 0;
}

function fetchAllRecords($table) {
    global $db;
    $stmt = $db->getConnection()->prepare("SELECT * FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateDashboardHTML() {
    ob_start();
    ?>
    <div class="mdl-grid">
        <div class="mdl-cell mdl-cell--12-col">
            <h3>Dashboard</h3>
            <div class="mdl-grid">
                <div class="mdl-cell mdl-cell--3-col mdl-card mdl-shadow--2dp">
                    <div class="mdl-card__title">
                        <h2 class="mdl-card__title-text">Activities</h2>
                    </div>
                    <div class="mdl-card__supporting-text">
                        Total Activities: <span id="total-activities">-</span>
                    </div>
                </div>
                <div class="mdl-cell mdl-cell--3-col mdl-card mdl-shadow--2dp">
                    <div class="mdl-card__title">
                        <h2 class="mdl-card__title-text">Courses</h2>
                    </div>
                    <div class="mdl-card__supporting-text">
                        Total Courses: <span id="total-courses">-</span>
                    </div>
                </div>
                <div class="mdl-cell mdl-cell--3-col mdl-card mdl-shadow--2dp">
                    <div class="mdl-card__title">
                        <h2 class="mdl-card__title-text">Instructors</h2>
                    </div>
                    <div class="mdl-card__supporting-text">
                        Total Instructors: <span id="total-instructors">-</span>
                    </div>
                </div>
                <div class="mdl-cell mdl-cell--3-col mdl-card mdl-shadow--2dp">
                    <div class="mdl-card__title">
                        <h2 class="mdl-card__title-text">Activity Sessions</h2>
                    </div>
                    <div class="mdl-card__supporting-text">
                        Total Sessions: <span id="total-sessions">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Fetch dashboard statistics
        function fetchDashboardStats() {
            fetch('api.php?route=dashboard-stats')
                .then(response => response.json())
                .then(stats => {
                    document.getElementById('total-activities').textContent = stats.activities || 0;
                    document.getElementById('total-courses').textContent = stats.courses || 0;
                    document.getElementById('total-instructors').textContent = stats.instructors || 0;
                    document.getElementById('total-sessions').textContent = stats.activity_sessions || 0;
                })
                .catch(error => console.error('Error fetching dashboard stats:', error));
        }

        // Initial dashboard stats load
        fetchDashboardStats();
    </script>
    <?php
    return ob_get_clean();
}
