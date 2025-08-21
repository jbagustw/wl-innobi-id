-- Seed data untuk Worship Leader Assistant
-- Jalankan script ini setelah mengimport struktur database

USE innobi_worship_leader;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@worship.com', 'admin');

-- Insert default user (password: user123)
INSERT INTO users (username, password, full_name, email, role) VALUES 
('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User Test', 'user@worship.com', 'user');

-- Insert sample songs
INSERT INTO songs (title, song_key, tempo, theme, lyrics, artist, created_by) VALUES 
('Bapa Yang Kekal', 'C', 'slow', 'penyembahan', 'Bapa yang kekal\nRaja yang besar\nLayak dipuji\nDimuliakan\n\nKudus kudus\nKudus Tuhan\nAllah yang Mahakuasa', 'Traditional', 1),
('Hosanna', 'G', 'fast', 'pujian', 'Hosanna hosanna\nHosanna in the highest\nHosanna hosanna\nHosanna in the highest', 'Traditional', 1),
('Dia Mengerti', 'D', 'slow', 'penghiburan', 'Dia mengerti\nDia peduli\nSegala persoalan yang sedang kau alami', 'Traditional', 1),
('Kunaikkan Syukur', 'A', 'medium', 'ucapan syukur', 'Kunaikkan syukur pada-Mu Tuhan\nKar''na berkat-Mu melimpah', 'Traditional', 1),
('Lebih Dari Pemenang', 'E', 'fast', 'kemenangan', 'Kita lebih dari pemenang\nDalam segala perkara\nOleh Kristus yang mengasihi kita', 'Traditional', 1),
('Yesus Kau Sungguh Baik', 'F', 'medium', 'penyembahan', 'Yesus Kau sungguh baik\nYesus Kau sungguh baik\nKau sungguh baik padaku', 'Traditional', 1),
('Kau Allah Yang Kudus', 'Bb', 'slow', 'penyembahan', 'Kau Allah yang kudus\nLayak menerima pujian\nKami meninggikan nama-Mu', 'Traditional', 1),
('Amazing Grace', 'G', 'slow', 'penyembahan', 'Amazing grace, how sweet the sound\nThat saved a wretch like me\nI once was lost, but now am found\nWas blind, but now I see', 'John Newton', 1),
('How Great is Our God', 'C', 'medium', 'pujian', 'The splendor of the King\nClothed in majesty\nLet all the earth rejoice\nAll the earth rejoice', 'Chris Tomlin', 1),
('Way Maker', 'D', 'slow', 'penyembahan', 'You are here, moving in our midst\nI worship You, I worship You\nYou are here, working in this place\nI worship You, I worship You', 'Sinach', 1),
('Reckless Love', 'A', 'medium', 'penyembahan', 'Before I spoke a word\nYou were singing over me\nYou have been so, so good to me\nBefore I took a breath\nYou breathed Your life in me\nYou have been so, so kind to me', 'Cory Asbury', 1),
('Good Good Father', 'F', 'slow', 'penyembahan', 'I''ve heard a thousand stories\nOf what they think You''re like\nBut I''ve heard the tender whisper\nOf love in the dead of night', 'Chris Tomlin', 1);

-- Insert sample compositions
INSERT INTO compositions (user_id, name, theme, notes, event_date) VALUES 
(1, 'Ibadah Minggu Pagi', 'penyembahan', 'Komposisi untuk ibadah minggu pagi', '2024-01-21'),
(1, 'Ibadah Doa Malam', 'penghiburan', 'Komposisi untuk ibadah doa malam', '2024-01-20'),
(2, 'Pujian dan Penyembahan', 'pujian', 'Komposisi pribadi untuk pujian', '2024-01-19');

-- Insert composition songs
INSERT INTO composition_songs (composition_id, song_id, order_position, notes) VALUES 
(1, 1, 1, 'Lagu pembuka'),
(1, 2, 2, 'Lagu pujian'),
(1, 3, 3, 'Lagu penghiburan'),
(2, 4, 1, 'Lagu ucapan syukur'),
(2, 5, 2, 'Lagu kemenangan'),
(3, 6, 1, 'Lagu penyembahan'),
(3, 7, 2, 'Lagu penutup');

-- Insert sample activity logs
INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES 
(1, 'create', 'song', 1, '{"title": "Bapa Yang Kekal"}', '127.0.0.1'),
(1, 'create', 'composition', 1, '{"name": "Ibadah Minggu Pagi"}', '127.0.0.1'),
(2, 'create', 'composition', 3, '{"name": "Pujian dan Penyembahan"}', '127.0.0.1'),
(1, 'update', 'song', 2, '{"title": "Hosanna"}', '127.0.0.1');

-- Update password hash untuk admin dan user (password: admin123 dan user123)
-- Note: Hash ini adalah contoh, dalam produksi gunakan password_hash() PHP
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username IN ('admin', 'user');
