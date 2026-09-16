-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2026 at 03:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `research_repository_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `file_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_07_12_045109_create_sessions_table', 1),
(2, '2026_07_12_045259_create_cache_table', 1),
(3, '2026_07_12_045942_create_users_table', 1),
(4, '2026_07_12_045957_create_password_reset_tokens_table', 1),
(5, '2026_07_12_050006_create_jobs_table', 1),
(6, '2026_07_12_050021_create_stud_file_table', 1),
(7, '2026_08_28_065859_add_requested_visibility_to_stud_file_table', 2),
(8, '2026_09_09_095915_create_sdgs_table', 3),
(10, '2026_09_09_102602_create_stud_file_sdg_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sdgs`
--

CREATE TABLE `sdgs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sdgs`
--

INSERT INTO `sdgs` (`id`, `number`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL),
(2, 2, NULL, NULL),
(3, 3, NULL, NULL),
(4, 4, NULL, NULL),
(5, 5, NULL, NULL),
(6, 6, NULL, NULL),
(7, 7, NULL, NULL),
(8, 8, NULL, NULL),
(9, 9, NULL, NULL),
(10, 10, NULL, NULL),
(11, 11, NULL, NULL),
(12, 12, NULL, NULL),
(13, 13, NULL, NULL),
(14, 14, NULL, NULL),
(15, 15, NULL, NULL),
(16, 16, NULL, NULL),
(17, 17, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('j5wbvqLSoXK4eixv7GcRe5ROOCCb4gHbqciFRYRi', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiREtxTGxlVnBuUDNFd3ZWNGZUQkpWd3VVUHdvQWNSeWk5MFNpdjB6NiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdHVkZW50IjtzOjU6InJvdXRlIjtzOjc6InN0dWRlbnQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1789458080),
('klNqmbwNHNnsJnCJ4g3RbUdTZltfLUwie1FNhw3X', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.127.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQml5NG1kTWdkaVJ3NjZ5YlpKR3VDaGlyV3EyMTBsZzJrdmd2NGd1QiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1789433706),
('rbPLevnibMcoaMmcFytkwryxI02t1TyQ0Jv7ggne', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.127.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT25kbEVtZEdFd3B3bWxpRExqQkpHS1VnWmRBQ2RER00wdDIwNFV1YyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1789451648),
('ZpWR0HUndm5pwXDuPW9Yu4CbjJYOHTjFfiHUoLxb', 2, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV2NucW9NZ2dVRHRCMG5XczZjT0dieVlXOXBOd0NOWUk4WW5icW1mQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdHVkZW50IjtzOjU6InJvdXRlIjtzOjc6InN0dWRlbnQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1789435710);

-- --------------------------------------------------------

--
-- Table structure for table `stud_file`
--

CREATE TABLE `stud_file` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `adviser` varchar(255) DEFAULT NULL,
  `desc` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `auto_tag` varchar(255) DEFAULT NULL,
  `status` enum('pending','admin_pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `visibility` enum('private','public') NOT NULL DEFAULT 'private',
  `requested_visibility` enum('private','public') NOT NULL DEFAULT 'private',
  `views` int(11) DEFAULT 0,
  `downloads` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stud_file`
--

INSERT INTO `stud_file` (`id`, `user_id`, `title`, `name`, `adviser`, `desc`, `file_path`, `auto_tag`, `status`, `visibility`, `requested_visibility`, `views`, `downloads`, `created_at`, `updated_at`) VALUES
(1, 2, 'Growth Performance of Tilapia Under Different Feeding Management Practices', 'John Patrick Labinay', 'Marinette Peralta', 'Tilapia is one of the most widely cultured freshwater fish because of its adaptability, relatively fast growth, and economic importance in aquaculture. However, the growth performance and production efficiency of tilapia can be greatly influenced by feeding management practices. This study, titled “Growth Performance of Tilapia Under Different Feeding Management Practices,” aims to evaluate how different feeding management practices affect the growth and overall performance of cultured tilapia. The study focuses on important growth indicators such as weight gain, length increment, specific growth rate, feed conversion ratio, and survival rate. Different feeding approaches may vary in terms of feeding frequency, amount of feed provided, feeding schedule, and consistency of feeding. Proper management of these factors is essential to ensure that fish receive adequate nutrients while minimizing feed waste and maintaining good water quality.\r\n\r\nThe study may be conducted using cultured tilapia that are maintained under controlled conditions and subjected to different feeding management practices. Fish growth measurements are recorded at regular intervals throughout the culture period to determine changes in body weight and length. Feed consumption and survival are also monitored to assess the efficiency of each feeding practice. The collected data can be analyzed and compared to determine which feeding management approach provides better growth performance and feed utilization.', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'growth,performance,tilapia,under,different,feeding,management,practices', 'pending', 'public', 'public', 3, 0, '2026-08-30 00:12:08', '2026-09-01 18:54:31'),
(2, 2, 'Assessment of Environmental Factors Affecting Bangus Aquaculture Production', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'Bangus, or milkfish, is an economically important species in aquaculture and contributes significantly to food production and the livelihood of many fish farmers. However, its growth, survival, and overall production can be affected by various environmental factors. This study, titled “Assessment of Environmental Factors Affecting Bangus Aquaculture Production,” aims to assess the environmental conditions that may influence the productivity of bangus in aquaculture systems. The study focuses on factors such as water temperature, dissolved oxygen, pH level, salinity, water quality, and other environmental conditions that may affect fish growth and survival. Proper monitoring and management of these factors are important in maintaining a suitable environment for bangus culture. The study may involve gathering information through observation, water quality monitoring, and assessment of production practices used by fish farmers. The collected data can be analyzed to determine the environmental conditions that are commonly associated with better or poorer aquaculture production. The findings of the study are expected to provide useful information about the importance of maintaining appropriate environmental conditions in bangus culture. It may also help identify environmental factors that require greater attention and management to improve production efficiency. The results can serve as a reference for fish farmers, aquaculture practitioners, students, and future researchers interested in improving bangus production. Ultimately, the study emphasizes the importance of proper environmental management in supporting healthy fish growth, improving survival rates, reducing production problems, and promoting sustainable bangus aquaculture.', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'assessment,environmental,factors,affecting,bangus,aquaculture,production', 'approved', 'private', 'private', 2, 0, '2026-08-30 00:13:46', '2026-09-01 16:02:21'),
(3, 2, 'Distribution and Abundance of Starfish in Coastal Waters', 'John Patrick Labinay', 'Marinette Peralta', 'Starfish are important marine organisms that contribute to the ecological balance and biodiversity of coastal ecosystems. Their presence and population size can provide valuable information about the condition of marine habitats. This study, titled “Distribution and Abundance of Starfish in Coastal Waters,” aims to determine the distribution and abundance of starfish in selected coastal areas. The study focuses on identifying the number of starfish present, their distribution across different coastal locations, and the environmental conditions associated with their presence. Data may be collected through field observations, sampling, and recording the number of starfish found in designated areas. Environmental factors such as water quality, substrate type, depth, and habitat conditions may also be observed to better understand their relationship with starfish distribution. The collected data can be analyzed to determine patterns of abundance and identify areas where starfish are more commonly found. The findings of the study are expected to provide useful information about the population and distribution of starfish within the selected coastal waters. The results may also contribute to a better understanding of coastal biodiversity and the importance of protecting marine habitats. Furthermore, this study can serve as a reference for students, researchers, coastal communities, and environmental organizations interested in marine biodiversity and conservation. Ultimately, the study emphasizes the importance of monitoring starfish populations to support the assessment and sustainable management of coastal marine ecosystems.', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'distribution,abundance,starfish,coastal,waters', 'pending', 'public', 'public', 0, 0, '2026-08-30 00:15:16', '2026-09-01 05:05:27'),
(4, 2, 'The Importance of Turtles in Marine and Terrestrial Ecosystems', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'Turtles play an important role in maintaining the balance and health of both marine and terrestrial ecosystems. As long-lived reptiles, they contribute to ecosystem stability through their feeding activities, movement, and interactions with other organisms. In marine environments, sea turtles help maintain healthy seagrass beds and coral reef ecosystems by controlling the growth of certain organisms and supporting nutrient cycling. Their activities also contribute to the distribution of nutrients between marine and coastal environments. On land, terrestrial and freshwater turtles influence their habitats by feeding on plants, insects, and other organisms, while also contributing to seed dispersal and nutrient movement. Despite their ecological importance, turtles face numerous threats, including habitat destruction, pollution, illegal hunting, climate change, and accidental capture in fishing activities. These threats have contributed to declining turtle populations in many areas. Protecting turtles is therefore essential not only for their survival but also for maintaining healthy and productive ecosystems. Conservation efforts such as habitat protection, pollution reduction, responsible fishing practices, wildlife law enforcement, and public education can help reduce these threats. Understanding the ecological roles of turtles can encourage communities to participate in conservation programs and promote sustainable environmental practices. Protecting turtles ultimately helps preserve biodiversity and maintain the ecological balance of marine and terrestrial environments for future generations.', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'importance,turtles,marine,terrestrial,ecosystems', 'approved', 'public', 'public', 2, 0, '2026-08-30 20:45:55', '2026-09-01 19:05:51'),
(5, 2, 'Growth Performance of Tilapia', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'Tilapia', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'growth,performance,tilapia', 'approved', 'public', 'public', 1, 0, '2026-09-01 19:08:32', '2026-09-11 22:18:34'),
(6, 2, 'Challenges Encountered by Bangus Farmers in Aquaculture Production', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'Bangus (Chanos chanos), commonly known as milkfish, is one of the important aquaculture species in the Philippines and provides income and livelihood opportunities for many fish farmers. However, despite its economic importance, bangus farmers face various challenges that can affect production, profitability, and the sustainability of their farming activities. This study aims to identify and assess the challenges encountered by bangus farmers in aquaculture production. It focuses on common problems related to water quality management, fish diseases, availability and cost of feeds, weather and environmental conditions, labor, capital, and market prices. The study may utilize a descriptive research approach to gather information from selected bangus farmers through questionnaires and interviews. The findings are expected to provide an overview of the major difficulties experienced by farmers throughout the production process, from pond preparation and stocking to feeding, harvesting, and marketing. Understanding these challenges is important in developing appropriate strategies that can help improve farm productivity and reduce production losses. The study also seeks to determine how these challenges influence the income and overall operations of bangus farmers. The results may serve as a useful reference for local government units, fisheries organizations, agricultural agencies, and other stakeholders in developing programs, training, and assistance for bangus farmers. Ultimately, addressing the identified challenges can contribute to more efficient, sustainable, and profitable bangus aquaculture production while supporting the livelihood of farming communities.', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'challenges,encountered,bangus,farmers,aquaculture,production', 'approved', 'public', 'public', 0, 0, '2026-09-07 18:41:37', '2026-09-07 19:48:48'),
(7, 2, 'The Economic Viability of Bangus Production Among Local Fish Farmers', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'Bangus (Chanos chanos), commonly known as milkfish, is one of the major aquaculture species produced in the Philippines and plays an important role in providing food, employment, and income to local fish farming communities. However, the profitability of bangus production may be influenced by various factors, including production costs, feed expenses, labor, fish mortality, market prices, and environmental conditions. This study aims to assess the economic viability of bangus production among local fish farmers by examining the costs, revenues, profitability, and challenges associated with bangus farming. The study will focus on identifying the major expenses incurred during the production process, such as pond preparation, fingerlings, feeds, labor, maintenance, and harvesting. It will also examine the income generated from the sale of harvested bangus and determine whether the returns are sufficient to sustain the farming operation. A descriptive research approach may be used, with data gathered from selected local bangus farmers through questionnaires and interviews. The collected data will be analyzed to determine the relationship between production expenses and income and to evaluate the overall profitability of bangus farming. The findings of the study are expected to provide useful information to fish farmers regarding effective cost management and production practices. Furthermore, the results may assist local government units, fisheries agencies, and other stakeholders in developing programs and strategies that can support the sustainability and profitability of bangus production. Ultimately, the study seeks to determine whether bangus farming remains an economically viable livelihood for local fish farmers.', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'economic,viability,bangus,production,among,local,fish,farmers', 'approved', 'public', 'public', 0, 0, '2026-09-07 19:33:41', '2026-09-07 19:52:20'),
(8, 2, 'test', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'test test', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'test', 'approved', 'public', 'public', 0, 0, '2026-09-07 20:04:37', '2026-09-07 20:05:32'),
(9, 2, 'on', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'onnnn', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', '', 'approved', 'public', 'public', 0, 0, '2026-09-07 20:21:56', '2026-09-07 20:22:46'),
(10, 2, 'ge', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'gegegegeg', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'ge', 'approved', 'public', 'public', 0, 0, '2026-09-07 20:40:56', '2026-09-07 20:43:29'),
(11, 2, 'koko', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'kokokokok', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'koko', 'approved', 'public', 'public', 0, 0, '2026-09-07 21:20:37', '2026-09-07 21:22:05'),
(12, 2, 'reject', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'rejected', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'reject', 'pending', 'public', 'public', 1, 0, '2026-09-08 22:27:46', '2026-09-08 22:53:10'),
(13, 2, 'testtestt', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'test', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'testtestt', 'pending', 'private', 'public', 0, 0, '2026-09-09 01:27:48', '2026-09-09 01:27:48'),
(14, 2, 'testing mo', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'test', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'testing,mo', 'pending', 'private', 'public', 0, 0, '2026-09-09 02:23:54', '2026-09-09 02:23:54'),
(15, 2, 'testing mo', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'test', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'testing,mo', 'pending', 'private', 'public', 0, 0, '2026-09-09 02:25:06', '2026-09-09 02:25:06'),
(16, 2, 'testing mo', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'test', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'testing,mo', 'pending', 'private', 'public', 0, 0, '2026-09-09 02:29:44', '2026-09-09 02:29:44'),
(17, 2, 'huhuhuhu', 'John Patrick Labinay', 'Ian Jorge Sagabaen', 'huuuu', 'research_files/RESEARCH REPOSITORY SYSTEM.pdf', 'huhuhuhu', 'pending', 'private', 'private', 0, 0, '2026-09-09 03:18:11', '2026-09-09 03:18:11');

-- --------------------------------------------------------

--
-- Table structure for table `stud_file_sdg`
--

CREATE TABLE `stud_file_sdg` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stud_file_id` bigint(20) UNSIGNED NOT NULL,
  `sdg_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stud_file_sdg`
--

INSERT INTO `stud_file_sdg` (`id`, `stud_file_id`, `sdg_id`) VALUES
(1, 16, 1),
(2, 16, 2),
(3, 16, 3),
(4, 17, 3),
(5, 17, 8),
(6, 17, 15);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `email` varchar(250) NOT NULL,
  `name` varchar(255) NOT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `account_status` enum('active','deactivated') NOT NULL DEFAULT 'active',
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `role`, `email`, `name`, `year_level`, `account_status`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN-001', 'admin', 'admin@gmail.com', 'System Administrator', NULL, 'active', '$2y$12$dunv9.KNFELGEAvlAG3Bveao1RcTYQ2Cj.HcWEYA0FOEcm8NfSDjq', NULL, '2026-08-30 07:40:40', '2026-09-01 03:41:20'),
(2, '23-10210', 'student', 'johnpatrickalbeyeslabinay@gmail.com', 'John Patrick Labinay', '4th year', 'active', '$2y$12$BOXWwzf/uCWyFmcfaaw8A.GVvJrI/p0uSqGFfIi5zOSWdT5Qo/tjK', NULL, '2026-08-30 00:05:56', '2026-09-01 03:42:53'),
(3, '20-24680', 'instructor', 'sagabaenianjorgedoblado@gmail.com', 'Ian Jorge Sagabaen', NULL, 'active', '$2y$12$vvtdS5BIYkGB8tvFbmP/QeRyb1NMQHhYC.EVmw6vkbWGX2pmCFQ6a', NULL, '2026-08-30 00:17:22', '2026-08-30 00:17:22'),
(4, '23-00001', 'student', 'peraltamharinett@gmail.com', 'Marinette Peralta', '4th year', 'active', '$2y$12$xbPVuNtvkrYSJoRpj5RzPeIP6yHsu7jkovmzZ2TUImpEJDxKX5hq.', NULL, '2026-09-01 03:00:01', '2026-09-01 03:00:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `favorites_user_id_foreign` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_reserved_at_index` (`queue`,`reserved_at`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sdgs`
--
ALTER TABLE `sdgs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sdgs_number_unique` (`number`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stud_file`
--
ALTER TABLE `stud_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stud_file_user_id_foreign` (`user_id`);

--
-- Indexes for table `stud_file_sdg`
--
ALTER TABLE `stud_file_sdg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stud_file_sdg_stud_file_id_sdg_id_unique` (`stud_file_id`,`sdg_id`),
  ADD KEY `stud_file_sdg_sdg_id_foreign` (`sdg_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_user_id_unique` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sdgs`
--
ALTER TABLE `sdgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `stud_file`
--
ALTER TABLE `stud_file`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `stud_file_sdg`
--
ALTER TABLE `stud_file_sdg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stud_file`
--
ALTER TABLE `stud_file`
  ADD CONSTRAINT `stud_file_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stud_file_sdg`
--
ALTER TABLE `stud_file_sdg`
  ADD CONSTRAINT `stud_file_sdg_sdg_id_foreign` FOREIGN KEY (`sdg_id`) REFERENCES `sdgs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stud_file_sdg_stud_file_id_foreign` FOREIGN KEY (`stud_file_id`) REFERENCES `stud_file` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
