# Thai 2D/3D Lottery & Betting Platform

ဤ Project သည် ထိုင်း 2D/3D ကံစမ်းမဲစနစ်ကို အခြေခံထားသော ဘက်စုံသုံး Betting Platform တစ်ခုဖြစ်သည်။ အသုံးပြုသူများအတွက် ရိုးရှင်းလွယ်ကူသော Interface နှင့်အတူ Admin များအတွက် အသေးစိတ်ကျသော စီမံခန့်ခွဲမှုစနစ်များ ပါဝင်ပါသည်။

## နည်းပညာများ (Technology Stack)

*   **Backend:** PHP (8.0+)
*   **Frontend:** HTML, JavaScript, **Tailwind CSS (via CDN)**
*   **Database:** MySQL / MariaDB
*   **PHP Dependencies:** Composer
*   **JS Libraries:** SweetAlert2, Canvas Confetti

## Project ၏ ဖွဲ့စည်းပုံ (Directory Structure)

-   `/`: အဓိကဝင်ထွက်သည့် PHP ဖိုင်များ (login.php, register.php, index.php)။
-   `/admin/`: Admin Panel နှင့် သက်ဆိုင်သော စာမျက်နှာများ နှင့် လုပ်ဆောင်ချက်များ။
-   `/api/`: Mobile Application သို့မဟုတ် ပြင်ပစနစ်များအတွက် API Endpoint များ။
-   `/assets/`: CSS, JavaScript, ပုံများကဲ့သို့သော Frontend ဆိုင်ရာ ဖိုင်များ။
-   `/core/`: Database ချိတ်ဆက်ခြင်း၊ အဓိက လုပ်ဆောင်ချက်များ နှင့် Configuration များအတွက် အရေးကြီးဆုံးဖိုင်များ။
-   `/cron/`: အလိုအလျောက် အလုပ်လုပ်ရန်လိုအပ်သော Script များ (ဥပမာ- အဖြေတိုက်ခြင်း၊ Cashback ပေးခြင်း)။
-   `/includes/`: အသုံးပြုသည့် စာမျက်နှာတိုင်းတွင် ပြန်လည်အသုံးပြုသော အစိတ်အပိုင်းများ (Header, Footer, SEO)။
-   `/lang/`: ဘာသာစကားဆိုင်ရာ (Localization) ဖိုင်များ (en.php, mm.php)။
--   `/src/`: Controller နှင့် View များ။

## အဓိက Features များ

### အသုံးပြုသူများအတွက်
-   **လုံခြုံရေး:** အကောင့်ဖွင့်ခြင်း၊ Login, PIN နံပါတ် နှင့် Google Authenticator ဖြင့် 2FA သတ်မှတ်ခြင်း။
-   **ငွေကြေးလွှဲပြောင်းမှု:** QR Code ဖြင့် ငွေသွင်းခြင်း၊ Admin ထံ ငွေထုတ်ရန် တောင်းဆိုခြင်း၊ User အချင်းချင်း ငွေလွှဲခြင်း။
-   **လောင်းကစား:** 2D (မနက်/ညနေ) နှင့် 3D ဂဏန်းများ ထိုးခြင်း၊ မိမိထိုးထားသော မှတ်တမ်းများ ပြန်လည်ကြည့်ရှုခြင်း။
-   **မှတ်တမ်းများ:** ငွေသွင်း/ထုတ်/လွှဲ မှတ်တမ်းများ နှင့် Commission ရရှိမှုမှတ်တမ်းများ ကြည့်ရှုခြင်း။
-   **VIP နှင့် မိတ်ဆက်စနစ်:** မိမိ၏ မိတ်ဆက်ကုဒ်ဖြင့် အခြားသူများကို ဖိတ်ခေါ်ခြင်း၊ လောင်းကြေးပမာဏအလိုက် VIP အဆင့်တိုးမြှင့်ခြင်း နှင့် Cashback ရရှိခြင်း။
-   **အခြား:** ကိုယ်ရေးအချက်အလက် နှင့် Avatar ပုံ ပြင်ဆင်ခြင်း၊ System မှ ပေးပို့သော Notification များ ကြည့်ရှုခြင်း။

