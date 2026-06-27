<?php
session_start();

// Login ဝင်ပြီးသား User ဖြစ်နေပါက index.php သို့ တန်းသွားစေရန်
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/lang/language.php';
require_once __DIR__ . '/core/db_connect.php'; // Now includes Database class
require_once __DIR__ . '/core/security_helper.php';

$error_message = "";

if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error_message = __('session_timeout_error');
} elseif (isset($_GET['banned']) && $_GET['banned'] == 1) {
    $error_message = __('account_banned');
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form မှ ပေးပို့လိုက်သော ဖုန်းနံပါတ်နှင့် စကားဝှက်ကို ရယူခြင်း
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'];

    if (!empty($phone) && !empty($password)) {
        try {
            $db = Database::getInstance()->getConnection();

            // Check rate limit first
            if (!check_login_rate_limit($db, $ip_address, $phone)) {
                $error_message = __('login_rate_limit_error');
                send_security_alert_to_telegram($db, "Multiple failed login attempts detected.\nIP: `{$ip_address}`\nPhone: `{$phone}`");
            } else {
                // Database ထဲတွင် အဆိုပါ ဖုန်းနံပါတ် ရှိ/မရှိ စစ်ဆေးခြင်း
                $stmt = $db->prepare("SELECT id, username, password, is_banned, verification_status, role, google2fa_secret, last_login_ip FROM users WHERE phone_number = ?");
            
                if ($stmt) {
                    $stmt->bind_param("s", $phone);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        
                        // Database မှ Hash လုပ်ထားသော Password နှင့် ရိုက်ထည့်လိုက်သော Password ကိုက်ညီမှု ရှိ/မရှိ စစ်ဆေးခြင်း
                        if (password_verify($password, $user['password'])) {
                            if (isset($user['verification_status']) && $user['verification_status'] === 'pending') {
                                $error_message = __('account_pending_verification');
                            } elseif (isset($user['verification_status']) && $user['verification_status'] === 'rejected') {
                                $error_message = __('account_rejected');
                            } elseif ($user['is_banned']) {
                                $error_message = __('account_banned');
                            } else {
                                // Maintenance Mode စစ်ဆေးခြင်း
                                $m_stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
                                $m_mode = ($m_stmt && $m_row = $m_stmt->fetch_assoc()) ? $m_row['setting_value'] : '0';
                                if ($m_mode === '1' && !in_array($user['role'], ['admin', 'sub_admin'])) {
                                    $error_message = __('maintenance_mode_error');
                                } else {
                                    // Clear failed attempts upon successful password verification
                                    clear_failed_logins($db, $ip_address, $phone);
                                    
                                    // Update last login IP
                                    $check_ip_column = $db->query("SHOW COLUMNS FROM users LIKE 'last_login_ip'");
                                    if ($check_ip_column && $check_ip_column->num_rows > 0) {
                                        if (in_array($user['role'], ['admin', 'sub_admin']) && $user['last_login_ip'] !== $ip_address && !empty($user['last_login_ip'])) {
                                            send_security_alert_to_telegram($db, "Admin Login from a NEW IP address!\nAdmin: `{$user['username']}`\nNew IP: `{$ip_address}`\nOld IP: `{$user['last_login_ip']}`");
                                        }
                                        $ip_stmt = $db->prepare("UPDATE users SET last_login_ip = ? WHERE id = ?");
                                        if ($ip_stmt) {
                                            $ip_stmt->bind_param("si", $ip_address, $user['id']);
                                            $ip_stmt->execute();
                                        }
                                    }

                                    // Check if 2FA is enabled
                                    if (!empty($user['google2fa_secret'])) {
                                        // Store temporary login details in session to pass to 2FA page
                                        $_SESSION['temp_2fa_user_id'] = $user['id'];
                                        $_SESSION['temp_2fa_username'] = $user['username'];
                                        $_SESSION['temp_2fa_phone'] = $phone;
                                        $_SESSION['temp_2fa_role'] = $user['role'];
                                        header("Location: verify_2fa.php");
                                        exit();
                                    } else {
                                        // Set final Session Data
                                        session_regenerate_id(true); // Session Fixation ကာကွယ်ရန်
                                        
                                        $_SESSION['user_id'] = $user['id'];
                                        $_SESSION['username'] = $user['username'];
                                        $_SESSION['role'] = $user['role'];
                                        $_SESSION['login_time'] = time(); // Session Timeout အတွက်
                                        unset($_SESSION['permissions']); // လော့ဂ်အင်အသစ်ဝင်တိုင်း ယခင်ခွင့်ပြုချက်များကို ရှင်းထုတ်မည်
                                        
                                        header("Location: index.php");
                                        exit();
                                    }
                                }
                            }
                        } else {
                            $error_message = __('incorrect_password');
                            record_failed_login($db, $ip_address, $phone);
                        }
                    } else {
                        $error_message = __('phone_not_found');
                        record_failed_login($db, $ip_address, $phone);
                    }
                    $stmt->close();
                } else {
                    // Query ကျရှုံးသွားပါက စနစ်မရပ်တန့်စေဘဲ Error Message သာ ပြသပေးမည်
                    // Log the detailed error for the admin
                    error_log("Login page database error: " . $db->error);
                    $error_message = __('system_error_try_again'); // Show a generic error to the user
                }
            }
        } catch (Exception $e) {
            // Catch any exceptions from the Database connection or other operations
            error_log("Login page critical error: " . $e->getMessage());
            $error_message = __('system_error_try_again');
        }
    } else {
        $error_message = __('fill_all_fields');
    }
}

// Set page title
$page_title = __('login_page_title');

// Include the view
require_once __DIR__ . '/src/views/login_view.php';