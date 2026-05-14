<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages • Al Burhan Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="sidebar.css" />
    <link rel="stylesheet" href="dashboard.css" />

    <style>
        /* ---- Messages Page Styles ---- */
        .messages-table {
            border: 1px solid var(--gold-border);
            border-radius: 4px;
            overflow: hidden;
        }
        .messages-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .messages-table th {
            padding: 14px 18px;
            text-align: left;
            font-family: var(--font-ui);
            font-size: 10px;
            letter-spacing: 2.5px;
            color: var(--gold);
            text-transform: uppercase;
            background: rgba(0,31,19,0.8);
            border-bottom: 1px solid var(--gold-border);
        }
        .messages-table td {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(212,175,55,0.08);
            font-family: var(--font-body);
            font-size: 15px;
            color: var(--text-muted);
            vertical-align: middle;
        }
        .messages-table tr:last-child td { border-bottom: none; }
        .messages-table tr:hover td { background: rgba(212,175,55,0.04); color: var(--text-white); }
        .messages-table tr.unread td { color: var(--text-white); }
        .messages-table tr.unread td:first-child::before {
            content: '';
            display: inline-block;
            width: 6px; height: 6px;
            background: var(--gold);
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }
        .msg-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msg-date {
            font-family: var(--font-ui);
            font-size: 10px;
            letter-spacing: 1px;
            color: var(--text-dim);
            white-space: nowrap;
        }
        .badge-unread {
            font-family: var(--font-ui);
            font-size: 9px;
            letter-spacing: 1px;
            background: var(--gold);
            color: var(--deep-green);
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 700;
        }
        .view-btn {
            background: transparent;
            border: 1px solid var(--gold-border);
            color: var(--gold);
            padding: 5px 14px;
            border-radius: 2px;
            font-family: var(--font-ui);
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .view-btn:hover { background: var(--gold); color: var(--deep-green); }
        .del-btn {
            background: transparent;
            border: 1px solid rgba(248,113,113,0.3);
            color: #f87171;
            padding: 5px 14px;
            border-radius: 2px;
            font-family: var(--font-ui);
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 6px;
        }
        .del-btn:hover { background: rgba(248,113,113,0.12); border-color: #f87171; }

        /* Modal */
        .msg-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .msg-modal-overlay.open { display: flex; }
        .msg-modal {
            background: var(--deep-green);
            border: 1px solid var(--gold-border);
            border-radius: 4px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }
        .msg-modal-close {
            position: absolute; top: 16px; right: 16px;
            background: transparent; border: 1px solid var(--gold-border);
            color: var(--gold); width: 32px; height: 32px;
            border-radius: 2px; cursor: pointer; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
        }
        .msg-modal h3 {
            font-family: var(--font-display);
            font-size: 18px; letter-spacing: 3px;
            color: var(--text-white); font-weight: 400;
            margin-bottom: 20px;
        }
        .msg-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px; }
        .msg-meta-item label {
            font-family: var(--font-ui); font-size: 9px;
            letter-spacing: 2px; color: var(--gold); text-transform: uppercase;
            display: block; margin-bottom: 4px; opacity: 0.7;
        }
        .msg-meta-item span {
            font-family: var(--font-body); font-size: 15px; color: var(--text-white);
        }
        .msg-body-label {
            font-family: var(--font-ui); font-size: 9px;
            letter-spacing: 2px; color: var(--gold); text-transform: uppercase;
            margin-bottom: 10px; opacity: 0.7;
        }
        .msg-body-text {
            font-family: var(--font-body); font-size: 16px;
            color: var(--text-muted); line-height: 1.8;
            background: rgba(0,0,0,0.2); border: 1px solid var(--gold-border);
            border-radius: 3px; padding: 18px;
        }
        .reply-link {
            display: inline-flex; align-items: center; gap: 8px;
            margin-top: 24px;
            font-family: var(--font-ui); font-size: 11px;
            letter-spacing: 2px; text-transform: uppercase;
            color: var(--deep-green);
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            padding: 11px 22px; border-radius: 3px; text-decoration: none;
            transition: all 0.3s;
        }
        .reply-link:hover { opacity: 0.9; transform: translateY(-1px); }
        .empty-msgs {
            text-align: center; padding: 80px 20px;
        }
        .empty-msgs i { font-size: 40px; color: var(--gold); opacity: 0.2; display: block; margin-bottom: 16px; }
        .empty-msgs p { font-family: var(--font-body); font-style: italic; color: var(--text-dim); }
    </style>
</head>
<body>
<?php
require_once __DIR__ . '/config.php';
$db = getDB();

/* ---- Mark as read ---- */
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id")
       ->execute([':id' => (int)$_GET['read']]);
}