### Admin များအတွက်
-   **Dashboard:** Real-time ဝင်ငွေ၊ ထွက်ငွေ၊ User အရေအတွက် နှင့် နောက်ဆုံးရ လောင်းကြေးအခြေအနေများ ကြည့်ရှုခြင်း။
-   **စီမံခန့်ခွဲမှု:**
    -   **User Management:** အသုံးပြုသူများ၏ စာရင်း၊ ငွေကြေး၊ မှတ်တမ်းများ ကြည့်ရှုတည်းဖြတ်ခြင်း၊ Ban/Unban လုပ်ခြင်း။
    -   **Finance Management:** ငွေသွင်း/ထုတ် တောင်းဆိုမှုများကို Approve/Reject လုပ်ခြင်း၊ ဘဏ်စာရင်းများ ထည့်သွင်းခြင်း။
    -   **Bet Management:** 2D/3D အဖြေများ ကိုယ်တိုင်ကြေညာခြင်း၊ ပေါက်ကြေးရှင်းတမ်းများ ကြည့်ရှုခြင်း၊ Overlimit ဖြစ်နေသောဂဏန်းများ စစ်ဆေးခြင်း။
-   **ချိန်ညှိမှုများ (Settings):**
    -   Website Maintenance Mode (ပိတ်/ဖွင့်)။
    -   VIP အဆင့် နှင့် Cashback ရာခိုင်နှုန်းများ သတ်မှတ်ခြင်း။
    -   Referral Commission ရာခိုင်နှုန်းများ သတ်မှတ်ခြင်း။
    -   Telegram Bot ဖြင့် ချိတ်ဆက်ခြင်း။
    -   API URL များ နှင့် အခြားသော နည်းပညာဆိုင်ရာ ချိန်ညှိမှုများ။
-   **လုံခြုံရေး:** Sub-Admin များခန့်အပ်ခြင်း နှင့် Permission များ စီမံခန့်ခွဲခြင်း၊ Admin များ၏ Activity Log များကို ကြည့်ရှုခြင်း။

## အသုံးပြုပုံ လမ်းညွှန် (Usage Guide)
 
### အသုံးပြုသူများအတွက် (For Users)
1.  **အကောင့်ဖွင့်ခြင်း နှင့် Login:** `register.php` မှတစ်ဆင့် အကောင့်ဖွင့်ပြီး `login.php` တွင် ဝင်ရောက်ပါ။ လုံခြုံရေးအတွက် PIN နှင့် 2FA ကို `setup_pin.php` နှင့် `setup_2fa.php` တွင် သတ်မှတ်နိုင်ပါသည်။
2.  **ငွေသွင်းခြင်း:** `deposit.php` စာမျက်နှာတွင် QR code (သို့မဟုတ်) ပေးထားသော ဘဏ်အကောင့်သို့ ငွေလွှဲ၍ ငွေဖြည့်သွင်းနိုင်ပါသည်။
3.  **2D/3D ထိုးခြင်း:** `2d_bet.php` သို့မဟုတ် `3d_bet.php` စာမျက်နှာများတွင် မိမိနှစ်သက်ရာ ဂဏန်းများကို ရွေးချယ်၍ လောင်းကစားနိုင်ပါသည်။
4.  **မှတ်တမ်းများ ကြည့်ရှုခြင်း:** `bet_history.php` တွင် လောင်းကြေးမှတ်တမ်းများ၊ `transaction_history.php` တွင် ငွေသွင်း/ထုတ်မှတ်တမ်းများ၊ `commissions_history.php` တွင် မိတ်ဆက်သူမှရရှိသော ကော်မရှင်များကို ကြည့်ရှုနိုင်ပါသည်။
5.  **ငွေထုတ်ခြင်း/လွှဲခြင်း:** `withdraw.php` တွင် ငွေထုတ်ရန် တောင်းဆိုနိုင်ပြီး `transfer.php` တွင် အခြားအသုံးပြုသူများထံ ငွေလွှဲနိုင်ပါသည်။

### Admin များအတွက် (For Admins)
1.  **Login:** `admin/index.php` တွင် Admin အကောင့်ဖြင့် ဝင်ရောက်ပါ။
2.  **Dashboard:** `admin/admin_dashboard.php` တွင် စနစ်၏ ခြုံငုံသုံးသပ်ချက်ကို ကြည့်ရှုနိုင်ပါသည်။
3.  **User စီမံခန့်ခွဲမှု:** `admin/admin_users.php` တွင် အသုံးပြုသူများ၏ အချက်အလက်များကို ကြည့်ရှု၊ တည်းဖြတ်၊ Ban/Unban လုပ်နိုင်ပါသည်။
4.  **ငွေကြေးစီမံခန့်ခွဲမှု:** `admin/admin_deposit.php` နှင့် `admin/admin_withdraw.php` တို့တွင် ငွေသွင်း/ထုတ် တောင်းဆိုမှုများကို စီမံခန့်ခွဲနိုင်ပါသည်။
5.  **ရလဒ်ကြေညာခြင်း:** `admin/admin_declare_result.php` တွင် 2D/3D အဖြေများကို ထည့်သွင်းကြေညာနိုင်ပါသည်။
6.  **ချိန်ညှိမှုများ:** `admin/admin_settings.php` နှင့် ဆက်စပ် စာမျက်နှာများတွင် Website ၏ အဓိကလုပ်ဆောင်ချက်များ နှင့် စနစ်ပိုင်းဆိုင်ရာ ချိန်ညှိမှုများကို ပြုလုပ်နိုင်ပါသည်။

