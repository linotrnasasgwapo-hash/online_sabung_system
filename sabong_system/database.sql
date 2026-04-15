-- ================================================================
-- SABONG COCKPIT ARENA MANAGEMENT SYSTEM
-- Database: sabong_arena_db
-- Compatible with XAMPP / MySQL 5.7+
-- ================================================================

CREATE DATABASE IF NOT EXISTS sabong_arena_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sabong_arena_db;

-- ----------------------------------------------------------------
-- ADMINS (Arena Staff)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    admin_id    INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(120) NOT NULL,
    email       VARCHAR(120) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('owner','manager','referee','cashier','encoder') DEFAULT 'encoder',
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------------------------------------------
-- OWNERS / HANDLERS (Manok owners)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS owners (
    owner_id    INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(120) NOT NULL,
    nickname    VARCHAR(60),
    phone       VARCHAR(20),
    address     TEXT,
    team_name   VARCHAR(100),
    wins        INT DEFAULT 0,
    losses      INT DEFAULT 0,
    draws       INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------------------------------------------
-- ROOSTERS (Manok / Chicken entries)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roosters (
    rooster_id  INT AUTO_INCREMENT PRIMARY KEY,
    owner_id    INT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    breed       VARCHAR(100),
    color       VARCHAR(60),
    weight_kg   DECIMAL(4,2),
    leg_color   VARCHAR(40),
    wins        INT DEFAULT 0,
    losses      INT DEFAULT 0,
    draws       INT DEFAULT 0,
    status      ENUM('active','retired','deceased') DEFAULT 'active',
    notes       TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES owners(owner_id) ON DELETE CASCADE
);

-- ----------------------------------------------------------------
-- DERBIES (Tournament / Event)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS derbies (
    derby_id        INT AUTO_INCREMENT PRIMARY KEY,
    derby_name      VARCHAR(150) NOT NULL,
    derby_type      ENUM('open_derby','local_derby','invitational','special_derby','fiesta_derby') DEFAULT 'open_derby',
    venue           VARCHAR(150),
    event_date      DATE NOT NULL,
    event_time      TIME DEFAULT '14:00:00',
    entry_fee       DECIMAL(10,2) DEFAULT 0.00,
    prize_pool      DECIMAL(12,2) DEFAULT 0.00,
    max_entries     INT DEFAULT 50,
    current_entries INT DEFAULT 0,
    status          ENUM('upcoming','registration_open','ongoing','completed','cancelled') DEFAULT 'upcoming',
    description     TEXT,
    rules           TEXT,
    created_by      INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(admin_id) ON DELETE SET NULL
);

-- ----------------------------------------------------------------
-- DERBY ENTRIES (Registration of roosters per derby)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS derby_entries (
    entry_id        INT AUTO_INCREMENT PRIMARY KEY,
    derby_id        INT NOT NULL,
    owner_id        INT NOT NULL,
    rooster_id      INT NOT NULL,
    entry_number    INT NOT NULL,
    weight_at_entry DECIMAL(4,2),
    side            ENUM('meron','wala','pending') DEFAULT 'pending',
    status          ENUM('registered','confirmed','scratched','disqualified') DEFAULT 'registered',
    entry_fee_paid  TINYINT(1) DEFAULT 0,
    notes           TEXT,
    registered_by   INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (derby_id)   REFERENCES derbies(derby_id)   ON DELETE CASCADE,
    FOREIGN KEY (owner_id)   REFERENCES owners(owner_id)    ON DELETE CASCADE,
    FOREIGN KEY (rooster_id) REFERENCES roosters(rooster_id) ON DELETE CASCADE,
    FOREIGN KEY (registered_by) REFERENCES admins(admin_id) ON DELETE SET NULL
);

-- ----------------------------------------------------------------
-- MATCHES (Fights / Sabong bouts)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS matches (
    match_id        INT AUTO_INCREMENT PRIMARY KEY,
    derby_id        INT,
    fight_number    INT NOT NULL,
    meron_entry_id  INT,
    wala_entry_id   INT,
    meron_weight    DECIMAL(4,2),
    wala_weight     DECIMAL(4,2),
    referee_id      INT,
    match_date      DATE NOT NULL,
    match_time      TIME,
    result          ENUM('meron','wala','draw','cancelled','no_contest') DEFAULT NULL,
    duration_min    INT DEFAULT NULL,
    status          ENUM('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (derby_id)       REFERENCES derbies(derby_id)       ON DELETE SET NULL,
    FOREIGN KEY (meron_entry_id) REFERENCES derby_entries(entry_id) ON DELETE SET NULL,
    FOREIGN KEY (wala_entry_id)  REFERENCES derby_entries(entry_id) ON DELETE SET NULL,
    FOREIGN KEY (referee_id)     REFERENCES admins(admin_id)        ON DELETE SET NULL
);

-- ----------------------------------------------------------------
-- BREED REFERENCE
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS breeds (
    breed_id    INT AUTO_INCREMENT PRIMARY KEY,
    breed_name  VARCHAR(100) NOT NULL,
    origin      VARCHAR(100),
    description TEXT
);

-- ----------------------------------------------------------------
-- SEED DATA
-- ----------------------------------------------------------------

-- Default admin (password: Admin@1234)
INSERT INTO admins (full_name, email, password, role) VALUES
('Arena Owner',   'owner@arena.com',   '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', 'owner'),
('Juan Manager',  'manager@arena.com', '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', 'manager'),
('Pedro Referee', 'referee@arena.com', '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', 'referee'),
('Maria Cashier', 'cashier@arena.com', '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', 'cashier');

-- Sample owners
INSERT INTO owners (full_name, nickname, phone, address, team_name, wins, losses) VALUES
('Roberto Cruz',   'Berto',   '09171111111', 'Himamaylan City', 'Cruz Fighting Cocks', 15, 8),
('Jose Reyes',     'Pepe',    '09282222222', 'Kabankalan City',  'Reyes Sabungeros',   22, 11),
('Mario Santos',   'Mario',   '09393333333', 'Binalbagan',       'Santos Gamecock Farm',12, 7),
('Carlos Flores',  'Caloy',   '09174444444', 'La Carlota City',  'Flores Bloodline',   8,  5),
('Fernando Lopez', 'Nando',   '09285555555', 'Sipalay City',     'Lopez Sabungan',     19, 13);

-- Sample roosters
INSERT INTO roosters (owner_id, name, breed, color, weight_kg, leg_color, wins, losses) VALUES
(1, 'Agila',      'Sweater',          'Red',         2.10, 'Yellow', 8,  3),
(1, 'Lawin',      'Kelso',            'Black',       2.25, 'White',  7,  5),
(2, 'Kidlat',     'Hatch',            'Grey Spangle',2.15, 'Yellow', 12, 6),
(2, 'Bagyo',      'Albany',           'Brown Red',   2.30, 'Blue',   10, 5),
(3, 'Kulog',      'Roundhead',        'White',       2.05, 'Yellow', 6,  4),
(3, 'Buhawi',     'Spanish Gamefowl', 'Black Red',   2.40, 'White',  6,  3),
(4, 'Apoy',       'Sweater',          'Red',         2.20, 'Yellow', 4,  2),
(5, 'Hangin',     'Kelso',            'Greys',       2.18, 'Blue',   9,  7);

-- Sample breeds
INSERT INTO breeds (breed_name, origin, description) VALUES
('Sweater',           'USA',         'Known for cutting ability and gameness'),
('Kelso',             'USA',         'Smart fighter, strategic and powerful'),
('Hatch',             'USA',         'Hard-hitting, aggressive style'),
('Albany',            'USA',         'High station, cutting and shuffling'),
('Roundhead',         'USA',         'Low station, powerful legs'),
('Leiper Hatch',      'USA',         'Strong, aggressive fighter'),
('Spanish Gamefowl',  'Spain',       'Traditional European bloodline'),
('Lemon 84',          'Philippines', 'Local Filipino breed, very game'),
('Butcher',           'Philippines', 'Known for power and heart'),
('Asil',              'India',       'Ancient breed, powerful and upright');

-- Sample derbies
INSERT INTO derbies (derby_name, derby_type, venue, event_date, event_time, entry_fee, prize_pool, max_entries, status, description, created_by) VALUES
('Himamaylan City Fiesta Derby 2026',  'fiesta_derby',   'Saraet Cockpit Arena', DATE_ADD(CURDATE(), INTERVAL 7 DAY),  '14:00:00', 1500.00, 50000.00, 30, 'registration_open', 'Annual fiesta derby open to all qualified roosters in Himamaylan City.', 1),
('Open Derby March 2026',              'open_derby',     'Saraet Cockpit Arena', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '13:00:00', 1000.00, 30000.00, 20, 'upcoming',          'Monthly open derby. All breeds welcome.', 1),
('Invitational Derby February 2026',   'invitational',   'Saraet Cockpit Arena', DATE_SUB(CURDATE(), INTERVAL 5 DAY),  '15:00:00', 2000.00, 75000.00, 16, 'completed',         'By invitation only. Top bloodlines compete.', 1);
