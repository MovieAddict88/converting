CREATE TABLE IF NOT EXISTS `admins` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS `songs` (
  `id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `source_type` varchar(50) NOT NULL,
  `source_url` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `lyrics` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `scores` (
  `id` varchar(36) NOT NULL,
  `song_id` varchar(36) NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  `accuracy` float NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
);