## PWA (Progressive Web App) Features

ဤ Project သည် PWA (Progressive Web App) အဖြစ် အလုပ်လုပ်နိုင်ရန် အောက်ပါတို့ကို ထောက်ပံ့ပေးထားသည်-
-   **Offline Support:** `sw.js` (Service Worker) ကို အသုံးပြု၍ အင်တာနက်မရှိသည့်အချိန်တွင်ပင် အချို့သော အချက်အလက်များကို Cache လုပ်၍ အသုံးပြုနိုင်ပါသည်။
-   **Installable:** `manifest.json` ကို အသုံးပြု၍ Mobile Phone ၏ Home Screen သို့ Application အဖြစ် တိုက်ရိုက် Install လုပ်နိုင်ပါသည်။
-   **Notifications:** Push Notification များ ပေးပို့ခြင်းကို Support လုပ်ပါသည်။

## ဘာသာစကား နှင့် Localization (Language & Localization)

Project တွင် ဘာသာစကားမျိုးစုံ ထောက်ပံ့ရန် ဒီဇိုင်းပြုလုပ်ထားပါသည်။
-   `/lang` directory အောက်တွင် `en.php` (English) နှင့် `mm.php` (Myanmar) ဖိုင်များ ပါဝင်ပါသည်။
-   အသုံးပြုသူများသည် Website တွင် ဘာသာစကားကို ရွေးချယ်နိုင်ပြီး ၎င်းတို့၏ ရွေးချယ်မှုကို Session တွင် မှတ်ထားပါသည်။
-   ဘာသာစကားအသစ်များ ထည့်သွင်းလိုပါက `lang/` directory အောက်တွင် `xx.php` ဟု ဖိုင်အသစ်ပြုလုပ်၍ `language.php` တွင် ထည့်သွင်းနိုင်ပါသည်။

## Project ကို Install လုပ်နည်း (Installation)

### Prerequisites

-   Web Server (Apache, Nginx) or Docker
-   PHP (8.0 or newer) with mysqli extension
-   MySQL or MariaDB

### Installation Steps
1.  **Project ကို Clone လုပ်ပါ**

3.  **Environment File ကို Setup လုပ်ပါ**
    `.env.example` ဖိုင်ကို copy ကူးပြီး `.env` ဟု အမည်ပြောင်းပါ။
    ```bash
    cp .env.example .env
    ```
    ထို့နောက် `.env` ဖိုင်ထဲတွင် သင်၏ Database ချိတ်ဆက်မှု အချက်အလက်များကို မှန်ကန်အောင် ထည့်သွင်းပါ။

4.  **Database ကို Import လုပ်ပါ**
    သင်၏ Database Management Tool (ဥပမာ: phpMyAdmin) ကို အသုံးပြု၍ `database/thai_2d3d_db.sql` ဖိုင်ကို import လုပ်ပါ။

5.  **Default Admin Password သတ်မှတ်ပါ**
    Database import လုပ်ပြီးပါက Default Admin Account (`09000000001`) တစ်ခုပါဝင်လာမည်ဖြစ်သည်။ လုံခြုံရေးအတွက် အောက်ပါ command ကို run ၍ password အသစ်တစ်ခု သတ်မှတ်ပါ။

    ```bash
    php scripts/set_admin_password.php your_new_strong_password
    ```
    > **အရေးကြီး:** `your_new_strong_password` နေရာတွင် သင်အသုံးပြုလိုသော ခိုင်မာသည့် password ကို အစားထိုးထည့်သွင်းပါ။ ဤနည်းလမ်းသည် web server မှတဆင့် script run ခြင်းထက် ပိုမိုလုံခြုံပါသည်။

## Docker ဖြင့် အသုံးပြုခြင်း (Recommended for Development)

