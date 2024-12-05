<?php
require_once "config.php";

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = '';
    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Get controller and action from URL
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'Home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Default to showing table list if no specific controller
if ($controller === 'Home') {
    try {
        $db = \App\Core\Database::getInstance();
        $tables = $db->fetchAll("SHOW TABLES");
        
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Pro Management System</title>
    <!-- Material Design Lite CSS and JS -->
    <link rel='stylesheet' href='https://code.getmdl.io/1.3.0/material.indigo-pink.min.css'>
    <script defer src='https://code.getmdl.io/1.3.0/material.min.js'></script>
    
    <!-- Custom Styles -->
    <style>
        .mdl-layout__drawer {
            width: 280px;
        }
        .mdl-layout__drawer .mdl-navigation {
            padding-top: 0;
        }
        .mdl-navigation__link {
            display: flex;
            align-items: center;
            padding: 16px;
        }
        .mdl-navigation__link i {
            margin-right: 16px;
        }
        .app-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class='mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header'>
        <header class='mdl-layout__header'>
            <div class='mdl-layout__header-row'>
                <span class='mdl-layout-title'>Pro Management System</span>
                <div class='mdl-layout-spacer'></div>
                <nav class='mdl-navigation mdl-layout--large-screen-only'>
                    <a class='mdl-navigation__link' href='#dashboard'>
                        <i class='material-icons'>dashboard</i> Dashboard
                    </a>
                </nav>
            </div>
        </header>
        
        <div class='mdl-layout__drawer'>
            <span class='mdl-layout-title'>Entities</span>
            <nav class='mdl-navigation'>";
            
        foreach ($tables as $table) {
            $tableName = reset($table); // Get the first column value
            $controllerName = str_replace('_', '', ucwords($tableName, '_'));
            echo "<a class='mdl-navigation__link' href='index.php?controller={$controllerName}'>
                    <i class='material-icons'>local_activity</i> {$tableName}
                </a>";
        }
        
        echo "          </nav>
        </div>
        
        <main class='mdl-layout__content'>
            <div class='app-content' id='app-content'>
                <!-- Dynamic content will be loaded here -->
                <div class='mdl-grid'>
                    <div class='mdl-cell mdl-cell--12-col'>
                        <h3>Dashboard</h3>
                        <div class='mdl-grid'>
                            <div class='mdl-cell mdl-cell--3-col mdl-card mdl-shadow--2dp'>
                                <div class='mdl-card__title'>
                                    <h2 class='mdl-card__title-text'>Activities</h2>
                                </div>
                                <div class='mdl-card__supporting-text'>
                                    Total Activities: <span id='total-activities'>-</span>
                                </div>
                            </div>
                            <div class='mdl-cell mdl-cell--3-col mdl-card mdl-shadow--2dp'>
                                <div class='mdl-card__title'>
                                    <h2 class='mdl-card__title-text'>Courses</h2>
                                </div>
                                <div class='mdl-card__supporting-text'>
                                    Total Courses: <span id='total-courses'>-</span>
                                </div>
                            </div>
                            <div class='mdl-cell mdl-cell--3-col mdl-card mdl-shadow--2dp'>
                                <div class='mdl-card__title'>
                                    <h2 class='mdl-card__title-text'>Instructors</h2>
                                </div>
                                <div class='mdl-card__supporting-text'>
                                    Total Instructors: <span id='total-instructors'>-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Material Icons -->
    <link rel='stylesheet' href='https://fonts.googleapis.com/icon?family=Material+Icons'>
    
    <!-- SPA Routing and Dynamic Content Loading -->
    <script>
        // Simple SPA routing
        function loadContent(route) {
            const contentArea = document.getElementById('app-content');
            
            // Fetch content based on route
            fetch(`api.php?route=${route}`)
                .then(response => response.text())
                .then(html => {
                    contentArea.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading content:', error);
                    contentArea.innerHTML = '<p>Error loading content</p>';
                });
        }

        // Handle hash change for routing
        window.addEventListener('hashchange', function() {
            const route = window.location.hash.substring(1);
            loadContent(route);
        });

        // Load dashboard by default
        loadContent('dashboard');

        // Fetch dashboard statistics
        function fetchDashboardStats() {
            fetch('api.php?route=dashboard-stats')
                .then(response => response.json())
                .then(stats => {
                    document.getElementById('total-activities').textContent = stats.activities || 0;
                    document.getElementById('total-courses').textContent = stats.courses || 0;
                    document.getElementById('total-instructors').textContent = stats.instructors || 0;
                })
                .catch(error => console.error('Error fetching dashboard stats:', error));
        }

        // Initial dashboard stats load
        fetchDashboardStats();
    </script>
</body>
</html>";
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Format controller class name
$controllerClass = "\\App\\Controllers\\{$controller}Controller";

// Create controller instance and call action
try {
    if (!class_exists($controllerClass)) {
        throw new Exception("Controller not found: {$controllerClass}");
    }
    
    $controller = new $controllerClass();
    
    if (!method_exists($controller, $action)) {
        throw new Exception("Action not found: {$action}");
    }
    
    if ($id !== null) {
        $controller->$action($id);
    } else {
        $controller->$action();
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