/* ---- Delete ---- */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM contact_messages WHERE id = :id")
       ->execute([':id' => (int)$_GET['delete']]);
    header("Location: messages.php");
    exit;
}

/* ---- Fetch messages ---- */
$messages  = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$unreadCnt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

/* ---- View single message ---- */
$viewMsg = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['view']]);
    $viewMsg = $stmt->fetch();
    if ($viewMsg && !$viewMsg['is_read']) {
        $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id")
           ->execute([':id' => $viewMsg['id']]);
        $viewMsg['is_read'] = 1;
        $unreadCnt = max(0, $unreadCnt - 1);
    }
}
?>

<div class="admin-container">

    <?php include 'sidebar.php'; ?>

    <main class="main-content">

        <!-- Header -->
        <header class="admin-header">
            <div class="admin-header-left">
                <h2>Messages</h2>
                <p>
                    Contact form submissions
                    <?php if ($unreadCnt > 0): ?>
                        — <span style="color:var(--gold)"><?= $unreadCnt ?> unread</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="admin-header-right">
                <button class="notif-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if ($unreadCnt > 0): ?><span class="notif-dot"></span><?php endif; ?>
                </button>
            </div>
        </header>

        <!-- Table -->
        <div class="section">
            <div class="section-header">
                <div class="section-header-left">
                    <span class="section-gem">◆</span>
                    <div>
                        <h3>All Messages</h3>
                        <p><?= count($messages) ?> total • <?= $unreadCnt ?> unread</p>
                    </div>
                </div>
            </div>

            <?php if (empty($messages)): ?>
                <div class="empty-msgs">
                    <i class="fas fa-envelope-open"></i>
                    <p>No messages received yet.</p>
                </div>
            <?php else: ?>
            <div class="messages-table">
                <table>
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Preview</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                        <tr class="<?= $msg['is_read'] ? '' : 'unread' ?>">
                            <td>
                                <div style="font-family:var(--font-body);color:var(--text-white);font-size:15px;">
                                    <?= htmlspecialchars($msg['firstname'] . ' ' . $msg['lastname']) ?>
                                </div>
                                <div style="font-family:var(--font-ui);font-size:10px;color:var(--text-dim);letter-spacing:1px;margin-top:2px;">
                                    <?= htmlspecialchars($msg['email']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($msg['subject']) ?></td>
                            <td>
                                <div class="msg-preview">
                                    <?= htmlspecialchars(substr($msg['message'], 0, 80)) ?>…
                                </div>
                            </td>
                            <td class="msg-date"><?= date('d M Y, H:i', strtotime($msg['created_at'])) ?></td>
                            <td>
                                <?php if (!$msg['is_read']): ?>
                                    <span class="badge-unread">Unread</span>
                                <?php else: ?>
                                    <span style="font-family:var(--font-ui);font-size:9px;color:var(--text-dim);letter-spacing:1px;">Read</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="messages.php?view=<?= $msg['id'] ?>" class="view-btn">View</a>
                                <a href="messages.php?delete=<?= $msg['id'] ?>"
                                   class="del-btn"
                                   onclick="return confirm('Delete this message?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- MESSAGE MODAL -->
<?php if ($viewMsg): ?>
<div class="msg-modal-overlay open" id="msgModal">
    <div class="msg-modal">
        <button class="msg-modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>

        <h3><?= htmlspecialchars($viewMsg['subject']) ?></h3>

        <div class="msg-meta">
            <div class="msg-meta-item">
                <label>From</label>
                <span><?= htmlspecialchars($viewMsg['firstname'] . ' ' . $viewMsg['lastname']) ?></span>
            </div>
            <div class="msg-meta-item">
                <label>Email</label>
                <span><?= htmlspecialchars($viewMsg['email']) ?></span>
            </div>
            <?php if ($viewMsg['phone']): ?>
            <div class="msg-meta-item">
                <label>Phone</label>
                <span><?= htmlspecialchars($viewMsg['phone']) ?></span>
            </div>
            <?php endif; ?>
            <div class="msg-meta-item">
                <label>Received</label>
                <span><?= date('d M Y, H:i', strtotime($viewMsg['created_at'])) ?></span>
            </div>
        </div>

        <div class="msg-body-label">Message</div>
        <div class="msg-body-text"><?= nl2br(htmlspecialchars($viewMsg['message'])) ?></div>

        <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['subject']) ?>"
           class="reply-link">
            <i class="fas fa-reply"></i> Reply via Email
        </a>
    </div>
</div>
<script>
function closeModal() {
    document.getElementById('msgModal').classList.remove('open');
    history.pushState({}, '', 'messages.php');
}
document.getElementById('msgModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
<?php endif; ?>

</body>
</html>