Docker ကို အသုံးပြုခြင်းဖြင့် PHP, MySQL, Composer တို့ကို သင့်စက်ထဲတွင် သီးသန့် install လုပ်ရန်မလိုဘဲ project ကို အလွယ်တကူ run နိုင်ပါသည်။

### Prerequisites
- Docker
- Docker Compose

### Docker Installation Steps
1.  **Environment File ကို Setup လုပ်ပါ**
    `.env.example` ဖိုင်ကို copy ကူးပြီး `.env` ဟု အမည်ပြောင်းပါ။ ထို့နောက် `.env` ဖိုင်ထဲတွင် `DB_HOST=db` ဟု သတ်မှတ်ထားကြောင်း သေချာပါစေ။

2.  **Docker Containers များကို စတင်ပါ**
    ```bash
    docker-compose up -d --build
    ```

3.  **Database ကို Setup လုပ်ပါ**
    Browser တွင် `http://localhost:8000/autosetup-db.php` သို့ ဝင်ရောက်ပြီး setup password (`88888888`) ကို ရိုက်ထည့်၍ database table များကို တည်ဆောက်ပါ။

    *(မှတ်ချက်: `scripts/set_admin_password.php` ဖိုင်ကို အောက်ပါအတိုင်း ပြုလုပ်ရန်လိုအပ်ပါသည်။)*
    ```php
    <?php
    // scripts/set_admin_password.php
    if ($argc < 2) {
        echo "Usage: php scripts/set_admin_password.php <new_password>\n";
        exit(1);
    }
    require_once __DIR__ . '/../core/db_connect.php';
    $new_password = $argv[1];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $admin_phone = '09000000001';
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE phone_number = ? AND role = 'admin'");
    $stmt->bind_param('ss', $hashed_password, $admin_phone);
    if ($stmt->execute()) {
        echo "Admin password updated successfully.\n";
    } else {
        echo "Error updating password: " . $stmt->error . "\n";
    }
    $stmt->close();
    $conn->close();
    ?>
    ```

6.  **Web Server ကို Configure လုပ်ပါ**
    Local Development အတွက် PHP built-in server ကို အသုံးပြုနိုင်သည်၊ သို့မဟုတ် Apache/Nginx တွင် Virtual Host အဖြစ် ထည့်သွင်း၍ အသုံးပြုနိုင်သည်။
    ```bash
    php -S localhost:8000 -t .
```


## Cron Jobs (Scheduled Tasks)

ဤ Project တွင် အလိုအလျောက်လုပ်ဆောင်ရန် လိုအပ်သော Cron Job များပါဝင်သည်။ Server တွင် ၎င်းတို့ကို သတ်မှတ်ရန်လိုအပ်သည်။

1.  **`cron/auto_result_update.php`**
    *   **အလုပ်လုပ်ပုံ:** Admin မှ သတ်မှတ်ထားသော API မှ 2D နှင့် 3D အဖြေများကို ရယူသည်။ ထို့နောက် ပေါက်ဂဏန်းများကို စစ်ဆေး၍ အနိုင်ရသူများအား လျော်ကြေးငွေများ အလိုအလျောက် ထည့်သွင်းပေးသည်။ ရလဒ်များကို Telegram Channel သို့လည်း ပေးပို့နိုင်သည်။
    *   **အချိန်:** 2D အတွက် တစ်နေ့နှစ်ကြိမ် (ဥပမာ: 12:05 PM, 4:35 PM), 3D အတွက် သတ်မှတ်ထားသော ရက်တွင် run ရန်။

2.  **`cron/auto_cashback.php`**
    *   **အလုပ်လုပ်ပုံ:** User များ၏ တစ်ပတ်အတွင်း ရှုံးငွေ (Net Loss) ကို တွက်ချက်ပြီး ၎င်းတို့၏ VIP အဆင့်အလိုက် Cashback ရာခိုင်နှုန်းကို ပြန်လည်ထည့်သွင်းပေးသည်။
    *   **အချိန်:** တစ်ပတ်တစ်ကြိမ် (ဥပမာ: တနင်္လာနေ့ မနက်တိုင်း)။

3.  **`cron/session_notifier.php`**
    *   **အလုပ်လုပ်ပုံ:** 3D ပွဲစဉ် မပိတ်မီ မိနစ် ၃၀ အလိုတွင် Admin ၏ Telegram သို့ သတိပေးချက် ပေးပို့သည်။
    *   **အချိန်:** ၅ မိနစ် သို့မဟုတ် ၁၀ မိနစ်လျှင် တစ်ကြိမ်။

