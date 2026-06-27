# Thai 2D/3D Lottery & Betting Platform

ဤ Project သည် ထိုင်း 2D/3D စနစ်ပါဝင်သော ဘက်စုံသုံး Betting Platform တစ်ခုဖြစ်သည်။ အသုံးပြုသူများအတွက် လွယ်ကူသော Interface နှင့် Admin များအတွက် အသေးစိတ်ကျသော ထိန်းချုပ်မှုများ ပါဝင်သည်။

## Project ၏ ဖွဲ့စည်းပုံ (Directory Structure)

Project ၏ အဓိက ဖိုင်တွဲများ နှင့် ၎င်းတို့၏ အလုပ်လုပ်ပုံကို အောက်တွင်ဖော်ပြထားသည်။

-   `/`: အဓိကဝင်ထွက်သည့် PHP ဖိုင်များ (login.php, register.php, index.php)။
-   `/admin/`: Admin Panel နှင့် သက်ဆိုင်သော စာမျက်နှာများ နှင့် လုပ်ဆောင်ချက်များ။
-   `/api/`: Mobile Application သို့မဟုတ် ပြင်ပစနစ်များအတွက် API Endpoint များ။
-   `/assets/`: CSS, JavaScript, ပုံများကဲ့သို့သော Frontend ဆိုင်ရာ ဖိုင်များ။
-   `/core/`: Database ချိတ်ဆက်ခြင်း၊ အဓိက လုပ်ဆောင်ချက်များ နှင့် Configuration များအတွက် အရေးကြီးဆုံးဖိုင်များ။
-   `/cron/`: အလိုအလျောက် အလုပ်လုပ်ရန်လိုအပ်သော Script များ (ဥပမာ- အဖြေတိုက်ခြင်း၊ Cashback ပေးခြင်း)။
-   `/includes/`: အသုံးပြုသည့် စာမျက်နှာတိုင်းတွင် ပြန်လည်အသုံးပြုသော အစိတ်အပိုင်းများ (Header, Footer, SEO)။
-   `/lang/`: ဘာသာစကားဆိုင်ရာ (Localization) ဖိုင်များ (en.php, mm.php)။
-   `/src/`: MVC Pattern ပုံစံဖြင့် ရေးသားထားသော Controller နှင့် View များ။
-   `/vendor/`: Composer ဖြင့် Install လုပ်ထားသော PHP Library များ။

## အဓိက Features များ (အသေးစိတ်)

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

## နည်းပညာများ (Technology Stack)

-   **Backend:** PHP
-   **Frontend:** HTML, JavaScript, **Tailwind CSS (via CDN)**
-   **Database:** MySQL / MariaDB
-   **PHP Dependencies:** Composer
-   **JS Libraries:** SweetAlert2, Canvas Confetti

## Project ကို Install လုပ်နည်း

### Prerequisites

-   Web Server (Apache, Nginx)
-   PHP (8.0 or newer)
-   MySQL or MariaDB
-   Composer

### Installation Steps

1.  **Project ကို Clone လုပ်ပါ။**
    ```bash
    git clone <repository-url>
    cd <project-directory>
    ```

2.  **PHP Dependencies များကို Install လုပ်ပါ။**
    ```bash
    composer install
    ```
    *(မှတ်ချက်: `package.json` ပါဝင်သော်လည်း `npm install` မလိုအပ်ပါ။ Frontend styling အတွက် Tailwind CSS CDN ကို တိုက်ရိုက်အသုံးပြုထားပါသည်။)*

3.  **Environment File ကို Setup လုပ်ပါ။**
    `.env.example` file ကို copy ကူးပြီး `.env` ဟု အမည်ပြောင်းပါ။
    ```bash
    cp .env.example .env
    ```
    ထို့နောက် `.env` file ထဲတွင် သင်၏ Database အချက်အလက်များကို မှန်ကန်အောင် ထည့်သွင်းပါ။

4.  **Database ကို Import လုပ်ပါ။**
    သင်၏ Database Management Tool (ဥပမာ: phpMyAdmin) ကို အသုံးပြု၍ `thai_2d3d_db.sql` file ကို import လုပ်ပါ။

5.  **Default Admin Account**
    Database import လုပ်ပြီးပါက Default Admin Account တစ်ခုပါဝင်လာမည်ဖြစ်သည်။
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
