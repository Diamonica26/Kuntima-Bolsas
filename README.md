# Kuntima — Bolsas de Estudo e Mentoria

Protótipo em PHP + MySQL, adaptado à linguagem visual do template Kuntima/DHospitais fornecido. A estrutura original de urgência hospitalar foi mantida fora desta nova pasta; este projecto é uma aplicação separada e focada em bolsas e mentoria.

## Funcionalidades incluídas
- Página inicial responsiva, listagem e pesquisa de bolsas.
- Página de detalhe de cada oportunidade.
- Registo de estudante e autenticação com `password_hash`/`password_verify`.
- Submissão de candidatura e prevenção de candidaturas duplicadas.
- Listagem de serviços de mentoria com preços indicativos em Kz.
- Solicitação de sessão de mentoria.
- Painel do estudante para acompanhar candidaturas e pedidos.
- Painel administrativo para actualizar estados de candidaturas e pedidos.
- Consultas SQL preparadas, escape de HTML e token CSRF nos formulários POST.

## Requisitos
- XAMPP (Apache + PHP 8.0 ou superior + MySQL/MariaDB).
- Extensão PDO MySQL activa.

## Instalação no XAMPP (Windows)
1. Extraia a pasta `Kuntima_Bolsas` para `C:\xampp\htdocs\Kuntima_Bolsas`.
2. Abra `http://localhost/phpmyadmin`.
3. Seleccione **Importar** e importe `sql/kuntima_bolsas.sql`. O script cria a base de dados `kuntima_bolsas` e insere dados demonstrativos.
4. Se o MySQL tiver credenciais diferentes, altere `DB_USER` e `DB_PASS` em `config.php`.
5. Inicie Apache e MySQL no painel do XAMPP.
6. Abra `http://localhost/Kuntima_Bolsas/`.
7. Crie uma conta através de **Criar conta**.

## Tornar uma conta administradora
Por segurança, o registo público só permite criar contas de aluno ou de mentor; o perfil de administrador nunca é oferecido. Depois de criar a conta, no phpMyAdmin execute (substitua pelo email usado):

```sql
USE kuntima_bolsas;
UPDATE users SET role = 'admin' WHERE email = 'o-seu-email@exemplo.com';
```

Termine a sessão e entre novamente para actualizar o papel na sessão. O menu Administração aparecerá e poderá gerir estados de candidaturas e pedidos.

## Notas importantes
- Bolsas, instituições, prazos e preços que vêm na base de dados são **dados fictícios de demonstração**. Substitua-os por oportunidades reais e confirme os requisitos/prazos antes de publicar.
- Os pedidos de mentoria não cobram pagamentos: apenas registam um pedido e um preço indicativo. Não existe integração de pagamentos.
- O painel de administração desta versão permite actualizar estados; a criação/edição completa de bolsas e serviços pode ser adicionada numa próxima fase.
- Antes de alojar online, configure HTTPS, backups, políticas de privacidade, recuperação de palavra-passe, limites de tentativas de login e permissões administrativas.

## Contas de teste (perfis)
Na página de entrada escolhe o perfil (Aluno, Mentor ou Administrador). Contas fictícias criadas pela importação de `sql/kuntima_bolsas.sql` (ou só de `sql/utilizadores_teste.sql`, se a base já existir):

| Perfil | Email | Palavra-passe |
|---|---|---|
| Administrador | admin@kuntima.ao | Admin@2026 |
| Mentor | mentor@kuntima.ao | Mentor@2026 |

Para testar como aluno ou mentor novo, usa **Criar conta** e escolhe o tipo de conta. Altera estas palavras-passe antes de publicar o sistema.

## Aprovação de mentores
Quem se regista como mentor fica **a aguardar aprovação** e só consegue entrar depois de um administrador aprovar em *Utilizadores* (filtro "Mentores a aguardar aprovação"). Rejeitar um mentor despublica os serviços dele. Alunos entram logo. Se a base de dados já existia, importe `sql/aprovacao_mentores.sql` (cria a coluna `account_status`; as contas actuais ficam aprovadas).
