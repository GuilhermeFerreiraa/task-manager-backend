# Task Manager Backend

Um sistema de gerenciamento de tarefas desenvolvido com Laravel, oferecendo uma API RESTful completa para gerenciamento de tarefas com autenticação, cache e políticas de acesso.

## 🚀 Funcionalidades

- **Autenticação Segura**
  - Registro de usuários
  - Login com tokens JWT
  - Logout com revogação de tokens
  - Cache de tokens para melhor performance

- **Gerenciamento de Tarefas**
  - Criação de tarefas com título, descrição, data de vencimento e prioridade
  - Atualização de tarefas existentes
  - Exclusão de tarefas (com soft delete)
  - Marcação de tarefas como concluídas
  - Filtros por status, prioridade e data
  - Ordenação por diferentes campos
  - Cache para melhor performance

- **Segurança**
  - Validação robusta de dados
  - Políticas de acesso por usuário
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
DB_DATABASE=nome_do_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

7. **Execute as migrações**:
```bash
php artisan migrate
```

8. **Inicie o servidor**:
```bash
php artisan serve
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
DB_DATABASE=nome_do_banco
DB_USERNAME=root
DB_PASSWORD=
```

8. **Execute as migrações**:
```bash
php artisan migrate
```

9. **Inicie o servidor**:
```bash
php artisan serve
```

## 📚 Documentação da API

### Autenticação

#### Registro
```http
POST /api/register
Content-Type: application/json

{
    "name": "Nome do Usuário",
    "email": "email@exemplo.com",
    "password": "Senha@123"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "email@exemplo.com",
    "password": "Senha@123"
}
```

### Tarefas

#### Listar Tarefas
```http
GET /api/tasks
Authorization: Bearer {token}
```

Parâmetros opcionais:
- `page`: Número da página (padrão: 1)
- `per_page`: Itens por página (padrão: 10)
- `status`: Filtrar por status (pending/completed)
- `priority`: Filtrar por prioridade (low/medium/high)
- `due_date`: Filtrar por data
- `sort_by`: Campo para ordenação
- `sort_direction`: Direção da ordenação (asc/desc)

#### Criar Tarefa
```http
POST /api/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Título da Tarefa",
    "description": "Descrição da tarefa",
    "due_date": "2024-12-31",
    "priority": "high"
}
```

#### Atualizar Tarefa
```http
PUT /api/tasks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Novo Título",
    "description": "Nova descrição",
    "due_date": "2024-12-31",
    "priority": "medium",
    "status": "completed"
}
```

#### Excluir Tarefa
```http
DELETE /api/tasks/{id}
Authorization: Bearer {token}
```

#### Marcar como Concluída
```http
POST /api/tasks/{id}/complete
Authorization: Bearer {token}
```

#### Listar Tarefas Atrasadas
```http
GET /api/tasks/overdue
Authorization: Bearer {token}
```

#### Listar Tarefas de Alta Prioridade
```http
GET /api/tasks/high-priority
Authorization: Bearer {token}
```

## 🔒 Segurança

- Todas as rotas (exceto login/registro) requerem autenticação
- Senhas são hasheadas usando bcrypt
- Tokens JWT com expiração de 1 hora
- Cache de tokens para melhor performance
- Proteção contra ataques de força bruta
- Validação de dados em todas as requisições
- Políticas de acesso por usuário

## 🛠️ Tecnologias Utilizadas

- Laravel 10
- PHP 8.1+
- MySQL/PostgreSQL
- JWT Authentication
- Laravel Cache
- Laravel Policies
- Laravel Soft Deletes

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👥 Contribuição

1. Faça o fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Faça commit das suas alterações (`git commit -m 'feat: adiciona nova feature'`)
4. Faça push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## 📧 Contato

Guilherme Ferreira - gui.2001@hotmail.com

Link do Projeto: [https://github.com/GuilhermeFerreiraa/task-manager-backend](https://github.com/GuilhermeFerreiraa/task-manager-backend)
