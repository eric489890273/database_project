-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-11-26 06:57:52
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `database_project`
--

-- --------------------------------------------------------

--
-- 資料表結構 `artifact`
--

CREATE TABLE `artifact` (
  `art_id` varchar(10) NOT NULL,
  `art_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `artifact`
--

INSERT INTO `artifact` (`art_id`, `art_name`) VALUES
('ART001', '睡蓮'),
('ART002', '日出印象'),
('ART003', '星夜'),
('ART004', '抽象構成'),
('ART005', '紅色方塊'),
('ART006', '流動空間'),
('ART007', '山水意境'),
('ART008', '水墨荷花'),
('ART009', '竹林深處'),
('ART010', '青銅器皿');

-- --------------------------------------------------------

--
-- 資料表結構 `create`
--

CREATE TABLE `create` (
  `id` varchar(10) NOT NULL,
  `art_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `create`
--

INSERT INTO `create` (`id`, `art_id`) VALUES
('P004', 'ART001'),
('P004', 'ART002'),
('P004', 'ART009'),
('P005', 'ART003'),
('P005', 'ART004'),
('P005', 'ART010'),
('P006', 'ART005'),
('P006', 'ART006'),
('P007', 'ART007'),
('P007', 'ART008');

-- --------------------------------------------------------

--
-- 資料表結構 `creator`
--

CREATE TABLE `creator` (
  `id` varchar(10) NOT NULL,
  `cr_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `creator`
--

INSERT INTO `creator` (`id`, `cr_id`) VALUES
('P004', 'CR001'),
('P005', 'CR002'),
('P006', 'CR003'),
('P007', 'CR004');

-- --------------------------------------------------------

--
-- 資料表結構 `curator`
--

CREATE TABLE `curator` (
  `id` varchar(10) NOT NULL,
  `cu_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `curator`
--

INSERT INTO `curator` (`id`, `cu_id`) VALUES
('P001', 'CU001'),
('P002', 'CU002'),
('P003', 'CU003');

-- --------------------------------------------------------

--
-- 資料表結構 `exhibit`
--

CREATE TABLE `exhibit` (
  `art_id` varchar(10) NOT NULL,
  `e_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `exhibit`
--

INSERT INTO `exhibit` (`art_id`, `e_name`) VALUES
('ART001', '印象派大師展'),
('ART002', '印象派大師展'),
('ART003', '印象派大師展'),
('ART004', '現代藝術特展'),
('ART005', '現代藝術特展'),
('ART006', '現代藝術特展'),
('ART007', '東方美學展覽'),
('ART008', '東方美學展覽'),
('ART009', '東方美學展覽');

-- --------------------------------------------------------

--
-- 資料表結構 `exhibition`
--

CREATE TABLE `exhibition` (
  `e_name` varchar(200) NOT NULL,
  `e_Date` date NOT NULL,
  `id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `exhibition`
--

INSERT INTO `exhibition` (`e_name`, `e_Date`, `id`) VALUES
('印象派大師展', '2025-01-15', 'P001'),
('東方美學展覽', '2025-03-10', 'P003'),
('現代藝術特展', '2025-02-20', 'P002'),
('雕塑藝術展', '2025-04-05', 'P001');

-- --------------------------------------------------------

--
-- 資料表結構 `feedback`
--

CREATE TABLE `feedback` (
  `id` varchar(10) NOT NULL,
  `fb_id` varchar(10) NOT NULL,
  `content` varchar(2000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `feedback`
--

INSERT INTO `feedback` (`id`, `fb_id`, `content`) VALUES
('P011', 'FB001', '印象派大師展非常精彩，藝術品的色彩運用令人驚艷！'),
('P011', 'FB011', '第二次來參觀，每次都有新的收穫和感動。'),
('P012', 'FB002', '導覽員的解說很專業，讓我對藝術有了更深的理解。'),
('P013', 'FB003', '現代藝術特展充滿創意，每件作品都讓人深思。'),
('P013', 'FB012', '展覽內容豐富，時間安排得很充實。'),
('P014', 'FB004', '展場設計很用心，動線流暢，參觀體驗很好。'),
('P015', 'FB005', '東方美學展覽展現了傳統藝術之美，值得細細品味。'),
('P016', 'FB006', '作品選擇多元，無論是古典還是現代都有涵蓋。'),
('P017', 'FB007', '雕塑藝術展的作品立體感十足，非常震撼！'),
('P018', 'FB008', '票價合理，展覽品質優良，會推薦給朋友。'),
('P019', 'FB009', '希望未來能有更多互動式的展覽體驗。'),
('P020', 'FB010', '整體環境舒適，工作人員服務態度親切。');

-- --------------------------------------------------------

--
-- 資料表結構 `guide`
--

CREATE TABLE `guide` (
  `id` varchar(10) NOT NULL,
  `g_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `guide`
--

INSERT INTO `guide` (`id`, `g_id`) VALUES
('P008', 'G001'),
('P009', 'G002'),
('P010', 'G003');

-- --------------------------------------------------------

--
-- 資料表結構 `guided`
--

CREATE TABLE `guided` (
  `id` varchar(10) NOT NULL,
  `e_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `guided`
--

INSERT INTO `guided` (`id`, `e_name`) VALUES
('P008', '印象派大師展'),
('P008', '現代藝術特展'),
('P009', '現代藝術特展'),
('P010', '東方美學展覽');

-- --------------------------------------------------------

--
-- 資料表結構 `person`
--

CREATE TABLE `person` (
  `id` varchar(10) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `birth_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `person`
--

INSERT INTO `person` (`id`, `gender`, `name`, `phone`, `mail`, `birth_date`) VALUES
('P001', '男', '王小明', '0912345678', 'wang@example.com', '1985-03-15'),
('P002', '女', '李美華', '0923456789', 'lee@example.com', '1990-07-22'),
('P003', '男', '張大偉', '0934567890', 'chang@example.com', '1988-11-30'),
('P004', '女', '陳雅婷', '0945678901', 'chen@example.com', '1992-05-18'),
('P005', '男', '林志明', '0956789012', 'lin@example.com', '1987-09-25'),
('P006', '女', '黃淑芬', '0967890123', 'huang@example.com', '1995-02-14'),
('P007', '男', '吳文傑', '0978901234', 'wu@example.com', '1983-12-08'),
('P008', '女', '劉詩涵', '0989012345', 'liu@example.com', '1991-06-03'),
('P009', '男', '鄭宇航', '0990123456', 'cheng@example.com', '1986-08-19'),
('P010', '女', '謝欣怡', '0901234567', 'hsieh@example.com', '1994-04-27'),
('P011', '男', '周建國', '0911223344', 'zhou@example.com', '1993-06-12'),
('P012', '女', '蔡雅琪', '0922334455', 'tsai@example.com', '1991-09-08'),
('P013', '男', '許志偉', '0933445566', 'hsu@example.com', '1989-12-25'),
('P014', '女', '郭美玲', '0944556677', 'kuo@example.com', '1996-03-17'),
('P015', '男', '楊俊傑', '0955667788', 'yang@example.com', '1984-07-30'),
('P016', '女', '賴雅文', '0966778899', 'lai@example.com', '1998-11-05'),
('P017', '男', '蕭明哲', '0977889900', 'hsiao@example.com', '1990-01-22'),
('P018', '女', '曾詩婷', '0988990011', 'tseng@example.com', '1995-08-14'),
('P019', '男', '范承翰', '0999001122', 'fan@example.com', '1987-04-19'),
('P020', '女', '彭雅筑', '0910112233', 'peng@example.com', '1993-10-28');

-- --------------------------------------------------------

--
-- 資料表結構 `ticket`
--

CREATE TABLE `ticket` (
  `t_id` varchar(10) NOT NULL,
  `price` int(11) NOT NULL,
  `id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `ticket`
--

INSERT INTO `ticket` (`t_id`, `price`, `id`) VALUES
('T001', 300, 'P011'),
('T002', 150, 'P012'),
('T003', 300, 'P013'),
('T004', 200, 'P014'),
('T005', 300, 'P015'),
('T006', 300, 'P016'),
('T007', 150, 'P017'),
('T008', 300, 'P018'),
('T009', 200, 'P019'),
('T010', 300, 'P020'),
('T011', 150, 'P011'),
('T012', 300, 'P013');

-- --------------------------------------------------------

--
-- 資料表結構 `visit`
--

CREATE TABLE `visit` (
  `id` varchar(10) NOT NULL,
  `e_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `visit`
--

INSERT INTO `visit` (`id`, `e_name`) VALUES
('P011', '現代藝術特展'),
('P012', '印象派大師展'),
('P013', '現代藝術特展'),
('P014', '雕塑藝術展'),
('P015', '東方美學展覽'),
('P016', '東方美學展覽'),
('P017', '雕塑藝術展'),
('P018', '印象派大師展'),
('P019', '現代藝術特展'),
('P020', '東方美學展覽');

-- --------------------------------------------------------

--
-- 資料表結構 `visitor`
--

CREATE TABLE `visitor` (
  `id` varchar(10) NOT NULL,
  `v_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `visitor`
--

INSERT INTO `visitor` (`id`, `v_id`) VALUES
('P011', 'V001'),
('P012', 'V002'),
('P013', 'V003'),
('P014', 'V004'),
('P015', 'V005'),
('P016', 'V006'),
('P017', 'V007'),
('P018', 'V008'),
('P019', 'V009'),
('P020', 'V010');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `artifact`
--
ALTER TABLE `artifact`
  ADD PRIMARY KEY (`art_id`);

--
-- 資料表索引 `create`
--
ALTER TABLE `create`
  ADD PRIMARY KEY (`id`,`art_id`),
  ADD KEY `create_ibfk_2` (`art_id`);

--
-- 資料表索引 `creator`
--
ALTER TABLE `creator`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `curator`
--
ALTER TABLE `curator`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `exhibit`
--
ALTER TABLE `exhibit`
  ADD PRIMARY KEY (`art_id`,`e_name`),
  ADD KEY `exhibit_ibfk_2` (`e_name`);

--
-- 資料表索引 `exhibition`
--
ALTER TABLE `exhibition`
  ADD PRIMARY KEY (`e_name`),
  ADD KEY `exhibition_ibfk_1` (`id`);

--
-- 資料表索引 `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`,`fb_id`);

--
-- 資料表索引 `guide`
--
ALTER TABLE `guide`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `guided`
--
ALTER TABLE `guided`
  ADD PRIMARY KEY (`id`,`e_name`),
  ADD KEY `guided_ibfk_2` (`e_name`);

--
-- 資料表索引 `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`t_id`),
  ADD KEY `ticket_ibfk_1` (`id`);

--
-- 資料表索引 `visit`
--
ALTER TABLE `visit`
  ADD PRIMARY KEY (`id`,`e_name`),
  ADD KEY `visit_ibfk_2` (`e_name`);

--
-- 資料表索引 `visitor`
--
ALTER TABLE `visitor`
  ADD PRIMARY KEY (`id`);

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `create`
--
ALTER TABLE `create`
  ADD CONSTRAINT `create_ibfk_1` FOREIGN KEY (`id`) REFERENCES `creator` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `create_ibfk_2` FOREIGN KEY (`art_id`) REFERENCES `artifact` (`art_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `creator`
--
ALTER TABLE `creator`
  ADD CONSTRAINT `creator_ibfk_1` FOREIGN KEY (`id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `curator`
--
ALTER TABLE `curator`
  ADD CONSTRAINT `curator_ibfk_1` FOREIGN KEY (`id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `exhibit`
--
ALTER TABLE `exhibit`
  ADD CONSTRAINT `exhibit_ibfk_1` FOREIGN KEY (`art_id`) REFERENCES `artifact` (`art_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exhibit_ibfk_2` FOREIGN KEY (`e_name`) REFERENCES `exhibition` (`e_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `exhibition`
--
ALTER TABLE `exhibition`
  ADD CONSTRAINT `exhibition_ibfk_1` FOREIGN KEY (`id`) REFERENCES `curator` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feadback_ibfk_1` FOREIGN KEY (`id`) REFERENCES `visitor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `guide`
--
ALTER TABLE `guide`
  ADD CONSTRAINT `guide_ibfk_1` FOREIGN KEY (`id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `guided`
--
ALTER TABLE `guided`
  ADD CONSTRAINT `guided_ibfk_1` FOREIGN KEY (`id`) REFERENCES `guide` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `guided_ibfk_2` FOREIGN KEY (`e_name`) REFERENCES `exhibition` (`e_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`id`) REFERENCES `visitor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `visit`
--
ALTER TABLE `visit`
  ADD CONSTRAINT `visit_ibfk_1` FOREIGN KEY (`id`) REFERENCES `visitor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `visit_ibfk_2` FOREIGN KEY (`e_name`) REFERENCES `exhibition` (`e_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `visitor`
--
ALTER TABLE `visitor`
  ADD CONSTRAINT `visitor_ibfk_1` FOREIGN KEY (`id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
