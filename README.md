# Task Manager Backend

Um sistema de gerenciamento de tarefas desenvolvido com Laravel, oferecendo uma API RESTful completa para gerenciamento de tarefas com autenticação, cache e políticas de acesso.

## 🚀 Funcionalidades

- **Autenticação Segura**
  - Registro de usuários
  - Login com tokens JWT
  - Revogação de tokens ao fazer logout
  - Cache de tokens para melhor desempenho

- **Gerenciamento de Tarefas**
  - Criação de tarefas com título, descrição, data de vencimento e prioridade
  - Atualização de tarefas existentes
  - Exclusão de tarefas (com soft delete)
  - Marcação de tarefas como concluídas
  - Filtragem por status, prioridade e data
  - Ordenação por diferentes campos
  - Cache para melhor desempenho

- **Notificações por E-mail**
  - Notificações automáticas por e-mail quando tarefas são criadas
  - Sistema de filas para envio assíncrono de e-mails
  - Integração com Mailtrap para ambiente de desenvolvimento

- **Segurança**
  - Validação robusta de dados
  - Políticas de acesso baseadas em usuário
  - Proteção contra ataques de força bruta
  - Tokens com expiração

## 📋 Pré-requisitos

- PHP 8.1 ou superior
- Composer
- MySQL 5.7+ ou PostgreSQL
- Node.js e NPM (para assets frontend)

## 🔧 Instalação

### No macOS

1. **Instale o Homebrew** (se ainda não tiver):
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

2. **Instale o PHP e dependências**:
```bash
brew install php@8.1
brew install composer
brew install mysql
```

3. **Clone o repositório**:
```bash
git clone https://github.com/GuilhermeFerreiraa/task-manager-backend.git
cd task-manager-backend
```

4. **Instale as dependências**:
```bash
composer install
npm install
```

5. **Configure o ambiente**:
```bash
cp .env.example .env
php artisan key:generate
```

6. **Configure o banco de dados**:
- Crie um banco de dados MySQL
- Atualize o arquivo `.env` com suas credenciais:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seu_banco_de_dados
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

7. **Configure as configurações de e-mail**:
- Atualize o arquivo `.env` com suas credenciais do Mailtrap:
```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=task-manager@example.com
MAIL_FROM_NAME="Task Manager API"
```

8. **Configure a fila**:
- Atualize o arquivo `.env` para configurar a fila:
```
QUEUE_CONNECTION=database
```

9. **Execute as migrações**:
```bash
php artisan migrate
```

10. **Inicie o servidor**:
```bash
php artisan serve
```

11. **Inicie o worker da fila** (em um terminal separado):
```bash
php artisan queue:work
```

### No Windows

1. **Instale o XAMPP**:
- Baixe e instale o [XAMPP](https://www.apachefriends.org/pt_br/index.html)
- Inclua PHP e MySQL durante a instalação

2. **Instale o Composer**:
- Baixe e instale o [Composer](https://getcomposer.org/download/)

3. **Instale o Git**:
- Baixe e instale o [Git](https://git-scm.com/download/win)

4. **Clone o repositório**:
```bash
git clone https://github.com/GuilhermeFerreiraa/task-manager-backend.git
cd task-manager-backend
```

5. **Instale as dependências**:
```bash
composer install
npm install
```

6. **Configure o ambiente**:
```bash
copy .env.example .env
php artisan key:generate
```

7. **Configure o banco de dados**:
- Abra o phpMyAdmin (http://localhost/phpmyadmin)
- Crie um novo banco de dados
- Atualize o arquivo `.env` com suas credenciais:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seu_banco_de_dados
DB_USERNAME=root
DB_PASSWORD=
```

8. **Configure as configurações de e-mail**:
- Atualize o arquivo `.env` com suas credenciais do Mailtrap:
```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=task-manager@example.com
MAIL_FROM_NAME="Task Manager API"
```

9. **Configure a fila**:
- Atualize o arquivo `.env` para configurar a fila:
```
QUEUE_CONNECTION=database
```

10. **Execute as migrações**:
```bash
php artisan migrate
```

11. **Inicie o servidor**:
```bash
php artisan serve
```

12. **Inicie o worker da fila** (em um terminal separado):
```bash
php artisan queue:work
```

## 📚 Documentação da API

### Autenticação

#### Registro
```http
POST /api/v1/register
Content-Type: application/json

{
    "name": "Nome do Usuário",
    "email": "email@exemplo.com",
    "password": "Senha@123"
}
```

#### Login
```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "email@exemplo.com",
    "password": "Senha@123"
}
```

### Tarefas

#### Listar Tarefas
```http
GET /api/v1/tasks
Authorization: Bearer {token}
```

Parâmetros opcionais:
- `status`: Filtrar por status (PENDING/COMPLETED)
- `priority`: Filtrar por prioridade (LOW/MEDIUM/HIGH)
- `due_date`: Filtrar por data

#### Criar Tarefa
```http
POST /api/v1/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Título da Tarefa",
    "description": "Descrição da tarefa",
    "due_date": "2024-12-31",
    "priority": "HIGH",
    "status": "PENDING"
}
```

#### Atualizar Tarefa
```http
PUT /api/v1/tasks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Novo Título",
    "description": "Nova descrição",
    "due_date": "2024-12-31",
    "priority": "MEDIUM",
    "status": "COMPLETED"
}
```

#### Excluir Tarefa
```http
DELETE /api/v1/tasks/{id}
Authorization: Bearer {token}
```

#### Marcar como Concluída
```http
PATCH /api/v1/tasks/{id}/complete
Authorization: Bearer {token}
```

#### Listar Tarefas Atrasadas
```http
GET /api/v1/tasks/overdue
Authorization: Bearer {token}
```

#### Listar Tarefas de Alta Prioridade
```http
GET /api/v1/tasks/high-priority
Authorization: Bearer {token}
```

## 📧 Notificações por E-mail

O sistema envia notificações por e-mail quando:
- Uma nova tarefa é criada

Para testar as notificações por e-mail:
```bash
php artisan email:test
```

## ⚙️ Sistema de Filas

O sistema utiliza o sistema de filas do Laravel para enviar e-mails de forma assíncrona. Para processar a fila:
```bash
php artisan queue:work
```

Você também pode usar o comando personalizado para processar apenas as filas de e-mail:
```bash
php artisan queue:process-emails
```

## 🔒 Segurança

- Todas as rotas (exceto login/registro) requerem autenticação
- Senhas são hasheadas usando bcrypt
- Tokens JWT com expiração de 1 hora
- Cache de tokens para melhor desempenho
- Proteção contra ataques de força bruta
- Validação de dados em todas as requisições
- Políticas de acesso baseadas em usuário

## 🛠️ Tecnologias Utilizadas

- Laravel 10
- PHP 8.1+
- MySQL/PostgreSQL
- JWT Authentication
- Laravel Queue
- Laravel Mail
- Laravel Cache
- Laravel Policies
- Laravel Soft Deletes

## 📝 Licença

Este projeto está licenciado sob a Licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👥 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua funcionalidade (`git checkout -b feature/nova-funcionalidade`)
3. Faça commit de suas alterações (`git commit -m 'feat: adiciona nova funcionalidade'`)
4. Faça push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## 📧 Contato

Guilherme Ferreira - gui.2001@hotmail.com

Link do Projeto: [https://github.com/GuilhermeFerreiraa/task-manager-backend](https://github.com/GuilhermeFerreiraa/task-manager-backend)