## ပြဿနာဖြေရှင်းခြင်း (Troubleshooting)

*   **Blank Page / White Screen:**
    *   PHP Error များကို စစ်ဆေးပါ။ `php.ini` တွင် `display_errors = On` ဟု သတ်မှတ်ပြီး `error_reporting = E_ALL` ဟု ပြုလုပ်ပါ။
    *   Web server (Apache/Nginx) error log များကို စစ်ဆေးပါ။
*   **Database Connection Error:**
    *   `.env` ဖိုင်ထဲရှိ `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` တို့ မှန်ကန်ကြောင်း သေချာပါစေ။
    *   MySQL/MariaDB server အလုပ်လုပ်နေကြောင်း စစ်ဆေးပါ။
*   **Cron Jobs မလုပ်ဆောင်ပါက:**
    *   `crontab -e` ဖြင့် Cron Job ချိန်ညှိမှုများ မှန်ကန်ကြောင်း စစ်ဆေးပါ။
    *   Cron command တွင် PHP interpreter path မှန်ကန်ကြောင်း သေချာပါစေ။ (ဥပမာ: `/usr/bin/php /path/to/project/cron/auto_result_update.php`)။

## လုံခြုံရေးဆိုင်ရာ အကြံပြုချက်များ (Security Best Practices)

*   **Default Admin Password ကို ချက်ချင်းပြောင်းလဲပါ။**
*   **`.env` ဖိုင်ကို Version Control (Git) ထဲသို့ လုံးဝမထည့်ပါနှင့်။** (`.gitignore` တွင် ထည့်သွင်းထားပြီးဖြစ်သင့်သည်)။
*   **Web Server Configuration ကို လုံခြုံအောင်ထားပါ။** (ဥပမာ- Directory Listing ကို ပိတ်ခြင်း၊ Sensitive ဖိုင်များသို့ တိုက်ရိုက်ဝင်ရောက်ခွင့်ကို ပိတ်ခြင်း)။
*   **SQL Injection, XSS ကဲ့သို့သော Web Vulnerabilities များမှ ကာကွယ်ရန် Code Review ပုံမှန်ပြုလုပ်ပါ။** (Prepared Statements များကို သုံးထားပြီးဖြစ်သင့်သည်)။
*   **Production Server တွင် HTTPS (SSL Certificate) ကို အသုံးပြုပါ။**
*   **PHP နှင့် Library များကို နောက်ဆုံး Version အမြဲတမ်း Update လုပ်ပါ။**
    -   **Username/Phone:** `09000000001`
    -   **Password:** စနစ်ထည့်သွင်းသူမှ သတ်မှတ်ရန်လိုအပ်သည်။

    **Admin Password အသစ်သတ်မှတ်ရန်:**
    Project root directory တွင် `set_admin_pass.php` ဟု file အသစ်တစ်ခုပြုလုပ်ပြီး အောက်ပါ code ကိုထည့်ပါ။
    ```php
    <?php
    require_once __DIR__ . '/core/db_connect.php';
    $new_password = 'your_new_strong_password'; // ဤနေရာတွင် Password အသစ်ထည့်ပါ
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $admin_phone = '09000000001';
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE phone_number = ? AND role = 'admin'");
    $stmt->bind_param('ss', $hashed_password, $admin_phone);
    
    if ($stmt->execute()) {
        echo "Admin password updated successfully to: " . $new_password;
    } else {
        echo "Error updating password: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
    ?>
    ```
    Browser မှ `http://your-site.test/set_admin_pass.php` ကို run ပါ။ အောင်မြင်ပါက **ထိုဖိုင်အား ဖျက်ပစ်ရန် မမေ့ပါနှင့်။**

6.  **Web Server ကို Configure လုပ်ပါ။**
    Local Development အတွက် PHP built-in server ကို အသုံးပြုနိုင်သည်၊ သို့မဟုတ် Apache/Nginx တွင် Virtual Host အဖြစ် ထည့်သွင်း၍ အသုံးပြုနိုင်သည်။
    ```bash
    php -S localhost:8000
    ```

## Cron Jobs (Scheduled Tasks)

ဤ Project တွင် အလိုအလျောက်လုပ်ဆောင်ရန် လိုအပ်သော Cron Job များပါဝင်သည်။ Server တွင် ၎င်းတို့ကို သတ်မှတ်ရန်လိုအပ်သည်။

