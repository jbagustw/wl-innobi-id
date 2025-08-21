-- Database: innobi_worship_leader
CREATE DATABASE IF NOT EXISTS innobi_worship_leader;
USE innobi_worship_leader;

-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Tabel Songs
CREATE TABLE songs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    song_key VARCHAR(10) NOT NULL,
    tempo ENUM('slow', 'medium', 'fast') NOT NULL,
    theme VARCHAR(50) NOT NULL,
    lyrics TEXT NOT NULL,
    artist VARCHAR(100),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_title (title),
    INDEX idx_theme (theme)
);

-- Tabel Compositions
CREATE TABLE compositions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    theme VARCHAR(50) NOT NULL,
    notes TEXT,
    event_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Tabel Composition Songs (Many-to-Many relationship)
CREATE TABLE composition_songs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    composition_id INT NOT NULL,
    song_id INT NOT NULL,
    order_position INT NOT NULL,
    notes VARCHAR(255),
    FOREIGN KEY (composition_id) REFERENCES compositions(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_composition_song_order (composition_id, order_position),
    INDEX idx_composition_id (composition_id)
);

-- Tabel Activity Log (untuk moderasi)
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created_at (created_at)
);

-- Tabel Sessions (untuk token management)
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- Insert default admin user (password: admin123 - harus diganti saat produksi)
INSERT INTO users (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$YourHashedPasswordHere', 'Administrator', 'admin@worship.com', 'admin');

-- Insert sample songs
INSERT INTO songs (title, song_key, tempo, theme, lyrics, created_by) VALUES 
('Bapa Yang Kekal', 'C', 'slow', 'penyembahan', 'Bapa yang kekal\nRaja yang besar\nLayak dipuji\nDimuliakan\n\nKudus kudus\nKudus Tuhan\nAllah yang Mahakuasa', 1),
('Hosanna', 'G', 'fast', 'pujian', 'Hosanna hosanna\nHosanna in the highest\nHosanna hosanna\nHosanna in the highest', 1),
('Dia Mengerti', 'D', 'slow', 'penghiburan', 'Dia mengerti\nDia peduli\nSegala persoalan yang sedang kau alami', 1),
('Kunaikkan Syukur', 'A', 'medium', 'ucapan syukur', 'Kunaikkan syukur pada-Mu Tuhan\nKar''na berkat-Mu melimpah', 1),
('Lebih Dari Pemenang', 'E', 'fast', 'kemenangan', 'Kita lebih dari pemenang\nDalam segala perkara\nOleh Kristus yang mengasihi kita', 1),
('Yesus Kau Sungguh Baik', 'F', 'medium', 'penyembahan', 'Yesus Kau sungguh baik\nYesus Kau sungguh baik\nKau sungguh baik padaku', 1),
('Kau Allah Yang Kudus', 'Bb', 'slow', 'penyembahan', 'Kau Allah yang kudus\nLayak menerima pujian\nKami meninggikan nama-Mu', 1);

-- Create indexes for better performance
CREATE INDEX idx_songs_search ON songs(title, lyrics(500));
CREATE INDEX idx_compositions_date ON compositions(event_date);

-- Create views for common queries
CREATE VIEW v_user_compositions AS
SELECT 
    c.id,
    c.name,
    c.theme,
    c.event_date,
    c.created_at,
    u.username,
    u.full_name,
    COUNT(cs.id) as song_count
FROM compositions c
JOIN users u ON c.user_id = u.id
LEFT JOIN composition_songs cs ON c.id = cs.composition_id
GROUP BY c.id;

CREATE VIEW v_composition_details AS
SELECT 
    cs.composition_id,
    cs.order_position,
    s.id as song_id,
    s.title,
    s.song_key,
    s.tempo,
    s.theme,
    s.lyrics,
    cs.notes
FROM composition_songs cs
JOIN songs s ON cs.song_id = s.id
WHERE s.is_active = TRUE
ORDER BY cs.composition_id, cs.order_position;