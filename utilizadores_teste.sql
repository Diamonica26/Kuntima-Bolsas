-- Utilizadores fictícios para testar os perfis (seguro executar mais de uma vez).
-- Use este ficheiro se a base de dados já foi importada antes.
USE kuntima_bolsas;

INSERT IGNORE INTO users (name, email, password_hash, role, phone) VALUES
('Administrador Teste', 'admin@kuntima.ao',  '$2y$10$ZVF/5/rN90MAp7D5baIkQeAz2dpYqIGVraYGpVtPv/1j2Ij7U2gdG', 'admin',  '+244 900 000 001'),
('Mentor Teste',        'mentor@kuntima.ao', '$2y$10$7mp/c0Z6bb8ztanwxg3XfuMw3QHk68RnhWyu94v9NFm/ppN2J4/OW', 'mentor', '+244 900 000 002');

-- Atribui dois serviços de demonstração ao mentor de teste (só os que ainda não têm mentor).
UPDATE servico_mentoria
SET mentor_id = (SELECT id FROM users WHERE email = 'mentor@kuntima.ao')
WHERE mentor_id IS NULL
  AND title IN ('Preparação de candidatura a bolsas', 'Revisão de carta de motivação');
