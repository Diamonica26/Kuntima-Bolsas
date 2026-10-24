CREATE DATABASE IF NOT EXISTS kuntima_bolsas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kuntima_bolsas;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('student','mentor','admin') NOT NULL DEFAULT 'student',
 account_status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'approved',
 phone VARCHAR(40) NULL,
 education_level VARCHAR(100) NULL,
 field_of_study VARCHAR(120) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS scholarships (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(180) NOT NULL,
 organization VARCHAR(160) NOT NULL,
 country VARCHAR(100) NOT NULL DEFAULT 'Angola',
 study_level VARCHAR(100) NOT NULL,
 field VARCHAR(120) NOT NULL,
 funding_type ENUM('Integral','Parcial','Propina','Investigação') NOT NULL DEFAULT 'Integral',
 deadline DATE NULL,
 description TEXT NOT NULL,
 requirements TEXT NULL,
 application_url VARCHAR(500) NULL,
 status ENUM('published','draft','closed') NOT NULL DEFAULT 'published',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS applications (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 scholarship_id INT UNSIGNED NOT NULL,
 motivation TEXT NULL,
 status ENUM('submitted','reviewing','approved','rejected') NOT NULL DEFAULT 'submitted',
 applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_application (user_id, scholarship_id),
 CONSTRAINT fk_application_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_application_scholarship FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mentorship_services (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 mentor_id INT UNSIGNED NULL,
 title VARCHAR(180) NOT NULL,
 category VARCHAR(100) NOT NULL,
 description TEXT NOT NULL,
 price DECIMAL(10,2) NOT NULL DEFAULT 0,
 duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
 delivery ENUM('Online','Presencial','Online e presencial') NOT NULL DEFAULT 'Online',
 status ENUM('published','draft') NOT NULL DEFAULT 'published',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_service_mentor FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mentorship_bookings (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 student_id INT UNSIGNED NOT NULL,
 service_id INT UNSIGNED NOT NULL,
 session_date DATETIME NOT NULL,
 message TEXT NULL,
 status ENUM('requested','confirmed','completed','cancelled') NOT NULL DEFAULT 'requested',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_booking_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_booking_service FOREIGN KEY (service_id) REFERENCES mentorship_services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO scholarships (title, organization, country, study_level, field, funding_type, deadline, description, requirements, status) VALUES
('Bolsa de Excelência Académica', 'Fundação Horizonte', 'Angola', 'Licenciatura', 'Todas as áreas', 'Integral', '2027-02-28', 'Apoio a estudantes com bom desempenho académico que pretendem iniciar ou continuar a licenciatura.', 'Certificado de habilitações; documento de identificação; carta de motivação.', 'published'),
('Programa Jovens Líderes', 'Instituto Futuro', 'Portugal', 'Mestrado', 'Gestão e Tecnologia', 'Integral', '2027-03-31', 'Programa de apoio à formação de jovens com interesse em inovação, liderança e impacto social.', 'Licenciatura concluída; currículo; duas referências; carta de motivação.', 'published'),
('Apoio STEM para Estudantes', 'Rede Ciência Aberta', 'África do Sul', 'Licenciatura', 'Ciência, Tecnologia, Engenharia e Matemática', 'Parcial', '2027-01-20', 'Bolsa parcial para apoiar propinas e materiais de estudantes nas áreas STEM.', 'Histórico escolar; comprovativo de matrícula ou candidatura; declaração de necessidade financeira.', 'published'),
('Bolsa de Investigação e Inovação', 'Centro de Inovação Lusófona', 'Angola', 'Mestrado e Doutoramento', 'Investigação', 'Investigação', '2027-04-15', 'Financiamento para projectos de investigação com impacto na comunidade.', 'Proposta de investigação; currículo; referências académicas.', 'published');

INSERT INTO mentorship_services (mentor_id, title, category, description, price, duration_minutes, delivery, status) VALUES
(NULL, 'Preparação de candidatura a bolsas', 'Candidaturas', 'Sessão individual para rever requisitos, organizar documentos e criar um plano de candidatura.', 7500.00, 60, 'Online', 'published'),
(NULL, 'Revisão de carta de motivação', 'Escrita académica', 'Orientação para tornar a carta de motivação clara, convincente e alinhada com a oportunidade.', 5000.00, 45, 'Online', 'published'),
(NULL, 'Orientação académica e escolha de curso', 'Orientação académica', 'Conversa estruturada para comparar cursos, países, instituições e caminhos de carreira.', 10000.00, 75, 'Online e presencial', 'published'),
(NULL, 'Preparação para entrevista', 'Entrevistas', 'Simulação de entrevista e feedback sobre respostas, comunicação e apresentação pessoal.', 6500.00, 60, 'Online', 'published');

-- ===== Utilizadores fictícios de teste (palavras-passe no README) =====

INSERT IGNORE INTO users (name, email, password_hash, role, phone) VALUES
('Administrador Teste', 'admin@kuntima.ao',  '$2y$10$ZVF/5/rN90MAp7D5baIkQeAz2dpYqIGVraYGpVtPv/1j2Ij7U2gdG', 'admin',  '+244 900 000 001'),
('Mentor Teste',        'mentor@kuntima.ao', '$2y$10$7mp/c0Z6bb8ztanwxg3XfuMw3QHk68RnhWyu94v9NFm/ppN2J4/OW', 'mentor', '+244 900 000 002');

-- Atribui dois serviços de demonstração ao mentor de teste (só os que ainda não têm mentor).
UPDATE mentorship_services
SET mentor_id = (SELECT id FROM users WHERE email = 'mentor@kuntima.ao')
WHERE mentor_id IS NULL
  AND title IN ('Preparação de candidatura a bolsas', 'Revisão de carta de motivação');
