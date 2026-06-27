# Thai 2D/3D Lottery & Betting Platform

ဤ Project သည် ထိုင်း 2D/3D စနစ်ပါဝင်သော ဘက်စုံသုံး Betting Platform တစ်ခုဖြစ်သည်။ အသုံးပြုသူများအတွက် လွယ်ကူသော Interface နှင့် Admin များအတွက် အသေးစိတ်ကျသော ထိန်းချုပ်မှုများ ပါဝင်သည်။

## အဓိက Features များ

### အသုံးပြုသူများအတွက်
-   🔐 လုံခြုံသော အကောင့်ဖွင့်ခြင်း နှင့် Login စနစ်
-   🔑 PIN နံပါတ် နှင့် Two-Factor Authentication (2FA)
-   💰 ငွေသွင်းခြင်း၊ ထုတ်ခြင်း နှင့် လွှဲပြောင်းခြင်း
-   📊 2D နှင့် 3D ထီထိုးခြင်း
-   📜 လောင်းကြေးမှတ်တမ်း နှင့် ငွေသွင်းငွေထုတ်မှတ်တမ်းများ ကြည့်ရှုခြင်း
-   🤝 မိတ်ဆက်သူအလိုက် Commission ရရှိခြင်း
-   👤 ကိုယ်ရေးအချက်အလက် ပြင်ဆင်ခြင်း

### Admin များအတွက်
-   📈 Real-time Dashboard ဖြင့် အချက်အလက်ခြုံငုံကြည့်ရှုခြင်း
-   👥 အသုံးပြုသူများ၊ ငွေကြေး နှင့် လောင်းကြေးများအား စီမံခန့်ခွဲခြင်း
-   ⚙️ Website ဆိုင်ရာ အထွေထွေချိန်ညှိမှုများ (Maintenance, ဘဏ်စာရင်းများ, ကြေငြာချက်များ)
-   📊 Report များ နှင့် မှတ်တမ်းများ ထုတ်ယူခြင်း
-   📢 အသုံးပြုသူများထံ Notification ပေးပို့ခြင်း
-   👮🏻 Sub-Admin များခန့်အပ်ခြင်း နှင့် Permission များ သတ်မှတ်ခြင်း

## နည်းပညာများ (Technology Stack)

-   **Backend:** PHP
-   **Frontend:** HTML, CSS, JavaScript, Tailwind CSS
-   **Database:** MySQL / MariaDB
-   **Dependencies:** Composer (PHP), NPM (Node.js)

## Project ကို Install လုပ်နည်း

Project ကို Local Computer တွင် Run ရန် အောက်ပါအဆင့်များအတိုင်း လုပ်ဆောင်ပါ။

### Prerequisites

-   Web Server (Apache, Nginx)
-   PHP (8.0 or newer)
-   MySQL or MariaDB
-   Node.js and NPM
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

3.  **Node.js Dependencies များကို Install လုပ်ပါ။**
    ```bash
    npm install
    ```

4.  **Environment File ကို Setup လုပ်ပါ။**
    `.env.example` file ကို copy ကူးပြီး `.env` ဟု အမည်ပြောင်းပါ။
    ```bash
    cp .env.example .env
    ```
    ထို့နောက် `.env` file ထဲတွင် သင်၏ Database အချက်အလက်များကို မှန်ကန်အောင် ထည့်သွင်းပါ။
    ```dotenv
    DB_HOST=localhost
    DB_NAME=thai_2d3d_db
    DB_USER=your_db_user
    DB_PASS=your_db_password
    ```

5.  **Database ကို Import လုပ်ပါ။**
    သင်၏ Database Management Tool (ဥပမာ: phpMyAdmin) ကို အသုံးပြု၍ `thai_2d3d_db.sql` file ကို import လုပ်ပါ။

6.  **Development Server ကို Run ပါ။**
    Project ကို run ရန် PHP တွင်ပါဝင်သော built-in web server ကို အသုံးပြုနိုင်သည်၊ သို့မဟုတ် Apache/Nginx ကဲ့သို့သော local server တွင်ထည့်သွင်း၍ run နိုင်သည်။

    **PHP's built-in server ဖြင့် run ရန်:**
    ```bash
    php -S localhost:8000
    ```
    ထို့နောက် browser တွင် `http://localhost:8000` သို့ သွားရောက်ကြည့်ရှုပါ။

---
ဤ Readme သည် Project ၏ အခြေခံအချက်အလက်များနှင့် Setup ပြုလုပ်ပုံအဆင့်ဆင့်ကို ဖော်ပြထားခြင်းဖြစ်သည်။
