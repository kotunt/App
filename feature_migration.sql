-- Feature parity migration: promotions, dream_book, external_games + settings
-- Idempotent: safe to run multiple times.

CREATE TABLE IF NOT EXISTS `promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `badge` varchar(40) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dream_book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `icon` varchar(20) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `number_2d` varchar(20) DEFAULT NULL,
  `number_3d` varchar(40) DEFAULT NULL,
  `category` varchar(40) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `external_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `provider` varchar(40) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `launch_url` varchar(255) DEFAULT NULL,
  `badge` varchar(40) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed promotions (only if empty)
INSERT INTO `promotions` (`title`, `description`, `image_url`, `badge`, `link_url`, `is_active`, `sort_order`)
SELECT * FROM (
  SELECT 'ပထမဆုံး ငွေသွင်း ၁၀% ဘောနပ်စ်' AS a, 'ပထမဆုံးအကြိမ် ငွေဖြည့်သွင်းသူတိုင်း ၁၀% အပို ဘောနပ်စ် ရရှိမည်။ အများဆုံး ၅၀,၀၀၀ ကျပ်အထိ။' AS b, '' AS c, 'NEW' AS d, 'deposit.php' AS e, 1 AS f, 10 AS g
) t WHERE NOT EXISTS (SELECT 1 FROM `promotions`);
INSERT INTO `promotions` (`title`, `description`, `image_url`, `badge`, `link_url`, `is_active`, `sort_order`)
SELECT * FROM (
  SELECT 'မိတ်ဆက်ပေးပြီး ကော်မရှင်ရယူပါ', 'သင့်သူငယ်ချင်းများကို မိတ်ဆက်ကုဒ်ဖြင့် ဖိတ်ခေါ်ပြီး သူတို့ထိုးကြေးအပေါ် ကော်မရှင် တသက်လုံး ရယူနိုင်ပါသည်။', '', 'HOT', 'referral.php', 1, 20
) t WHERE NOT EXISTS (SELECT 1 FROM `promotions` WHERE `sort_order` = 20);
INSERT INTO `promotions` (`title`, `description`, `image_url`, `badge`, `link_url`, `is_active`, `sort_order`)
SELECT * FROM (
  SELECT 'VIP အဆင့်မြှင့် Cashback', 'VIP အဆင့်မြှင့်တက်လေ Cashback ရာခိုင်နှုန်း များလေ ဖြစ်ပါသည်။ Diamond အဆင့်တွင် ၁၀% အထိ ပြန်အမ်းပါသည်။', '', NULL, 'profile.php', 1, 30
) t WHERE NOT EXISTS (SELECT 1 FROM `promotions` WHERE `sort_order` = 30);

-- Seed external games (only if empty)
INSERT INTO `external_games` (`name`, `provider`, `image_url`, `launch_url`, `badge`, `is_active`, `sort_order`)
SELECT * FROM (
  SELECT 'ရှမ်းကိုးမီး' AS a, 'A8' AS b, '' AS c, '' AS d, 'HOT' AS e, 1 AS f, 10 AS g
) t WHERE NOT EXISTS (SELECT 1 FROM `external_games`);
INSERT INTO `external_games` (`name`, `provider`, `image_url`, `launch_url`, `badge`, `is_active`, `sort_order`)
SELECT 'Live22','Live22','','','NEW',1,20 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `external_games` WHERE `sort_order` = 20);

-- Seed dream book (only if empty)
INSERT INTO `dream_book` (`title`, `icon`, `number_2d`, `number_3d`, `sort_order`)
SELECT * FROM (SELECT 'မီး' AS a,'🔥' AS b,'07, 70' AS c,'007, 070, 700' AS d, 10 AS e) t
WHERE NOT EXISTS (SELECT 1 FROM `dream_book`);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ရေ','💧','12, 21','120, 210, 012', 20 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=20);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'နဂါး','🐉','38, 83','389, 839, 938', 30 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=30);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ဆင်','🐘','45, 54','456, 546, 645', 40 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=40);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'မြွေ','🐍','06, 60','606, 660, 066', 50 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=50);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ကျား','🐅','19, 91','199, 919, 991', 60 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=60);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ငွေ','💰','28, 82','288, 828, 882', 70 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=70);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ရွှေ','🥇','35, 53','355, 535, 553', 80 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=80);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ဘုရား','🛕','09, 90','099, 909, 990', 90 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=90);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ကား','🚗','24, 42','244, 424, 442', 100 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=100);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ငါး','🐟','17, 71','177, 717, 771', 110 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=110);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ခွေး','🐶','13, 31','133, 313, 331', 120 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=120);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'ကြောင်','🐱','26, 62','266, 626, 662', 130 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=130);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'လေယာဉ်','✈️','58, 85','588, 858, 885', 140 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=140);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'သွား','🦷','04, 40','044, 404, 440', 150 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=150);
INSERT INTO `dream_book` (`title`,`icon`,`number_2d`,`number_3d`,`sort_order`)
SELECT 'သွေး','🩸','23, 32','233, 323, 332', 160 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `dream_book` WHERE `sort_order`=160);

-- Settings (insert only if key missing)
INSERT INTO `settings` (`setting_key`,`setting_value`)
SELECT 'deposit_video_url','' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='deposit_video_url');
INSERT INTO `settings` (`setting_key`,`setting_value`)
SELECT 'withdraw_video_url','' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='withdraw_video_url');
INSERT INTO `settings` (`setting_key`,`setting_value`)
SELECT 'results_video_url','' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='results_video_url');
INSERT INTO `settings` (`setting_key`,`setting_value`)
SELECT 'cs_phone','' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='cs_phone');
INSERT INTO `settings` (`setting_key`,`setting_value`)
SELECT 'cs_channel_link','' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='cs_channel_link');
INSERT INTO `settings` (`setting_key`,`setting_value`)
SELECT 'enable_game_wallet','1' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='enable_game_wallet');
