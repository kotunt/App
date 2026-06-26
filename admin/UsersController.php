<?php

function handle_post_requests($conn) {
    $success_message = "";
    $error_message = "";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return compact('success_message', 'error_message');
    }

    // CSRF Token Check for all POST requests
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
        $error_message = __('csrf_token_mismatch');
        return compact('success_message', 'error_message');
    }

    // Avatar ပြင်ဆင်ရန် / ဖျက်သိမ်းရန်
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_avatar' && !empty($_POST['cropped_avatar_data'])) {
            $target_user_id = intval($_POST['target_user_id'] ?? 0);
            if ($target_user_id > 0) {
                $base64_string = $_POST['cropped_avatar_data'];

                if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $base64_string, $matches)) {
                    $error_message = 'Invalid image format. Only JPEG, PNG, and WEBP are allowed.';
                } else {
                    list($type, $data) = explode(';', $base64_string);
                    list(, $data)      = explode(',', $data);
                    $decoded_data = base64_decode($data);

                    if (strlen($decoded_data) > 5 * 1024 * 1024) {
                        $error_message = 'File size is too large. Maximum is 5MB.';
                    } else {
                        $upload_dir = '../uploads/avatars/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }

                        $ext = $matches[1];
                        if ($ext === 'jpeg') $ext = 'jpg';
                        
                        $temp_file = tempnam(sys_get_temp_dir(), 'admin_avatar_');
                        file_put_contents($temp_file, $decoded_data);
                        
                        $new_filename = 'admin_avatar_' . $target_user_id . '_' . time() . '.' . $ext;
                        $upload_path = $upload_dir . $new_filename;
                        $db_path = 'uploads/avatars/' . $new_filename;

                        if (compressImage($temp_file, $upload_path, 80)) {
                            $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
                            $stmt->bind_param("i", $target_user_id);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            if ($row = $res->fetch_assoc()) {
                                $old_avatar = '../' . ltrim($row['avatar'], '../');
                                if (!empty($row['avatar']) && file_exists($old_avatar) && $old_avatar !== $upload_path) {
                                    unlink($old_avatar);
                                }
                            }
                            $stmt->close();
                            
                            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                            $stmt->bind_param("si", $db_path, $target_user_id);
                            $stmt->execute();
                            $stmt->close();
                            log_activity($_SESSION['user_id'], 'UPDATE_AVATAR', "Updated avatar for User ID: {$target_user_id}");
                            $success_message = sprintf(__('admin_users_avatar_success'), $target_user_id);
                        } else {
                            $error_message = __('admin_users_avatar_error');
                        }

                        if (file_exists($temp_file)) {
                            unlink($temp_file);
                        }
                    }
                }
            }
        } elseif ($_POST['action'] === 'remove_avatar') {
            $target_user_id = intval($_POST['target_user_id'] ?? 0);
            if ($target_user_id > 0) {
                $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
                $stmt->bind_param("i", $target_user_id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $old_avatar = '../' . ltrim($row['avatar'], '../');
                    if (!empty($row['avatar']) && file_exists($old_avatar)) {
                        unlink($old_avatar);
                    }
                }
                $stmt->close();
                
                $stmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
                $stmt->bind_param("i", $target_user_id);
                $stmt->execute();
                $stmt->close();
                log_activity($_SESSION['user_id'], 'REMOVE_AVATAR', "Removed avatar for User ID: {$target_user_id}");
                $success_message = sprintf(__('admin_users_avatar_remove_success'), $target_user_id);
            }
        }
    }

    // အကောင့်သစ်ဖွင့်ရန် (Add New User)
    if (isset($_POST['add_user'])) {
        $new_username = trim($_POST['new_username'] ?? '');
        $new_phone = trim($_POST['new_phone'] ?? '');
        $new_password = $_POST['new_user_password'] ?? '';
        $new_balance = floatval($_POST['initial_balance'] ?? 0);
        $new_vip_level = trim($_POST['new_vip_level'] ?? 'Standard');

        if (!empty($new_username) && !empty($new_phone) && strlen($new_password) >= 6) {
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
            $check_stmt->bind_param("s", $new_phone);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error_message = __('admin_users_phone_exists');
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $new_ref_code = strtoupper(substr(md5(uniqid() . time()), 0, 6));

                $insert_stmt = $conn->prepare("INSERT INTO users (username, phone_number, password, referral_code, balance, verification_status, vip_level) VALUES (?, ?, ?, ?, ?, 'approved', ?)");
                $insert_stmt->bind_param("ssssds", $new_username, $new_phone, $hashed_password, $new_ref_code, $new_balance, $new_vip_level);

                if ($insert_stmt->execute()) {
                    log_activity($_SESSION['user_id'], 'ADD_USER', "Created new user '{$new_username}' (Phone: {$new_phone}) with balance {$new_balance}, VIP: {$new_vip_level}");
                    $success_message = sprintf(__('admin_users_add_success'), $new_username);
                } else {
                    $error_message = __('admin_users_add_error');
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        } else {
            $error_message = __('admin_users_add_validation');
        }
    }

    // CSV ဖြင့် အစုလိုက်အပြုံလိုက် အကောင့်ဖွင့်ရန် (Bulk Import)
    if (isset($_POST['bulk_import_users'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['csv_file']['size'] > 5 * 1024 * 1024) {
                $error_message = 'File size is too large. Maximum is 5MB.';
            } else {
                $file_tmp = $_FILES['csv_file']['tmp_name'];
                $file_ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));

                if ($file_ext === 'csv') {
                    $handle = fopen($file_tmp, "r");
                    fgetcsv($handle, 1000, ","); // Skip header
                    
                    $success_count = 0;
                    $error_count = 0;
                    
                    $conn->begin_transaction();
                    try {
                        $insert_stmt = $conn->prepare("INSERT INTO users (username, phone_number, password, referral_code, balance, verification_status) VALUES (?, ?, ?, ?, ?, 'approved')");
                        $check_stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
                        
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if (count($data) >= 3) {
                                $csv_username = trim($data[0]);
                                $csv_phone = trim($data[1]);
                                $csv_password = trim($data[2]);
                                $csv_balance = isset($data[3]) && is_numeric(trim($data[3])) ? floatval(trim($data[3])) : 0.00;

                                if (!empty($csv_username) && !empty($csv_phone) && strlen($csv_password) >= 6) {
                                    $check_stmt->bind_param("s", $csv_phone);
                                    $check_stmt->execute();
                                    $check_stmt->store_result();
                                    
                                    if ($check_stmt->num_rows == 0) {
                                        $hashed_password = password_hash($csv_password, PASSWORD_DEFAULT);
                                        $new_ref_code = strtoupper(substr(md5(uniqid() . time() . $csv_phone), 0, 6));
                                        
                                        $insert_stmt->bind_param("ssssd", $csv_username, $csv_phone, $hashed_password, $new_ref_code, $csv_balance);
                                        if ($insert_stmt->execute()) {
                                            $success_count++;
                                        } else {
                                            $error_count++;
                                        }
                                    } else {
                                        $error_count++;
                                    }
                                    $check_stmt->free_result();
                                } else {
                                    $error_count++;
                                }
                            }
                        }
                        
                        $conn->commit();
                        $insert_stmt->close();
                        $check_stmt->close();
                        fclose($handle);
                        
                        if ($success_count > 0) {
                            log_activity($_SESSION['user_id'], 'BULK_IMPORT_USERS', "Bulk imported {$success_count} users via CSV.");
                            $success_message = sprintf(__('admin_users_bulk_success'), $success_count) . ($error_count > 0 ? sprintf(__('admin_users_bulk_error_skip'), $error_count) : "");
                        } elseif ($error_count > 0) {
                            $error_message = sprintf(__('admin_users_bulk_error'), $error_count);
                        } else {
                            $error_message = __('admin_users_bulk_empty');
                        }
                        
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = __('admin_users_system_error') . $e->getMessage();
                    }
                } else {
                    $error_message = __('admin_users_invalid_csv');
                }
            }
        } else {
            $error_message = __('admin_users_upload_error');
        }
    }

    // Balance ပြင်ဆင်ရန်
    if (isset($_POST['update_balance'])) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);
        $new_balance = floatval($_POST['new_balance'] ?? -1);

        if ($target_user_id > 0 && $new_balance >= 0) {
            $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->bind_param("di", $new_balance, $target_user_id);
            if ($stmt->execute()) {
                log_activity($_SESSION['user_id'], 'UPDATE_BALANCE', "Updated balance for User ID: {$target_user_id} to {$new_balance}");
                $success_message = sprintf(__('admin_users_balance_success'), $target_user_id);
            } else {
                $error_message = __('admin_users_update_error') . $conn->error;
            }
            $stmt->close();
        } else {
            $error_message = __('admin_users_invalid_amount');
        }
    }

    // Password ပြင်ဆင်ရန်
    if (isset($_POST['update_password'])) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);
        $new_password = trim($_POST['new_password'] ?? '');

        if ($target_user_id > 0 && strlen($new_password) >= 6) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $target_user_id);
            if ($stmt->execute()) {
                log_activity($_SESSION['user_id'], 'UPDATE_PASSWORD', "Reset password for User ID: {$target_user_id}");
                $success_message = sprintf(__('admin_users_password_success'), $target_user_id);
            } else {
                $error_message = __('admin_users_update_error') . $conn->error;
            }
            $stmt->close();
        } else {
            $error_message = __('admin_users_invalid_password');
        }
    }

    // Phone ပြင်ဆင်ရန်
    if (isset($_POST['update_phone'])) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);
        $new_phone = trim($_POST['new_phone'] ?? '');

        if ($target_user_id > 0 && !empty($new_phone)) {
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ? AND id != ?");
            $check_stmt->bind_param("si", $new_phone, $target_user_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error_message = __('admin_users_phone_used');
            } else {
                $stmt = $conn->prepare("UPDATE users SET phone_number = ? WHERE id = ?");
                $stmt->bind_param("si", $new_phone, $target_user_id);
                if ($stmt->execute()) {
                    log_activity($_SESSION['user_id'], 'UPDATE_PHONE', "Updated phone number for User ID: {$target_user_id} to {$new_phone}");
                    $success_message = sprintf(__('admin_users_phone_success'), $target_user_id);
                } else {
                    $error_message = __('admin_users_update_error') . $conn->error;
                }
                $stmt->close();
            }
            $check_stmt->close();
        } else {
            $error_message = __('admin_users_invalid_phone_input');
        }
    }

    // User အကောင့်သို့ ဝင်ရောက်ရန်
    if (isset($_POST['login_as_user'])) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);

        if ($target_user_id > 0) {
            $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
            $stmt->bind_param("i", $target_user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                log_activity($_SESSION['user_id'], 'LOGIN_AS_USER', "Admin logged in as User ID: {$target_user_id} ({$user['username']})");
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                unset($_SESSION['permissions']);
                header("Location: index.php");
                exit();
            } else {
                $error_message = __('admin_users_not_found');
            }
            $stmt->close();
        }
    }

    // Ban/Unban ပြုလုပ်ရန်
    if (isset($_POST['toggle_ban'])) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);
        $current_status = intval($_POST['current_status'] ?? 0);
        $new_status = $current_status ? 0 : 1;

        if ($target_user_id > 1) {
            $stmt = $conn->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_status, $target_user_id);
            if ($stmt->execute()) {
                $status_text = $new_status ? __('admin_users_ban') : __('admin_users_unban');
                log_activity($_SESSION['user_id'], 'TOGGLE_BAN', "Set ban status to {$status_text} for User ID: {$target_user_id}");
                $success_message = sprintf(__('admin_users_ban_success'), $target_user_id, $status_text);
            }
            $stmt->close();
        } else {
            $error_message = __('admin_users_cannot_ban_admin');
        }
    }

    // Verify User လုပ်ရန်
    if (isset($_POST['verify_user'])) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);
        $action = $_POST['verify_action'] ?? '';
        
        if (in_array($action, ['approved', 'rejected']) && $target_user_id > 0) {
            $stmt = $conn->prepare("UPDATE users SET verification_status = ? WHERE id = ?");
            $stmt->bind_param("si", $action, $target_user_id);
            if ($stmt->execute()) {
                $status_text = $action == 'approved' ? __('admin_users_approve') : __('admin_users_reject');
                $success_message = sprintf(__('admin_users_verify_success'), $target_user_id, $status_text);
            }
            $stmt->close();
        }
    }

    // User အကောင့်ကို အပြီးတိုင်ဖျက်သိမ်းရန်
    if (isset($_POST['delete_user'])) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);

        if ($target_user_id > 1) {
            $stmt = $conn->prepare("SELECT avatar, username FROM users WHERE id = ?");
            $stmt->bind_param("i", $target_user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $deleted_username = "Unknown";
            if ($row = $res->fetch_assoc()) {
                $deleted_username = $row['username'];
                $old_avatar = '../' . ltrim($row['avatar'], '../');
                if (!empty($row['avatar']) && file_exists($old_avatar)) {
                    unlink($old_avatar);
                }
            }
            $stmt->close();

            $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $del_stmt->bind_param("i", $target_user_id);
            if ($del_stmt->execute()) {
                log_activity($_SESSION['user_id'], 'DELETE_USER', "Deleted User ID: {$target_user_id} ({$deleted_username})");
                $success_message = sprintf(__('admin_users_delete_success'), $deleted_username);
            } else {
                $error_message = __('admin_users_delete_error');
            }
            $del_stmt->close();
        } else {
            $error_message = __('admin_users_cannot_delete_admin');
        }
    }

    return compact('success_message', 'error_message');
}

?>