1.  **`cron/auto_result_update.php`**
    -   **အလုပ်လုပ်ပုံ:** Admin မှ သတ်မှတ်ထားသော API မှ 2D နှင့် 3D အဖြေများကို ရယူသည်။ ထို့နောက် ပေါက်ဂဏန်းများကို စစ်ဆေး၍ အနိုင်ရသူများအား လျော်ကြေးငွေများ အလိုအလျောက် ထည့်သွင်းပေးသည်။ ရလဒ်များကို Telegram Channel သို့လည်း ပေးပို့နိုင်သည်။
    -   **အချိန်:** 2D အတွက် တစ်နေ့နှစ်ကြိမ် (ဥပမာ: 12:05 PM, 4:35 PM), 3D အတွက် သတ်မှတ်ထားသော ရက်တွင် run ရန်။

2.  **`cron/auto_cashback.php`**
    -   **အလုပ်လုပ်ပုံ:** User များ၏ တစ်ပတ်အတွင်း ရှုံးငွေ (Net Loss) ကို တွက်ချက်ပြီး ၎င်းတို့၏ VIP အဆင့်အလိုက် Cashback ရာခိုင်နှုန်းကို ပြန်လည်ထည့်သွင်းပေးသည်။
    -   **အချိန်:** တစ်ပတ်တစ်ကြိမ် (ဥပမာ: တနင်္လာနေ့ မနက်တိုင်း)။

3.  **`cron/session_notifier.php`**
    -   **အလုပ်လုပ်ပုံ:** 3D ပွဲစဉ် မပိတ်မီ မိနစ် ၃၀ အလိုတွင် Admin ၏ Telegram သို့ သတိပေးချက် ပေးပို့သည်။
    -   **အချိန်:** ၅ မိနစ် သို့မဟုတ် ၁၀ မိနစ်လျှင် တစ်ကြိမ်။

## ပြဿနာဖြေရှင်းခြင်း (Troubleshooting)

-   **Blank Page / White Screen:**
    -   PHP Error များကို စစ်ဆေးပါ။ `php.ini` တွင် `display_errors = On` ဟု သတ်မှတ်ပြီး `error_reporting = E_ALL` ဟု ပြုလုပ်ပါ။
    -   Web server (Apache/Nginx) error log များကို စစ်ဆေးပါ။
-   **Database Connection Error:**
    -   `.env` file ထဲရှိ `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` တို့ မှန်ကန်ကြောင်း သေချာပါစေ။
    -   MySQL/MariaDB server အလုပ်လုပ်နေကြောင်း စစ်ဆေးပါ။
-   **Cron Jobs မလုပ်ဆောင်ပါက:**
    -   `crontab -e` ဖြင့် Cron Job ချိန်ညှိမှုများ မှန်ကန်ကြောင်း စစ်ဆေးပါ။
    -   Cron command တွင် PHP interpreter path မှန်ကန်ကြောင်း သေချာပါစေ။ (ဥပမာ: `/usr/bin/php /path/to/project/cron/auto_result_update.php`)။

## လုံခြုံရေးဆိုင်ရာ အကြံပြုချက်များ (Security Best Practices)

-   **Default Admin Password ကို ချက်ချင်းပြောင်းလဲပါ။**
-   **`set_admin_pass.php` (သို့မဟုတ်) ထိုကဲ့သို့သော Utility ဖိုင်များကို အသုံးပြုပြီးပါက ချက်ချင်း ဖျက်ပစ်ပါ။**
-   **`.env` file ကို Version Control (Git) ထဲသို့ လုံးဝမထည့်ပါနှင့်။** (`.gitignore` တွင် ထည့်သွင်းထားပြီးဖြစ်သင့်သည်)။
-   **Web Server Configuration ကို လုံခြုံအောင်ထားပါ။** (ဥပမာ- Directory Listing ကို ပိတ်ခြင်း၊ Sensitive ဖိုင်များသို့ တိုက်ရိုက်ဝင်ရောက်ခွင့်ကို ပိတ်ခြင်း)။
-   **SQL Injection, XSS ကဲ့သို့သော Web Vulnerabilities များမှ ကာကွယ်ရန် Code Review ပုံမှန်ပြုလုပ်ပါ။** (Prepared Statements များကို သုံးထားပြီးဖြစ်သင့်သည်)။
-   **HTTPS ကို အသုံးပြုပါ။** (SSL Certificate ထည့်သွင်းပါ)။
-   **PHP Version ကို နောက်ဆုံးပေါ် အမြဲတမ်းထားပါ။**
