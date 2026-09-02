-- Xiuno BBS 4.0.4 -> 5.0 存量站点 utf8mb4 升级脚本 / Legacy site upgrade script
-- 用法: 1. 先备份数据库! 2. 将 bbs_ 替换为你的表前缀, yourdb 替换为库名后执行
--       3. 执行后必须把 conf/conf.php 里两处 'charset' 从 'utf8' 改为 'utf8mb4', 否则 emoji 仍会截断
-- Usage: 1. BACK UP FIRST. 2. Replace bbs_ with your table prefix, yourdb with your database name.
--        3. After running, change both 'charset' entries in conf/conf.php from 'utf8' to 'utf8mb4', or emoji will still be truncated.
-- 插件建的表不在此列表内, 请照样对插件表逐一执行 CONVERT TO / Plugin tables are not covered, convert them the same way.
-- 老站 session.useragent 为 char(128)(官方) 或 text(社区包), 建议统一执行 / Recommended for all legacy sites:
-- ALTER TABLE bbs_session MODIFY useragent varchar(500) NOT NULL DEFAULT '';

ALTER DATABASE yourdb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

ALTER TABLE bbs_user CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_group CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_forum CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_forum_access CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_thread CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_thread_top CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_post CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_attach CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_mythread CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_mypost CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_session CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_session_data CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_modlog CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_kv CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_cache CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_queue CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE bbs_table_day CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
